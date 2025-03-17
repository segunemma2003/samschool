
import React, { useRef, useEffect, useState } from 'react';
import { toast } from "../hooks/use-toast";
import { Button } from '../ui/button';
import { Camera, CameraOff, ScanFace } from 'lucide-react';

const CameraFeed = ({
  isActive,
  onToggle,
  onFaceDetected,
  verificationMode = false
}) => {
  const videoRef = useRef(null);
  const streamRef = useRef(null);
  const [isLoading, setIsLoading] = useState(false);
  const [hasError, setHasError] = useState(false);
  const [faceDetected, setFaceDetected] = useState(false);
  const faceDetectionIntervalRef = useRef(null);

  // Start/stop camera based on isActive prop
  useEffect(() => {
    if (isActive) {
      startCamera();
    } else {
      stopCamera();
      setFaceDetected(false);
      if (onFaceDetected) onFaceDetected(false);
    }

    return () => {
      stopCamera();
      if (faceDetectionIntervalRef.current) {
        window.clearInterval(faceDetectionIntervalRef.current);
        faceDetectionIntervalRef.current = null;
      }
    };
  }, [isActive, onFaceDetected]);

  const startCamera = async () => {
    setIsLoading(true);
    setHasError(false);
    setFaceDetected(false);

    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: {
          width: { ideal: 1280 },
          height: { ideal: 720 },
          facingMode: "user"
        },
        audio: false
      });

      streamRef.current = stream;

      if (videoRef.current) {
        videoRef.current.srcObject = stream;

        // Start face detection after camera is initialized
        if (verificationMode) {
          videoRef.current.onloadedmetadata = () => {
            startFaceDetection();
          };
        }
      }
    } catch (error) {
      console.error('Error accessing camera:', error);
      setHasError(true);
      toast({
        title: "Camera Error",
        description: "Unable to access your camera. Please check your permissions.",
        variant: "destructive"
      });
    } finally {
      setIsLoading(false);
    }
  };

  const stopCamera = () => {
    if (faceDetectionIntervalRef.current) {
      window.clearInterval(faceDetectionIntervalRef.current);
      faceDetectionIntervalRef.current = null;
    }

    if (streamRef.current) {
      streamRef.current.getTracks().forEach(track => track.stop());
      streamRef.current = null;
    }

    if (videoRef.current) {
      videoRef.current.srcObject = null;
    }
  };

  const startFaceDetection = async () => {
    try {
      // Simple face detection using canvas and analyzing pixel data
      // This is a basic implementation - in a production app you'd use a proper face detection API
      const checkForFace = () => {
        if (!videoRef.current || !streamRef.current) return;

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        if (!context) return;

        canvas.width = videoRef.current.videoWidth;
        canvas.height = videoRef.current.videoHeight;

        // Draw the current video frame to the canvas
        context.drawImage(videoRef.current, 0, 0, canvas.width, canvas.height);

        // Get the image data
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;

        // Simple algorithm to detect significant variation in pixel values
        // which might indicate a face is present
        let totalVariation = 0;
        const sampleSize = 1000; // Sample a subset of pixels for performance
        const samples = [];

        for (let i = 0; i < sampleSize; i++) {
          // Get random pixels from the middle area of the frame (where the face is likely to be)
          const x = Math.floor(canvas.width * 0.25 + Math.random() * canvas.width * 0.5);
          const y = Math.floor(canvas.height * 0.25 + Math.random() * canvas.height * 0.5);
          const pixelIndex = (y * canvas.width + x) * 4;

          // Calculate color intensity
          const r = data[pixelIndex];
          const g = data[pixelIndex + 1];
          const b = data[pixelIndex + 2];
          const intensity = (r + g + b) / 3;

          samples.push(intensity);
        }

        // Calculate standard deviation of sample intensities
        const mean = samples.reduce((sum, val) => sum + val, 0) / samples.length;
        const variance = samples.reduce((sum, val) => sum + Math.pow(val - mean, 2), 0) / samples.length;
        const stdDev = Math.sqrt(variance);

        // If stdDev is above threshold, assume there's a face (or at least something interesting)
        const isFaceDetected = stdDev > 25; // Adjust threshold as needed

        if (isFaceDetected !== faceDetected) {
          setFaceDetected(isFaceDetected);
          if (onFaceDetected) onFaceDetected(isFaceDetected);
        }
      };

      // Run face detection every 500ms
      faceDetectionIntervalRef.current = window.setInterval(checkForFace, 500);
    } catch (error) {
      console.error('Error during face detection:', error);
    }
  };

  return (
    <div className="glass-panel overflow-hidden flex flex-col h-full">
      <div className="p-4 border-b border-border flex justify-between items-center">
        <h3 className="text-sm font-semibold">
          {verificationMode ? "Camera Verification" : "Exam Proctoring"}
        </h3>
        <Button
          variant="ghost"
          size="sm"
          onClick={onToggle}
          className="text-xs"
        >
          {isActive ? (
            <>
              <CameraOff className="h-3.5 w-3.5 mr-1" />
              Disable
            </>
          ) : (
            <>
              <Camera className="h-3.5 w-3.5 mr-1" />
              Enable
            </>
          )}
        </Button>
      </div>

      <div className="flex-1 relative bg-black/5 dark:bg-black/20 flex items-center justify-center">
        {isActive ? (
          <>
            <video
              ref={videoRef}
              autoPlay
              playsInline
              muted
              className={`w-full h-full object-cover ${isLoading ? 'opacity-0' : 'opacity-100 transition-opacity duration-500'}`}
            />

            {isLoading && (
              <div className="absolute inset-0 flex items-center justify-center">
                <div className="h-8 w-8 rounded-full border-2 border-primary/30 border-t-primary animate-spin"></div>
              </div>
            )}

            {hasError && (
              <div className="absolute inset-0 flex flex-col items-center justify-center p-6 text-center">
                <Camera className="h-8 w-8 text-muted-foreground mb-2" />
                <p className="text-sm text-muted-foreground">
                  Unable to access camera. Please check your browser permissions.
                </p>
              </div>
            )}

            {verificationMode && faceDetected && (
              <div className="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs flex items-center">
                <ScanFace className="h-3 w-3 mr-1" />
                Face Detected
              </div>
            )}

            {verificationMode && isActive && !faceDetected && !isLoading && !hasError && (
              <div className="absolute inset-0 flex flex-col items-center justify-center">
                <div className="bg-background/80 backdrop-blur-sm rounded-lg p-4 text-center max-w-xs">
                  <ScanFace className="h-8 w-8 mx-auto text-primary mb-2" />
                  <p className="text-sm font-medium mb-1">Position your face in frame</p>
                  <p className="text-xs text-muted-foreground">
                    Make sure your face is clearly visible and well-lit
                  </p>
                </div>
              </div>
            )}
          </>
        ) : (
          <div className="text-center p-6">
            <Camera className="h-8 w-8 mx-auto text-muted-foreground mb-2" />
            <p className="text-sm text-muted-foreground">
              Camera is disabled. {verificationMode ? "Enable it to verify your identity." : "Enable it for exam proctoring."}
            </p>
          </div>
        )}
      </div>
    </div>
  );
};

export default CameraFeed;
