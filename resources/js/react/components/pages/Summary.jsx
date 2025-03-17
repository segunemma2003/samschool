import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useExam } from '../hooks/useExam';
import { Button } from '../ui/button';
import { Card } from '../ui/card';
import Timer from '../component/Timer';
import { ArrowLeft, ArrowRight, Check, AlertTriangle } from 'lucide-react';

const Summary = () => {
  const navigate = useNavigate();
  const {
    exam,
    answers,
    goToQuestion,
    completeExam,
    examStartedAt
  } = useExam();

  useEffect(() => {
    if (!examStartedAt) {
      navigate('/');
    }
  }, [examStartedAt, navigate]);

  const totalQuestions = exam.questions.length;
  const answeredQuestions = answers.filter(a => a.selectedOptionId !== null).length;
  const unansweredQuestions = totalQuestions - answeredQuestions;
  const completionPercentage = Math.round((answeredQuestions / totalQuestions) * 100);

  const handleGoToQuestion = (index) => {
    goToQuestion(index);
    navigate('/exam');
  };

  const handleSubmitExam = () => {
    if (window.confirm('Are you sure you want to submit your exam? You cannot return once submitted.')) {
      completeExam();
    }
  };

  return (
    <div className="min-h-screen bg-background">
      <header className="h-16 border-b border-border bg-background/80 backdrop-blur-sm sticky top-0 z-10">
        <div className="container h-full flex items-center justify-between px-4">
          <h1 className="text-lg font-medium">Exam Summary</h1>
          <Timer />
        </div>
      </header>

      <main className="container px-4 py-8 max-w-5xl mx-auto">
        <Card className="p-8 animate-fade-in">
          <div className="text-center mb-8">
            <h1 className="text-2xl font-semibold mb-2">Review Your Answers</h1>
            <p className="text-muted-foreground">
              You have answered {answeredQuestions} out of {totalQuestions} questions ({completionPercentage}% complete).
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div className="bg-muted/30 p-4 rounded-lg border border-border">
              <div className="flex items-center mb-3">
                <div className="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-3">
                  <Check className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="font-medium">Answered Questions</h3>
                  <p className="text-2xl font-semibold">{answeredQuestions}</p>
                </div>
              </div>
              <div className="h-2 w-full bg-muted rounded-full overflow-hidden">
                <div
                  className="h-full bg-primary rounded-full transition-all duration-500 ease-out"
                  style={{ width: `${completionPercentage}%` }}
                />
              </div>
            </div>

            <div className="bg-muted/30 p-4 rounded-lg border border-border">
              <div className="flex items-center mb-3">
                <div className="h-10 w-10 rounded-full bg-orange-500/10 text-orange-500 flex items-center justify-center mr-3">
                  <AlertTriangle className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="font-medium">Unanswered Questions</h3>
                  <p className="text-2xl font-semibold">{unansweredQuestions}</p>
                </div>
              </div>
              <div className="h-2 w-full bg-muted rounded-full overflow-hidden">
                <div
                  className="h-full bg-orange-500 rounded-full transition-all duration-500 ease-out"
                  style={{ width: `${100 - completionPercentage}%` }}
                />
              </div>
            </div>
          </div>

          <div className="mb-8">
            <h3 className="text-lg font-medium mb-4">Question Navigator</h3>
            <div className="grid grid-cols-5 sm:grid-cols-10 gap-2">
              {exam.questions.map((question, index) => {
                const answer = answers.find(a => a.questionId === question.id);
                const isAnswered = answer && answer.selectedOptionId !== null;

                return (
                  <Button
                    key={question.id}
                    variant={isAnswered ? "default" : "outline"}
                    onClick={() => handleGoToQuestion(index)}
                    className={isAnswered ? '' : 'border-orange-500/30 text-orange-500'}
                  >
                    {index + 1}
                  </Button>
                );
              })}
            </div>
          </div>

          <div className="bg-muted/50 rounded-lg p-4 mb-8">
            <h3 className="font-medium mb-2">Before You Submit:</h3>
            <ul className="space-y-2 text-sm text-muted-foreground">
              <li>• You have {unansweredQuestions} unanswered questions.</li>
              <li>• Once submitted, you cannot return to the exam.</li>
              <li>• Click on any question number above to go back and answer it.</li>
              <li>• Make sure you've reviewed all your answers before submitting.</li>
            </ul>
          </div>

          <div className="flex justify-between">
            <Button
              variant="outline"
              onClick={() => navigate('/exam')}
            >
              <ArrowLeft className="mr-2 h-4 w-4" />
              Return to Exam
            </Button>

            <Button
              onClick={handleSubmitExam}
              disabled={answeredQuestions === 0}
            >
              Submit Exam
              <ArrowRight className="ml-2 h-4 w-4" />
            </Button>
          </div>
        </Card>
      </main>
    </div>
  );
};

export default Summary;
