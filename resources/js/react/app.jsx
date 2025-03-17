import React from "react";
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
    return <Exam examId={examId} />; // Pass to Exam component
  };

  const App = () => (
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <Toaster />
        <Sonner />
        <HashRouter> {/* HashRouter keeps routes inside `#/` */}
          <ExamProvider>
            <Routes>
              <Route path="/" element={<Index />} />
              <Route path="/exam" element={<ExamWrapper />} />
              <Route path="/summary" element={<Summary />} />
              <Route path="/results" element={<Results />} />
              <Route path="*" element={<NotFound />} />
            </Routes>
          </ExamProvider>
        </HashRouter>
      </TooltipProvider>
    </QueryClientProvider>
  );

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