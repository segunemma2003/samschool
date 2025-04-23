<?php

namespace App\Filament\Teacher\Resources\ComplaintReplyResource\Pages;

use App\Filament\Teacher\Resources\ComplaintReplyResource;
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
