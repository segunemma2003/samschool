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
use Illuminate\Support\Facades\Cache;

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
                Section::make('📢 Announcement Details')
                    ->description('Create engaging announcements for your audience')
                    ->icon('heroicon-m-megaphone')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('title')
                                    ->label('📝 Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter a compelling title...')
                                    ->columnSpan(['default' => 1, 'md' => 2])
                                    ->prefixIcon('heroicon-m-pencil')
                                    ->live(onBlur: true),

                                TextInput::make('sub')
                                    ->label('📄 Subtitle')
                                    ->maxLength(100)
                                    ->placeholder('Brief description...')
                                    ->columnSpan(['default' => 1, 'md' => 2])
                                    ->prefixIcon('heroicon-m-document-text')
                                    ->helperText('This appears as a preview under the title'),

                                Select::make('type_of_user_sent_to')
                                    ->label('🎯 Target Audience')
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
                                    ->columnSpan(1)
                                    ->prefixIcon('heroicon-m-users'),

                                // Conditional priority field
                                Select::make('priority')
                                    ->label('⚡ Priority Level')
                                    ->options([
                                        'low' => '🟢 Low Priority',
                                        'medium' => '🟡 Medium Priority',
                                        'high' => '🔴 High Priority',
                                        'urgent' => '🚨 Urgent',
                                    ])
                                    ->default('medium')
                                    ->native(false)
                                    ->columnSpan(1)
                                    ->prefixIcon('heroicon-m-flag')
                                    ->visible(fn() => static::columnExists('priority')),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                Section::make('✍️ Content')
                    ->description('Add rich content to your announcement')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        RichEditor::make('text')
                            ->label('📝 Announcement Content')
                            ->placeholder('Write your announcement here...')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'link', 'heading', 'bulletList', 'orderedList',
                                'blockquote', 'codeBlock',
                            ])
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('s3')
                            ->fileAttachmentsDirectory('announcements/attachments'),

                        TextInput::make('link')
                            ->label('🔗 Additional Link')
                            ->url()
                            ->placeholder('https://example.com')
                            ->helperText('Optional: Add a relevant link for more information')
                            ->prefixIcon('heroicon-m-link')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed(),

                Section::make('📎 Attachments')
                    ->description('Add files or images to support your announcement')
                    ->icon('heroicon-m-paper-clip')
                    ->schema([
                        FileUpload::make('file')
                            ->label('📁 Upload File')
                            ->disk('s3')
                            ->directory('announcements')
                            ->acceptedFileTypes(['image/*', 'application/pdf', '.doc', '.docx', '.txt'])
                            ->maxSize(10240) // 10MB
                            ->imagePreviewHeight('200')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->columnSpanFull()
                            ->helperText('Max file size: 10MB. Supported formats: Images, PDF, Word documents'),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(static::getOptimizedQuery())
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    // Header row with title and badges
                    Tables\Columns\Layout\Split::make([
                        TextColumn::make('title')
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->searchable()
                            ->sortable()
                            ->size(TextColumn\TextColumnSize::Large)
                            ->icon('heroicon-m-megaphone')
                            ->grow(true)
                            ->tooltip(fn ($record) => $record->title),

                        Tables\Columns\Layout\Stack::make([
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

                            BadgeColumn::make('priority')
                                ->colors([
                                    'success' => 'low',
                                    'primary' => 'medium',
                                    'warning' => 'high',
                                    'danger' => 'urgent',
                                ])
                                ->icons([
                                    'low' => 'heroicon-m-information-circle',
                                    'medium' => 'heroicon-m-exclamation-circle',
                                    'high' => 'heroicon-m-exclamation-triangle',
                                    'urgent' => 'heroicon-m-fire',
                                ])
                                ->visible(fn() => static::columnExists('priority')),
                        ])->alignment('end'),
                    ]),

                    // Subtitle
                    TextColumn::make('sub')
                        ->color('gray')
                        ->size(TextColumn\TextColumnSize::Small)
                        ->searchable()
                        ->limit(100)
                        ->placeholder('No subtitle')
                        ->tooltip(fn ($record) => $record->sub),

                    // Footer row with metadata
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\Layout\Stack::make([
                            TextColumn::make('owner.name')
                                ->label('Posted by')
                                ->icon('heroicon-m-user')
                                ->size(TextColumn\TextColumnSize::Small)
                                ->color('gray'),

                            TextColumn::make('created_at')
                                ->label('Posted')
                                ->since()
                                ->size(TextColumn\TextColumnSize::Small)
                                ->color('gray')
                                ->dateTimeTooltip()
                                ->sortable(),
                        ]),

                        Tables\Columns\Layout\Stack::make([
                            IconColumn::make('file')
                                ->label('📎')
                                ->boolean()
                                ->trueIcon('heroicon-m-paper-clip')
                                ->falseIcon('')
                                ->trueColor('success')
                                ->size(IconColumn\IconColumnSize::Small)
                                ->tooltip('Has attachment'),

                            IconColumn::make('link')
                                ->label('🔗')
                                ->boolean()
                                ->trueIcon('heroicon-m-link')
                                ->falseIcon('')
                                ->trueColor('info')
                                ->size(IconColumn\IconColumnSize::Small)
                                ->tooltip('Has link'),
                        ])->alignment('end'),
                    ]),
                ])->space(3),
            ])
            ->contentGrid([
                'md' => 1,
                'lg' => 2,
                'xl' => 3,
                '2xl' => 4,
            ])
            ->filters([
                SelectFilter::make('type_of_user_sent_to')
                    ->label('🎯 Target Audience')
                    ->options([
                        'all' => '🌐 Everyone',
                        'teacher' => '👨‍🏫 Teachers',
                        'student' => '👨‍🎓 Students',
                        'admin' => '👑 Administrators',
                        'parent' => '👨‍👩‍👧‍👦 Parents',
                    ])
                    ->multiple(),

                SelectFilter::make('from_id')
                    ->label('👤 Posted By')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Filter::make('has_attachments')
                    ->label('📎 Has Attachments')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('file')),

                Filter::make('recent')
                    ->label('🕒 Recent (Last 7 days)')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),

                SelectFilter::make('priority')
                    ->label('⚡ Priority')
                    ->options([
                        'low' => '🟢 Low',
                        'medium' => '🟡 Medium',
                        'high' => '🔴 High',
                        'urgent' => '🚨 Urgent',
                    ])
                    ->multiple()
                    ->visible(fn() => static::columnExists('priority')),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(['default' => 1, 'md' => 2, 'xl' => 4])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->iconButton()
                        ->icon('heroicon-m-eye')
                        ->color('info')
                        ->tooltip('View announcement'),

                    Tables\Actions\EditAction::make()
                        ->iconButton()
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->tooltip('Edit announcement')
                        ->visible(fn ($record): bool => $record->from_id === Auth::id()),
                ])
                ->tooltip('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => Auth::user()->user_type === 'admin'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-megaphone')
            ->emptyStateHeading('📢 No announcements yet')
            ->emptyStateDescription('When announcements are posted, they will appear here.')
            ->striped()
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession();
    }

    // Optimized query method
    protected static function getOptimizedQuery(): Builder
    {
        $userId = Auth::id();

        return static::getModel()::query()
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
                ...(static::columnExists('priority') ? ['announcements.priority'] : []),
                ...(static::columnExists('status') ? ['announcements.status'] : []),
                ...(static::columnExists('views_count') ? ['announcements.views_count'] : []),
            ])
            ->with(['owner:id,name,avatar,user_type'])
            ->where(function ($query) use ($userId) {
                $query->where('type_of_user_sent_to', 'teacher')
                      ->orWhere('from_id', $userId)
                      ->orWhere('type_of_user_sent_to', 'all');
            })
            ->when(static::columnExists('status'), function ($query) {
                return $query->where('status', 'published');
            })
            ->orderBy('created_at', 'desc');
    }

    // Cached column existence check
    protected static function columnExists(string $column): bool
    {
        return Cache::remember(
            "announcements_column_exists_{$column}",
            3600, // 1 hour
            fn() => Schema::hasColumn('announcements', $column)
        );
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
        $count = Cache::remember(
            'announcements_nav_badge_' . Auth::id(),
            300, // 5 minutes
            function () {
                $userId = Auth::id();
                return static::getModel()::query()
                    ->where(function ($query) use ($userId) {
                        $query->where('type_of_user_sent_to', 'teacher')
                              ->orWhere('from_id', $userId)
                              ->orWhere('type_of_user_sent_to', 'all');
                    })
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
            }
        );

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
}
