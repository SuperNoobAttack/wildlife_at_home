#ifndef EVENT_TYPE_HEADER
#define EVENT_TYPE_HEADER

#include <stdexcept>
#include <string>
#include <vector>
#include <fstream>

#include <opencv2/core/core.hpp>
#include <opencv2/nonfree/features2d.hpp>

using namespace std;

class EventType {
    std::string id;
    cv::Mat descriptors;
    vector<cv::KeyPoint> keypoints;
    public:
    EventType(const std::string);
    void setId(const std::string);
    void setDescriptors(const cv::Mat);
    void setKeypoints(const vector<cv::KeyPoint> keypoints);
    std::string getId();
    cv::Mat getDescriptors();
    vector<cv::KeyPoint> getKeypoints();

    void addDescriptors(const cv::Mat descriptors);
    void addKeypoints(const vector<cv::KeyPoint> keypoints);
    void read(cv::FileStorage infile) throw(runtime_error);
    void writeDescriptors(cv::FileStorage outfile) throw(runtime_error);
    void writeKeypoints(cv::FileStorage outfile) throw(runtime_error);
    void writeForSVM(ofstream &outfile, string label, bool add_keypoints) throw(runtime_error);
};

#endif
