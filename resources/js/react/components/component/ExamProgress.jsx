
import React from 'react';
import { useExam } from '../hooks/useExam';

const ExamProgress = ({
  className = '',
  showTimer = false
}) => {
  const { exam, answers, timeRemaining } = useExam();

  // Calculate progress percentage
  const answeredCount = answers.filter(a => a.selectedOptionId !== null).length;
  const totalQuestions = exam.questions.length;
  const progressPercentage = Math.round((answeredCount / totalQuestions) * 100);

  // Calculate timer percentage
  const totalTime = exam.timeLimit * 60; // in seconds
  const timerPercentage = Math.round((timeRemaining / totalTime) * 100);
  const timerColor = timerPercentage <= 20 ? 'bg-destructive' : timerPercentage <= 50 ? 'bg-orange-500' : 'bg-emerald-500';

  return (
    <div className={`space-y-2 ${className}`}>
      <div className="flex justify-between items-center text-xs">
        <span className="text-muted-foreground">Progress</span>
        <span className="font-medium">{answeredCount}/{totalQuestions} questions answered</span>
      </div>

      <div className="h-1.5 w-full bg-secondary rounded-full overflow-hidden">
        <div
          className="h-full bg-primary rounded-full transition-all duration-500 ease-out"
          style={{ width: `${progressPercentage}%` }}
        />
      </div>

      {showTimer && (
        <>
          <div className="flex justify-between items-center text-xs mt-3">
            <span className="text-muted-foreground">Time Remaining</span>
            <span className="font-medium">{Math.floor(timeRemaining / 60)} minutes</span>
          </div>

          <div className="h-1.5 w-full bg-secondary rounded-full overflow-hidden">
            <div
              className={`h-full ${timerColor} rounded-full transition-all duration-500 ease-out`}
              style={{ width: `${timerPercentage}%` }}
            />
          </div>
        </>
      )}
    </div>
  );
};

export default ExamProgress;
