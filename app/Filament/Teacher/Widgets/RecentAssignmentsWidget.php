<?php

namespace App\Filament\Teacher\Widgets;

use App\Models\Assignment;
use App\Models\Teacher;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RecentAssignmentsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Assignments';
    protected static ?string $description = 'Your latest assignments at a glance';
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $teacher = $this->getCurrentTeacher();

        if (!$teacher) {
            return $table->query(Assignment::query()->whereRaw('1 = 0'));
        }

        return $table
            ->query(
                Assignment::query()
                    ->where('teacher_id', $teacher->id)
                    ->with(['class:id,name', 'subject:id,name,code'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->weight('bold')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'available',
                        'danger' => 'closed',
                    ])
                    ->icons([
                        'draft' => 'heroicon-m-document',
                        'available' => 'heroicon-m-check-circle',
                        'closed' => 'heroicon-m-lock-closed',
                    ]),

                Tables\Columns\TextColumn::make('class.name')
                    ->label('Class')
                    ->icon('heroicon-m-academic-cap'),

                Tables\Columns\TextColumn::make('subject.code')
                    ->label('Subject')
                    ->icon('heroicon-m-book-open'),

                Tables\Columns\TextColumn::make('total_students_answered')
                    ->label('Submissions')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('info'),

                Tables\Columns\TextColumn::make('deadline')
                    ->label('Due')
                    ->since()
                    ->icon(fn ($record) => $record->is_overdue ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-clock')
                    ->color(fn ($record) => $record->is_overdue ? 'danger' : 'gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Assignment $record): string => route('filament.teacher.resources.assignments.view', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Assignment $record): string => route('filament.teacher.resources.assignments.edit', $record)),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('No recent assignments')
            ->emptyStateDescription('Create your first assignment to get started.')
            ->striped()
            ->paginated(false);
    }

    private function getCurrentTeacher(): ?Teacher
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return Cache::remember(
            "teacher_data_{$user->id}",
            600,
            fn() => Teacher::where('email', $user->email)->first()
        );
    }

    public static function canView(): bool
    {
        return Auth::check();
    }
}
