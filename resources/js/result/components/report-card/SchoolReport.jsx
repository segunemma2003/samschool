
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
    <div className="box-border w-full report-card print:box-border print:w-full" id="printable-report">
      <StudentHeader data={mdata} schoolLogoUrl={schoolLogoUrl} title={title} />

      <StudentInfo
        name={mdata.student.name}
        admissionNo={mdata.student.admissionNo}
        className={mdata.student.class}
        session={mdata.student.session}
        term={mdata.student.term}
        school= {mdata.school}
        // grade={mdata.student.grade}
        position={mdata.resultData.studentPosition}
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
        subjectsOffered={mdata.resultData.totalSubject}
        marksObtained={mdata.analysis.totalScore}
        marksObtainable={mdata.analysis.marksObtainable}
        classAverage={mdata.resultData.classAverage}
        studentAverage={mdata.resultData.studentAverage || 0.000}
      />

      <DomainRatings
        affectiveDomain={mdata.affectiveDomain}
        psychomotor={mdata.psychomotorNormal}
      />

      <GradingScale />

      <Comments
       comments={mdata.comments}
       decision={mdata.decision}
       nextTerm={mdata.nextTerm}
       classTeacher={mdata.classTeacher}
       resultData={mdata.resultData}
       school={mdata.school}
      />

      <Footer />
    </div>
  );
};

export default SchoolReport;
