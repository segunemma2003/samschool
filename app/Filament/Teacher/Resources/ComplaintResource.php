<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use App\Models\Student;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Complaints';

    protected static ?string $modelLabel = 'Complaint';

    protected static ?string $pluralModelLabel = 'Complaints';

    protected static ?int $navigationSort = 3;

    // Performance optimization - eager load relationships
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Complaint Details')
                    ->description('Provide detailed information about the complaint')
                    ->icon('heroicon-m-document-text')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Complaint Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Brief description of the issue')
                                    ->columnSpan(2),

                                Select::make('user_id')
                                    ->label('Student')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Select a student')
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Select::make('category')
                                    ->label('Category')
                                    ->options([
                                        'academic' => 'Academic Issues',
                                        'behavioral' => 'Behavioral Problems',
                                        'attendance' => 'Attendance Issues',
                                        'bullying' => 'Bullying/Harassment',
                                        'facilities' => 'Facilities/Infrastructure',
                                        'other' => 'Other'
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->placeholder('Select category'),

                                Select::make('priority')
                                    ->label('Priority Level')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                        'urgent' => 'Urgent'
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->default('medium'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'investigating' => 'Under Investigation',
                                        'resolved' => 'Resolved',
                                        'closed' => 'Closed'
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->default('pending'),
                            ]),

                        Textarea::make('description')
                            ->label('Detailed Description')
                            ->required()
                            ->rows(4)
                            ->placeholder('Provide a detailed description of the complaint...'),

                        Textarea::make('resolution_notes')
                            ->label('Resolution Notes')
                            ->rows(3)
                            ->placeholder('Add notes about resolution actions taken...')
                            ->visible(fn ($get) => in_array($get('status'), ['resolved', 'closed'])),

                        DatePicker::make('incident_date')
                            ->label('Incident Date')
                            ->native(false)
                            ->displayFormat('M d, Y')
                            ->maxDate(now()),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->color('gray'),

                BadgeColumn::make('category')
                    ->label('Category')
                    ->colors([
                        'primary' => 'academic',
                        'warning' => 'behavioral',
                        'danger' => 'bullying',
                        'success' => 'facilities',
                        'secondary' => 'attendance',
                        'gray' => 'other',
                    ])
                    ->icons([
                        'heroicon-m-academic-cap' => 'academic',
                        'heroicon-m-exclamation-triangle' => 'behavioral',
                        'heroicon-m-shield-exclamation' => 'bullying',
                        'heroicon-m-building-office' => 'facilities',
                        'heroicon-m-calendar-days' => 'attendance',
                        'heroicon-m-question-mark-circle' => 'other',
                    ]),

                BadgeColumn::make('priority')
                    ->label('Priority')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'red' => 'urgent',
                    ])
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'investigating',
                        'success' => 'resolved',
                        'danger' => 'closed',
                    ])
                    ->icons([
                        'heroicon-m-clock' => 'pending',
                        'heroicon-m-magnifying-glass' => 'investigating',
                        'heroicon-m-check-circle' => 'resolved',
                        'heroicon-m-x-circle' => 'closed',
                    ])
                    ->sortable(),

                TextColumn::make('incident_date')
                    ->label('Incident Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Reported')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'investigating' => 'Under Investigation',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed'
                    ])
                    ->multiple(),

                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent'
                    ])
                    ->multiple(),

                SelectFilter::make('category')
                    ->options([
                        'academic' => 'Academic Issues',
                        'behavioral' => 'Behavioral Problems',
                        'attendance' => 'Attendance Issues',
                        'bullying' => 'Bullying/Harassment',
                        'facilities' => 'Facilities/Infrastructure',
                        'other' => 'Other'
                    ])
                    ->multiple(),

                Filter::make('recent')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->label('Last 7 Days'),

                Filter::make('urgent_complaints')
                    ->query(fn (Builder $query): Builder => $query->where('priority', 'urgent')->where('status', '!=', 'resolved'))
                    ->label('Urgent & Unresolved')
                    ->indicator('Urgent'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\Action::make('resolve')
                    ->label('Mark Resolved')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Complaint $record) => $record->update(['status' => 'resolved']))
                    ->visible(fn (Complaint $record): bool => $record->status !== 'resolved'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_investigating')
                        ->label('Mark as Investigating')
                        ->icon('heroicon-m-magnifying-glass')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => 'investigating']))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('mark_resolved')
                        ->label('Mark as Resolved')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'resolved']))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s') // Auto-refresh every 30 seconds
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSortInSession();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'view' => Pages\ViewComplaint::route('/{record}'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }

    // Performance optimization - modify query to eager load relationships
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user']) // Eager load student relationship
            ->latest(); // Default ordering for better performance
    }

    // Add global search for better UX
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'user.name'];
    }

    // Add navigation badge to show pending complaints count
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();

        if ($pendingCount > 10) {
            return 'danger';
        } elseif ($pendingCount > 5) {
            return 'warning';
        }

        return 'primary';
    }
}
