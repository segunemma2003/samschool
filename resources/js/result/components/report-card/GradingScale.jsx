
import React from 'react';

const GradingScale = () => {
  return (
    <div className="mb-1 text-[10px]">
      <h3 className="font-bold mb-0.5 text-[9px]">Academic Grading Scale</h3>
      <div className="grid grid-cols-3 gap-0.5">
        <div className="flex">
          <div className="w-6">A1</div>
          <div className="w-12">75 - 100</div>
          <div>Excellent</div>
        </div>
        <div className="flex">
          <div className="w-6">B2</div>
          <div className="w-12">70 - 74</div>
          <div>Very Good</div>
        </div>
        <div className="flex">
          <div className="w-6">B3</div>
          <div className="w-12">65 - 69</div>
          <div>Good</div>
        </div>
        <div className="flex">
          <div className="w-6">C4</div>
          <div className="w-12">60 - 64</div>
          <div>Upper Credit</div>
        </div>
        <div className="flex">
          <div className="w-6">C5</div>
          <div className="w-12">55 - 59</div>
          <div>Credit</div>
        </div>
        <div className="flex">
          <div className="w-6">C6</div>
          <div className="w-12">50 - 54</div>
          <div>Lower Credit</div>
        </div>
        <div className="flex">
          <div className="w-6">D7</div>
          <div className="w-12">45 - 49</div>
          <div>Pass</div>
        </div>
        <div className="flex">
          <div className="w-6">D8</div>
          <div className="w-12">40 - 44</div>
          <div>Weak Pass</div>
        </div>
        <div className="flex">
          <div className="w-6">F9</div>
          <div className="w-12">0 - 39</div>
          <div>Fail</div>
        </div>
      </div>
    </div>
  );
};

export default GradingScale;
