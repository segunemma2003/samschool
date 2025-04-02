
import React from 'react';
import { Table, TableBody, TableCell, TableRow } from "@/components/ui/table";

const Comments = ({
  teacherComments,
  headmasterComments,
  decision,
  nextTermFees,
  resumptionDate,
  otherCharges
}) => {
  return (
    <div className="mb-1">
      <Table className="border border-gray-300 text-[9px]">
        <TableBody>
          <TableRow>
            <TableCell className="border border-gray-300 p-0.5">
              <div className="font-semibold mb-0.5">Class Teacher's Comments:</div>
              <div className="pl-1">{teacherComments}</div>
            </TableCell>
            <TableCell className="border border-gray-300 p-0.5">
              <div className="font-semibold mb-0.5">Headmaster/Principal's Comments:</div>
              <div className="pl-1">{headmasterComments}</div>
            </TableCell>
            <TableCell className="border border-gray-300 p-0.5 flex justify-end items-end h-4">
              <div className="text-right">
                <div className="font-semibold mb-0.5">Headmaster/Principal's Signature:</div>
                <div className="h-3 w-12">
                  <img 
                    src="/lovable-uploads/54f7313f-28a5-4c5d-9f58-1c11d7d32dc5.png" 
                    alt="Principal's Signature"
                    className="h-full w-full object-contain"
                  />
                </div>
              </div>
            </TableCell>
          </TableRow>
          <TableRow>
            <TableCell className="border border-gray-300 p-0.5">
              <div className="font-semibold mb-0.5">DECISION:</div>
              <div className="pl-1">{decision}</div>
            </TableCell>
            <TableCell className="border border-gray-300 p-0.5">
              <div className="font-semibold mb-0.5">RESUMPTION DATE FOR SECOND TERM:</div>
              <div className="pl-1">{resumptionDate}</div>
            </TableCell>
            <TableCell className="border border-gray-300 p-0.5">
              <div className="font-semibold mb-0.5">OTHER CHARGES:</div>
              <div className="pl-1">{otherCharges}</div>
              <div className="font-semibold mb-0.5 mt-1">NEXT TERM SCHOOL FEES:</div>
              <div className="pl-1">{nextTermFees}</div>
            </TableCell>
          </TableRow>
          <TableRow>
            <TableCell colSpan={3} className="border border-gray-300 p-0.5 text-center">
              <div className="font-semibold text-[9px]">NOTE: EACH CHILD MUST RETURN TO SCHOOL WITH EVIDENCE OF PAYMENT OR THEY WILL BE SENT BACK HOME</div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  );
};

export default Comments;
