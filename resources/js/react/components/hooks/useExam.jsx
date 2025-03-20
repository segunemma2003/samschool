
import React, { createContext, useContext, useEffect, useState, useRef, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { mockExam, mockUser } from '../../data/mockQuestions';
import { toast } from './use-toast';

// Import refactored modules
import {
  STORAGE_KEYS,
  loadFromStorage,
  saveToStorage,
  clearExamData,
  incrementAttempts,
  getAttempts,
  resetAttempts
} from './examStorage';
import { saveTimerState, calculateRemainingTime, convertDurationToSeconds, shouldRefreshData } from './examTimer';
import { formatExamData, formatQuestionsData, formatAnswers } from './examDataFormatter';
import { calculateExamScore } from './examScoring';
import { saveExamDataToAPI } from './examAPI';

const ExamContext = createContext(undefined);

export const ExamProvider = ({ children }) => {
  const navigate = useNavigate();

  // Load exam data
  const [exam, setExam] = useState(null);
  const [userData, setUserData] = useState(null);

  // Exam state
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
  const [answers, setAnswers] = useState([]);
  const [timeRemaining, setTimeRemaining] = useState(0); // Will be set based on exam duration
  const [isExamComplete, setIsExamComplete] = useState(false);
  const [examStartedAt, setExamStartedAt] = useState(null);
  const [hasViewedResults, setHasViewedResults] = useState(false);
  const [isSavingResults, setIsSavingResults] = useState(false);

  // Camera state
  const [isCameraActive, setIsCameraActive] = useState(false);
  const [isCameraVerified, setIsCameraVerified] = useState(false);
  const [isCameraRequired, setIsCameraRequired] = useState(false);

  // Attempts management
  const [attempts, setAttempts] = useState(0);
  const [maxAttempts, setMaxAttempts] = useState(1); // Default to 1 attempt

  // Timer reference
  const timerRef = useRef(null);

  const isNewAttempt = useRef(true);
  // Flag to track if timer has been initialized from localStorage
  const timerInitialized = useRef(false);

  // Get student ID and exam ID for namespacing
  const getStudentId = useCallback(() => {
    const studentData = loadFromStorage(STORAGE_KEYS.STUDENT, null);
    return studentData?.id || null;
  }, []);

  const getExamId = useCallback(() => {
    const examData = loadFromStorage(STORAGE_KEYS.EXAM_DATA, null);
    return examData?.id || null;
  }, []);

  // Memoized function to load exam data
  const loadExamData = useCallback(() => {
    try {
      // Try to load student data
      const studentData = loadFromStorage(STORAGE_KEYS.STUDENT, null);
      if (studentData) {
        console.log('Loaded student data:', studentData);
        setUserData(studentData);
      } else {
        setUserData(mockUser);
      }

      // Try to load exam data
      const examData = loadFromStorage(STORAGE_KEYS.EXAM_DATA, null);
      const questionsData = loadFromStorage(STORAGE_KEYS.QUESTIONS, null);
      const termData = loadFromStorage(STORAGE_KEYS.TERM, null);
      const academyData = loadFromStorage(STORAGE_KEYS.ACADEMY, null);

      // Get student and exam IDs for namespacing
      const studentId = studentData?.id;
      const examId = examData?.id;

      if (examData && questionsData) {
        console.log('Loaded exam data:', examData);
        console.log('Loaded questions data:', questionsData);

        // Format the exam data using our utility
        const formattedExam = formatExamData(examData, questionsData, termData, academyData);
        setExam(formattedExam);

        // Set the maximum number of attempts from the exam data
        // if (examData.maxAttempts) {
        //   setMaxAttempts(examData.maxAttempts);
        // }

            setMaxAttempts(examData.maxAttempts || 1);
        // Load the current attempt count
        if (studentId && examId) {
          const currentAttempts = getAttempts(studentId, examId);
          setAttempts(currentAttempts);
        }


        const savedTimer = loadFromStorage(STORAGE_KEYS.TIMER, null, studentId, examId);
        const savedState = loadFromStorage(STORAGE_KEYS.EXAM, null, studentId, examId);

        isNewAttempt.current = !savedTimer || !savedState || !savedState.examStartedAt;

        if (!timerInitialized.current) {
          if (isNewAttempt.current) {
            // FIX 3: For new attempts, always use exam duration from examData
            // examData.duration is in minutes, convert to seconds
            const durationInSeconds = convertDurationToSeconds(examData.duration);
            console.log('Setting initial time for new attempt:', durationInSeconds, 'seconds');
            setTimeRemaining(durationInSeconds);
          } else {
            // For page reloads, use remaining time
            console.log('Page reload detected, using saved timer state');
          }
        }

        // // Set the time remaining based on the exam duration
        // if (!timerInitialized.current) {
        //   // Always interpret examData.duration as seconds
        //   setTimeRemaining(examData.duration || 3600); // Default to 1 hour if not specified
        // }

        // Try to load previous answers - now with namespacing
        const previousAnswers = loadFromStorage(
          STORAGE_KEYS.ANSWERS,
          [],
          studentId,
          examId
        );

        if (previousAnswers && previousAnswers.length) {
          console.log('Found previous answers in localStorage with namespace');
          const formattedAnswers = formatAnswers(previousAnswers);
          setAnswers(formattedAnswers);
          console.log('Loaded previous answers from localStorage:', formattedAnswers);
        } else if (window.answers) {
          console.log('Found previous answers in window.answers');
          try {
            // Map window.answers to our expected format
            if (Array.isArray(window.answers)) {
              const formattedAnswers = formatAnswers(window.answers);
              setAnswers(formattedAnswers);
              console.log('Loaded previous answers from window.answers:', formattedAnswers);

              // Save to localStorage for future use - now with namespacing
              saveToStorage(
                STORAGE_KEYS.ANSWERS,
                window.answers,
                studentId,
                examId
              );
            }
          } catch (error) {
            console.error('Error processing window.answers:', error);
          }
        } else {
          console.log('No previous answers found');
        }
      } else {
        // Fall back to mock data if exam data isn't available
        console.log('Using mock exam data');
        setExam(mockExam);
        // mockExam.timeLimit is in minutes, convert to seconds for the timer
        setTimeRemaining(mockExam.timeLimit * 60);
      }

      return true;
    } catch (error) {
      console.error('Error loading exam data:', error);
      // Fall back to mock data if there's an error
      setExam(mockExam);
      setUserData(mockUser);
      setTimeRemaining(mockExam.timeLimit * 60);
      return false;
    }
  }, []);

  // Check if user has already viewed results - now with namespacing
  useEffect(() => {
    const studentId = getStudentId();
    const examId = getExamId();

    const hasViewed = loadFromStorage(
      STORAGE_KEYS.HAS_VIEWED_RESULTS,
      false,
      studentId,
      examId
    );

    if (hasViewed === true) {
      setHasViewedResults(true);
    }
  }, [getStudentId, getExamId]);

  // Load exam and user data from localStorage on initial mount
  useEffect(() => {
    const studentId = getStudentId();
    const examId = getExamId();

    // Only load exam data if the user hasn't viewed results or has attempts left
    if (!hasViewedResults || (attempts < maxAttempts)) {
      loadExamData();
    } else {
      // If they've already viewed results and used all attempts, show message
      toast({
        title: "Exam Already Completed",
        description: "You have already completed this exam and cannot retake it.",
        variant: "warning"
      });
      navigate('/results');
    }
  }, [hasViewedResults, navigate, loadExamData, attempts, maxAttempts, getStudentId, getExamId]);

  // Load saved state from localStorage on initial mount only - now with namespacing
  useEffect(() => {
    const loadSavedState = () => {
      const studentId = getStudentId();
      const examId = getExamId();

      const savedState = loadFromStorage(
        STORAGE_KEYS.EXAM,
        null,
        studentId,
        examId
      );

      const savedTimer = loadFromStorage(
        STORAGE_KEYS.TIMER,
        null,
        studentId,
        examId
      );

      if (savedState) {
        try {
          setCurrentQuestionIndex(savedState.currentQuestionIndex || 0);

          // Only set answers from exam state if we haven't already loaded them
          if (!answers.length && savedState.answers && savedState.answers.length) {
            setAnswers(savedState.answers);
          }

          setIsExamComplete(savedState.isExamComplete || false);
          setExamStartedAt(savedState.examStartedAt || null);
        } catch (error) {
          console.error('Error parsing saved exam state:', error);
        }
      }

      if (savedTimer && !timerInitialized.current) {
        try {
          const newTimeRemaining = calculateRemainingTime(savedTimer);

          if (newTimeRemaining !== null) {
            console.log('Restoring timer:', {
              saved: savedTimer.timeRemaining,
              new: newTimeRemaining
            });

            setTimeRemaining(newTimeRemaining);
            timerInitialized.current = true;

            // If time has run out while away, complete the exam
            if (newTimeRemaining <= 0 && !isExamComplete) {
              setIsExamComplete(true);
              navigate('/summary');
              toast({
                title: "Time's up!",
                description: "Your exam has been automatically submitted.",
                variant: "destructive"
              });
            }
          }
        } catch (error) {
          console.error('Error parsing saved timer:', error);
        }
      }
    };

    // Load state only once on initial mount
    if (!hasViewedResults || (attempts < maxAttempts)) {
      loadSavedState();
    }
  }, [navigate, answers.length, hasViewedResults, attempts, maxAttempts, getStudentId, getExamId]);

  // Add a function to force reload exam data
  const reloadExamData = useCallback(() => {
    console.log("Forcing reload of exam data");
    return loadExamData();
  }, [loadExamData]);

  // Save state to localStorage whenever it changes - now with namespacing
  useEffect(() => {
    if (examStartedAt) {
      const studentId = getStudentId();
      const examId = getExamId();

      const stateToSave = {
        currentQuestionIndex,
        answers,
        isExamComplete,
        examStartedAt
      };

      saveToStorage(
        STORAGE_KEYS.EXAM,
        stateToSave,
        studentId,
        examId
      );

      // Also save answers separately for easier access
      saveToStorage(
        STORAGE_KEYS.ANSWERS,
        answers,
        studentId,
        examId
      );
    }
  }, [currentQuestionIndex, answers, isExamComplete, examStartedAt, getStudentId, getExamId]);

  // Timer effect - now with namespacing
  useEffect(() => {
    // Only run the timer if exam has started, is not complete, and has time remaining
    if (examStartedAt && !isExamComplete && timeRemaining > 0 && !hasViewedResults) {
      console.log('Starting timer with', timeRemaining, 'seconds remaining');

      const studentId = getStudentId();
      const examId = getExamId();

      // Save current timer state to localStorage immediately
      saveTimerState(timeRemaining, studentId, examId);

      // Set up the interval
      timerRef.current = window.setInterval(() => {
        setTimeRemaining((prev) => {
          const newTime = prev - 1;

          // Update localStorage with new timer state
          saveTimerState(newTime, studentId, examId);

          // Check if time is up
          if (newTime <= 0) {
            if (timerRef.current) clearInterval(timerRef.current);
            setIsExamComplete(true);
            navigate('/summary');
            toast({
              title: "Time's up!",
              description: "Your exam has been automatically submitted.",
              variant: "destructive"
            });
            return 0;
          }

          return newTime;
        });
      }, 1000);

      return () => {
        if (timerRef.current) clearInterval(timerRef.current);
      };
    }

    // Clear timer if exam is complete or time is up
    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
    };
  }, [examStartedAt, isExamComplete, timeRemaining, navigate, hasViewedResults, getStudentId, getExamId]);

  // Start the exam
  const startExam = () => {
    const studentId = getStudentId();
    const examId = getExamId();

    // Check if maximum attempts reached
    if (attempts >= maxAttempts) {
      toast({
        title: "Maximum Attempts Reached",
        description: `You have already used all ${maxAttempts} attempts for this exam.`,
        variant: "warning"
      });
      navigate('/results');
    //   return;
    }

    // Prevent starting if already viewed results
    if (hasViewedResults) {
      toast({
        title: "Exam Already Completed",
        description: "You have already completed this exam and cannot retake it.",
        variant: "warning"
      });
      navigate('/results');
    //   return;
    }

    if (isCameraRequired && (!isCameraActive || !isCameraVerified)) {
      toast({
        title: "Camera Verification Required",
        description: "Please enable your camera and verify your face before starting the exam.",
        variant: "destructive"
      });
      return;
    }

    // Increment attempt counter
    if (studentId && examId) {
      const newAttempts = incrementAttempts(studentId, examId);
      setAttempts(newAttempts);
    }

    const startTime = Date.now();
    setExamStartedAt(startTime);

    // Only reset timeRemaining if we're not restoring from a saved state
    if (!timerInitialized.current && exam) {
      // Convert minutes to seconds - exam.timeLimit is in minutes
      const timeInSeconds = convertDurationToSeconds(exam.timeLimit);
      console.log('Setting initial time to', timeInSeconds, 'seconds');
      setTimeRemaining(timeInSeconds);
    } else {
      console.log('Continuing with existing time of', timeRemaining, 'seconds');
    }

    setCurrentQuestionIndex(0);
    setIsExamComplete(false);

    // Initialize answers array if not already set
    if (answers.length === 0 && exam?.questions) {
      const initialAnswers = exam.questions.map(q => ({
        questionId: q.id,
        selectedOptionId: null
      }));
      setAnswers(initialAnswers);
    }

    // Save initial state - now with namespacing
    const initialState = {
      currentQuestionIndex: 0,
      answers: answers.length && exam?.questions ? answers : exam?.questions?.map(q => ({
        questionId: q.id,
        selectedOptionId: null
      })) || [],
      isExamComplete: false,
      examStartedAt: startTime
    };

    saveToStorage(
      STORAGE_KEYS.EXAM,
      initialState,
      studentId,
      examId
    );

    saveToStorage(
      STORAGE_KEYS.ANSWERS,
      answers.length ? answers : [],
      studentId,
      examId
    );

    // Initialize timer state only if not already initialized
    if (!timerInitialized.current && exam) {
      const timerState = {
        timeRemaining: convertDurationToSeconds(exam.timeLimit),
        timestamp: startTime
      };
      saveToStorage(
        STORAGE_KEYS.TIMER,
        timerState,
        studentId,
        examId
      );
      timerInitialized.current = true;
    } else {
      // Update timestamp for existing timer
      saveTimerState(timeRemaining, studentId, examId);
    }

    navigate('/exam');
  };

  // Reset the exam
  const resetExam = async() => {
    const studentId = getStudentId();
    const examId = getExamId();

    // If exam is complete, try to save results first
    if (isExamComplete && !hasViewedResults) {
      try {
        await saveExamData();
      } catch (error) {
        console.error('Error saving exam data during reset:', error);
      }
    }

    // Clear exam data for THIS student/exam combination
    clearExamData(studentId, examId);

    // Reset all state
    setCurrentQuestionIndex(0);
    setAnswers([]);
    if (exam) {
      setTimeRemaining(convertDurationToSeconds(exam.timeLimit));
    }
    setIsExamComplete(false);
    setExamStartedAt(null);
    setHasViewedResults(false);
    timerInitialized.current = false;

    navigate('/');
  };

  // Reset attempts for this student and exam
  const resetExamAttempts = () => {
    const studentId = getStudentId();
    const examId = getExamId();

    if (studentId && examId) {
      resetAttempts(studentId, examId);
      setAttempts(0);

      toast({
        title: "Attempts Reset",
        description: "Your exam attempts have been reset. You can now take the exam again.",
      });
    }
  };

  // Navigation functions
  const goToQuestion = (index) => {
    if (exam?.questions && index >= 0 && index < exam.questions.length) {
      setCurrentQuestionIndex(index);
    }
  };

  const goToNextQuestion = () => {
    if (exam?.questions && currentQuestionIndex < exam.questions.length - 1) {
      setCurrentQuestionIndex(prevIndex => prevIndex + 1);
    } else {
      // At the last question, show the summary
      navigate('/summary');
    }
  };

  const goToPreviousQuestion = () => {
    if (currentQuestionIndex > 0) {
      setCurrentQuestionIndex(prevIndex => prevIndex - 1);
    }
  };

  // Save answer - now with namespacing
  const saveAnswer = (questionId, optionId) => {
    const studentId = getStudentId();
    const examId = getExamId();

    setAnswers(prevAnswers => {
      const updatedAnswers = [...prevAnswers];
      const answerIndex = updatedAnswers.findIndex(a => a.questionId === questionId);

      if (answerIndex >= 0) {
        updatedAnswers[answerIndex] = { questionId, selectedOptionId: optionId };
      } else {
        updatedAnswers.push({ questionId, selectedOptionId: optionId });
      }

      // Save to localStorage immediately
      saveToStorage(
        STORAGE_KEYS.ANSWERS,
        updatedAnswers,
        studentId,
        examId
      );

      return updatedAnswers;
    });
  };

  // Complete the exam - now with namespacing
  const completeExam = async () => {
    // Set saving flag
    setIsSavingResults(true);

    try {
      // Save the exam data to API
      await saveExamData();

      // Only proceed after saving is successful
      setIsExamComplete(true);

      if (timerRef.current) {
        clearInterval(timerRef.current);
      }

      const studentId = getStudentId();
      const examId = getExamId();

      // Update local storage
      const finalState = {
        currentQuestionIndex,
        answers,
        isExamComplete: true,
        examStartedAt
      };

      saveToStorage(
        STORAGE_KEYS.EXAM,
        finalState,
        studentId,
        examId
      );

      saveToStorage(
        STORAGE_KEYS.ANSWERS,
        answers,
        studentId,
        examId
      );

      // Navigate to results
      navigate('/results');
    } catch (error) {
      console.error('Error saving exam results:', error);

      // Show an error to the user
      toast({
        title: "Error Saving Results",
        description: "There was a problem saving your exam results. Please try again.",
        variant: "destructive"
      });

      // Still set exam as complete
      setIsExamComplete(true);
      navigate('/results');
    } finally {
      setIsSavingResults(false);
    }
  };

  // Calculate score using the utility
  const calculateScore = () => {
    return calculateExamScore(answers, exam?.questions);
  };

  // Toggle camera
  const toggleCamera = () => {
    const newState = !isCameraActive;
    setIsCameraActive(newState);

    // Reset verification when camera is turned off
    if (!newState) {
      setIsCameraVerified(false);
    }
  };

  const saveExamData = async () => {
    if (!exam || !userData) {
      console.error('Missing exam or user data for API submission');
      return Promise.reject('Missing exam or user data');
    }

    // Get calculated score
    const { score } = calculateScore();

    const studentId = getStudentId();
    const examId = getExamId();

    try {
      // Call the API utility function
      const data = await saveExamDataToAPI(exam, userData, answers, isCameraActive, score);

      // Mark as having viewed results - now with namespacing
      saveToStorage(
        STORAGE_KEYS.HAS_VIEWED_RESULTS,
        true,
        studentId,
        examId
      );

      setHasViewedResults(true);

      return data;
    } catch (error) {
      console.error("Error saving exam data:", error.message);
      throw error;
    }
  };

  const value = {
    exam,
    userData,
    currentQuestionIndex,
    answers,
    timeRemaining,
    isExamComplete,
    examStartedAt,
    startExam,
    goToQuestion,
    goToNextQuestion,
    goToPreviousQuestion,
    saveAnswer,
    completeExam,
    calculateScore,
    resetExam,
    saveExamData,
    isCameraActive,
    toggleCamera,
    isCameraVerified,
    setIsCameraVerified,
    isCameraRequired,
    setIsCameraRequired,
    hasViewedResults,
    isSavingResults,
    reloadExamData,
    // New properties for attempts management
    attempts,
    maxAttempts,
    setMaxAttempts,
    resetExamAttempts,
    isNewAttempt: isNewAttempt.current
  };

  return <ExamContext.Provider value={value}>{children}</ExamContext.Provider>;
};

export const useExam = () => {
  const context = useContext(ExamContext);

  if (context === undefined) {
    throw new Error('useExam must be used within an ExamProvider');
  }

  return context;
};
