
import React from 'react';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from "../ui/table";

const DomainRatings = ({ affectiveDomain, psychomotor }) => {
    const getRating = (item) => {
        // Check if the item has a direct rating property
        if (item.rating !== undefined) return item.rating;

        // Check if there's a nested psychomotor_student array with ratings
        if (item.psychomotor_student &&
            Array.isArray(item.psychomotor_student) &&
            item.psychomotor_student.length > 0 &&
            item.psychomotor_student[0].rating !== undefined) {
          return item.psychomotor_student[0].rating;
        }

        // Default value if no rating found
        return '-';
      };
  return (
    <div className="grid grid-cols-3 gap-0.5 mb-1 text-[6px]">
      <div>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="border border-gray-300 p-0.5"> Affective Domain</TableHead>
              <TableHead className="border border-gray-300 p-0.5">Rating</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody className="text-[8px]">
            {affectiveDomain.map((trait, index) => {
            const rating = getRating(trait);
            return(
              <TableRow key={index}>
                <TableCell className="border border-gray-300 p-0.5">{trait.skill}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{rating}</TableCell>
              </TableRow>
            )})}
          </TableBody>
        </Table>
      </div>
      <div>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead className="border border-gray-300 p-0.5">Pyschomotor</TableHead>
              <TableHead className="border border-gray-300 p-0.5">Rating</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody className="text-[8px]">
            {psychomotor.map((trait, index) => {
             const rating = getRating(trait);
            return(
              <TableRow key={index}>
                <TableCell className="border border-gray-300 p-0.5">{trait.skill}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{rating}</TableCell>
              </TableRow>
            )})}
          </TableBody>
        </Table>
      </div>
      <div>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead colSpan={2} className="border border-gray-300 p-0.5 text-center">KEY TO RATING</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody className="text-[8px]">
            <TableRow>
              <TableCell className="border border-gray-300 p-0.5">No Observable Trait</TableCell>
              <TableCell className="border border-gray-300 p-0.5 text-center">1</TableCell>
            </TableRow>
            <TableRow>
              <TableCell className="border border-gray-300 p-0.5">Poor Level of Observable Trait</TableCell>
              <TableCell className="border border-gray-300 p-0.5 text-center">2</TableCell>
            </TableRow>
            <TableRow>
              <TableCell className="border border-gray-300 p-0.5">Fair But Acceptable Level Observable Trait</TableCell>
              <TableCell className="border border-gray-300 p-0.5 text-center">3</TableCell>
            </TableRow>
            <TableRow>
              <TableCell className="border border-gray-300 p-0.5">Good Level of Observable Trait</TableCell>
              <TableCell className="border border-gray-300 p-0.5 text-center">4</TableCell>
            </TableRow>
            <TableRow>
              <TableCell className="border border-gray-300 p-0.5">Excellence Level of Observable Trait</TableCell>
              <TableCell className="border border-gray-300 p-0.5 text-center">5</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
  );
};

export default DomainRatings;
