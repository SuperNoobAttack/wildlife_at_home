#include <vector>

#include <boost/program_options.hpp>
#include <boost/filesystem.hpp>

#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>

#include <EventType.hpp>

using namespace std;
using namespace boost;


string root_dir = "/projects/wildlife/feature_files/";
string output_file = "svm.dat";
vector<string> positive_files;
vector<string> negative_files;
EventType positive_events("positive");
EventType negative_events("negative");

int main(int argc, char **argv) {
    namespace po = program_options;
    namespace fs = filesystem;
    namespace sys = system;

    po::options_description desc("Allowed options");
    desc.add_options()
        ("help,h", "Show help menu")
        ("root,r", po::value<string>(), "Root feature directory")
        ("positive,p", po::value<vector<string> >(), "Tags for positive features")
        ("negative,n", po::value<vector<string> >(), "Tags for negative features")
        ("output,o", po::value<string>(), "Filename for SVM features")
    ;
    po::variables_map vm;
    po::store(po::parse_command_line(argc, argv, desc), vm);
    po::notify(vm);

    if (vm.count("help") || !vm.count("root") || !vm.count("positive")) {
        cout << desc << endl;
        return 1;
    }

    root_dir = vm["root"].as<string>();

    vector<string> positives = vm["positive"].as<vector<string> >();
    //cout << "Positives: " << positives.size() << endl;
    for(int i=0; i < positives.size(); i++) {
        positive_files.push_back(positives[i]);
    }

    if (vm.count("negative")) {
        vector<string> negatives = vm["negative"].as<vector<string> >();
        //cout << "Negaties: " << negatives.size() << endl;
        for(int i=0; i < negatives.size(); i++) {
            negative_files.push_back(negatives[i]);
        }
    } else {
        // Get all file names in directory.
        cout << "[ERROR] No negative names given!" << endl;
    }

    if (vm.count("output")) {
        output_file = vm["output"].as<string>();
    }

    cout << "Root       : '" << root_dir << "'" << endl;
    cout << "Positive   : " << endl;
    for(int i=0; i < positive_files.size(); i++) {
        cout << "\t" << positive_files[i] << endl;
    }
    cout << "Negative   : " << endl;
    for(int i=0; i < negative_files.size(); i++) {
        cout << "\t" << negative_files[i] << endl;
    }
    cout << "Output     : '" << output_file << "'" << endl;

    // Load all positive files.
    for(int i=0; i < positive_files.size(); i++) {
        string filename = root_dir + positive_files[i] + ".desc";
        cv::FileStorage infile(filename, cv::FileStorage::READ);
        cout << "Loading from file: " << filename << endl;
        try {
            positive_events.setId(positive_files[i]); //Set Id to read in correct events.
            positive_events.read(infile);
        } catch (const std::exception &ex) {
            cerr << "main positives: " << ex.what() << endl;
            exit(1);
        }
        infile.release();
    }
    cout << "Positive Size: " << positive_events.getKeypoints().size() << endl;
    

    // Load all negative files.
    for(int i=0; i < negative_files.size(); i++) {
        string filename = root_dir + negative_files[i] + ".desc";
        cv::FileStorage infile(filename, cv::FileStorage::READ);
        try {
            negative_events.setId(negative_files[i]); //Set Id to read in correct events.
            negative_events.read(infile);
        } catch (const std::exception &ex) {
            cerr << "main negatives: " << ex.what() << endl;
            exit(1);
        }
        infile.release();
    }
    cout << "Negative Size: " << negative_events.getKeypoints().size() << endl;

    ofstream outfile;
    outfile.open((output_file).c_str(), ofstream::out);
    positive_events.writeForSVM(outfile, "+1");
    negative_events.writeForSVM(outfile, "-1");
    outfile.close();
}
