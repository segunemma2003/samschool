<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentExport implements FromCollection , WithHeadings
{

    protected Builder $query;
    /**
    * @return \Illuminate\Support\Collection
    */



    public function collection()
    {
        return $this->query->with('class')->get()->map(function ($student) {
            return [
                'Name' => $student->name,
                'Username' => $student->username,
                'Class' => $student->class->name ?? 'N/A',
            ];
        });
    }


    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function headings(): array
    {
        return ['Name', 'Username', 'Class'];
    }
}
