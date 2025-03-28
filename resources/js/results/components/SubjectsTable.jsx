import React from 'react';

const SubjectsTable = ({ resultData, markObtained, studentSummary, termSummary, remarks, analysis }) => {
  // Safely get score from the score_board
  const getScore = (course, markId) => {
    if (!course || !course.score_board) return 'N/A';

    const score = course.score_board.find(score =>
      score.result_section_type_id === markId
    );

    return score ? score.score : 'N/A';
  };

  // Ensure we have all required data
  if (!resultData || !resultData.courses) {
    return (
      <div className="my-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
        <p className="text-yellow-800">No subject data available</p>
      </div>
    );
  }

  return (
    <div className="print:break-inside-avoid my-3">
      <h3 className="text-lg font-semibold mb-2 bg-report-blue/10 py-1 px-2 rounded text-report-dark">
        SUBJECTS ANALYSIS
      </h3>

      <div className="overflow-x-auto">
        <div className="min-w-full inline-block align-middle">
          <div className="overflow-hidden border border-gray-400 rounded">
            <table className="min-w-full divide-y divide-gray-400 report-table">
              <thead>
                <tr className="bg-gray-200 text-base">
                  <th className="px-2 py-1 border border-gray-400 whitespace-nowrap" rowSpan="2" style={{ minWidth: '150px' }}>
                    SUBJECTS
                  </th>

                  {markObtained && Object.keys(markObtained).length > 0 && (
                    <th className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center" colSpan={Object.values(markObtained).length}>
                      MARKS OBTAINED
                    </th>
                  )}

                  {studentSummary && Object.keys(studentSummary).length > 0 && Object.values(studentSummary).map((mark) => (
                    <th key={mark.id} className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center" rowSpan="2" style={{ minWidth: '100px' }}>
                      {mark.name}
                    </th>
                  ))}

                  {termSummary && Object.keys(termSummary).length > 0 &&  (
                    <th className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center" colSpan={Object.values(termSummary).length}>
                      TERM SUMMARY
                    </th>
                  )}

                  {remarks && Object.keys(remarks).length > 0 && (
                    <th className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center" rowSpan="2" style={{ minWidth: '100px' }}>
                      Remarks
                    </th>
                  )}

                  <th className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center" rowSpan="2" style={{ minWidth: '80px' }}>
                    Sign.
                  </th>
                </tr>
                <tr className="bg-gray-200">
                  {markObtained && Object.keys(markObtained).length > 0 && Object.values(markObtained).map((heading) => (
                    <th key={heading.id} className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center" style={{ minWidth: '80px' }}>
                      {heading.name}
                    </th>
                  ))}

                  {termSummary && Object.keys(termSummary).length > 0 && Object.values(termSummary).map((heading) => (
                    <th key={heading.id} className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center" style={{ minWidth: '80px' }}>
                      {heading.name}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-400">
                {resultData.courses.map((course, index) => (
                  <tr key={course.id || index} className="hover:bg-gray-50">
                    <td className="px-2 py-1 border border-gray-400 whitespace-nowrap font-medium text-left">
                      {course.subject?.subject_depot?.name || 'Unknown Subject'}
                    </td>

                    {markObtained && Object.keys(markObtained).length > 0 && Object.values(markObtained).map((heading) => (
                      <td key={heading.id} className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center">
                        {getScore(course, heading.id)}
                      </td>
                    ))}

                    {studentSummary && Object.keys(studentSummary).length > 0 && Object.values(studentSummary).map((heading) => (
                      <td key={heading.id} className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center">
                        {getScore(course, heading.id)}
                      </td>
                    ))}

                    {termSummary && Object.keys(termSummary).length > 0 && Object.values(termSummary).map((heading) => (
                      <td key={heading.id} className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center">
                        {getScore(course, heading.id)}
                      </td>
                    ))}

                    {remarks && Object.keys(remarks).length > 0 && Object.values(remarks).map((heading) => (
                      <td key={heading.id} className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center">
                        {getScore(course, heading.id)}
                      </td>
                    ))}

                    <td className="px-2 py-1 border border-gray-400 whitespace-nowrap text-center">
                      {course.subject?.teacher?.signature && (
                        <img
                          width="20"
                          height="20"
                          src={course.subject.teacher.signature.startsWith('data:image')
                            ? course.subject.teacher.signature
                            : `https://schoolcompasse.s3.us-east-1.amazonaws.com/${course.subject.teacher.signature}`}
                          alt="Teacher Signature"
                          className="mx-auto rounded-md w-[20px] h-[20px] object-cover"
                        />
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div className="mt-3 border border-gray-300 rounded">
        <div className="bg-report-blue/10 py-2 px-3 border-b border-gray-300 font-semibold text-sm text-report-dark">
          ANALYSIS
        </div>
        <div className="grid grid-cols-3 md:grid-cols-6 text-sm">
          <div className="p-2 border-r border-gray-300">
            <span className="font-medium text-xs">Subjects Offered:</span>
            <span className="block text-center font-bold">{resultData.totalSubject || 'N/A'}</span>
          </div>
          <div className="p-2 border-r border-gray-300">
            <span className="font-medium text-xs">Marks Obtained:</span>
            <span className="block text-center font-bold">{resultData.totalScore || 'N/A'}</span>
          </div>
          <div className="p-2 border-r border-gray-300 md:border-r-0">
            <span className="font-medium text-xs">Marks Obtainable:</span>
            <span className="block text-center font-bold">{analysis?.marksObtainable || 'N/A'}</span>
          </div>
          <div className="p-2 border-r border-gray-300 border-t md:border-t-0">
            <span className="font-medium text-xs">Class Average:</span>
            <span className="block text-center font-bold">{analysis?.classAverage || 'N/A'}</span>
          </div>
          <div className="p-2 border-r border-gray-300 border-t md:border-t-0 col-span-2">
            <span className="font-medium text-xs">Student's Average:</span>
            <span className="block text-center font-bold">{analysis?.studentAverage || 'N/A'}</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SubjectsTable;