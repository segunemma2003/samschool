
import { formatAnswersForAPI } from './examScoring';

// Save exam data to the API
export const saveExamDataToAPI = async (exam, userData, answers, isCameraActive, score) => {
  if (!exam || !userData) {
    console.error('Missing exam or user data for API submission');
    return Promise.reject('Missing exam or user data');
  }

  // Dynamically get the root URL
  const rootUrl = window.location.origin;
  let mcourse = null;
  
  try {
    const courseform = localStorage.getItem('course');
    if (courseform) {
      mcourse = JSON.parse(courseform);
    }
  } catch (error) {
    console.error('Error parsing course data:', error);
  }

  const payload = {
    exam_id: exam.id,
    student_id: userData.id,
    course_form_id: mcourse?.id || null,
    recording_path: isCameraActive ? "uploads/exam_recordings/exam1.mp4" : null,
    total_score: score,
    answers: formatAnswersForAPI(answers, exam.questions),
  };

  console.log('Submitting exam data to API:', payload);

  try {
    const response = await fetch(`${rootUrl}/api/save-exam-data`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      // Try to get error message from response
      let errorMessage = 'Failed to save exam data';
      try {
        const errorData = await response.json();
        errorMessage = errorData.message || errorMessage;
      } catch (e) {
        // If we can't parse the JSON, use status text
        errorMessage = response.statusText || errorMessage;
      }
      throw new Error(errorMessage);
    }

    const data = await response.json();
    console.log("Exam data saved successfully:", data);
    
    return data;
  } catch (error) {
    console.error("Error saving exam data:", error.message);
    throw error;
  }
};
