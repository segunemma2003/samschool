<?php

namespace App\Filament\App\Resources\ComplaintReplyResource\Pages;

use App\Filament\App\Resources\ComplaintReplyResource;
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
