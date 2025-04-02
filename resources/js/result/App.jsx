
import { Toaster } from "./components/ui/toaster";
import { Toaster as Sonner } from "./components/ui/sonner";
import { TooltipProvider } from "./components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
// import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { HashRouter, Routes, Route , useLocation, Navigate} from "react-router-dom";
import Index from "./pages/Index";
import React, { useEffect } from "react";
import Result from "./pages/Result";
import NotFound from "./pages/NotFound";

import { createRoot } from 'react-dom/client'
import './index.css'

const queryClient = new QueryClient();


const RouteObserver = () => {
// clearAllExamData();

    console.log(window.scoreData);
    if (window.student) localStorage.setItem('student', JSON.stringify(window.student));
  if (window.scoreData) localStorage.setItem('scoreData', JSON.stringify(window.scoreData));
  if (window.totalHeadings) localStorage.setItem('totalHeadings', JSON.stringify(window.totalHeadings));
  if (window.percent) localStorage.setItem('percent', JSON.stringify(window.percent));
  if (window.groupedHeadings) localStorage.setItem('groupedHeadings', JSON.stringify(window.groupedHeadings));
  if (window.headings) localStorage.setItem('headings', JSON.stringify(window.headings));
  if (window.psychomotorAffective) localStorage.setItem('psychomotorAffective', JSON.stringify(window.psychomotorAffective));
  if (window.psychomotorNormal) localStorage.setItem('psychomotorNormal', JSON.stringify(window.psychomotorNormal));
  if (window.termAndAcademy) localStorage.setItem('termAndAcademy', JSON.stringify(window.termAndAcademy));
  if (window.relatedData) localStorage.setItem('relatedData', JSON.stringify(window.relatedData));
  if (window.resultData) localStorage.setItem('resultData', JSON.stringify(window.resultData));
    const location = useLocation();

    useEffect(() => {
      // If navigating to index, set flag to check if data refresh needed
      if (location.pathname === '/') {
        // This flag will be checked by the Index component
        window.dataRefreshNeeded = true;
      }
    }, [location]);

    return null;
  };

const App = () => {
    useEffect(() =>{
        console.log(window.scoreData);
        if (window.student) localStorage.setItem('student', JSON.stringify(window.student));
      if (window.scoreData) localStorage.setItem('scoreData', JSON.stringify(window.scoreData));
      if (window.totalHeadings) localStorage.setItem('totalHeadings', JSON.stringify(window.totalHeadings));
      if (window.percent) localStorage.setItem('percent', JSON.stringify(window.percent));
      if (window.groupedHeadings) localStorage.setItem('groupedHeadings', JSON.stringify(window.groupedHeadings));
      if (window.headings) localStorage.setItem('headings', JSON.stringify(window.headings));
      if (window.psychomotorAffective) localStorage.setItem('psychomotorAffective', JSON.stringify(window.psychomotorAffective));
      if (window.psychomotorNormal) localStorage.setItem('psychomotorNormal', JSON.stringify(window.psychomotorNormal));
      if (window.termAndAcademy) localStorage.setItem('termAndAcademy', JSON.stringify(window.termAndAcademy));
      if (window.relatedData) localStorage.setItem('relatedData', JSON.stringify(window.relatedData));
      if (window.resultData) localStorage.setItem('resultData', JSON.stringify(window.resultData));
     },[]);

 return(

 <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner />
      <HashRouter>
      <RouteObserver />
        <Routes>
          {/* Redirect from root to result page */}
          <Route path="/" element={<Navigate to="/result" replace />} />
          <Route path="/index" element={<Navigate to="/result" replace />} />
          <Route path="/result" element={<Result />} />
          {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
          <Route path="*" element={<NotFound />} />
        </Routes>
        </HashRouter>
    </TooltipProvider>
  </QueryClientProvider>
);
}



// Mount React inside Filament Livewire Page
  document.addEventListener("DOMContentLoaded", () => {
      const element = document.getElementById("result-root");
      if (element) {
          const root = createRoot(element);
          root.render(
            <React.StrictMode>
              <App />
            </React.StrictMode>
          );
      }
  });
