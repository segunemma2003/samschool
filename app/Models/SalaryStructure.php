<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryStructure extends Model
{
    protected $guarded = ['id'];

    public function allowances(): HasMany
    {
        return $this->hasMany(SalaryAllowance::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    public function staffSalaries(): HasMany
    {
        return $this->hasMany(StaffSalary::class);
    }
}
