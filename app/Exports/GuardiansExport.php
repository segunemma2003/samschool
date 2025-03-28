<?php

namespace App\Exports;

use App\Models\Guardians;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuardiansExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Guardians::all();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Username',
            'Address'
        ];
    }

    public function map($guardian): array
    {
        return [
            $guardian->name,
            $guardian->email,
            $guardian->phone,
            $guardian->username,
            $guardian->address
        ];
    }
}
