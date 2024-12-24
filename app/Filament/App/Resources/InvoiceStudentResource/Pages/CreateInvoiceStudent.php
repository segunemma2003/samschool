<?php

namespace App\Filament\App\Resources\InvoiceStudentResource\Pages;

use App\Filament\App\Resources\InvoiceStudentResource;
use App\Models\InvoiceStudentDetails;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoiceStudent extends CreateRecord
{
    protected static string $resource = InvoiceStudentResource::class;
    public function afterCreate()
    {
        $data  = $this->getRecord();
        $details = $data->invoice_details;
        $this->getRecord()->update([
            "amount_owed"=> $data->total_amount
        ]);


        // foreach($details as $detail){
        // InvoiceStudentDetails::create([
        //         'invoice_student_id'=> $this->getRecord()->id,
        //         'amount'=>$detail['amount'],
        //         'invoice_group_id'=>$detail['invoice_group_id']
        //     ]);
        // }

    }

}
