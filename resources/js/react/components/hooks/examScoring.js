
// Calculate the score for the exam
export const calculateExamScore = (answers, questions) => {
  let correctAnswers = 0;
  const totalQuestions = questions?.length || 0;
  
  answers.forEach(answer => {
    const question = questions?.find(q => q.id === answer.questionId);
    if (question && question.correctOptionId === answer.selectedOptionId) {
      correctAnswers++;
    }
  });
  
  const score = totalQuestions > 0 ? Math.round((correctAnswers / totalQuestions) * 100) : 0;
  return { score, totalQuestions, correctAnswers };
};

// Format answers for API submission
export const formatAnswersForAPI = (answers, questions) => {
  return answers.map((ans) => {
    const question = questions?.find(q => q.id === ans.questionId);
    const isCorrect = question && question.correctOptionId === ans.selectedOptionId;
    const questionScore = question?.score ?? 1; // Default to 1 if no score is set
    
    return {
      question_id: ans.questionId,
      answer: ans.selectedOptionId || null,
      score: isCorrect ? questionScore : 0,
      correct: isCorrect,
      comments: ans.comments || "",
    };
  });
};
