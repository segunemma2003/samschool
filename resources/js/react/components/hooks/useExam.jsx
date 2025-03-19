
import React, { createContext, useContext, useEffect, useState, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { mockExam, mockUser } from '../../data/mockQuestions';
import { toast } from './use-toast';

const ExamContext = createContext(undefined);

// Constants
const STORAGE_KEY_EXAM = 'cbt-exam-state';
const STORAGE_KEY_TIMER = 'cbt-exam-timer';
const STORAGE_KEY_EXAM_DATA = 'exam';
const STORAGE_KEY_STUDENT = 'student';
const STORAGE_KEY_QUESTIONS = 'questions';
const STORAGE_KEY_ANSWERS = 'answers';
const STORAGE_KEY_ACADEMY = 'academy';
const STORAGE_KEY_TERM = 'term';
const STORAGE_KEY_HAS_VIEWED_RESULTS = 'has-viewed-results';

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

  // Timer reference
  const timerRef = useRef(null);

  // Flag to track if timer has been initialized from localStorage
  const timerInitialized = useRef(false);

  // Check if user has already viewed results
  useEffect(() => {
    const hasViewed = localStorage.getItem(STORAGE_KEY_HAS_VIEWED_RESULTS);
    if (hasViewed === 'true') {
      setHasViewedResults(true);
    }
  }, []);

  // Load exam and user data from localStorage on initial mount
  useEffect(() => {
    const loadExamData = () => {
      try {
        // Try to load student data
        const studentJson = localStorage.getItem(STORAGE_KEY_STUDENT);
        if (studentJson) {
          const studentData = JSON.parse(studentJson);
          console.log('Loaded student data:', studentData);
          setUserData(studentData);
        } else {
          setUserData(mockUser);
        }

        // Try to load exam data
        const examJson = localStorage.getItem(STORAGE_KEY_EXAM_DATA);
        const questionsJson = localStorage.getItem(STORAGE_KEY_QUESTIONS);
        const termJson = localStorage.getItem(STORAGE_KEY_TERM);
        const academyJson = localStorage.getItem(STORAGE_KEY_ACADEMY);

        if (examJson && questionsJson) {
          const examData = JSON.parse(examJson);
          const questionsData = JSON.parse(questionsJson);
          console.log('Loaded exam data:', examData);
          console.log('Loaded questions data:', questionsData);

          // Transform the exam data to match our app's structure
          const formattedQuestions = Array.isArray(questionsData) ? questionsData.map(q => {
            // Find the correct option (the one with is_correct: true)
            let correctOptionId = '';

            // Format options and determine correct answer
            const formattedOptions = Object.entries(q.options || {}).map(([key, value]) => {
              // Check if the option has the new format with is_correct flag
              if (Array.isArray(value) && value.length > 0) {
                const optionObj = value[0];
                if (optionObj.is_correct) {
                  correctOptionId = key;
                }

                return {
                  id: key, // A, B, C, etc.
                  text: optionObj.option || optionObj.text || '',
                  image: optionObj.image || null,
                  is_correct: optionObj.is_correct || false
                };
              }
              // If it's just a string or old format
              else if (typeof value === 'object' && value !== null) {
                if (value.is_correct) {
                  correctOptionId = key;
                }

                return {
                  id: key,
                  text: value.text || value.option || value.toString(),
                  image: value.image || null,
                  is_correct: value.is_correct || false
                };
              }
              // Simple string value
              return {
                id: key,
                text: value,
                is_correct: false // Default
              };
            });

            // If we didn't find a correct option with is_correct flag, use the answer property
            if (!correctOptionId && q.answer) {
              correctOptionId = q.answer;
            }

            return {
              id: q.id,
              text: q.question,
              type: q.question_type,
              image: q.image, // Include question image if available
              options: formattedOptions,
              correctOptionId: correctOptionId // The correct answer (A, B, C, etc.)
            };
          }) : [];

          // Format the exam data
          const formattedExam = {
            id: examData.id,
            title: examData.subject?.subject_depot?.name || 'Exam',
            timeLimit: examData.duration ? Math.ceil(examData.duration / 60) : 60, // Convert seconds to minutes for display
            passingScore: examData.subject?.pass_mark || 60, // Default passing score
            questions: formattedQuestions,
            instructions: examData.instructions,
            term: termJson ? JSON.parse(termJson) : null,
            academy: academyJson ? JSON.parse(academyJson) : null
          };

          setExam(formattedExam);

          // Set the time remaining based on the exam duration
          // If duration is already in seconds, use it directly; if it's in minutes, convert to seconds
          if (!timerInitialized.current) {
            // Always interpret examData.duration as seconds
            setTimeRemaining(examData.duration || 3600); // Default to 1 hour if not specified
          }

          // Try to load previous answers - first check localStorage, then window.answers
          const previousAnswersJson = localStorage.getItem(STORAGE_KEY_ANSWERS);

          if (previousAnswersJson) {
            console.log('Found previous answers in localStorage');
            try {
              const previousAnswers = JSON.parse(previousAnswersJson);

              // Map the answers to our expected format if needed
              if (Array.isArray(previousAnswers)) {
                const formattedAnswers = previousAnswers.map(answer => ({
                  questionId: answer.question_id || answer.questionId,
                  selectedOptionId: answer.answer || answer.selectedOptionId
                }));

                setAnswers(formattedAnswers);
                console.log('Loaded previous answers from localStorage:', formattedAnswers);
              }
            } catch (error) {
              console.error('Error parsing previous answers from localStorage:', error);
            }
          } else if (window.answers) {
            console.log('Found previous answers in window.answers');
            try {
              // Map window.answers to our expected format
              if (Array.isArray(window.answers)) {
                const formattedAnswers = window.answers.map(answer => ({
                  questionId: answer.question_id || answer.questionId,
                  selectedOptionId: answer.answer || answer.selectedOptionId
                }));

                setAnswers(formattedAnswers);
                console.log('Loaded previous answers from window.answers:', formattedAnswers);

                // Save to localStorage for future use
                localStorage.setItem(STORAGE_KEY_ANSWERS, JSON.stringify(window.answers));
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
      } catch (error) {
        console.error('Error loading exam data:', error);
        // Fall back to mock data if there's an error
        setExam(mockExam);
        setUserData(mockUser);
        setTimeRemaining(mockExam.timeLimit * 60);
      }
    };

    // Only load exam data if the user hasn't viewed results
    if (!hasViewedResults) {
      loadExamData();
    } else {
      // If they've already viewed results, show message and redirect
      toast({
        title: "Exam Already Completed",
        description: "You have already completed this exam and cannot retake it.",
        variant: "warning"
      });
      navigate('/');
    }
  }, [hasViewedResults, navigate]);

  // Load saved state from localStorage on initial mount only
  useEffect(() => {
    const loadSavedState = () => {
      const savedState = localStorage.getItem(STORAGE_KEY_EXAM);
      const savedTimer = localStorage.getItem(STORAGE_KEY_TIMER);

      if (savedState) {
        try {
          const parsedState = JSON.parse(savedState);
          setCurrentQuestionIndex(parsedState.currentQuestionIndex || 0);

          // Only set answers from exam state if we haven't already loaded them
          if (!answers.length && parsedState.answers && parsedState.answers.length) {
            setAnswers(parsedState.answers);
          }

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
    if (!hasViewedResults) {
      loadSavedState();
    }
  }, [navigate, answers.length, hasViewedResults]);

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

      // Also save answers separately for easier access
      localStorage.setItem(STORAGE_KEY_ANSWERS, JSON.stringify(answers));
    }
  }, [currentQuestionIndex, answers, isExamComplete, examStartedAt]);

  // Timer effect - completely separated from localStorage loading
  useEffect(() => {
    // Only run the timer if exam has started, is not complete, and has time remaining
    if (examStartedAt && !isExamComplete && timeRemaining > 0 && !hasViewedResults) {
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
  }, [examStartedAt, isExamComplete, timeRemaining, navigate, hasViewedResults]);

  // Start the exam
  const startExam = () => {
    // Prevent starting if already viewed results
    if (hasViewedResults) {
      toast({
        title: "Exam Already Completed",
        description: "You have already completed this exam and cannot retake it.",
        variant: "warning"
      });
      return;
    }

    if (isCameraRequired && (!isCameraActive || !isCameraVerified)) {
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
    if (!timerInitialized.current && exam) {
      // Convert minutes to seconds - exam.timeLimit is in minutes
      const timeInSeconds = exam.timeLimit * 60;
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

    // Save initial state
    const initialState = {
      currentQuestionIndex: 0,
      answers: answers.length && exam?.questions ? answers : exam?.questions?.map(q => ({
        questionId: q.id,
        selectedOptionId: null
      })) || [],
      isExamComplete: false,
      examStartedAt: startTime
    };

    localStorage.setItem(STORAGE_KEY_EXAM, JSON.stringify(initialState));
    localStorage.setItem(STORAGE_KEY_ANSWERS, JSON.stringify(answers.length ? answers : []));

    // Initialize timer state only if not already initialized
    if (!timerInitialized.current && exam) {
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
  const resetExam = async() => {
    // If exam is complete, try to save results first
    if (isExamComplete && !hasViewedResults) {
      try {
        await saveExamData();
      } catch (error) {
        console.error('Error saving exam data during reset:', error);
      }
    }

    // Clear ALL localStorage items related to the exam
    const keysToRemove = [
      STORAGE_KEY_EXAM,
      STORAGE_KEY_TIMER,
      STORAGE_KEY_ANSWERS,
      STORAGE_KEY_EXAM_DATA,
      STORAGE_KEY_STUDENT,
      STORAGE_KEY_QUESTIONS,
      STORAGE_KEY_ACADEMY,
      STORAGE_KEY_TERM,
      'course',
      'quizScore',
      STORAGE_KEY_HAS_VIEWED_RESULTS
    ];

    keysToRemove.forEach(key => localStorage.removeItem(key));

    // Reset all state
    setCurrentQuestionIndex(0);
    setAnswers([]);
    if (exam) {
      setTimeRemaining(exam.timeLimit * 60);
    }
    setIsExamComplete(false);
    setExamStartedAt(null);
    setHasViewedResults(false);
    timerInitialized.current = false;

    navigate('/');
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

      // Save to localStorage immediately
      localStorage.setItem(STORAGE_KEY_ANSWERS, JSON.stringify(updatedAnswers));

      return updatedAnswers;
    });
  };

  // Complete the exam
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

      // Update local storage
      const finalState = {
        currentQuestionIndex,
        answers,
        isExamComplete: true,
        examStartedAt
      };
      localStorage.setItem(STORAGE_KEY_EXAM, JSON.stringify(finalState));
      localStorage.setItem(STORAGE_KEY_ANSWERS, JSON.stringify(answers));

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

  // Calculate score
  const calculateScore = () => {
    let correctAnswers = 0;
    const totalQuestions = exam?.questions?.length || 0;

    answers.forEach(answer => {
      const question = exam?.questions?.find(q => q.id === answer.questionId);
      if (question && question.correctOptionId === answer.selectedOptionId) {
        correctAnswers++;
      }
    });

    const score = totalQuestions > 0 ? Math.round((correctAnswers / totalQuestions) * 100) : 0;
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

  const saveExamData = async () => {
    if (!exam || !userData) {
      console.error('Missing exam or user data for API submission');
      return Promise.reject('Missing exam or user data');
    }

    // Get calculated score
    const { score } = calculateScore();

    // Dynamically get the root URL
    const rootUrl = window.location.origin; // Automatically gets the current root domain
    let mcourse = null;

    try {
      const courseform = localStorage.getItem('course');
      if (courseform) {
        mcourse = JSON.parse(courseform);
      }
    } catch (error) {
      console.error('Error parsing course data:', error);
    }

    const payload = {
      exam_id: exam.id,
      student_id: userData.id,
      course_form_id: mcourse?.id || null, // Use null if course_form_id doesn't exist
      recording_path: isCameraActive ? "uploads/exam_recordings/exam1.mp4" : null,
      total_score: score,
      answers: answers.map((ans) => {
        const question = exam?.questions?.find(q => q.id === ans.questionId);
        const isCorrect = question && question.correctOptionId === ans.selectedOptionId;
        const questionScore = question?.score ?? 1; // Default to 1 if no score is set

        return {
          question_id: ans.questionId,
          answer: ans.selectedOptionId || null,
          score: isCorrect ? questionScore : 0,
          correct: isCorrect,
          comments: ans.comments || "",
        };
      }),
    };

    console.log('Submitting exam data to API:', payload);

    try {
      const response = await fetch(`${rootUrl}/api/save-exam-data`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        // Try to get error message from response
        let errorMessage = 'Failed to save exam data';
        try {
          const errorData = await response.json();
          errorMessage = errorData.message || errorMessage;
        } catch (e) {
          // If we can't parse the JSON, use status text
          errorMessage = response.statusText || errorMessage;
        }
        throw new Error(errorMessage);
      }

      const data = await response.json();
      console.log("Exam data saved successfully:", data);

      // Mark as having viewed results
      localStorage.setItem(STORAGE_KEY_HAS_VIEWED_RESULTS, 'true');
      setHasViewedResults(true);

      return data;
    } catch (error) {
      console.error("Error saving exam data:", error.message);
      throw error; // Rethrow to handle in calling function
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
    isSavingResults
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
