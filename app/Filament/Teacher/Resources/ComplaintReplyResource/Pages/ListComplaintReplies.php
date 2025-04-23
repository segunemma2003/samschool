<?php

namespace App\Filament\Teacher\Resources\ComplaintReplyResource\Pages;

use App\Filament\Teacher\Resources\ComplaintReplyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComplaintReplies extends ListRecords
{
    protected static string $resource = ComplaintReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
