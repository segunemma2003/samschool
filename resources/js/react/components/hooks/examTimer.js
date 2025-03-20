import { STORAGE_KEYS, saveToStorage } from './examStorage';

// Helper to calculate remaining time based on saved timer state
export const calculateRemainingTime = (savedTimer) => {
  if (!savedTimer || typeof savedTimer.timeRemaining !== 'number' || typeof savedTimer.timestamp !== 'number') {
    return null;
  }

  const elapsedSeconds = Math.floor((Date.now() - savedTimer.timestamp) / 1000);
  const remaining = Math.max(0, savedTimer.timeRemaining - elapsedSeconds);

  return remaining;
};

// Save timer state to localStorage
export const saveTimerState = (timeRemaining, studentId, examId) => {
  const timerState = {
    timeRemaining,
    timestamp: Date.now()
  };

  saveToStorage(
    STORAGE_KEYS.TIMER,
    timerState,
    studentId,
    examId
  );
};

// Should refresh data check
export const shouldRefreshData = () => {
  return window.dataRefreshNeeded === true;
};

// Convert minutes to seconds for the timer
export const convertDurationToSeconds = (durationInMinutes) => {
  // Default to 60 minutes (3600 seconds) if duration is not provided
  if (!durationInMinutes) return 3600;

  // Convert minutes to seconds
  return durationInMinutes * 60;
};

// Format timer for display (converts seconds to HH:MM:SS)
export const formatTimer = (seconds) => {
  if (seconds <= 0) return '00:00:00';

  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const remainingSeconds = seconds % 60;

  return [
    hours.toString().padStart(2, '0'),
    minutes.toString().padStart(2, '0'),
    remainingSeconds.toString().padStart(2, '0')
  ].join(':');
};

// Format duration for display (e.g., 70 mins → 1h 10m)
export const formatDuration = (minutes) => {
  if (!minutes) return '0m';

  const hours = Math.floor(minutes / 60);
  const remainingMinutes = minutes % 60;

  if (hours > 0) {
    return `${hours}h ${remainingMinutes > 0 ? `${remainingMinutes}m` : ''}`;
  }

  return `${minutes}m`;
};