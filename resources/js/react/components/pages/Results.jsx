
import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useExam } from '../hooks/useExam';
import { Button } from '../ui/button';
import { Card } from '../ui/card';
import { Progress } from '../ui/progress';
import { Trophy, Home, Check, X } from 'lucide-react';

const Results = () => {
  const navigate = useNavigate();
  const {
    exam,
    isExamComplete,
    calculateScore,
    resetExam
  } = useExam();

  // If the exam isn't complete, redirect to home
  useEffect(() => {
    if (!isExamComplete) {
      navigate('/');
    }
  }, [isExamComplete, navigate]);

  // Calculate the final score
  const { score, totalQuestions, correctAnswers } = calculateScore();
  const hasPassed = score >= exam.passingScore;

//   const handleReturnHome = () => {
//     resetExam();
//     navigate('/');

//   };

const handleReturnHome = () => {
    resetExam();
    // return;
    window.location.href = "/student/exams"; // Redirects to Laravel's route
};
  return (
    <div className="min-h-screen bg-gradient-to-b from-background to-muted/30 px-4 py-12">
      <div className="container max-w-2xl mx-auto">
        <Card className="p-8 animate-fade-in">
          <div className="text-center mb-10">
            <div className="inline-flex items-center justify-center h-20 w-20 rounded-full bg-primary/10 text-primary mb-4">
              <Trophy className="h-10 w-10" />
            </div>
            <h1 className="text-3xl font-semibold mb-2">Exam Completed!</h1>
            <p className="text-muted-foreground">
              You have completed the {exam.title} exam.
            </p>
          </div>

          <div className="mb-8">
            <div className="mb-2 flex justify-between items-end">
              <h3 className="text-lg font-medium">Your Score</h3>
              <span className="text-3xl font-bold">{score}%</span>
            </div>
            <Progress value={score} className="h-4" />
            <div className="mt-1 flex justify-between text-sm text-muted-foreground">
              <span>0%</span>
              <span>Passing: {exam.passingScore}%</span>
              <span>100%</span>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div className="p-4 bg-muted/30 rounded-lg border border-border">
              <h4 className="text-sm font-medium text-muted-foreground mb-1">Total Questions</h4>
              <p className="text-xl font-semibold">{totalQuestions}</p>
            </div>
            <div className="p-4 bg-muted/30 rounded-lg border border-border">
              <h4 className="text-sm font-medium text-muted-foreground mb-1">Correct Answers</h4>
              <p className="text-xl font-semibold">{correctAnswers}</p>
            </div>
          </div>

          <div className={`p-4 rounded-lg border mb-8 ${hasPassed ? 'bg-green-500/10 border-green-500/30 text-green-700 dark:text-green-400' : 'bg-destructive/10 border-destructive/30 text-destructive'}`}>
            <div className="flex items-center">
              <div className={`h-8 w-8 rounded-full flex items-center justify-center mr-3 ${hasPassed ? 'bg-green-500/20' : 'bg-destructive/20'}`}>
                {hasPassed ? <Check className="h-5 w-5" /> : <X className="h-5 w-5" />}
              </div>
              <div>
                <h3 className="font-medium">{hasPassed ? 'Congratulations! You passed the exam.' : 'Unfortunately, you did not pass the exam.'}</h3>
                <p className="text-sm mt-1">
                  {hasPassed
                    ? `You achieved a score of ${score}%, which is above the required ${exam.passingScore}% passing score.`
                    : `You achieved a score of ${score}%, which is below the required ${exam.passingScore}% passing score.`}
                </p>
              </div>
            </div>
          </div>

          <div className="text-center">
            <Button
              onClick={handleReturnHome}
              size="lg"
              className="px-8"
            >
              <Home className="mr-2 h-4 w-4" />
              Return to Dashboard
            </Button>
            <p className="mt-4 text-xs text-muted-foreground">
              Your results have been saved. You may close this page now.
            </p>
          </div>
        </Card>
      </div>
    </div>
  );
};

export default Results;
