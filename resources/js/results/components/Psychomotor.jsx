import React from 'react';

const Psychomotor = ({ psychomotorNormal }) => {
  return (
    <div className="print:break-inside-avoid my-3 animate-fade-in" style={{ animationDelay: '0.4s' }}>
      <h3 className="text-lg font-semibold mb-2 bg-report-blue/10 py-1 px-2 rounded text-report-dark">
        PSYCHOMOTOR DOMAIN
      </h3>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {psychomotorNormal.map((item, index) => (
          <div
            key={index}
            className="bg-white rounded-lg shadow-sm p-4 border border-gray-200"
            style={{ animationDelay: `${index * 0.05}s` }}
          >
            <div className="flex justify-between items-center">
              <h4 className="font-medium text-report-dark">{item.skill}</h4>
              <span className="text-lg font-bold text-report-blue">{item.psychomotor_student[0].rating}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default Psychomotor;
