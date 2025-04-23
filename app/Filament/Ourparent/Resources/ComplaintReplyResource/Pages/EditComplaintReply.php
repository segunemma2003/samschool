<?php

namespace App\Filament\Ourparent\Resources\ComplaintReplyResource\Pages;

use App\Filament\Ourparent\Resources\ComplaintReplyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplaintReply extends EditRecord
{
    protected static string $resource = ComplaintReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
