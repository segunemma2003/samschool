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