
import React from 'react';
import { Button } from '../ui/button';
import { useExam } from '../hooks/useExam';
import { Card } from '../ui/card';
import { Separator } from '../ui/separator';
import { ArrowRight, Clock, ListChecks, Shield, Camera, ScanFace, Eye } from 'lucide-react';
import CameraFeed from '../component/CameraFeed';
import { Switch } from '../ui/switch';

const Index = () => {
    const {
      exam,
      startExam,
      isCameraActive,
      toggleCamera,
      isCameraVerified,
      setIsCameraVerified,
      setIsCameraRequired
    } = useExam();

    const [cameraRequired, setCameraRequired] = React.useState(false);

    const handleFaceDetected = (detected) => {
      setIsCameraVerified(detected);
    };

    const toggleCameraRequired = () => {
      const newValue = !cameraRequired;
      setCameraRequired(newValue);
      setIsCameraRequired(newValue);
    };

    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-gradient-to-b from-background to-muted/30 p-6">
        <div className="w-full max-w-4xl animate-fade-in">
          <div className="glass-panel p-8 md:p-10">
            <div className="flex flex-col items-center text-center mb-8">
              <div className="inline-flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 text-primary mb-4">
                <ListChecks className="h-6 w-6" />
              </div>
              <h1 className="text-3xl md:text-4xl font-semibold tracking-tight mb-2">
                {exam?.title || "Loading exam..."}
              </h1>
              <p className="text-muted-foreground max-w-xl">
                Welcome to your online examination. Please read the instructions carefully before proceeding.
              </p>
            </div>

            <Separator className="mb-8" />

            <div className="grid md:grid-cols-3 gap-6 mb-8">
              <Card className="p-4 flex flex-col items-center text-center">
                <div className="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3">
                  <Clock className="h-5 w-5" />
                </div>
                <h3 className="font-medium mb-1">Time Limit</h3>
                <p className="text-sm text-muted-foreground">{exam?.timeLimit || "--"} minutes</p>
              </Card>

              <Card className="p-4 flex flex-col items-center text-center">
                <div className="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3">
                  <ListChecks className="h-5 w-5" />
                </div>
                <h3 className="font-medium mb-1">Questions</h3>
                <p className="text-sm text-muted-foreground">{exam?.questions?.length || "--"} multiple choice</p>
              </Card>

              <Card className="p-4 flex flex-col items-center text-center">
                <div className="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3">
                  <Shield className="h-5 w-5" />
                </div>
                <h3 className="font-medium mb-1">Passing Score</h3>
                <p className="text-sm text-muted-foreground">{exam?.passingScore || "--"}% or higher</p>
              </Card>
            </div>

            <div className="bg-muted/50 rounded-lg p-4 mb-8">
              <h3 className="font-medium mb-2">Instructions:</h3>
              <ul className="space-y-2 text-sm text-muted-foreground">
                <li>• You have {exam?.timeLimit || "--"} minutes to complete the exam.</li>
                <li>• All questions are multiple choice with only one correct answer.</li>
                <li>• You can navigate between questions using the navigation buttons.</li>
                <li>• Your answers are saved automatically; you can revisit questions.</li>
                <li>• The camera feature is optional for proctoring purposes.</li>
                <li>• If you refresh the page, your progress and time will be preserved.</li>
                <li>• Click "Start Exam" when you're ready to begin.</li>
              </ul>
            </div>

            <div className="flex items-center justify-between p-4 bg-muted/30 rounded-lg mb-6">
              <div className="flex items-center">
                <Eye className="h-5 w-5 mr-2 text-primary" />
                <span className="font-medium">Enable Camera Proctoring</span>
              </div>
              <Switch
                checked={cameraRequired}
                onCheckedChange={toggleCameraRequired}
                aria-label="Toggle camera requirement"
              />
            </div>

            <div className="grid md:grid-cols-2 gap-6 mb-8">
              <div>
                <Card className={`h-full flex flex-col ${!cameraRequired ? 'opacity-50' : ''}`}>
                  <div className="p-4 border-b border-border">
                    <h3 className="font-medium">Camera Verification</h3>
                    <p className="text-sm text-muted-foreground">
                      {cameraRequired
                        ? "Please enable your camera and position your face in the frame"
                        : "Camera verification is disabled"}
                    </p>
                  </div>
                  <div className="flex-1 p-4">
                    <div className="h-[300px]">
                      <CameraFeed
                        isActive={isCameraActive && cameraRequired}
                        onToggle={cameraRequired ? toggleCamera : null}
                        onFaceDetected={handleFaceDetected}
                        verificationMode={true}
                        disabled={!cameraRequired}
                      />
                    </div>
                  </div>
                  <div className="p-4 bg-muted/30 border-t border-border">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center">
                        {isCameraActive && cameraRequired ? (
                          <Camera className="h-4 w-4 text-primary mr-2" />
                        ) : (
                          <Camera className="h-4 w-4 text-muted-foreground mr-2" />
                        )}
                        <span className="text-sm font-medium">Camera</span>
                      </div>
                      <div className={`px-2 py-1 rounded-full text-xs ${isCameraActive && cameraRequired ? 'bg-green-500/10 text-green-500' : 'bg-muted text-muted-foreground'}`}>
                        {!cameraRequired ? 'Disabled' : (isCameraActive ? 'Enabled' : 'Disabled')}
                      </div>
                    </div>

                    <div className="flex items-center justify-between mt-2">
                      <div className="flex items-center">
                        {isCameraVerified && cameraRequired ? (
                          <ScanFace className="h-4 w-4 text-primary mr-2" />
                        ) : (
                          <ScanFace className="h-4 w-4 text-muted-foreground mr-2" />
                        )}
                        <span className="text-sm font-medium">Face Detection</span>
                      </div>
                      <div className={`px-2 py-1 rounded-full text-xs ${isCameraVerified && cameraRequired ? 'bg-green-500/10 text-green-500' : 'bg-muted text-muted-foreground'}`}>
                        {!cameraRequired ? 'Disabled' : (isCameraVerified ? 'Verified' : 'Not Detected')}
                      </div>
                    </div>
                  </div>
                </Card>
              </div>

              <div>
                <Card className="h-full p-6 flex flex-col justify-center items-center text-center">
                  <div className="mb-6">
                    <div className="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary/10 text-primary mb-4">
                      <ArrowRight className="h-8 w-8" />
                    </div>
                    <h3 className="text-xl font-medium mb-2">Ready to Begin?</h3>
                    <p className="text-sm text-muted-foreground mb-6">
                      {cameraRequired
                        ? "Once you enable your camera and verify your face, you can start the exam."
                        : "You can start the exam immediately since camera verification is disabled."}
                    </p>
                  </div>

                  <Button
                    onClick={startExam}
                    size="lg"
                    className="w-full"
                    disabled={cameraRequired && (!isCameraActive || !isCameraVerified)}
                  >
                    {cameraRequired && !isCameraActive ? (
                      "Enable Camera First"
                    ) : cameraRequired && !isCameraVerified ? (
                      "Position Your Face"
                    ) : (
                      <>
                        Start Exam
                        <ArrowRight className="ml-2 h-4 w-4" />
                      </>
                    )}
                  </Button>

                  {cameraRequired && !isCameraActive && (
                    <p className="text-xs text-muted-foreground mt-2">
                      You must enable your camera before starting
                    </p>
                  )}

                  {cameraRequired && isCameraActive && !isCameraVerified && (
                    <p className="text-xs text-muted-foreground mt-2">
                      Position your face clearly in the frame
                    </p>
                  )}
                </Card>
              </div>
            </div>
          </div>

          <div className="mt-4 text-center text-xs text-muted-foreground">
            © {new Date().getFullYear()} CBT Examination System. All rights reserved.
          </div>
        </div>
      </div>
    );
  };

  export default Index;
