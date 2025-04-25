<?php

namespace App\Filament\Hostel\Resources;

use App\Filament\Hostel\Resources\HostelRuleViolationResource\Pages;
use App\Filament\Hostel\Resources\HostelRuleViolationResource\RelationManagers;
use App\Models\HostelRuleViolation;
use App\Models\HostelWarning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class HostelRuleViolationResource extends Resource
{
    protected static ?string $model = HostelRuleViolation::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Hostel Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('violation_type')
                    ->options([
                        'curfew' => 'Curfew Violation',
                        'noise' => 'Excessive Noise',
                        'property' => 'Property Damage',
                        'visitors' => 'Unauthorized Visitors',
                        'other' => 'Other Violation'
                    ])
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('violation_date')
                    ->required(),
                Forms\Components\Select::make('severity')
                    ->options([
                        'minor' => 'Minor',
                        'major' => 'Major',
                        'critical' => 'Critical'
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'reported' => 'Reported',
                        'reviewed' => 'Reviewed',
                        'action_taken' => 'Action Taken',
                        'resolved' => 'Resolved'
                    ])
                    ->required(),
                Forms\Components\Textarea::make('action_taken')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('resolution_notes')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                ->searchable(),
            Tables\Columns\TextColumn::make('violation_type')
                ->badge(),
            Tables\Columns\TextColumn::make('violation_date')
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('severity')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'minor' => 'gray',
                    'major' => 'warning',
                    'critical' => 'danger',
                }),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'reported' => 'warning',
                    'reviewed' => 'info',
                    'action_taken' => 'primary',
                    'resolved' => 'success',
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'minor' => 'Minor',
                        'major' => 'Major',
                        'critical' => 'Critical'
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'reported' => 'Reported',
                        'reviewed' => 'Reviewed',
                        'action_taken' => 'Action Taken',
                        'resolved' => 'Resolved'
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('issueWarning')
                    ->form([
                        Forms\Components\Select::make('warning_type')
                            ->options([
                                'verbal' => 'Verbal Warning',
                                'written' => 'Written Warning',
                                'final' => 'Final Warning'
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->required(),
                        Forms\Components\DatePicker::make('valid_until')
                            ->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (HostelRuleViolation $record, array $data): void {
                        HostelWarning::create([
                            'student_id' => $record->student_id,
                            'issued_by' => Auth::id(),
                            'rule_violation_id' => $record->id,
                            'warning_type' => $data['warning_type'],
                            'description' => $data['description'],
                            'issue_date' => now(),
                            'valid_until' => $data['valid_until'],
                            'notes' => $data['notes'],
                        ]);

                        $record->update([
                            'status' => 'action_taken',
                            'action_taken' => "{$data['warning_type']} warning issued",
                        ]);

                        Notification::make()
                            ->title('Warning issued successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (HostelRuleViolation $record): bool => $record->status !== 'resolved')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\WarningsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHostelRuleViolations::route('/'),
            'create' => Pages\CreateHostelRuleViolation::route('/create'),
            'view' => Pages\ViewHostelRuleViolation::route('/{record}'),
            'edit' => Pages\EditHostelRuleViolation::route('/{record}/edit'),
        ];
    }
}
