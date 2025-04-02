
import React from 'react';
import { Table, TableBody, TableCell, TableRow } from "../ui/table";

const ScoreSummary = ({
  subjectsOffered,
  marksObtained,
  marksObtainable,
  classAverage,
  studentAverage
}) => {
  return (
    <div className="mt-1 mb-1">
      <Table className="w-full border-collapse text-[10px]">
        <TableBody>
          <TableRow className="border border-gray-300">
            <TableCell className="border border-gray-300 p-0.5 font-semibold" rowSpan={2}>ANALYSIS</TableCell>
            <TableCell className="border border-gray-300 p-0.5 font-semibold">Subjects Offered</TableCell>
            <TableCell className="border border-gray-300 p-0.5 text-center">{subjectsOffered}</TableCell>
            <TableCell className="border border-gray-300 p-0.5 font-semibold">Class Average</TableCell>
            <TableCell className="border border-gray-300 p-0.5 text-center">{classAverage.toFixed(2)}%</TableCell>
            {/* <TableCell className="border border-gray-300 p-0.5 font-semibold"></TableCell>
            <TableCell className="border border-gray-300 p-0.5 text-center"></TableCell> */}
             <TableCell className="border border-gray-300 p-0.5 font-semibold" rowSpan={2}>Student Average</TableCell>
             <TableCell className="border border-gray-300 p-0.5 text-center" rowSpan={2}>{studentAverage.toFixed(2)}%</TableCell>
          </TableRow>
          <TableRow className="border border-gray-300">
            <TableCell className="border border-gray-300 p-0.5 font-semibold">Marks Obtained</TableCell>
            <TableCell className="border border-gray-300 p-0.5 text-center">{marksObtained}</TableCell>
            <TableCell className="border border-gray-300 p-0.5 font-semibold">Marks Obtainable</TableCell>
            <TableCell className="border border-gray-300 p-0.5 text-center">{marksObtainable}</TableCell>

          </TableRow>
        </TableBody>
      </Table>
    </div>
  );
};

export default ScoreSummary;
