import React from 'react';
import Watermark from './Watermark';

const ReportHeader = ({ school, student, attendance, date, code }) => {
  // Helper function to handle image loading with proper attributes
  const getImageSrc = (imagePath) => {
    if (!imagePath) return null;
    return imagePath.startsWith('data:image')
      ? imagePath
      : `https://schoolcompasse.s3.us-east-1.amazonaws.com/${imagePath}`;
  };

  return (
    <div className="report-header print:break-inside-avoid animate-fade-in">
      {/* Watermark - visible only on print */}
      {/* <Watermark logoUrl={school.school_logo} opacity={0.05} /> */}

      {/* Date and Code */}
      <div className="flex justify-between text-xs text-white">
        <div>{date}</div>
        <div>{code}</div>
      </div>

      {/* School Header */}
      <div className="flex flex-col md:flex-row items-center justify-between mb-2">
        {/* School Logo */}
        <div className="w-16 h-16 md:w-20 md:h-20 md:mr-4 flex-shrink-0 bg-white rounded-full overflow-hidden">
          {school.school_logo ? (
            <img
              src={getImageSrc(school.school_logo)}
              alt={`${school.name} Logo`}
              className="w-full h-full object-contain"
              onError={(e) => {
                e.target.onerror = null;
                e.target.src = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2YwZjBmMCIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOWE5YTlhIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIj5Mb2dvPC90ZXh0Pjwvc3ZnPg==";
              }}
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center bg-gray-100">
              <span className="text-gray-400">Logo</span>
            </div>
          )}
        </div>

        {/* School Info - Now properly centered */}
        <div className="text-center flex-grow mx-auto">
          <h1 className="text-lg md:text-2xl lg:text-3xl font-bold tracking-tight">{school.school_name}</h1>
          <p className="text-xs md:text-sm opacity-90">{school.school_address}</p>
          {/* <p className="text-xs md:text-sm italic">Motto: {school.motto}</p> */}
          <p className="text-xs opacity-80">
            Email: {school.email} {school.school_website && `URL: ${school.school_website}`}
          </p>
        </div>

        {/* Student Photo */}
        <div className="w-16 h-16 md:w-20 md:h-20 rounded overflow-hidden hidden md:block flex-shrink-0 bg-white">
          {student.data?.avatar ? (
            <img
              src={getImageSrc(student.data.avatar)}
              alt="Student Photo"
              className="w-full h-full object-cover"
              onError={(e) => {
                e.target.onerror = null;
                e.target.src = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2YwZjBmMCIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOWE5YTlhIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIj5QaG90bzwvdGV4dD48L3N2Zz4=";
              }}
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center bg-gray-100">
              <span className="text-gray-400">Photo</span>
            </div>
          )}
        </div>
      </div>

      {/* Report Title */}
      <div className="text-center py-1 border-t border-b border-white/30">
        <h2 className="text-lg md:text-xl lg:text-2xl font-semibold tracking-wide">
          STUDENT REPORT SHEET
        </h2>
      </div>

      {/* Student Info */}
      <div className="grid grid-cols-2 md:grid-cols-3 gap-2 mt-3 text-sm">
        <div className="flex flex-col">
          <div className="flex">
            <span className="font-semibold mr-1">Name:</span>
            <span id="student-name">{student.name}</span>
          </div>
          <div className="flex">
            <span className="font-semibold mr-1">Admission No:</span>
            <span>{student.admissionNo}</span>
          </div>
          <div className="flex">
            <span className="font-semibold mr-1">Class:</span>
            <span>{student.class}</span>
          </div>
        </div>

        <div className="flex flex-col">
          <div className="flex">
            <span className="font-semibold mr-1">Session:</span>
            <span>{student.session}</span>
          </div>
          <div className="flex">
            <span className="font-semibold mr-1">Term:</span>
            <span>{student.term}</span>
          </div>
          <div className="flex">
            <span className="font-semibold mr-1">Grade:</span>
            <span>{student.class.name}</span>
          </div>
        </div>

        <div className="col-span-2 md:col-span-1">
          <div className="bg-white/10 rounded overflow-hidden">
            <div className="text-center font-semibold text-xs p-1 bg-white/20">
              ATTENDANCE
            </div>
            <table className="w-full text-xs">
              <tbody>
                <tr className="border-b border-white/20">
                  <td className="p-1 font-medium">Days School Open</td>
                  <td className="p-1 text-right">{attendance.expected_present || "N/A"}</td>
                </tr>
                <tr className="border-b border-white/20">
                  <td className="p-1 font-medium">Days Present</td>
                  <td className="p-1 text-right">{attendance.total_present || "N/A"}</td>
                  <td className="p-1 text-right w-12">{(attendance.total_present/attendance.expected_present*100).toFixed(1) || "N/A"}%</td>
                </tr>
                <tr>
                  <td className="p-1 font-medium">Days Absent</td>
                  <td className="p-1 text-right">{attendance.total_absent || "N/A"}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ReportHeader;