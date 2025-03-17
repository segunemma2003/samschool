
import React, { createContext, useContext, useEffect, useState, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { mockExam, mockUser } from '../../data/mockQuestions';
import { toast } from './use-toast';

const ExamContext = createContext(undefined);

// Constants
const STORAGE_KEY_EXAM = 'cbt-exam-state';
const STORAGE_KEY_TIMER = 'cbt-exam-timer';

export const ExamProvider = ({ children }) => {
  const navigate = useNavigate();

  // Load exam data
  const [exam, setExam] = useState(mockExam);
  const [userData, setUserData] = useState(mockUser);

  // Exam state
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
  const [answers, setAnswers] = useState([]);
  const [timeRemaining, setTimeRemaining] = useState(exam.timeLimit * 60); // Convert minutes to seconds
  const [isExamComplete, setIsExamComplete] = useState(false);
  const [examStartedAt, setExamStartedAt] = useState(null);

  // Camera state
  const [isCameraActive, setIsCameraActive] = useState(false);
  const [isCameraVerified, setIsCameraVerified] = useState(false);

  // Timer reference
  const timerRef = useRef(null);

  // Flag to track if timer has been initialized from localStorage
  const timerInitialized = useRef(false);

  // Load saved state from localStorage on initial mount only
  useEffect(() => {
    const loadSavedState = () => {
      const savedState = localStorage.getItem(STORAGE_KEY_EXAM);
      const savedTimer = localStorage.getItem(STORAGE_KEY_TIMER);

      if (savedState) {
        try {
          const parsedState = JSON.parse(savedState);
          setCurrentQuestionIndex(parsedState.currentQuestionIndex || 0);
          setAnswers(parsedState.answers || []);
          setIsExamComplete(parsedState.isExamComplete || false);
          setExamStartedAt(parsedState.examStartedAt || null);
        } catch (error) {
          console.error('Error parsing saved exam state:', error);
        }
      }

      if (savedTimer && !timerInitialized.current) {
        try {
          const parsedTimer = JSON.parse(savedTimer);

          // Calculate the correct remaining time by subtracting elapsed time
          if (parsedTimer.timestamp && parsedTimer.timeRemaining) {
            const elapsedTime = Math.floor((Date.now() - parsedTimer.timestamp) / 1000);
            const newTimeRemaining = Math.max(0, parsedTimer.timeRemaining - elapsedTime);

            console.log('Restoring timer:', {
              saved: parsedTimer.timeRemaining,
              elapsed: elapsedTime,
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
    loadSavedState();
  }, [navigate]); // Empty dependency array to run only once

  // Save state to localStorage whenever it changes
  useEffect(() => {
    if (examStartedAt) {
      const stateToSave = {
        currentQuestionIndex,
        answers,
        isExamComplete,
        examStartedAt
      };

      localStorage.setItem(STORAGE_KEY_EXAM, JSON.stringify(stateToSave));
    }
  }, [currentQuestionIndex, answers, isExamComplete, examStartedAt]);

  // Timer effect - completely separated from localStorage loading
  useEffect(() => {
    // Only run the timer if exam has started, is not complete, and has time remaining
    if (examStartedAt && !isExamComplete && timeRemaining > 0) {
      console.log('Starting timer with', timeRemaining, 'seconds remaining');

      // Save current timer state to localStorage immediately
      const timerState = {
        timeRemaining,
        timestamp: Date.now()
      };
      localStorage.setItem(STORAGE_KEY_TIMER, JSON.stringify(timerState));

      // Set up the interval
      timerRef.current = window.setInterval(() => {
        setTimeRemaining((prev) => {
          const newTime = prev - 1;

          // Update localStorage with new timer state
          const updatedTimerState = {
            timeRemaining: newTime,
            timestamp: Date.now()
          };
          localStorage.setItem(STORAGE_KEY_TIMER, JSON.stringify(updatedTimerState));

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
  }, [examStartedAt, isExamComplete, timeRemaining, navigate]);

  // Start the exam
  const startExam = () => {
    if (!isCameraActive || !isCameraVerified) {
      toast({
        title: "Camera Verification Required",
        description: "Please enable your camera and verify your face before starting the exam.",
        variant: "destructive"
      });
      return;
    }

    const startTime = Date.now();
    setExamStartedAt(startTime);

    // Only reset timeRemaining if we're not restoring from a saved state
    if (!timerInitialized.current) {
      console.log('Setting initial time to', exam.timeLimit * 60, 'seconds');
      setTimeRemaining(exam.timeLimit * 60);
    } else {
      console.log('Continuing with existing time of', timeRemaining, 'seconds');
    }

    setCurrentQuestionIndex(0);
    setIsExamComplete(false);

    // Initialize answers array if not already set
    if (answers.length === 0) {
      const initialAnswers = exam.questions.map(q => ({
        questionId: q.id,
        selectedOptionId: null
      }));
      setAnswers(initialAnswers);
    }

    // Save initial state
    const initialState = {
      currentQuestionIndex: 0,
      answers: answers.length ? answers : exam.questions.map(q => ({
        questionId: q.id,
        selectedOptionId: null
      })),
      isExamComplete: false,
      examStartedAt: startTime
    };

    localStorage.setItem(STORAGE_KEY_EXAM, JSON.stringify(initialState));

    // Initialize timer state only if not already initialized
    if (!timerInitialized.current) {
      const timerState = {
        timeRemaining: exam.timeLimit * 60,
        timestamp: startTime
      };
      localStorage.setItem(STORAGE_KEY_TIMER, JSON.stringify(timerState));
      timerInitialized.current = true;
    } else {
      // Update timestamp for existing timer
      const timerState = {
        timeRemaining,
        timestamp: Date.now()
      };
      localStorage.setItem(STORAGE_KEY_TIMER, JSON.stringify(timerState));
    }

    navigate('/exam');
  };

  // Reset the exam
  const resetExam = () => {
    // Clear localStorage
    localStorage.removeItem(STORAGE_KEY_EXAM);
    localStorage.removeItem(STORAGE_KEY_TIMER);

    // Reset all state
    setCurrentQuestionIndex(0);
    setAnswers([]);
    setTimeRemaining(exam.timeLimit * 60);
    setIsExamComplete(false);
    setExamStartedAt(null);
    timerInitialized.current = false;

    navigate('/');
  };

  // Navigation functions
  const goToQuestion = (index) => {
    if (index >= 0 && index < exam.questions.length) {
      setCurrentQuestionIndex(index);
    }
  };

  const goToNextQuestion = () => {
    if (currentQuestionIndex < exam.questions.length - 1) {
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

  // Save answer
  const saveAnswer = (questionId, optionId) => {
    setAnswers(prevAnswers => {
      const updatedAnswers = [...prevAnswers];
      const answerIndex = updatedAnswers.findIndex(a => a.questionId === questionId);

      if (answerIndex >= 0) {
        updatedAnswers[answerIndex] = { questionId, selectedOptionId: optionId };
      } else {
        updatedAnswers.push({ questionId, selectedOptionId: optionId });
      }

      return updatedAnswers;
    });
  };

  // Complete the exam
  const completeExam = () => {
    setIsExamComplete(true);

    if (timerRef.current) {
      clearInterval(timerRef.current);
    }

    // Update local storage
    const finalState = {
      currentQuestionIndex,
      answers,
      isExamComplete: true,
      examStartedAt
    };
    localStorage.setItem(STORAGE_KEY_EXAM, JSON.stringify(finalState));

    navigate('/results');
  };

  // Calculate score
  const calculateScore = () => {
    let correctAnswers = 0;
    const totalQuestions = exam.questions.length;

    answers.forEach(answer => {
      const question = exam.questions.find(q => q.id === answer.questionId);
      if (question && question.correctOptionId === answer.selectedOptionId) {
        correctAnswers++;
      }
    });

    const score = Math.round((correctAnswers / totalQuestions) * 100);
    return { score, totalQuestions, correctAnswers };
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
    isCameraActive,
    toggleCamera,
    isCameraVerified,
    setIsCameraVerified
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
