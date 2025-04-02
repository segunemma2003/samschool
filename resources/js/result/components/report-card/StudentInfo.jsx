
import React from 'react';

const StudentInfo = ({
  name,
  admissionNo,
  className,
  session,
  term,
  grade,
  attendanceData
}) => {
  return (
    <div className="flex justify-between items-start mb-2 student-info-section">
      {/* Left: Student Details */}
      <div className="w-1/3 text-xs">
        <div className="h-full flex flex-col justify-between" style={{ minHeight: '80px' }}>
          <div className="space-y-2">
            <div className="flex">
              <span className="font-bold w-24">Name:</span>
              <span>{name}</span>
            </div>
            <div className="flex">
              <span className="font-bold w-24">Admission No:</span>
              <span>{admissionNo}</span>
            </div>
            <div className="flex">
              <span className="font-bold w-24">Class:</span>
              <span>{className}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Middle: Session, Term, Grade - Text aligned to left */}
      <div className="w-1/3 text-xs">
        <div className="h-full flex flex-col justify-between" style={{ minHeight: '80px' }}>
          <div className="space-y-2">
            <div className="flex">
              <span className="font-bold w-24">Session:</span>
              <span>{session}</span>
            </div>
            <div className="flex">
              <span className="font-bold w-24">Term:</span>
              <span>{term}</span>
            </div>
            <div className="flex">
              <span className="font-bold w-24">Grade:</span>
              <span>{grade}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Right: Attendance Table with added % column */}
      <div className="w-1/3 flex items-center justify-end">
        <div className="border border-gray-300 rounded w-4/5">
          <table className="w-full text-[8px] attendance-table">
            <thead>
              <tr>
                <th colSpan={3} className="border border-gray-300 bg-gray-100 text-center p-1 text-[8px]">ATTENDANCE</th>
              </tr>
            </thead>
            <tbody className="text-[8px]">
              <tr>
                <td className="border border-gray-300 p-1">Days School Open</td>
                <td className="border border-gray-300 p-1 text-center">{attendanceData.expected_present}</td>
                <td rowSpan={3} className="border border-gray-300 p-1 text-center align-middle">{(attendanceData.total_present/attendanceData.expected_present).toFixed(1)||"N/A"}%</td>
              </tr>
              <tr>
                <td className="border border-gray-300 p-1">Days Present</td>
                <td className="border border-gray-300 p-1 text-center">{attendanceData.total_present}</td>
              </tr>
              <tr>
                <td className="border border-gray-300 p-1">Days Absent</td>
                <td className="border border-gray-300 p-1 text-center">{attendanceData.total_absent}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default StudentInfo;
