
import React from 'react';
import StudentHeader from './StudentHeader';
import StudentInfo from './StudentInfo';
import SubjectsAnalysis from './SubjectsAnalysis';
import ScoreSummary from './ScoreSummary';
import DomainRatings from './DomainRatings';
import GradingScale from './GradingScale';
import Comments from './Comments';
import Footer from './Footer';

const SchoolReport = ({
  schoolLogoUrl,
  title = "NURSERY REPORT SHEET",
  watermarkOpacity,
  watermarkScale,
  data,
  mdata
}) => {
  return (
    <div className="report-card w-full box-border print:box-border print:w-full" id="printable-report">
      <StudentHeader data={mdata} schoolLogoUrl={schoolLogoUrl} title={title} />

      <StudentInfo
        name={mdata.student.name}
        admissionNo={mdata.student.admissionNo}
        className={mdata.student.class}
        session={mdata.student.session}
        term={mdata.student.term}
        grade={mdata.student.grade}
        attendanceData={mdata.attendance}
      />

      <SubjectsAnalysis
        subjects={data.subjects}
        watermarkUrl={schoolLogoUrl}
        watermarkOpacity={watermarkOpacity}
        watermarkScale={watermarkScale}
        resultData={mdata.resultData}
        markObtained={mdata.markObtained}
        studentSummary={mdata.studentSummary}
        termSummary={mdata.termSummary}
        remarks={mdata.remarks}
        analysis={mdata.analysis}
      />

      <ScoreSummary
        subjectsOffered={data.summary.subjectsOffered}
        marksObtained={data.summary.marksObtained}
        marksObtainable={data.summary.marksObtainable}
        classAverage={data.summary.classAverage}
        studentAverage={data.summary.studentAverage}
      />

      <DomainRatings
        affectiveDomain={data.affectiveDomain}
        psychomotor={data.psychomotor}
      />

      <GradingScale />

      <Comments
        teacherComments={data.comments.teacher}
        headmasterComments={data.comments.headmaster}
        decision={data.comments.decision}
        nextTermFees={data.comments.nextTermFees}
        resumptionDate={data.comments.resumptionDate}
        otherCharges={data.comments.otherCharges}
      />

      <Footer />
    </div>
  );
};

export default SchoolReport;
