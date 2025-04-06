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
      <div className="flex flex-col items-center justify-between mb-2 md:flex-row">
        {/* School Logo */}
        <div className="flex-shrink-0 w-16 h-16 overflow-hidden bg-white rounded-full md:w-20 md:h-20 md:mr-4">
          {school.school_logo ? (
            <img
              src={getImageSrc(school.school_logo)}
              alt={`${school.name} Logo`}
              className="object-contain w-full h-full"
              onError={(e) => {
                e.target.onerror = null;
                e.target.src = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2YwZjBmMCIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOWE5YTlhIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIj5Mb2dvPC90ZXh0Pjwvc3ZnPg==";
              }}
            />
          ) : (
            <div className="flex items-center justify-center w-full h-full bg-gray-100">
              <span className="text-gray-400">Logo</span>
            </div>
          )}
        </div>

        {/* School Info - Now properly centered */}
        <div className="flex-grow mx-auto text-center">
          <h1 className="text-lg font-bold tracking-tight md:text-2xl lg:text-3xl">{school.school_name}</h1>
          <p className="text-xs md:text-sm opacity-90">{school.school_address}</p>
          <p className="text-xs italic md:text-sm">Motto: {school.mission??""}</p>
          <p className="text-xs opacity-80">
            Email: {school.email} {school.school_website && `URL: ${school.school_website}`}
          </p>
        </div>

        {/* Student Photo */}
        <div className="flex-shrink-0 hidden w-16 h-16 overflow-hidden bg-white rounded md:w-20 md:h-20 md:block">
          {student.data?.avatar ? (
            <img
              src={getImageSrc(student.data.avatar)}
              alt="Student Photo"
              className="object-cover w-full h-full"
              onError={(e) => {
                e.target.onerror = null;
                e.target.src = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2YwZjBmMCIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOWE5YTlhIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIj5QaG90bzwvdGV4dD48L3N2Zz4=";
              }}
            />
          ) : (
            <div className="flex items-center justify-center w-full h-full bg-gray-100">
              <span className="text-gray-400">Photo</span>
            </div>
          )}
        </div>
      </div>

      {/* Report Title */}
      <div className="py-1 text-center border-t border-b border-white/30">
        <h2 className="text-lg font-semibold tracking-wide md:text-xl lg:text-2xl">
          STUDENT REPORT SHEET
        </h2>
      </div>

      {/* Student Info */}
      <div className="grid grid-cols-2 gap-2 mt-3 text-sm md:grid-cols-3">
        <div className="flex flex-col">
          <div className="flex">
            <span className="mr-1 font-semibold">Name:</span>
            <span id="student-name">{student.name}</span>
          </div>
          <div className="flex">
            <span className="mr-1 font-semibold">Admission No:</span>
            <span>{student.admissionNo}</span>
          </div>
          <div className="flex">
            <span className="mr-1 font-semibold">Class:</span>
            <span>{student.class}</span>
          </div>
        </div>

        <div className="flex flex-col">
          <div className="flex">
            <span className="mr-1 font-semibold">Session:</span>
            <span>{student.session}</span>
          </div>
          <div className="flex">
            <span className="mr-1 font-semibold">Term:</span>
            <span>{student.term}</span>
          </div>
          <div className="flex">
            <span className="mr-1 font-semibold">Grade:</span>
            <span>{student.class.name}</span>
          </div>
        </div>

        <div className="col-span-2 md:col-span-1">
          <div className="overflow-hidden rounded bg-white/10">
            <div className="p-1 text-xs font-semibold text-center bg-white/20">
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
                  <td className="w-12 p-1 text-right">{(attendance.total_present/attendance.expected_present*100).toFixed(1) || "N/A"}%</td>
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
