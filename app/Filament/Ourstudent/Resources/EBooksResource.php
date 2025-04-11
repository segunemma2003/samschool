<?php

namespace App\Filament\Ourstudent\Resources;

use App\Filament\Ourstudent\Resources\EbooksResource\Pages;
use App\Filament\Ourstudent\Resources\EbooksResource\RelationManagers;
use App\Models\AcademicYear;
use App\Models\Ebooks;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EbooksResource extends Resource
{
    protected static ?string $model = Ebooks::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $userId = Auth::user()->id;
        $user = User::whereId($userId)->first();
        $teacher = Teacher::where('email', $user->email)->first();
        Subject::all();
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {

        $user = Auth::user();
        $academicYear = AcademicYear::whereStatus('true')->first();
        $academicYearId = $academicYear->id ?? null;
    // dd($user);
        if (!$user) {
            return $table;
        }

        $student = Student::whereEmail($user->email)->first();

        return $table
        ->modifyQueryUsing(function (Builder $query) use ($student, $academicYearId) {
            if ($student && $academicYearId) {
                $query->whereHas('subject', function ($subQuery) use ($student, $academicYearId) {
                    $subQuery->where('class_id', $student->class_id);
                })->with(['subject']);
            }
        })->columns([
            TextColumn::make('index')
            ->rowIndex(),
                        TextColumn::make('title')
                        ->description('description')->limit(50),
                        TextColumn::make('link')->copyable()
                        ->copyableState(fn (string $state): string => "URL: {$state}"),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Action::make('preview')
                ->label('Preview File')
                ->icon('heroicon-o-eye')
                ->modalHeading('File Preview')
                ->modalContent(function ($record) {
                    $url = Storage::disk('s3')->temporaryUrl($record->file, now()->addMinutes(10));
                    $ext = pathinfo($record->file, PATHINFO_EXTENSION);
                    // dd($url);
                    return view('components.ebook-preview-modal', [
                        'url' => $url,
                        'ext' => $ext,
                    ]);
                }),

            Action::make('download')
                ->label('Download File')
                ->icon('heroicon-m-arrow-down-tray')
                ->url(fn ($record) => Storage::disk('s3')->temporaryUrl($record->file, now()->addMinutes(10)))
                ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListEbooks::route('/'),
            'create' => Pages\CreateEbooks::route('/create'),
            'view' => Pages\ViewEbooks::route('/{record}'),
            'edit' => Pages\EditEbooks::route('/{record}/edit'),
        ];
    }
}
