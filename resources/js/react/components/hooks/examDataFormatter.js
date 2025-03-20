
// Format questions data from the API/localStorage format to our app structure
export const formatQuestionsData = (questionsData) => {
  if (!Array.isArray(questionsData)) {
    return [];
  }

  return questionsData.map(q => {
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
  });
};

// Format exam data from the API/localStorage format to our app structure
export const formatExamData = (examData, questionsData, termData, academyData) => {
  const formattedQuestions = formatQuestionsData(questionsData);

  return {
    id: examData.id,
    title: examData.subject?.subject_depot?.name || 'Exam',
    timeLimit: examData.duration ? examData.duration  : 60, // Convert seconds to minutes for display
    passingScore: examData.subject?.pass_mark || 60, // Default passing score
    questions: formattedQuestions,
    instructions: examData.instructions,
    term: termData,
    academy: academyData
  };
};

// Format answers for our application
export const formatAnswers = (answersData) => {
  if (!Array.isArray(answersData)) {
    return [];
  }

  return answersData.map(answer => ({
    questionId: answer.question_id || answer.questionId,
    selectedOptionId: answer.answer || answer.selectedOptionId
  }));
};
