
import React from 'react';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "../ui/table";

const SubjectsAnalysis = ({resultData, markObtained, studentSummary, termSummary, remarks, analysis, subjects, watermarkUrl, watermarkOpacity, watermarkScale }) => {

    const getScore = (course, markId) => {
        if (!course || !course.score_board) return 'N/A';

        const score = course.score_board.find(score =>
          score.result_section_type_id === markId
        );

        return score ? score.score : 'N/A';
      };
console.log(resultData);
      // Ensure we have all required data
      if (!resultData || !resultData.courses) {
        return (
          <div className="my-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
            <p className="text-yellow-800">No subject data available</p>
          </div>
        );
      }

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
              <TableHead className="border border-gray-300 p-0.5 text-center" rowSpan={2}>SUBJECTS</TableHead>
                {markObtained && Object.keys(markObtained).length > 0 && (
                     <TableHead className="border border-gray-300 p-0.5 text-center" colSpan={Object.keys(markObtained).length}> MARKS OBTAINED</TableHead>
                  )}
                  {studentSummary && Object.keys(studentSummary).length > 0 && Object.values(studentSummary).map((mark) => (
                     <TableHead  key={mark.id} className="border border-gray-300 p-0.5 text-center" rowSpan={2}> {mark.name}</TableHead>
                  ))}
                  {termSummary && Object.keys(termSummary).length > 0 &&  (
                     <TableHead className="border border-gray-300 p-0.5 text-center" colSpan={Object.keys(termSummary).length}>Term Summary</TableHead>
                  )}
                  {remarks && Object.keys(remarks).length > 0 && (
                    <TableHead className="border border-gray-300 p-0.5 text-center" colSpan={Object.keys(remarks).length} rowSpan={2}>Remarks</TableHead>
                  )}
            </TableRow>
            <TableRow>
                 {markObtained && Object.keys(markObtained).length > 0 && Object.values(markObtained).map((heading) => (
                     <TableHead key={heading.id} className="border border-gray-300 p-0.5 text-center">{heading.name}</TableHead>
                  ))}

                {termSummary && Object.keys(termSummary).length > 0 && Object.values(termSummary).map((heading) => (
                    <TableHead key={heading.id} className="border border-gray-300 p-0.5 text-center">{heading.name}</TableHead>
                    ))}

                {/* {remarks && Object.keys(remarks).length > 0 && Object.values(remarks).map((heading) => (
                    <TableHead key={heading.id} className="border border-gray-300 p-0.5 text-center">{heading.name}</TableHead>
                    ))} */}
            </TableRow>
          </TableHeader>
          <TableBody>
            {resultData.courses.map((course, index) => (
              <TableRow key={course.id || index}>
                <TableCell className="border border-gray-300 p-0.5">{course.subject?.subject_depot?.name || 'Unknown Subject'}</TableCell>
                {markObtained && Object.keys(markObtained).length > 0 && Object.values(markObtained).map((heading) => (
                      <TableCell key={heading.id} className="border border-gray-300 p-0.5 text-center">{getScore(course, heading.id)}</TableCell>
                    ))}

            {studentSummary && Object.keys(studentSummary).length > 0 && Object.values(studentSummary).map((heading) => (
                      <TableCell key={heading.id} className="border border-gray-300 p-0.5 text-center">{getScore(course, heading.id)}</TableCell>
                    ))}
                    {termSummary && Object.keys(termSummary).length > 0 && Object.values(termSummary).map((heading) => (
                      <TableCell key={heading.id} className="border border-gray-300 p-0.5 text-center">{getScore(course, heading.id)}</TableCell>
                    ))}

                    {remarks && Object.keys(remarks).length > 0 && Object.values(remarks).map((heading) => (
                      <TableCell key={heading.id} className="border border-gray-300 p-0.5 text-center">{getScore(course, heading.id)}</TableCell>
                    ))}
                {/* <TableCell className="border border-gray-300 p-0.5 text-center">{subject.ca1}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.ca2}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.ca3}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.exam}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center font-bold">{subject.total}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.grade}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.classAverage}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.lowestScore}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.highestScore}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.position}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{subject.remark}</TableCell> */}
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>
    </div>
  );
};

export default SubjectsAnalysis;
