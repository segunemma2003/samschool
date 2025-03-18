
import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useExam } from '../hooks/useExam';
import UserPanel from '../component/UserPanel';
import CameraFeed from '../component/CameraFeed';
import QuestionCard from '../component/QuestionCard';
import ExamProgress from '../component/ExamProgress';
import Timer from '../component/Timer';
import QuestionNav from '../component/QuestionNav';

const Exam = ({ examId }) => {
    const navigate = useNavigate();
    const {
      exam,
      userData,
      currentQuestionIndex,
      examStartedAt,
      isCameraActive,
      toggleCamera,
    } = useExam();

    // If exam hasn't been started, redirect to home
    useEffect(() => {
      if (!examStartedAt) {
        navigate('/');
      }
    }, [examStartedAt, navigate]);

    // Get current question
    const currentQuestion = exam?.questions ? exam.questions[currentQuestionIndex] : null;

    // Show loading state if exam data isn't loaded yet
    if (!exam) {
      return (
        <div className="min-h-screen flex items-center justify-center">
          <div className="text-center">
            <h2 className="text-xl font-medium mb-2">Loading exam data...</h2>
            <div className="h-2 w-40 bg-muted rounded-full overflow-hidden mx-auto">
              <div className="h-full bg-primary animate-pulse w-full" />
            </div>
          </div>
        </div>
      );
    }

    if (!currentQuestion) {
      return (
        <div className="min-h-screen flex items-center justify-center">
          <div className="text-center">
            <h2 className="text-xl font-medium mb-2">Loading question...</h2>
            <div className="h-2 w-40 bg-muted rounded-full overflow-hidden mx-auto">
              <div className="h-full bg-primary animate-pulse w-full" />
            </div>
          </div>
        </div>
      );
    }

    return (
      <div className="min-h-screen bg-background overflow-hidden">
        <header className="h-16 border-b border-border bg-background/80 backdrop-blur-sm sticky top-0 z-10">
          <div className="container h-full flex items-center justify-between px-4">
            <h1 className="text-lg font-medium">{exam.title}</h1>
            <Timer />
          </div>
        </header>

        <main className="container px-4 py-6">
          <div className="grid lg:grid-cols-12 gap-6">
            {/* Left column - User info and Camera */}
            <div className="lg:col-span-3 space-y-6">
              <div className="h-auto lg:h-[250px]">
                <UserPanel userData={userData} />
              </div>

              <div className="h-auto lg:h-[250px]">
                <CameraFeed isActive={isCameraActive} onToggle={toggleCamera} />
              </div>
            </div>

            {/* Center column - Question */}
            <div className="lg:col-span-9">
              <div className="mb-6">
                <ExamProgress />
              </div>

              <QuestionCard
                question={currentQuestion}
                questionNumber={currentQuestionIndex + 1}
                totalQuestions={exam.questions.length}
              />

              <div className="mt-6">
                <QuestionNav />
              </div>
            </div>
          </div>
        </main>
      </div>
    );
  };

  export default Exam;