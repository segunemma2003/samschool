<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelDamageReport extends Model
{
    protected $guarded = ['id'];

    public function inventoryItem()
    {
        return $this->belongsTo(HostelInventory::class, 'hostel_inventory_id');
    }

    public function room()
    {
        return $this->belongsTo(HostelRoom::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
