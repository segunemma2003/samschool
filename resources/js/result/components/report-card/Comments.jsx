
import React from 'react';
import { Table, TableBody, TableCell, TableRow } from "../ui/table";

const Comments = ({
    comments, decision, nextTerm, classTeacher, resultData, school
}) => {
  return (
    <div className="mb-1">
      <Table className="border border-gray-300 text-[9px]">
        <TableBody>
          <TableRow>
            <TableCell className="border border-gray-300 p-0.5">
              <div className="font-semibold mb-0.5">Class Teacher's Comments:</div>
              <div className="pl-1">{comments?.teacherComment || 'No comment provided'}</div>
            </TableCell>
            <TableCell className="border border-gray-300 p-0.5">
              <div className="font-semibold mb-0.5">Headmaster/Principal's Comments:</div>
              <div className="pl-1">{comments?.principalComment || 'No comment provided'}</div>
            </TableCell>
            <TableCell className="border border-gray-300 p-0.5 flex justify-center items-center">
              <div className="text-center">
                <div className="font-semibold mb-0.5">School Stamp:</div>
                <div className="w-16 h-16 mx-auto">
                {school?.school_stamp ? (
            <img
              src={school.school_stamp.startsWith('data:image')
                ? school.school_stamp
                : `https://schoolcompasse.s3.us-east-1.amazonaws.com/${school.school_stamp}`}
              alt="School Stamp"
              className="object-contain w-full h-full"
            />
          ) : (  <img
                    src="https://images.unsplash.com/photo-1459767129954-1b1c1f9b9ace"
                    alt="School Stamp"
                    className="object-contain w-full h-full"
                  />
                )}
                </div>
              </div>
            </TableCell>
          </TableRow>
          <TableRow>
            <TableCell className="border border-gray-300 p-0.5">
              <div className="font-semibold mb-0.5">DECISION:</div>
              <div className="pl-1">{""}</div>
            </TableCell>
            <TableCell className="border border-gray-300 p-0.5">
              <div className="font-semibold mb-0.5">RESUMPTION DATE FOR THE NEXT TERM:</div>
              <div className="pl-1">{nextTerm}</div>
            </TableCell>
            <TableCell className="border border-gray-300 p-0.5">
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
