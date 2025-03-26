import React from 'react';

const CommentsSection = ({ comments, decision, nextTerm, classTeacher, resultData, school }) => {
  return (
    <div className="print:break-inside-avoid my-3 animate-fade-in" style={{ animationDelay: '0.5s' }}>
      <h3 className="text-lg font-semibold mb-2 bg-report-blue/10 py-1 px-2 rounded text-report-dark">
        COMMENTS & DECISION
      </h3>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {/* Teacher's Comment */}
        <div className="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
          <h4 className="font-medium text-report-dark mb-2">Class Teacher's Comment</h4>
          <p className="text-gray-700">{comments.teacherComment}</p>
        </div>

        {/* Principal's Comment */}
        <div className="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
          <h4 className="font-medium text-report-dark mb-2">Principal's Comment</h4>
          <p className="text-gray-700">{comments.principalComment}</p>
        </div>
      </div>

      {/* Decision */}
      {/* <div className="mt-4 bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <h4 className="font-medium text-report-dark mb-2">Decision</h4>
        <p className="text-gray-700">{decision}</p>
      </div> */}

      {/* Next Term Date */}
      <div className="mt-4 bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <h4 className="font-medium text-report-dark mb-2">Next Term Begins</h4>
        <p className="text-gray-700">{nextTerm}</p>
      </div>

      {/* Signatures */}
      <div className="mt-6 grid grid-cols-2 gap-4">
        {/* Class Teacher Signature */}
        <div className="text-center">
          <div className="h-20 border-b border-gray-300 mb-2">

            {resultData.class.teacher.signature ? (
              <img
                src={resultData.class.teacher.signature.startsWith('data:image')
                  ? resultData.class.teacher.signature
                  : `https://schoolcompasse.s3.us-east-1.amazonaws.com/${resultData.class.teacher.signature}`}
                alt="Teacher's Signature"
                className="w-full h-full object-contain"
              />
            ) : (
              <div className="w-full h-full flex items-center justify-center">
                <span className="text-gray-400">Signature</span>
              </div>
            )}
          </div>
          <p className="font-medium text-report-dark">Class Teacher</p>
          <p className="text-sm text-gray-600">{classTeacher?.name}</p>
        </div>

        {/* Principal Signature */}
        <div className="text-center">
          <div className="h-20 border-b border-gray-300 mb-2">
            {school.principal_sign ? (
              <img
                src={school.principal_sign.startsWith('data:image')
                  ? school.principal_sign
                  : `https://schoolcompasse.s3.us-east-1.amazonaws.com/${school.principal_sign}`}
                alt="Principal's Signature"
                className="w-full h-full object-contain"
              />
            ) : (
              <div className="w-full h-full flex items-center justify-center">
                <span className="text-gray-400">Signature</span>
              </div>
            )}
          </div>
          <p className="font-medium text-report-dark">Principal</p>
        </div>
      </div>

      {/* School Stamp */}
      <div className="mt-4 text-center">
        <div className="h-24 mx-auto w-24 border rounded-full overflow-hidden">
          {school.school_stamp ? (
            <img
              src={school.school_stamp.startsWith('data:image')
                ? school.school_stamp
                : `https://schoolcompasse.s3.us-east-1.amazonaws.com/${school.school_stamp}`}
              alt="School Stamp"
              className="w-full h-full object-contain"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center bg-gray-100">
              <span className="text-gray-400 text-xs">School Stamp</span>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default CommentsSection;
