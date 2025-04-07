
import React from 'react';

const StudentInfo = ({
  name,
  admissionNo,
  className,
  session,
  term,

  school,
 position,
  attendanceData
}) => {
  return (
    <div className="flex items-start justify-between mb-2 student-info-section">
      {/* Left: Student Details */}
      <div className="w-1/3 text-xs">
        <div className="flex flex-col justify-between h-full" style={{ minHeight: '80px' }}>
          <div className="space-y-2">
            <div className="flex">
              <span className="w-24 font-bold">Name:</span>
              <span>{name}</span>
            </div>
            <div className="flex">
              <span className="w-24 font-bold">Admission No:</span>
              <span>{admissionNo}</span>
            </div>
            <div className="flex">
              <span className="w-24 font-bold">Class:</span>
              <span>{className}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Middle: Session, Term, Grade - Text aligned to left */}
      <div className="w-1/3 text-xs">
        <div className="flex flex-col justify-between h-full" style={{ minHeight: '80px' }}>
          <div className="space-y-2">
            <div className="flex">
              <span className="w-24 font-bold">Session:</span>
              <span>{session}</span>
            </div>
            <div className="flex">
              <span className="w-24 font-bold">Term:</span>
              <span>{term}</span>
            </div>
            {school.activate_position == "yes" && (<div className="flex">
              <span className="w-24 font-bold">Position:</span>
              <span>{position}</span>
            </div>
            )}
          </div>
        </div>
      </div>

      {/* Right: Attendance Table with added % column */}
      <div className="flex items-center justify-end w-1/3">
        <div className="w-4/5 border border-gray-300 rounded">
          <table className="w-full text-[8px] attendance-table">
            <thead>
              <tr>
                <th colSpan={3} className="border border-gray-300 bg-gray-100 text-center p-1 text-[8px]">ATTENDANCE</th>
              </tr>
            </thead>
            <tbody className="text-[8px]">
              <tr>
                <td className="p-1 border border-gray-300">Days School Open</td>
                <td className="p-1 text-center border border-gray-300">{attendanceData.expected_present}</td>
                <td rowSpan={3} className="p-1 text-center align-middle border border-gray-300">{((attendanceData.total_present/attendanceData.expected_present)*100).toFixed(1)||"N/A"}%</td>
              </tr>
              <tr>
                <td className="p-1 border border-gray-300">Days Present</td>
                <td className="p-1 text-center border border-gray-300">{attendanceData.total_present}</td>
              </tr>
              <tr>
                <td className="p-1 border border-gray-300">Days Absent</td>
                <td className="p-1 text-center border border-gray-300">{attendanceData.total_absent}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default StudentInfo;
