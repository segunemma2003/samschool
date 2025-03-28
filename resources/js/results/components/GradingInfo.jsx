import React from 'react';
import { reportData } from '../utils/dummyData';

const GradingInfo = () => {
  const { gradingScale, keyToRatings } = reportData;

  return (
    <div className="mt-4">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {/* Grading Keys */}
        <div className="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
          <h3 className="text-sm font-semibold mb-2 text-gray-700">Grading Keys</h3>
          <div className="grid grid-cols-2 gap-2 text-xs">
            {keyToRatings.map((rating) => (
              <div key={rating.key} className="flex items-center space-x-1">
                <span className="font-medium">{rating.key}.</span>
                <span>{rating.description}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Academic Grading Scale */}
        <div className="bg-white rounded-lg shadow-sm p-3 border border-gray-200">
          <h3 className="text-sm font-semibold mb-2 text-gray-700">Academic Grading Scale</h3>
          <div className="grid grid-cols-2 gap-2 text-xs">
            {gradingScale.map((grade) => (
              <div key={grade.grade} className="flex items-center space-x-1">
                <span className="font-medium">{grade.grade}:</span>
                <span>{grade.range} - {grade.description}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default GradingInfo;