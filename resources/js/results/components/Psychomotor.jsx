import React from 'react';

const Psychomotor = ({ psychomotorNormal }) => {
  if (!psychomotorNormal || psychomotorNormal.length === 0) {
    return null;
  }

  // Helper to get background color based on rating
  const getRatingColor = (rating) => {
    if (rating === 5 || rating === '5') return 'bg-green-100';
    if (rating === 4 || rating === '4') return 'bg-blue-50';
    if (rating === 3 || rating === '3') return 'bg-gray-50';
    if (rating === 2 || rating === '2') return 'bg-yellow-50';
    return 'bg-red-50';
  };

  // Helper to safely extract the rating
  const getRating = (item) => {
    // Check if the item has a direct rating property
    if (item.rating !== undefined) return item.rating;

    // Check if there's a nested psychomotor_student array with ratings
    if (item.psychomotor_student &&
        Array.isArray(item.psychomotor_student) &&
        item.psychomotor_student.length > 0 &&
        item.psychomotor_student[0].rating !== undefined) {
      return item.psychomotor_student[0].rating;
    }

    // Default value if no rating found
    return '-';
  };

  return (
    <div className="print:break-inside-avoid my-3">
      <h3 className="text-lg font-semibold mb-2 bg-report-blue/10 py-1 px-2 rounded text-report-dark">
        PSYCHOMOTOR DOMAIN
      </h3>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {psychomotorNormal.map((item, index) => {
          const rating = getRating(item);

          return (
            <div
              key={index}
              className={`bg-white rounded-lg shadow-sm p-4 border border-gray-200 ${getRatingColor(rating)}`}
            >
              <div className="flex justify-between items-center">
                <h4 className="font-medium text-report-dark">{item.skill}</h4>
                <span className="text-lg font-bold text-report-blue">{rating}</span>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default Psychomotor;