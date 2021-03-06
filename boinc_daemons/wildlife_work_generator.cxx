// This file is part of BOINC.
// http://boinc.berkeley.edu
// Copyright (C) 2008 University of California
//
// BOINC is free software; you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation,
// either version 3 of the License, or (at your option) any later version.
//
// BOINC is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with BOINC.  If not, see <http://www.gnu.org/licenses/>.

// sample_work_generator.cpp: an example BOINC work generator.
// This work generator has the following properties
// (you may need to change some or all of these):
//
// - Runs as a daemon, and creates an unbounded supply of work.
//   It attempts to maintain a "cushion" of 100 unsent job instances.
//   (your app may not work this way; e.g. you might create work in batches)
// - Creates work for the application "example_app".
// - Creates a new input file for each job;
//   the file (and the workunit names) contain a timestamp
//   and sequence number, so they're unique.


#include <iostream>
#include <unistd.h>
#include <cstdlib>
#include <string>
#include <cstring>
#include <sstream>
#include <cmath>
#include <fstream>

#include "boinc_db.h"
#include "error_numbers.h"
#include "backend_lib.h"
#include "parse.h"
#include "util.h"
#include "svn_version.h"

#include "sched_config.h"
#include "sched_util.h"
#include "sched_msgs.h"
#include "str_util.h"

#include "mysql.h"

#include "arguments.hxx"
#include "file_io.hxx"

#include "wildlife_surf.hpp"

#define CUSHION 2000
    // maintain at least this many unsent results
#define REPLICATION_FACTOR  1

const char* app_name = NULL;
const char* out_template_file = "wildlife_out.xml";

DB_APP app;
int start_time;
int seqno;

using namespace std;

struct Event {
    EventType *type;
    int start_time;
    int end_time;
};

/**
 *  This wrapper makes for much more informative error messages when doing MYSQL queries
 */
#define mysql_query_check(conn, query) __mysql_check (conn, query, __FILE__, __LINE__)

void __mysql_check(MYSQL *conn, string query, const char *file, const int line) {
    mysql_query(conn, query.c_str());

    if (mysql_errno(conn) != 0) {
        ostringstream ex_msg;
        ex_msg << "ERROR in MySQL query: '" << query.c_str() << "'. Error: " << mysql_errno(conn) << " -- '" << mysql_error(conn) << "'. Thrown on " << file << ":" << line;
        cerr << ex_msg.str() << endl;
        exit(1);
    }
}

/**
 *  The following opens a database connection to the remote database on wildlife.und.edu
 */
MYSQL *wildlife_db_conn = NULL;

void initialize_database() {
    wildlife_db_conn = mysql_init(NULL);

    //shoud get database info from a file
    string db_host, db_name, db_password, db_user;
    ifstream db_info_file("../wildlife_db_info");

    db_info_file >> db_host >> db_name >> db_user >> db_password;
    db_info_file.close();

    cout << "parsed db info:" << endl;
    cout << "\thost: " << db_host << endl;
    cout << "\tname: " << db_name << endl;
    cout << "\tuser: " << db_user << endl;
    cout << "\tpass: " << db_password << endl;

    if (mysql_real_connect(wildlife_db_conn, db_host.c_str(), db_user.c_str(), db_password.c_str(), db_name.c_str(), 0, NULL, 0) == NULL) {
        cerr << "Error connecting to database: " << mysql_errno(wildlife_db_conn) << ", '" << mysql_error(wildlife_db_conn) << "'" << endl;
        exit(1);
    }
}

int time_to_seconds(string time) {
    vector<string> temp;
    istringstream iss(time);
    while(getline(iss, time, ':')) {
        temp.push_back(time);
    }
    int seconds = 0;
    seconds += atoi(temp[0].c_str())*3600;
    seconds += atoi(temp[1].c_str())*60;
    seconds += atoi(temp[2].c_str());
    return seconds;
}

bool config_is_good(string fileName, int duration_s) {
    vector<EventType*> event_types;
    vector<Event*> events;

    int video_start_time;
    string line, species, event_id, start_time, end_time;
    ifstream infile;
    infile.open(fileName.c_str());
    getline(infile, species);
    getline(infile, line);
    video_start_time = time_to_seconds(line.c_str());
    while(getline(infile, event_id, ',')) {
        Event *newEvent = new Event();
        EventType *event_type = NULL;
        for(vector<EventType*>::iterator it = event_types.begin(); it != event_types.end(); ++it) {
            if((*it)->id.compare(event_id) == 0) {
                event_type = *it;
                break;
            }
        }
        if(event_type == NULL) {
            event_type = new EventType();
            event_type->id = event_id;
            event_types.push_back(event_type);
        }
        if(!getline(infile, start_time, ',') || !getline(infile, end_time)) {
            cout << "Error: Malformed config file!" << endl;
            return false;
        }
        newEvent->type = event_type;
        newEvent->start_time = time_to_seconds(start_time);
        newEvent->end_time = time_to_seconds(end_time);
        events.push_back(newEvent);
    }
    infile.close();

    for(int i=0; i<duration_s; i++) {
        int time = video_start_time + i;
        bool has_event = false;
        for(vector<Event*>::iterator it = events.begin(); it != events.end(); ++it) {
            if((*it)->start_time <= time && (*it)->end_time >= time) {
                has_event = true;
                break;
            }
        }
        if(!has_event) {
            int hours = time / 3600;
            time = time % 3600;
            int minutes = time / 60;
            time = time % 60;
            int seconds = time;
            cout << "Config file is invalid at time: " << hours << " : " << minutes << " : " << seconds << endl;
            return false;
        }
    }
    return true;
}

// create one new job
int make_job(int video_id, int species_id, int location_id, string video_address, double duration_s, int filesize, int min_hessian, string md5_hash, string features_file, string tag) {
    DB_WORKUNIT wu;

    char name[256], path[256];
    char command_line[512];
    char additional_xml[512];
    const char* infiles[2];

    cout << "job: " << video_id << ", species: " << species_id << ", location: " << location_id << ", video_address: " << video_address << ", durations_s: " << duration_s << endl;

    /**
     * make a unique name (for the job and its input file)
     */
    sprintf(name, "%s_%d_%lu", tag.c_str(), video_id, time(NULL));
    cout << "\tworkunit name: '" << name << "'" << endl;


    /**
     * TODO: figure out an estimate of how many fpops per second of video
     * The credit right now is rather high.
     */
    double fpops_est = duration_s * (2.5 * 10e8) * 0.75;
    int delay_bound = 86400;        //24 * 60 * 60 == 86400,  this is one day (it is measured in seconds)
    if (0 == strcmp(app_name, "wildlife_surf")) {
        fpops_est *= 40; //SURF seems to run approximately 40 times slower
        delay_bound *= 10;  //this should give a 10 day delay for the surf application
    } else if (0 == strcmp(app_name, "wildlife_surf_collect")) {
        fpops_est *= 40; // TODO: adjust this estimate. Leaving the same as surf for now.
        delay_bound *= 10;  //this should give a 10 day delay for the surf application
    }

    double credit = fpops_est / (2.5 * 10e10);

    /**
     * Fill in the job parameters
     */
    wu.clear();
    wu.appid = app.id;
    strcpy(wu.name, name);
    wu.rsc_fpops_est = fpops_est;
    wu.rsc_fpops_bound = fpops_est * 100;
    wu.rsc_memory_bound = 1e7;
    wu.rsc_disk_bound = 1e8;
    wu.delay_bound = delay_bound;
    wu.min_quorum = REPLICATION_FACTOR;
    wu.target_nresults = REPLICATION_FACTOR;
    wu.max_error_results = REPLICATION_FACTOR*4;
    wu.max_total_results = REPLICATION_FACTOR*8;
    wu.max_success_results = REPLICATION_FACTOR*4;

    /**
     *  The surf application uses two files (the feature file and the video file),
     *  while the motion detection application only uses the video file.
     */
    int n_files = 1;
    string config_filename, feats_filename, video_filename;

    if (0 == strcmp(app_name, "wildlife_surf")) {
        copy_file_to_download_dir(features_file);
        feats_filename = features_file.substr(features_file.find_last_of('/') + 1);
        infiles[0] = feats_filename.c_str();

        video_filename = video_address.substr(video_address.find_last_of("/") + 1, (video_address.length() - video_address.find_last_of("/") + 1));
        infiles[1] = video_filename.c_str();

        cout << "\tinfile[0]: " << infiles[0] << endl;
        cout << "\tinfile[1]: " << infiles[1] << endl;
        n_files = 2;

        sprintf(command_line, " video.mp4 input.feats");
    } else if (0 == strcmp(app_name, "wildlife_surf_collect")) {
        video_filename = video_address.substr(video_address.find_last_of("/") + 1, (video_address.length() - video_address.find_last_of("/") + 1));
        infiles[1] = video_filename.c_str();

        config_filename = video_filename + "." + name + ".config";
        ofstream config_file(config_filename.c_str());

        ostringstream video_species_query;
        video_species_query << "SELECT name"
            << " FROM species s"
            << " JOIN video_2 v"
            << " ON s.id = v.species_id"
            << " WHERE v.id = "
            << video_id;
        mysql_query_check(wildlife_db_conn, video_species_query.str());
        MYSQL_RES *species_result = mysql_store_result(wildlife_db_conn);

        MYSQL_ROW species_row;
        string video_species = "";
        while ((species_row = mysql_fetch_row(species_result)) != NULL) {
            video_species = species_row[0];
        }
        config_file << video_species << endl;
        cout << "Video Species: " << video_species << endl;

        mysql_free_result(species_result);

        ostringstream video_start_query;
        video_start_query << "SELECT start_time"
            << " FROM video_2"
            << " WHERE id = "
            << video_id
            << " LIMIT 1";
        mysql_query_check(wildlife_db_conn, video_start_query.str());
        MYSQL_RES *start_result = mysql_store_result(wildlife_db_conn);

        MYSQL_ROW video_row;
        string video_start_time = "";
        while ((video_row = mysql_fetch_row(start_result)) != NULL) {
            video_start_time = video_row[0];
            video_start_time = video_start_time.substr(video_start_time.find(' ')+1);
            config_file << video_start_time << endl;
        }
        cout << "Video Start Time: " << video_start_time << endl;

        mysql_free_result(start_result);

        ostringstream expert_event_query;
        expert_event_query << "SELECT event_type, start_time, end_time"
            << " FROM expert_observations"
            << " WHERE video_id = "
            << video_id;
        mysql_query_check(wildlife_db_conn, expert_event_query.str());
        MYSQL_RES *expert_result = mysql_store_result(wildlife_db_conn);

        MYSQL_ROW expert_row;
        while ((expert_row = mysql_fetch_row(expert_result)) != NULL) {
            string event_type = expert_row[0];
            string start_time = expert_row[1];
            string end_time = expert_row[2];
            cout << "Event: " << event_type << " " << start_time << " " << end_time << endl;
            config_file << event_type << "," <<  start_time << "," <<  end_time << endl;
        }

        mysql_free_result(expert_result);

        config_file.close();
        if (config_is_good(config_filename, duration_s)) {
            cout << "Copy file to download dir: '" << config_filename << "'" << endl;
            copy_file_to_download_dir(config_filename);
            cout << "Delete file: '" << config_filename << "'" << endl;
            remove(config_filename.c_str()); // delete the config file from the local directory.
        } else {
            remove(config_filename.c_str());
            cout << "Error: Corrupt config file!" << endl;
            return -1;
        }
        config_filename = config_filename.substr(config_filename.find_last_of('/') + 1);
        infiles[0] = config_filename.c_str();

        cout << "\tinfile[0]: " << infiles[0] << endl;
        cout << "\tinfile[1]: " << infiles[1] << endl;
        n_files = 2;

        sprintf(command_line, " -c input.config -v video.mp4 -h %d", min_hessian);
    } else {
        video_filename = video_address.substr(video_address.find_last_of("/") + 1, (video_address.length() - video_address.find_last_of("/") + 1));
        infiles[0] = video_filename.c_str();

        cout << "\tinfile[0]: " << infiles[0] << endl;
        n_files = 1;

        sprintf(command_line, " video.mp4");
    }

    sprintf(path, "templates/%s", out_template_file);

    cout << "\tpath: '" << path << "'" << endl;
    cout << "\tcommand line: '" << command_line << "'" << endl;

    /**
     *  Add the credit to the workunit xml so we can run the validator with the
     *  --credit_from_wu flag, to use the fixed credit calculated here.
     *
     *  Also put a tag for the set of workunits so we can analyze them later.
     */
    sprintf(additional_xml, "<credit>%.3lf</credit><tag>%s</tag>", credit, tag.c_str());
    cout << "\tcredit: " << credit << endl;
    cout << "\tadditional_xml: " << additional_xml << endl;

    /**
     * We actually need to automatically generate the in template file because the input files are going to
     * be received from a URL.
     */

    ostringstream input_template_stream;
    if (0 == strcmp(app_name, "wildlife_bgsub")) {
        input_template_stream
            << "<file_info>" << endl
            << "    <number>0</number>" << endl
            << "    <url>http://wildlife.und.edu" << video_address.substr(0, video_address.find_last_of("/") + 1)<< "</url>" << endl
            << "    <nbytes>" << filesize << "</nbytes>" << endl
            << "    <md5_cksum>" << md5_hash << "</md5_cksum>" << endl
            << "</file_info>" << endl
            << "<workunit>" << endl
            << "    <file_ref>" << endl
            << "        <file_number>0</file_number>" << endl
            << "        <open_name>video.mp4</open_name>" << endl
            << "    </file_ref>" << endl;
    } else if (0 == strcmp(app_name, "wildlife_surf")) {
        input_template_stream
            << "<file_info>" << endl
            << "    <number>0</number>" << endl
            << "</file_info>" << endl
            << "<file_info>" << endl
            << "    <number>1</number>" << endl
            << "    <url>http://wildlife.und.edu" << video_address.substr(0, video_address.find_last_of("/") + 1)<< "</url>" << endl
            << "    <nbytes>" << filesize << "</nbytes>" << endl
            << "    <md5_cksum>" << md5_hash << "</md5_cksum>" << endl
            << "</file_info>" << endl
            << "<workunit>" << endl
            << "    <file_ref>" << endl
            << "        <file_number>0</file_number>" << endl
            << "        <open_name>input.feats</open_name>" << endl
            << "    </file_ref>" << endl
            << "    <file_ref>" << endl
            << "        <file_number>1</file_number>" << endl
            << "        <open_name>video.mp4</open_name>" << endl
            << "    </file_ref>" << endl;
    } else if (0 == strcmp(app_name, "wildlife_surf_collect")) {
        input_template_stream
            << "<file_info>" << endl
            << "    <number>0</number>" << endl
            << "</file_info>" << endl
            << "<file_info>" << endl
            << "    <number>1</number>" << endl
            << "    <url>http://wildlife.und.edu" << video_address.substr(0, video_address.find_last_of("/") + 1)<< "</url>" << endl
            << "    <nbytes>" << filesize << "</nbytes>" << endl
            << "    <md5_cksum>" << md5_hash << "</md5_cksum>" << endl
            << "</file_info>" << endl
            << "<workunit>" << endl
            << "    <file_ref>" << endl
            << "        <file_number>0</file_number>" << endl
            << "        <open_name>input.config</open_name>" << endl
            << "    </file_ref>" << endl
            << "    <file_ref>" << endl
            << "        <file_number>1</file_number>" << endl
            << "        <open_name>video.mp4</open_name>" << endl
            << "    </file_ref>" << endl;
    } else {
        input_template_stream
            << "<file_info>" << endl
            << "    <number>0</number>" << endl
            << "    <url>http://wildlife.und.edu" << video_address.substr(0, video_address.find_last_of("/") + 1)<< "</url>" << endl
            << "    <nbytes>" << filesize << "</nbytes>" << endl
            << "    <md5_cksum>" << md5_hash << "</md5_cksum>" << endl
            << "</file_info>" << endl
            << "<workunit>" << endl
            << "    <file_ref>" << endl
            << "        <file_number>0</file_number>" << endl
            << "        <open_name>video.mp4</open_name>" << endl
            << "    </file_ref>" << endl;
    }

    input_template_stream
            << "    <rsc_memory_bound>" << wu.rsc_memory_bound << "</rsc_memory_bound>" << endl
            << "    <delay_bound>" << wu.delay_bound << "</delay_bound>" << endl
            << "    <max_error_results>5</max_error_results>" << endl
            << "    <min_quorum>2</min_quorum>" << endl
            << "    <target_nresults>2</target_nresults>" << endl
            << "    <max_total_results>7</max_total_results>" << endl
            << "    <max_success_results>2</max_success_results>" << endl
            << "</workunit>";

    cout << "input template:" << endl << input_template_stream.str() << endl << endl;


    cout << "\tinfiles[0]: " << infiles[0] << endl;
    if (n_files > 1) {
        cout << "\tinfiles[1]: " << infiles[1] << endl;
    }

//    exit(0);

    // TODO: Update the video processing_status to 'BGSUB_PROCESSING'.

    /**
     * Register the job with BOINC
     */
    return create_work(
            wu,
            input_template_stream.str().c_str(),
            path,
            config.project_path(path),
            infiles,
            n_files,
            config,
            command_line,
            additional_xml
            );
}


void main_loop(const vector<string> &arguments) {
    int unsent_results;
    int retval;
    long total_generated = 0;
    long total_errors = 0;

    int species_id = 0;
    int location_id = 0;
    int number_jobs = 100;  //jobs to generate when under the cushion
    int min_hessian = 500;

    if (!get_argument(arguments, "--species_id", false, species_id)) {
        cout << "generating workunits for all species." << endl;
    } else {
        cout << "generating workunits for species " << species_id << endl;
    }

    if (!get_argument(arguments, "--location_id", false, location_id)) {
        cout << "generating workunits for all locations." << endl;
    } else {
        cout << "generating workunits for locations " << species_id << endl;
    }

    if (!get_argument(arguments, "--number_jobs", false, number_jobs)) {
        cout << "generating a max of " << number_jobs << " workunits, to run for all videos set number_jobs <= 0." << endl;
    } else {
        if (number_jobs <= 0) {
            cout << "generating workunits for all videos.." << endl;
        } else {
            cout << "generating a max of " << number_jobs << " workunits, to run for all videos set number_jobs <= 0." << endl;
        }
    }

    if (0 == strcmp(app_name, "wildlife_surf_collect")) {
        if(!get_argument(arguments, "--min_hessian", false, min_hessian)) {
            min_hessian = 500;
        }
        cout << "Generateing jobs with hessian value of " << min_hessian << "." << endl;
    }

    string tag;
    get_argument(arguments, "--tag", true, tag);

    string features_file;
    if (0 == strcmp(app_name, "wildlife_surf")) {
        get_argument(arguments, "--features_file", true, features_file);
    }

    initialize_database();

    //This checks to see if there is a stop in place, if there is it will exit the work generator.
    check_stop_daemons();

    //Aaron Comment: retval tells us if the count_unsent_results
    //function is working properly. If it is, then it's value
    //should be 0. Anything creater than 0 and the program exits.
    retval = count_unsent_results(unsent_results, app.id);

    cout << "got unsent results: " << unsent_results << endl;

    if (retval) {
        log_messages.printf(MSG_CRITICAL,"count_unsent_jobs() failed: %s\n", boincerror(retval));
        exit(retval);
    }

    log_messages.printf(MSG_DEBUG, "%d results are available, with a cushion of %d\n", unsent_results, CUSHION);

    /*
    retval = count_unsent_results(unsent_results, 0);
    if (retval) {
        log_messages.printf(MSG_CRITICAL, "count_unsent_jobs() failed: %s\n", boincerror(retval) );
        exit(retval);
    }
    */

    /**
     *  Get a set of videos (which haven't been sent out as workunits yet) from the database. We'll need
     *  their video id (for tracking), duration in seconds (for calculating credits), and the video file's
     *  address on wildlife.und.edu
     */
    ostringstream unclassified_video_query, finished_expert_query;
    finished_expert_query << "SELECT DISTINCT id, watermarked_filename, duration_s, species_id, location_id, size, md5_hash"
        << " FROM video_2"
        << " WHERE processing_status != 'UNWATERMARKED'"
        << " AND processing_status != 'WATERMARKING'"
        << " AND md5_hash IS NOT NULL"
        << " AND size IS NOT NULL"
        << " AND expert_finished = 'FINISHED'";
    unclassified_video_query << "SELECT id, watermarked_filename, duration_s, species_id, location_id, size, md5_hash"
        << " FROM video_2"
        << " WHERE processing_status != 'UNWATERMARKED'"
        << " AND processing_status != 'WATERMARKING'"
        << " AND md5_hash IS NOT NULL"
        << " AND size IS NOT NULL";

    // SELECT DISTINCT v.id, watermarked_filename, duration_s, species_id, location_id, size, md5_hash FROM video_2 AS v
    // JOIN expert_observations AS o ON v.id = o.video_id WHERE processing_status != 'UNWATERMARKED' AND md5_hash IS NOT NULL AND size IS NOT NULL AND expert_finished = 'FINISHED';
    /**
     *  If the species is specified the videos will be limited to that species,
     *  otherwise it will generate videos for all species
     */
    if (species_id > 0) {
        finished_expert_query << " AND species_id = " << species_id;
        unclassified_video_query << " AND species_id = " << species_id;
    }

    /**
     *  If the location is specified the videos will be limited to that species,
     *  otherwise it will generate videos for all species
     */
    if (location_id > 0) {
        finished_expert_query << " AND location_id = " << location_id;
        unclassified_video_query << " AND location_id = " << location_id;
    }

    /**
     *  If the number of jobs is set to 0 it will generate a job for every video, otherwise it
     *  generates a number as specified.
     */
    if (number_jobs > 0) {
        finished_expert_query << " LIMIT " << number_jobs;
        unclassified_video_query << " LIMIT " << number_jobs;
    }

    cout << "query built..." << endl;

    if (0 == strcmp(app_name, "wildlife_surf_collect"))  {
        mysql_query_check(wildlife_db_conn, finished_expert_query.str());
    } else {
        mysql_query_check(wildlife_db_conn, unclassified_video_query.str());
    }
    MYSQL_RES *video_result = mysql_store_result(wildlife_db_conn);

    if (video_result == NULL) {
        log_messages.printf(MSG_CRITICAL,"SQL Results was NULL\n");
    }

    cout << "query made..." << endl;
    MYSQL_ROW video_row;
    while ((video_row = mysql_fetch_row(video_result))) {
        cout << "pulled first results..." << endl;
        int video_id = atoi(video_row[0]);
        string video_address = video_row[1];
        video_address += ".mp4";
        double duration_s = atof(video_row[2]);
        int species_id = atoi(video_row[3]);
        int location_id = atoi(video_row[4]);
        int filesize = atoi(video_row[5]);
        string md5_hash = video_row[6];
        cout << "loaded query resuls..." << endl;

        cout << "generating work unit" << endl << endl;
        int job_id = make_job(video_id, species_id, location_id, video_address, duration_s, filesize, min_hessian, md5_hash, features_file, tag); 
        if (job_id == -1) {
            total_errors++;
        } else {
            total_generated++;
        }
        cout << "generated " << total_generated << " workunits. " << endl << endl;
        cout << "generated " << total_errors << " errored workunits. " << endl << endl;
    }

    cout << "done getting results..." << endl;
    mysql_free_result(video_result);

    log_messages.printf(MSG_DEBUG, "workunits generated: %lu\n", total_generated);
}


void usage(char *name) {
    fprintf(stderr, "This is an example BOINC work generator.\n"
        "This work generator has the following properties\n"
        "(you may need to change some or all of these):\n"
        "  It attempts to maintain a \"cushion\" of 100 unsent job instances.\n"
        "  (your app may not work this way; e.g. you might create work in batches)\n"
        "- Creates work for the application \"example_app\".\n"
        "- Creates a new input file for each job;\n"
        "  the file (and the workunit names) contain a timestamp\n"
        "  and sequence number, so that they're unique.\n\n"
        "Usage: %s [OPTION]...\n\n"
        "Options:\n"
        "  --app X                      Application name (default: example_app)\n"
        "  --out_template_file          Output template (default: example_app_out)\n"
        "  [ -d X ]                     Sets debug level to X.\n"
        "  [ -h | --help ]              Shows this help text.\n"
        "  [ -v | --version ]           Shows version information.\n",
        name
    );
}

int main(int argc, char** argv) {
    int i, retval;
    char buf[256];

//Aaron Comment: command line flags are explained in descriptions above.
    for (i=1; i<argc; i++) {
        if (is_arg(argv[i], "d")) {
            if (!argv[++i]) {
                log_messages.printf(MSG_CRITICAL, "%s requires an argument\n\n", argv[--i]);
                usage(argv[0]);
                exit(1);
            }
            int dl = atoi(argv[i]);
            log_messages.set_debug_level(dl);
            if (dl == 4) g_print_queries = true;
        } else if (!strcmp(argv[i], "--app")) {
            app_name = argv[++i];
        } else if (!strcmp(argv[i], "--out_template_file")) {
            out_template_file = argv[++i];
        } else if (is_arg(argv[i], "h") || is_arg(argv[i], "help")) {
            usage(argv[0]);
            exit(0);
        } else if (is_arg(argv[i], "v") || is_arg(argv[i], "version")) {
            printf("%s\n", SVN_VERSION);
            exit(0);

        } else {
//            log_messages.printf(MSG_CRITICAL, "unknown command line argument: %s\n\n", argv[i]);
//            usage(argv[0]);
//            exit(1);
        }
    }

    if (app_name == NULL) {
        cout << "'--app' not specified" << endl;
    }

    cout << "parsed arugments" << endl;

//Aaron Comment: if at any time the retval value is greater than 0, then the program
//has failed in some manner, and the program then exits.

//Aaron Comment: processing project's config file.
    retval = config.parse_file();
    if (retval) {
        log_messages.printf(MSG_CRITICAL,
            "Can't parse config.xml: %s\n", boincerror(retval)
        );
        exit(1);
    }

    cout << "parsed config file " << endl;

//Aaron Comment: opening connection to database.
    retval = boinc_db.open(
        config.db_name, config.db_host, config.db_user, config.db_passwd
    );
    if (retval) {
        log_messages.printf(MSG_CRITICAL, "can't open db\n");
        exit(1);
    }

    cout << "opened the db" << endl;

//Aaron Comment: looks for applicaiton to be run. If not found, program exits.

    cout << "name: " << app_name << endl;
    sprintf(buf, "where name='%s'", app_name);
    if (app.lookup(buf)) {
        log_messages.printf(MSG_CRITICAL, "can't find app %s\n", app_name);
        exit(1);
    }

    cout << "looked up the name: " << app_name << endl;

    //Aaron Comment: if work generator passes all startup tests, the main work gneration
    //loop is called.
    start_time = time(0);
    seqno = 0;

    log_messages.printf(MSG_NORMAL, "Starting\n");

    main_loop(vector<string>(argv, argv + argc));
}
