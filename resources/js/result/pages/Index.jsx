
import { Button } from "../components/ui/button";
import { Link } from "react-router-dom";
import { FileText } from "lucide-react";

const Index = () => {
     const [data, setData] = useState(null);

     useEffect(() => {
         // Try to get data from localStorage, fall back to dummy data
         try {
           // Get data from localStorage
           const student = JSON.parse(localStorage.getItem('student') || '{}');
           const scoreData = JSON.parse(localStorage.getItem('scoreData') || '{}');
           const totalHeadings = JSON.parse(localStorage.getItem('totalHeadings') || '[]');
           const percent = JSON.parse(localStorage.getItem('percent') || '0');
           const groupedHeadings = JSON.parse(localStorage.getItem('groupedHeadings') || '{}');
           const headings = JSON.parse(localStorage.getItem('headings') || '[]');
           const psychomotorAffective = JSON.parse(localStorage.getItem('psychomotorAffective') || '[]');
           const psychomotorNormal = JSON.parse(localStorage.getItem('psychomotorNormal') || '[]');
           const termAndAcademy = JSON.parse(localStorage.getItem('termAndAcademy') || '{}');
           const relatedData = JSON.parse(localStorage.getItem('relatedData') || '{}');
           const resultData = JSON.parse(localStorage.getItem('resultData') || '{}');

           // Get markObtained from groupedHeadings
           const markObtained = [
             ...(groupedHeadings.input || []),
             ...(groupedHeadings.total || [])
           ];

           // Transform the data to match the expected format
           const transformedData = {
             school: relatedData.school || {},
             classTeacher: resultData.class?.teacher,
             courses: resultData.courses,
             student: {
               data: student,
               name: student.name || '',
               admissionNo: student.registration_number || '',
               class: student.class?.name || '',
               session: termAndAcademy.academy?.title || '',
               term: termAndAcademy.term?.name || '',
               grade: scoreData.grade || ''
             },
             totalHeadings: totalHeadings,
             psychomotorNormal: psychomotorNormal.map(item => ({
               ...item,
               rating: item.psychomotor_student?.[0]?.rating || '-'
             })),
             attendance: relatedData.studentAttendance || {},
             markObtained: resultData.markObtained,
             studentSummary: resultData.studentSummary,
             termSummary: resultData.termSummary,
             remarks: resultData.remarks,
             subjects: headings.map(heading => {
               const subjectData = {
                 name: heading.name,
                 scoreBoard: heading.scoreBoard || []
               };
               return subjectData;
             }),
             analysis: {
               totalScore: scoreData.totalScore || 0,
               percentage: percent || 0,
               position: scoreData.position || '',
               grade: scoreData.grade || '',
               marksObtainable: (resultData.totalSubject || 0) * 100,
               classAverage: resultData.classAverage || '-',
               studentAverage: percent || 0
             },
             affectiveDomain: psychomotorAffective.map(item => ({
               ...item,
               rating: item.psychomotor_student?.[0]?.rating || '-'
             })),
             comments: {
               teacherComment: relatedData.studentComment?.comment || '',
               principalComment: resultData.principalComment || ''
             },
             decision: scoreData.decision || '',
             nextTerm: relatedData.school?.next_term_begins || '',
             date: new Date().toLocaleDateString(),
             code: student.registration_number || '',
             resultData: resultData
           };

           setData(transformedData);
         } catch (error) {
           console.error('Error fetching data from localStorage', error);
           // Fallback to dummy data if there's an error
           setData(reportData);
         }
       }, []);

       if (!data) {
         return (
           <div className="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 py-8 px-4 flex items-center justify-center">
             <div className="text-center">
               <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Loading...</h2>
               <p className="text-gray-600 dark:text-gray-400">Please wait while we load your report card.</p>
             </div>
           </div>
         );
       }

  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-gray-50 p-4">
      <div className="max-w-md w-full bg-white shadow-lg rounded-lg p-8 text-center">
        <h1 className="text-3xl font-bold mb-6">Print Watermark Magic</h1>
        <p className="text-lg text-gray-600 mb-8">
          Create beautiful documents with watermarks that look perfect when printed from any device.
        </p>

        <div className="space-y-4">
          <Link to="/result">
            <Button className="w-full flex items-center justify-center gap-2 py-6" size="lg">
              <FileText className="h-5 w-5" />
              View Sample Document
            </Button>
          </Link>

          <p className="text-sm text-gray-500 mt-4">
            Our technology ensures that your documents maintain their design
            integrity when printed from any device.
          </p>
        </div>
      </div>

      <div className="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl text-center">
        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="font-semibold mb-2">Consistent Printing</h3>
          <p className="text-gray-600 text-sm">Print documents that look identical across all devices</p>
        </div>

        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="font-semibold mb-2">Customizable Watermarks</h3>
          <p className="text-gray-600 text-sm">Adjust opacity and size to get the perfect watermark effect</p>
        </div>

        <div className="bg-white p-6 rounded-lg shadow">
          <h3 className="font-semibold mb-2">Professional Results</h3>
          <p className="text-gray-600 text-sm">Create certificates, documents, and materials with a professional look</p>
        </div>
      </div>
    </div>
  );
};

export default Index;
