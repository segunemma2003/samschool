
import React from 'react';
import { useExam } from '../hooks/useExam';
import { Button } from '../ui/button';

const QuestionNav = ({ onComplete }) => {
  const { exam, answers, currentQuestionIndex, goToQuestion, completeExam } = useExam();

  const handleCompleteExam = () => {
    if (window.confirm('Are you sure you want to submit your exam?')) {
      completeExam();
      if (onComplete) onComplete();
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center">
        <h3 className="text-sm font-medium">Question Navigator</h3>
        <Button
          variant="default"
          size="sm"
          onClick={handleCompleteExam}
          className="text-xs"
        >
          Submit Exam
        </Button>
      </div>

      <div className="grid grid-cols-5 gap-2">
        {exam.questions.map((question, index) => {
          const answer = answers.find(a => a.questionId === question.id);
          const isAnswered = answer && answer.selectedOptionId !== null;
          const isCurrent = index === currentQuestionIndex;

          return (
            <Button
              key={question.id}
              variant={isCurrent ? "default" : "outline"}
              className={`h-10 w-10 p-0 ${isAnswered && !isCurrent ? 'bg-primary/10 border-primary/30' : ''}`}
              onClick={() => goToQuestion(index)}
            >
              {index + 1}
            </Button>
          );
        })}
      </div>
    </div>
  );
};

export default QuestionNav;
