
import React from 'react';
import { Avatar, AvatarImage, AvatarFallback } from "../ui/avatar";
import { Separator } from "../ui/separator";
import { User } from "lucide-react";

const StudentHeader = ({data, schoolLogoUrl, title = "NURSERY REPORT SHEET" }) => {
  return (
    <div className="mb-5">
      {/* Header layout with logo, school details, and student avatar */}
      <div className="flex items-center justify-between mb-2">
        {/* Left: School Logo */}
        <div className="flex-shrink-0">
          <img
            src={schoolLogoUrl}
            alt="School Logo"
            className="h-32 w-32 object-contain"
            onError={(e) => {
              e.target.style.display = 'none';
            }}
          />
        </div>

        {/* Center: School Information */}
        <div className="text-center flex-grow mx-4">
          <h1 className="text-2xl font-bold text-black">{data.school.school_name}</h1>
          <p className="text-xs">{data.school.school_address}</p>
          <p className="text-xs italic">Motto: Building Leaders</p>
          <p className="text-xs mt-1">Email: {data.school.email} URL: {data.school.school_website}</p>
          <h2 className="text-xl font-bold text-red-600 mt-2">{title}</h2>
        </div>

        {/* Right: Student Avatar */}
        <div className="flex-shrink-0">
          <Avatar className="h-32 w-32 border-2 border-gray-300">
            <AvatarImage src={`https://schoolcompasse.s3.us-east-1.amazonaws.com/${data.student.data.avatar}`} alt="Student" />
            <AvatarFallback>
              <User className="h-16 w-16 text-gray-400" />
            </AvatarFallback>
          </Avatar>
        </div>
      </div>

      <Separator className="my-2" />
    </div>
  );
};

export default StudentHeader;
