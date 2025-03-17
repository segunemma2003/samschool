
import React, { useEffect, useState } from 'react';
import { useExam } from '../hooks/useExam';
import { Clock } from 'lucide-react';

const Timer = () => {
  const { timeRemaining, isExamComplete } = useExam();
  const [isWarning, setIsWarning] = useState(false);
  const [isDanger, setIsDanger] = useState(false);

  // Format the time remaining into minutes and seconds
  const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    if (hours > 0) {
      return `${hours}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
  };

  // Calculate warning states based on time remaining
  useEffect(() => {
    if (timeRemaining <= 300 && timeRemaining > 60) { // 5 minutes or less
      setIsWarning(true);
      setIsDanger(false);
    } else if (timeRemaining <= 60) { // 1 minute or less
      setIsWarning(false);
      setIsDanger(true);
    } else {
      setIsWarning(false);
      setIsDanger(false);
    }
  }, [timeRemaining]);

  const timerClasses = isExamComplete
    ? 'bg-secondary text-muted-foreground'
    : isDanger
      ? 'bg-destructive/10 text-destructive animate-pulse'
      : isWarning
        ? 'bg-orange-500/10 text-orange-600 dark:text-orange-400'
        : 'bg-primary/10 text-primary';

  return (
    <div
      className={`inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium ${timerClasses}`}
      title={isExamComplete ? "Exam completed" : `Time remaining: ${formatTime(timeRemaining)}`}
    >
      <Clock className="h-3.5 w-3.5 mr-1.5" />
      <span className="font-mono">{formatTime(timeRemaining)}</span>
    </div>
  );
};

export default Timer;
