
// Mock exam data
export const mockExam = {
    title: "Web Development Fundamentals",
    timeLimit: 45, // 45 minutes
    passingScore: 70, // 70% to pass
    questions: [
      {
        id: 1,
        text: "What does HTML stand for?",
        options: [
          { id: "a", text: "Hyper Text Markup Language" },
          { id: "b", text: "Hyper Transfer Markup Language" },
          { id: "c", text: "Hyper Text Makeup Language" },
          { id: "d", text: "Hyper Transfer Makeup Language" }
        ],
        correctOptionId: "a"
      },
      {
        id: 2,
        text: "Which of the following is a CSS preprocessor?",
        options: [
          { id: "a", text: "jQuery" },
          { id: "b", text: "SASS" },
          { id: "c", text: "TypeScript" },
          { id: "d", text: "Node.js" }
        ],
        correctOptionId: "b"
      },
      {
        id: 3,
        text: "Which JavaScript method is used to access an HTML element by its ID?",
        options: [
          { id: "a", text: "querySelector()" },
          { id: "b", text: "getElementsByClassName()" },
          { id: "c", text: "getElementById()" },
          { id: "d", text: "getElementByName()" }
        ],
        correctOptionId: "c"
      },
      {
        id: 4,
        text: "What is the correct way to declare a variable in JavaScript that cannot be reassigned?",
        options: [
          { id: "a", text: "var x = 5;" },
          { id: "b", text: "let x = 5;" },
          { id: "c", text: "const x = 5;" },
          { id: "d", text: "static x = 5;" }
        ],
        correctOptionId: "c"
      },
      {
        id: 5,
        text: "Which HTTP method is used to update a resource?",
        options: [
          { id: "a", text: "GET" },
          { id: "b", text: "POST" },
          { id: "c", text: "PUT" },
          { id: "d", text: "DELETE" }
        ],
        correctOptionId: "c"
      },
      {
        id: 6,
        text: "What does API stand for?",
        options: [
          { id: "a", text: "Application Programming Interface" },
          { id: "b", text: "Application Process Integration" },
          { id: "c", text: "Advanced Programming Interface" },
          { id: "d", text: "Application Protocol Interface" }
        ],
        correctOptionId: "a"
      },
      {
        id: 7,
        text: "Which of the following is NOT a JavaScript framework or library?",
        options: [
          { id: "a", text: "React" },
          { id: "b", text: "Angular" },
          { id: "c", text: "Django" },
          { id: "d", text: "Vue" }
        ],
        correctOptionId: "c"
      },
      {
        id: 8,
        text: "What is the purpose of the 'viewport' meta tag in HTML?",
        options: [
          { id: "a", text: "To enhance SEO for the website" },
          { id: "b", text: "To control the layout on mobile browsers" },
          { id: "c", text: "To specify the character encoding for the HTML document" },
          { id: "d", text: "To define the default programming language for the website" }
        ],
        correctOptionId: "b"
      },
      {
        id: 9,
        text: "Which of the following is used to persist data in a web browser even after the browser is closed?",
        options: [
          { id: "a", text: "Cookies" },
          { id: "b", text: "Session Storage" },
          { id: "c", text: "Local Storage" },
          { id: "d", text: "Cache Storage" }
        ],
        correctOptionId: "c"
      },
      {
        id: 10,
        text: "What does the acronym 'SPA' stand for in web development?",
        options: [
          { id: "a", text: "Server Page Application" },
          { id: "b", text: "Single Page Application" },
          { id: "c", text: "Static Page Application" },
          { id: "d", text: "Server Process Application" }
        ],
        correctOptionId: "b"
      }
    ]
  };

  // Mock user data
  export const mockUser = {
    id: "user-123",
    name: "John Doe",
    email: "john.doe@example.com",
    examId: "WD-FUND-2023",
    profileImage: "https://randomuser.me/api/portraits/men/32.jpg"
  };
