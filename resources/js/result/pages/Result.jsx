import React, { useEffect, useState } from "react";
import ResultPage from '../components/ResultPage';
import { Button } from '../components/ui/button';
import { Link } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';

import '../styles/print.css';

const Result = () => {
    const [data, setData] = useState(null);

     useEffect(() => {
         // Try to get data from localStorage, fall back to dummy data
         try {
           // Get data from localStorage
           const student = JSON.parse(localStorage.getItem('student') || '{}');
           const scoreData = JSON.parse(localStorage.getItem('scoreData') || '{}');
           const totalHeadings = JSON.parse(localStorage.getItem('totalHeadings') || '[]');
           const percent = JSON.parse(localStorage.getItem('percent') || '0');
           const groupedHeadings = JSON.parse(localStorage.getItem('groupedHeadings') || '{}');
           const headings = JSON.parse(localStorage.getItem('headings') || '[]');
           const psychomotorAffective = JSON.parse(localStorage.getItem('psychomotorAffective') || '[]');
           const psychomotorNormal = JSON.parse(localStorage.getItem('psychomotorNormal') || '[]');
           const termAndAcademy = JSON.parse(localStorage.getItem('termAndAcademy') || '{}');
           const relatedData = JSON.parse(localStorage.getItem('relatedData') || '{}');
           const resultData = JSON.parse(localStorage.getItem('resultData') || '{}');

           // Get markObtained from groupedHeadings
           const markObtained = [
             ...(groupedHeadings.input || []),
             ...(groupedHeadings.total || [])
           ];

           // Transform the data to match the expected format
           const transformedData = {
             school: relatedData.school || {},
             classTeacher: resultData.class?.teacher,
             courses: resultData.courses,
             student: {
               data: student,
               name: student.name || '',
               admissionNo: student.registration_number || '',
               class: student.class?.name || '',
               session: termAndAcademy.academy?.title || '',
               term: termAndAcademy.term?.name || '',
               grade: scoreData.grade || ''
             },
             totalHeadings: totalHeadings,
             psychomotorNormal: psychomotorNormal.map(item => ({
               ...item,
               rating: item.psychomotor_student?.[0]?.rating || '-'
             })),
             attendance: relatedData.studentAttendance || {},
             markObtained: resultData.markObtained,
             studentSummary: resultData.studentSummary,
             termSummary: resultData.termSummary,
             remarks: resultData.remarks,
             subjects: headings.map(heading => {
               const subjectData = {
                 name: heading.name,
                 scoreBoard: heading.scoreBoard || []
               };
               return subjectData;
             }),
             analysis: {
               totalScore: scoreData.totalScore || 0,
               percentage: percent || 0,
               position: scoreData.position || '',
               grade: scoreData.grade || '',
               marksObtainable: (resultData.totalSubject || 0) * 100,
               classAverage: resultData.classAverage || 0,
               studentAverage: percent || 0
             },
             affectiveDomain: psychomotorAffective.map(item => ({
               ...item,
               rating: item.psychomotor_student?.[0]?.rating || '-'
             })),
             comments: {
               teacherComment: relatedData.studentComment?.comment || '',
               principalComment: resultData.principalComment || ''
             },
             decision: scoreData.decision || '',
             nextTerm: relatedData.school?.next_term_begins || '',
             date: new Date().toLocaleDateString(),
             code: student.registration_number || '',
             resultData: resultData
           };

           setData(transformedData);
         } catch (error) {
           console.error('Error fetching data from localStorage', error);
           // Fallback to dummy data if there's an error
           setData(reportData);
         }
       }, []);

       if (!data) {
         return (
           <div className="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 py-8 px-4 flex items-center justify-center">
             <div className="text-center">
               <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Loading...</h2>
               <p className="text-gray-600 dark:text-gray-400">Please wait while we load your report card.</p>
             </div>
           </div>
         );
       }
  return (
    <div className="result-container">
      <div className="no-print p-4">
        {/* <Link to="/">
          <Button variant="outline" size="sm" className="flex items-center gap-2">
            <ArrowLeft className="h-4 w-4" /> Back to Home
          </Button>
        </Link> */}
      </div>

      <ResultPage
        title={data.student.class}
        data={data}
      />
    </div>
  );
};

export default Result;
