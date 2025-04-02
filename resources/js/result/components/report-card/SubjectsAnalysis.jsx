
import React from 'react';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/table";

const SubjectsAnalysis = ({ subjects, watermarkUrl, watermarkOpacity, watermarkScale }) => {
  return (
    <div className="relative mt-2 mb-2">
      {/* Watermark */}
      <div 
        className="absolute inset-0 z-0 flex items-center justify-center overflow-hidden pointer-events-none"
        style={{ opacity: watermarkOpacity / 100 }}
      >
        <img 
          src={watermarkUrl} 
          alt="Watermark" 
          className="object-contain max-w-full max-h-full opacity-50"
          style={{ 
            transform: `scale(${watermarkScale / 100})`,
            position: 'absolute',
            top: '40%',
            left: '50%',
            transform: `translate(-50%, -50%) scale(${watermarkScale / 100})`,
          }}
        />
      </div>

      {/* Subjects Table */}
      <div className="relative z-10">
        <h2 className="text-[12px] font-bold mb-0.5 text-center">SUBJECTS ANALYSIS</h2>
        <Table className="w-full border-collapse text-[10px]">
          <TableHeader>
            <TableRow>
              <TableHead className="border border-gray-300 p-0.5 text-center">SUBJECTS</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">CA1 10%</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">CA2 10%</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">CA3 10%</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">EXAM 70%</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">TOTAL SCORE</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">GRADE</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">CLASS AVG</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">LOW. SCORE</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">HIGH. SCORE</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">POSITION</TableHead>
              <TableHead className="border border-gray-300 p-0.5 text-center">REMARK</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {subjects.map((subject, index) => (
              <TableRow key={index}>
                <TableCell className="border border-gray-300 p-0.5">{subject.name}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.ca1}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.ca2}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.ca3}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.exam}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center font-bold">{subject.total}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.grade}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.classAverage}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.lowestScore}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.highestScore}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.position}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.remark}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>
    </div>
  );
};

export default SubjectsAnalysis;
