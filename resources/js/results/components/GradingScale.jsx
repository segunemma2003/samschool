
import React from 'react';

const GradingScale = ({ gradingScale }) => {
  return (
    <div className="print:break-inside-avoid my-3 animate-fade-in" style={{ animationDelay: '0.5s' }}>
      <div className="border border-gray-300 rounded overflow-hidden">
        <div className="bg-report-blue/10 py-2 px-3 border-b border-gray-300 font-semibold text-sm text-report-dark">
          Academic Grading Scale
        </div>
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-9 text-xs text-center">
          {gradingScale.map((item, index) => (
            <div 
              key={index} 
              className={`p-2 ${index < gradingScale.length - 1 ? 'border-r' : ''} ${index > 2 ? 'border-t' : ''} border-gray-300`}
            >
              <div className="font-semibold">{item.grade}</div>
              <div className="text-xxs my-1">{item.range}</div>
              <div className={`text-xxs ${
                item.description === 'Excellent' ? 'text-green-600' :
                item.description === 'Fail' ? 'text-red-600' : 'text-gray-600'
              }`}>
                {item.description}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default GradingScale;
