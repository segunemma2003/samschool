import React from 'react';
import ReportHeader from './ReportHeader';
import SubjectsTable from './SubjectsTable';
import AffectiveDomain from './AffectiveDomain';
import CommentsSection from './CommentsSection';
import GradingInfo from './GradingInfo';
import Psychomotor from './Psychomotor';

const ReportCard = ({ data }) => {
  return (
    <div id="report-card" className="report-container">
      <ReportHeader
        school={data.school}
        student={data.student}
        attendance={data.attendance}
        date={data.date}
        code={data.code}
      />

      <div className="p-2 md:p-4 bg-white">
        <SubjectsTable
          resultData={data.resultData}
          markObtained={data.markObtained}
          studentSummary={data.studentSummary}
          termSummary={data.termSummary}
          remarks={data.remarks}
          analysis={data.analysis}
        />

        {/* Psychomotor and Affective Domain side by side */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <AffectiveDomain affectiveDomain={data.affectiveDomain} />
          <Psychomotor psychomotorNormal={data.psychomotorNormal} />
        </div>

        {/* Grading Info */}
        <GradingInfo />

        {/* Promotion Status (only if present and not null) */}
        {data.resultData && data.resultData.promoted !== null && (
          <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded animate-fade-in">
            <h3 className="text-lg font-semibold text-blue-900 mb-2">Promotion Status</h3>
            <div className="flex flex-col md:flex-row md:items-center md:space-x-6">
              <span className="text-base font-bold">
                {data.resultData.promoted ? (
                  <span className="text-green-600">Promoted</span>
                ) : (
                  <span className="text-red-600">Not Promoted</span>
                )}
              </span>
              {data.resultData.promotionCriteria && (
                <div className="text-sm text-blue-900 mt-2 md:mt-0">
                  <div>Criteria:</div>
                  <ul className="list-disc list-inside ml-2">
                    <li>Minimum Average: <span className="font-semibold">{data.resultData.promotionCriteria.min_average}</span></li>
                    {data.resultData.promotionCriteria.required_subjects && data.resultData.promotionCriteria.required_subjects.length > 0 && (
                      <li>Required Subjects: {data.resultData.promotionCriteria.required_subjects.join(', ')}</li>
                    )}
                  </ul>
                </div>
              )}
            </div>
          </div>
        )}

        <CommentsSection
          comments={data.comments}
          decision={data.decision}
          nextTerm={data.nextTerm}
          classTeacher={data.classTeacher}
          resultData={data.resultData}
          school={data.school}
        />
      </div>
    </div>
  );
};

export default ReportCard;
