<?php

namespace App\Filament\App\Resources\InvoiceStudentResource\Pages;

use App\Filament\App\Resources\InvoiceStudentResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoiceStudent extends ViewRecord
{
    protected static string $resource = InvoiceStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Section::make('Student Details')
                    ->description('Invoice Details')
                    ->schema([
                        Grid::make(3) // Creates a grid with 3 columns for larger screens
                            ->schema([
                                TextEntry::make('order_code'),
                                TextEntry::make('term.name'),
                                TextEntry::make('academy.title'),
                                TextEntry::make('student.name'),
                                TextEntry::make('total_amount')
                                    ->prefix('₦')
                                    ->formatStateUsing(fn ($state) => number_format(round($state, 2), 2)),
                                TextEntry::make('amount_owed')
                                    ->prefix('₦')
                                    ->formatStateUsing(fn ($state) => number_format(round($state, 2), 2)),
                                TextEntry::make('amount_paid')
                                    ->prefix('₦')
                                    ->formatStateUsing(fn ($state) => number_format(round($state, 2), 2)),
                            ])
                    ])
                    ->collapsible()
                    ->compact(),

                Section::make('Invoice Details')
                    ->description('Student Invoice Details')
                    ->schema([
                        RepeatableEntry::make('invoice_details')
                            ->schema([
                                Grid::make(2) // Creates a grid with 2 columns for larger screens
                                    ->schema([
                                        TextEntry::make('group.name'),
                                        TextEntry::make('amount')->numeric(decimalPlaces: 2),
                                    ]),
                            ])
                    ]),

            ]);
    }
}
