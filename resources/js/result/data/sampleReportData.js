
export const sampleReportData = {
  student: {
    name: "Micheal Temidayo Ayegbusi",
    admissionNo: "LYSB/1063",
    className: "Nursery 1A"
  },
  session: "2024/2025",
  term: "First Term",
  grade: "A1",
  attendance: {
    daysOpen: 132,
    daysPresent: 120,
    daysAbsent: 12,
    percentage: 91
  },
  subjects: [
    { 
      name: "LITERACY", 
      ca1: 9, ca2: 5, ca3: 5, exam: 45, total: 64, 
      grade: "C4", classAverage: "83.41", lowestScore: 55, highestScore: 100, position: "28th", remark: "UPPER CREDIT"
    },
    { 
      name: "NUMERACY", 
      ca1: 10, ca2: 10, ca3: 4, exam: 50, total: 74, 
      grade: "B2", classAverage: "87.38", lowestScore: 48, highestScore: 100, position: "25th", remark: "VERY GOOD"
    },
    { 
      name: "PRACTICAL LIFE EXERCISE", 
      ca1: 10, ca2: 10, ca3: 8, exam: 62, total: 90, 
      grade: "A1", classAverage: "89.38", lowestScore: 28, highestScore: 100, position: "18th", remark: "EXCELLENT"
    },
    { 
      name: "SENSORIAL EDUCATION", 
      ca1: 10, ca2: 10, ca3: 10, exam: 61, total: 91, 
      grade: "A1", classAverage: "90.45", lowestScore: 65, highestScore: 100, position: "12th", remark: "EXCELLENT"
    },
    { 
      name: "CULTURAL SUBJECT", 
      ca1: 10, ca2: 10, ca3: 5, exam: 68, total: 93, 
      grade: "A1", classAverage: "83.59", lowestScore: 30, highestScore: 97, position: "7th", remark: "EXCELLENT"
    },
    { 
      name: "CULTURAL AND CREATIVE ART", 
      ca1: 10, ca2: 10, ca3: 7, exam: 65, total: 92, 
      grade: "A1", classAverage: "90.76", lowestScore: 27, highestScore: 100, position: "18th", remark: "EXCELLENT"
    },
    { 
      name: "MORAL INSTRUCTION", 
      ca1: 10, ca2: 10, ca3: 10, exam: 61, total: 91, 
      grade: "A1", classAverage: "89.86", lowestScore: 27, highestScore: 100, position: "15th", remark: "EXCELLENT"
    },
    { 
      name: "RHYMES", 
      ca1: 10, ca2: 10, ca3: 8, exam: 60, total: 88, 
      grade: "A1", classAverage: "89.21", lowestScore: 25, highestScore: 99, position: "20th", remark: "EXCELLENT"
    },
    { 
      name: "COMPUTER STUDIES", 
      ca1: 10, ca2: 10, ca3: 6, exam: 60, total: 86, 
      grade: "A1", classAverage: "87.76", lowestScore: 63, highestScore: 100, position: "17th", remark: "EXCELLENT"
    },
    { 
      name: "HAND WRITING", 
      ca1: 10, ca2: 10, ca3: 6, exam: 30, total: 56, 
      grade: "C5", classAverage: "79.38", lowestScore: 27, highestScore: 99, position: "27th", remark: "CREDIT"
    },
  ],
  summary: {
    subjectsOffered: 10,
    marksObtained: 825,
    marksObtainable: 1000,
    classAverage: 86.72,
    studentAverage: 82.50
  },
  affectiveDomain: [
    { name: "Honesty", rating: 4 },
    { name: "Neatness", rating: 3 },
    { name: "Punctuality", rating: 3 },
    { name: "Reliability", rating: 4 },
    { name: "Cooperation", rating: 4 },
  ],
  psychomotor: [
    { name: "Games", rating: 4 },
    { name: "Creativity", rating: 3 },
    { name: "Handwriting", rating: 3 },
    { name: "Verbal Fluency", rating: 3 },
    { name: "Perceptual Ability", rating: 3 },
  ],
  comments: {
    teacher: "V.Good",
    headmaster: "An Excellent Result, Keep it up",
    decision: "PASS",
    nextTermFees: "₦0",
    resumptionDate: "2025-01-06",
    otherCharges: "-"
  }
};
