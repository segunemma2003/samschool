
import React from 'react';
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from "@/components/ui/table";

const DomainRatings = ({ affectiveDomain, psychomotor }) => {
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
          <TableBody className="text-[10px]">
            {affectiveDomain.map((trait, index) => (
              <TableRow key={index}>
                <TableCell className="border border-gray-300 p-0.5">{trait.name}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{trait.rating}</TableCell>
              </TableRow>
            ))}
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
          <TableBody className="text-[10px]">
            {psychomotor.map((trait, index) => (
              <TableRow key={index}>
                <TableCell className="border border-gray-300 p-0.5">{trait.name}</TableCell>
                <TableCell className="border border-gray-300 p-0.5 text-center">{trait.rating}</TableCell>
              </TableRow>
            ))}
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
          <TableBody className="text-[10px]">
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
