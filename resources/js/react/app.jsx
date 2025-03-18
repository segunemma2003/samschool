import React, { useEffect } from "react";
import { Toaster } from "./components/ui/toaster";
import { createRoot } from 'react-dom/client';
import { Toaster as Sonner } from "./components/ui/sonner";
import { TooltipProvider } from "./components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
// import { BrowserRouter, Routes, Route } from "react-router-dom";
import { HashRouter, Routes, Route } from "react-router-dom";
import { ExamProvider } from "./components/hooks/useExam";
import Index from "./components/pages/Index";
import Exam from "./components/pages/Exam";
import Summary from "./components/pages/Summary";
import Results from "./components/pages/Results";
import NotFound from "./components/pages/NotFound";
import "../../css/app.css";
import './src/App.css';
import './src/react.css';

const queryClient = new QueryClient();

const getExamId = () => {
    const element = document.getElementById("quiz-root");
    return element ? element.getAttribute("data-exam-id") : null;
  };

  const ExamWrapper = () => {
    const examId = getExamId(); // Get exam ID from Laravel
    console.log(examId);
    return <Exam examId={examId} />; // Pass to Exam component
  };

  const App = () => {
    useEffect(() =>{
console.log(window.exam);
    if (window.student) localStorage.setItem('student', JSON.stringify(window.student));
  if (window.exam) localStorage.setItem('exam', JSON.stringify(window.exam));
  if (window.term) localStorage.setItem('term', JSON.stringify(window.term));
  if (window.academy) localStorage.setItem('academy', JSON.stringify(window.academy));
  if (window.course) localStorage.setItem('course', JSON.stringify(window.course));
  if (window.questions) localStorage.setItem('questions', JSON.stringify(window.questions));
  if (window.answers) localStorage.setItem('answers', JSON.stringify(window.answers));
  if (window.quizScore) localStorage.setItem('quizScore', JSON.stringify(window.quizScore));
    }, []);

    return (
        <QueryClientProvider client={queryClient}>
          <TooltipProvider>
            <Toaster />
            <Sonner />
            <HashRouter>
              <ExamProvider>
                <Routes>
                  <Route path="/" element={<Index />} />
                  <Route path="/exam" element={<Exam examId={getExamId()} />} />
                  <Route path="/summary" element={<Summary />} />
                  <Route path="/results" element={<Results />} />
                  <Route path="*" element={<NotFound />} />
                </Routes>
              </ExamProvider>
            </HashRouter>
          </TooltipProvider>
        </QueryClientProvider>
      );
  };

  // Mount React inside Filament Livewire Page
  document.addEventListener("DOMContentLoaded", () => {
      const element = document.getElementById("quiz-root");
      if (element) {
          const root = createRoot(element);
          root.render(
            <React.StrictMode>
              <App />
            </React.StrictMode>
          );
      }
  });