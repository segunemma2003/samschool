
// Constants for localStorage keys
export const STORAGE_KEYS = {
    EXAM: 'cbt-exam-state',
    TIMER: 'cbt-exam-timer',
    EXAM_DATA: 'exam',
    STUDENT: 'student',
    QUESTIONS: 'questions',
    ANSWERS: 'answers',
    ACADEMY: 'academy',
    TERM: 'term',
    HAS_VIEWED_RESULTS: 'has-viewed-results',
    THEME: 'cbt-theme',
    ATTEMPTS: 'cbt-exam-attempts'
  };

  // Helper to create namespaced keys based on student and exam IDs
  export const createNamespacedKey = (key, studentId, examId) => {
    if (!studentId || !examId) return key;
    return `${key}-${studentId}-${examId}`;
  };

  // Load data from localStorage with namespace support
  export const loadFromStorage = (key, defaultValue = null, studentId = null, examId = null) => {
    try {
      // Create namespaced key if IDs are provided
      const namespacedKey = createNamespacedKey(key, studentId, examId);
      const data = localStorage.getItem(namespacedKey);
      return data ? JSON.parse(data) : defaultValue;
    } catch (error) {
      console.error(`Error loading ${key} from localStorage:`, error);
      return defaultValue;
    }
  };

  // Save data to localStorage with namespace support
  export const saveToStorage = (key, data, studentId = null, examId = null) => {
    try {
      // Create namespaced key if IDs are provided
      const namespacedKey = createNamespacedKey(key, studentId, examId);
      localStorage.setItem(namespacedKey, JSON.stringify(data));
      return true;
    } catch (error) {
      console.error(`Error saving ${key} to localStorage:`, error);
      return false;
    }
  };

  // Clear specific keys from localStorage with namespace support
  export const clearFromStorage = (keys, studentId = null, examId = null) => {
    try {
      keys.forEach(key => {
        const namespacedKey = createNamespacedKey(key, studentId, examId);
        localStorage.removeItem(namespacedKey);
      });
      return true;
    } catch (error) {
      console.error('Error clearing localStorage:', error);
      return false;
    }
  };

  // Clear all exam-related data from localStorage for a specific student and exam
  export const clearExamData = (studentId = null, examId = null) => {
    const keysToRemove = [
      STORAGE_KEYS.EXAM,
      STORAGE_KEYS.TIMER,
      STORAGE_KEYS.ANSWERS,
      STORAGE_KEYS.HAS_VIEWED_RESULTS
    ];

    return clearFromStorage(keysToRemove, studentId, examId);
  };

  // Clear ALL exam-related data (use with caution)
  export const clearAllExamData = () => {
    const keysToRemove = [
      STORAGE_KEYS.EXAM,
      STORAGE_KEYS.TIMER,
      STORAGE_KEYS.ANSWERS,
      STORAGE_KEYS.EXAM_DATA,
      STORAGE_KEYS.STUDENT,
      STORAGE_KEYS.QUESTIONS,
      STORAGE_KEYS.ACADEMY,
      STORAGE_KEYS.TERM,
      'course',
      'quizScore',
      STORAGE_KEYS.HAS_VIEWED_RESULTS
    ];

    return clearFromStorage(keysToRemove);
  };

  // Theme management
  export const saveTheme = (theme) => {
    return saveToStorage(STORAGE_KEYS.THEME, theme);
  };

  export const loadTheme = () => {
    return loadFromStorage(STORAGE_KEYS.THEME, 'light');
  };

  // Exam attempts management
  export const incrementAttempts = (studentId, examId) => {
    if (!studentId || !examId) return false;

    const key = createNamespacedKey(STORAGE_KEYS.ATTEMPTS, studentId, examId);
    const currentAttempts = loadFromStorage(key, 0);
    const newAttempts = currentAttempts + 1;
    saveToStorage(key, newAttempts);
    return newAttempts;
  };

  export const getAttempts = (studentId, examId) => {
    if (!studentId || !examId) return 0;

    const key = createNamespacedKey(STORAGE_KEYS.ATTEMPTS, studentId, examId);
    return loadFromStorage(key, 0);
  };

  export const resetAttempts = (studentId, examId) => {
    if (!studentId || !examId) return false;

    const key = createNamespacedKey(STORAGE_KEYS.ATTEMPTS, studentId, examId);
    return saveToStorage(key, 0);
  };
