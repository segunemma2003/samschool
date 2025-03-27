
import React, { useState } from 'react';
import { Download } from 'lucide-react';
import { generatePDF } from '../utils/pdfGenerator';
import { useToast } from "../hooks/use-toast";

const DownloadButton = () => {
  const [isGenerating, setIsGenerating] = useState(false);
  const { toast } = useToast();

  const handleDownload = async () => {
    // Prevent multiple clicks
    if (isGenerating) return;

    setIsGenerating(true);
    toast({
      title: "Processing",
      description: "Generating high-quality PDF with full content visibility, please wait...",
      variant: "default",
    });

    try {
      // Add a slight delay to ensure toast is shown
      await new Promise(resolve => setTimeout(resolve, 300));
      await generatePDF();
      toast({
        title: "Success",
        description: "PDF has been downloaded successfully with all content visible and high quality",
        variant: "default",
      });
    } catch (error) {
      console.error('Error in PDF generation:', error);
      toast({
        title: "Error",
        description: "Failed to generate PDF. Please try again.",
        variant: "destructive",
      });
    } finally {
      // Use setTimeout to ensure state isn't changed during render
      setTimeout(() => setIsGenerating(false), 500);
    }
  };

  return (
    <button
      className="download-btn"
      onClick={handleDownload}
      disabled={isGenerating}
      aria-label="Download as PDF"
    >
      {isGenerating ? (
        <span className="flex items-center gap-2">
          <svg className="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Creating High-Quality PDF...
        </span>
      ) : (
        <span className="flex items-center gap-2">
          <Download size={18} />
          Download Report
        </span>
      )}
    </button>
  );
};

export default DownloadButton;
