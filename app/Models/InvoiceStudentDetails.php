<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceStudentDetails extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function group()
    {
        return $this->belongsTo(InvoiceGroup::class,'invoice_group_id' );
    }

}
