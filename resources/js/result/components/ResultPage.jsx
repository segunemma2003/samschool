
import React, { useState, useRef } from 'react';
import { Button } from "./ui/button";
import { Slider } from "./ui/slider";
import { useToast } from "./ui/use-toast";
import { Card } from "./ui/card";
import SchoolReport from './report-card/SchoolReport';
import { sampleReportData } from '../data/sampleReportData';
import '../styles/print.css';
import { Printer, Download } from "lucide-react";

const ResultPage = ({
  content,
  data,
  title = "NURSERY REPORT SHEET"
}) => {
  const [watermarkOpacity, setWatermarkOpacity] = useState(15);
  const [watermarkScale, setWatermarkScale] = useState(120);
  const resultPageRef = useRef(null);
  const { toast } = useToast();

  const handlePrint = () => {
    // toast({
    //   title: "Preparing document for printing",
    //   description: "Maintaining exactly the same layout as on screen"
    // });

    // Delay to ensure all styles are properly applied before printing
    setTimeout(() => {
      window.print();
    }, 500);
  };

  const handleDownload = () => {
    toast({
      title: "Feature coming soon",
      description: "PDF download will be available in the next update"
    });
  };

  // We're modifying the sample report data for shorter comments
  const modifiedReportData = {
    ...sampleReportData,
    comments: {
      ...sampleReportData.comments,
      teacher: "Good",
      headmaster: "Excellent",
      decision: "PASS",
      resumptionDate: "2025-01-06",
      otherCharges: "-",
      nextTermFees: "₦0"
    }
  };

//   const watermarkSrc = "https://lysacademy.com.ng/images/logo.png";
  const watermarkSrc =  `https://schoolcompasse.s3.us-east-1.amazonaws.com/${data.school.school_logo}`;
  return (
    <div className="min-h-screen px-4 py-6 bg-gray-50 sm:px-6 lg:px-8">
      <div className="max-w-4xl p-4 mx-auto mb-6 bg-white rounded-lg shadow no-print">
        <h2 className="mb-4 text-lg font-medium">Report Card Settings</h2>
        <div className="space-y-4">
          <div className="space-y-2">
            <label className="text-sm font-medium">Watermark Opacity: {watermarkOpacity}%</label>
            <Slider
              value={[watermarkOpacity]}
              onValueChange={(values) => setWatermarkOpacity(values[0])}
              min={5}
              max={30}
              step={1}
            />
          </div>
          <div className="space-y-2">
            <label className="text-sm font-medium">Watermark Size: {watermarkScale}%</label>
            <Slider
              value={[watermarkScale]}
              onValueChange={(values) => setWatermarkScale(values[0])}
              min={50}
              max={200}
              step={5}
            />
          </div>
          <div className="flex flex-wrap gap-4">
            <Button onClick={handlePrint} className="flex items-center gap-2">
              <Printer className="w-4 h-4" />
              Print Report Card
            </Button>
            <Button variant="outline" onClick={handleDownload} className="flex items-center gap-2">
              <Download className="w-4 h-4" />
              Download as PDF
            </Button>
          </div>
        </div>
      </div>

      <div className="relative max-w-4xl mx-auto" ref={resultPageRef}>
        <Card className="relative z-10 p-6 bg-white shadow-lg result-page-content print:shadow-none print:p-6">
          {content ? content : (
            <SchoolReport
              schoolLogoUrl={watermarkSrc}
              title={title}
              watermarkOpacity={watermarkOpacity}
              watermarkScale={watermarkScale}
              data={modifiedReportData}
              mdata={data}
            />
          )}
        </Card>
      </div>
    </div>
  );
};

export default ResultPage;
