<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceStudent extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function invoice_details(){
        return $this->hasMany(InvoiceStudentDetails::class, 'invoice_student_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }
    public function academy()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_id');
    }
    public function student()
    {

        return $this->belongsTo(Student::class, 'student_id');
    }

    public function invoicePaid()
    {
        return $this->hasMany(InvoiceStudentPaid::class, 'invoice_group_id');
    }
}
