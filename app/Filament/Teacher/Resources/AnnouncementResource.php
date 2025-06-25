<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Facades\Schema;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Announcements';

    protected static ?string $modelLabel = 'Announcement';

    protected static ?string $pluralModelLabel = 'Announcements';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Communication';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Announcement Details')
                    ->description('Create engaging announcements for your audience')
                    ->icon('heroicon-m-megaphone')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Announcement Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter a compelling title...')
                                    ->columnSpan(2),

                                TextInput::make('sub')
                                    ->label('Subtitle')
                                    ->maxLength(100)
                                    ->placeholder('Brief description...')
                                    ->columnSpan(2)
                                    ->helperText('This appears as a preview under the title'),

                                Select::make('type_of_user_sent_to')
                                    ->label('Target Audience')
                                    ->options([
                                        'all' => '🌐 Everyone',
                                        'teacher' => '👨‍🏫 Teachers Only',
                                        'student' => '👨‍🎓 Students Only',
                                        'admin' => '👑 Administrators',
                                        'parent' => '👨‍👩‍👧‍👦 Parents/Guardians',
                                    ])
                                    ->default('teacher')
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(1),

                                // Only show priority if column exists
                                ...static::conditionalFields([
                                    Select::make('priority')
                                        ->label('Priority Level')
                                        ->options([
                                            'low' => '🟢 Low Priority',
                                            'medium' => '🟡 Medium Priority',
                                            'high' => '🔴 High Priority',
                                            'urgent' => '🚨 Urgent',
                                        ])
                                        ->default('medium')
                                        ->native(false)
                                        ->columnSpan(1)
                                        ->visible(fn() => Schema::hasColumn('announcements', 'priority')),
                                ]),
                            ]),
                    ]),

                Section::make('Content')
                    ->description('Add rich content to your announcement')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        RichEditor::make('text')
                            ->label('Announcement Content')
                            ->placeholder('Write your announcement here...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'link',
                                'heading',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                            ])
                            ->columnSpanFull(),

                        TextInput::make('link')
                            ->label('Additional Link')
                            ->url()
                            ->placeholder('https://example.com')
                            ->helperText('Optional: Add a relevant link for more information')
                            ->columnSpanFull(),
                    ]),

                Section::make('Attachments')
                    ->description('Add files or images to support your announcement')
                    ->icon('heroicon-m-plus')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Upload File')
                            ->disk('s3')
                            ->directory('announcements')
                            ->acceptedFileTypes(['image/*', 'application/pdf', '.doc', '.docx'])
                            ->maxSize(10240) // 10MB
                            ->imagePreviewHeight('200')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // OPTIMIZED QUERY with backward compatibility
            ->modifyQueryUsing(function (Builder $query) {
                $userId = Auth::id();

                return $query
                    ->select([
                        'announcements.id',
                        'announcements.title',
                        'announcements.sub',
                        'announcements.type_of_user_sent_to',
                        'announcements.from_id',
                        'announcements.file',
                        'announcements.link',
                        'announcements.created_at',
                        'announcements.updated_at',
                        // Only select new columns if they exist
                        ...(Schema::hasColumn('announcements', 'priority') ? ['announcements.priority'] : []),
                        ...(Schema::hasColumn('announcements', 'status') ? ['announcements.status'] : []),
                        ...(Schema::hasColumn('announcements', 'views_count') ? ['announcements.views_count'] : []),
                    ])
                    ->with(['owner:id,name,avatar,user_type'])
                    ->where(function ($query) use ($userId) {
                        $query->where('type_of_user_sent_to', 'teacher')
                              ->orWhere('from_id', $userId)
                              ->orWhere('type_of_user_sent_to', 'all');
                    })
                    ->when(Schema::hasColumn('announcements', 'status'), function ($query) {
                        return $query->where('status', 'published');
                    })
                    ->orderBy('created_at', 'desc');
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        TextColumn::make('title')
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->searchable()
                            ->sortable()
                            ->size(TextColumn\TextColumnSize::Large)
                            ->icon('heroicon-m-megaphone')
                            ->grow(false),

                        BadgeColumn::make('type_of_user_sent_to')
                            ->label('Audience')
                            ->colors([
                                'success' => 'all',
                                'primary' => 'teacher',
                                'warning' => 'student',
                                'danger' => 'admin',
                                'info' => 'parent',
                            ])
                            ->icons([
                                'all' => 'heroicon-m-globe-alt',
                                'teacher' => 'heroicon-m-academic-cap',
                                'student' => 'heroicon-m-user-group',
                                'admin' => 'heroicon-m-shield-check',
                                'parent' => 'heroicon-m-home',
                            ])
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'all' => 'Everyone',
                                'teacher' => 'Teachers',
                                'student' => 'Students',
                                'admin' => 'Admins',
                                'parent' => 'Parents',
                                default => ucfirst($state),
                            }),

                        // Only show priority badge if column exists
                        ...static::conditionalColumns([
                            BadgeColumn::make('priority')
                                ->colors([
                                    'success' => 'low',
                                    'primary' => 'medium',
                                    'warning' => 'high',
                                    'danger' => 'urgent',
                                ])
                                ->icons([
                                    'low' => 'heroicon-m-chat-bubble-bottom-center-text',
                                    'medium' => 'heroicon-m-information-circle',
                                    'high' => 'heroicon-m-exclamation-circle',
                                    'urgent' => 'heroicon-m-exclamation-triangle',
                                ])
                                ->visible(fn() => Schema::hasColumn('announcements', 'priority')),
                        ]),
                    ]),

                    TextColumn::make('sub')
                        ->color('gray')
                        ->size(TextColumn\TextColumnSize::Small)
                        ->searchable()
                        ->limit(80)
                        ->placeholder('No subtitle'),

                    Tables\Columns\Layout\Split::make([
                        TextColumn::make('owner.name')
                            ->label('Posted by')
                            ->icon('heroicon-m-user')
                            ->size(TextColumn\TextColumnSize::Small)
                            ->color('gray')
                            ->grow(false),

                        IconColumn::make('file')
                            ->label('Has File')
                            ->boolean()
                            ->trueIcon('heroicon-m-plus')
                            ->falseIcon('')
                            ->trueColor('success')
                            ->size(IconColumn\IconColumnSize::Small)
                            ->grow(false),

                        IconColumn::make('link')
                            ->label('Has Link')
                            ->boolean()
                            ->trueIcon('heroicon-m-link')
                            ->falseIcon('')
                            ->trueColor('info')
                            ->size(IconColumn\IconColumnSize::Small)
                            ->grow(false),

                        TextColumn::make('created_at')
                            ->label('Posted')
                            ->since()
                            ->size(TextColumn\TextColumnSize::Small)
                            ->color('gray')
                            ->dateTimeTooltip()
                            ->sortable(),
                    ]),
                ])->space(2),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                SelectFilter::make('type_of_user_sent_to')
                    ->label('Target Audience')
                    ->options([
                        'all' => 'Everyone',
                        'teacher' => 'Teachers',
                        'student' => 'Students',
                        'admin' => 'Administrators',
                        'parent' => 'Parents',
                    ]),

                SelectFilter::make('from_id')
                    ->label('Posted By')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('has_attachments')
                    ->label('Has Attachments')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('file')),

                Filter::make('recent')
                    ->label('Recent (Last 7 days)')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),

                // Only show priority filter if column exists
                ...static::conditionalFilters([
                    SelectFilter::make('priority')
                        ->options([
                            'low' => 'Low Priority',
                            'medium' => 'Medium Priority',
                            'high' => 'High Priority',
                            'urgent' => 'Urgent',
                        ])
                        ->visible(fn() => Schema::hasColumn('announcements', 'priority')),
                ]),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->iconButton()
                        ->icon('heroicon-m-eye')
                        ->color('info'),

                    Tables\Actions\EditAction::make()
                        ->iconButton()
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->visible(fn ($record): bool => $record->from_id === Auth::id()),
                ])
                ->tooltip('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => Auth::user()->user_type === 'admin'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-megaphone')
            ->emptyStateHeading('No announcements yet')
            ->emptyStateDescription('When announcements are posted, they will appear here.')
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
            'view' => Pages\ViewAnnouncement::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = Auth::id();
        $count = static::getModel()::query()
            ->where(function ($query) use ($userId) {
                $query->where('type_of_user_sent_to', 'teacher')
                      ->orWhere('from_id', $userId)
                      ->orWhere('type_of_user_sent_to', 'all');
            })
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'sub', 'text'];
    }

    // Helper method for conditional fields (works with or without new columns)
    protected static function conditionalFields(array $fields): array
    {
        return array_filter($fields, function ($field) {
            if (method_exists($field, 'isVisible')) {
                return $field->isVisible();
            }
            return true;
        });
    }

    // Helper method for conditional columns
    protected static function conditionalColumns(array $columns): array
    {
        return array_filter($columns, function ($column) {
            if (method_exists($column, 'isVisible')) {
                return $column->isVisible();
            }
            return true;
        });
    }

    // Helper method for conditional filters
    protected static function conditionalFilters(array $filters): array
    {
        return array_filter($filters, function ($filter) {
            if (method_exists($filter, 'isVisible')) {
                return $filter->isVisible();
            }
            return true;
        });
    }
}
