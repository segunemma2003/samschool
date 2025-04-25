<?php

namespace App\Filament\Ourstudent\Pages;

use App\Models\OnlineLibraryMaterial;
use App\Models\OnlineLibrarySubject;
use App\Models\OnlineLibraryType;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OnlineLibrary extends Page implements HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static string $view = 'filament.ourstudent.pages.online-library';
    protected static ?string $navigationGroup = 'Library';

    use InteractsWithForms;

    public $search = '';
    public $type_id = '';
    public $subject_id = '';
    public $selectedMaterial = null;
    public $currentPage = 1;
    public $rating = 0;
    public $review = '';

    // Loading states
    public $isViewing = false;
    public $isDownloading = false;
    public $isTogglingFavorite = false;
    public $isSubmittingReview = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('search')
                ->placeholder('Search by title, author, etc.')
                ->reactive()
                ->debounce(500),
            Select::make('type_id')
                ->label('Type')
                ->options(OnlineLibraryType::all()->pluck('name', 'id'))
                ->reactive(),
            Select::make('subject_id')
                ->label('Subject')
                ->options(OnlineLibrarySubject::all()->pluck('name', 'id'))
                ->reactive(),
        ];
    }

    public function getMaterialsProperty()
    {
        return OnlineLibraryMaterial::query()
            ->when($this->search, function($query, $search) {
                $query->where(function($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($this->type_id, fn($query) => $query->where('type_id', $this->type_id))
            ->when($this->subject_id, function($query) {
                $query->whereHas('subjects', function($query) {
                    $query->where('id', $this->subject_id);
                });
            })
            ->with(['type', 'subjects'])
            ->paginate(12);
    }

    public function viewMaterial($materialId)
    {
        $this->isViewing = true;

        try {
            $user = User::whereId(Auth::id())->first();
            $this->selectedMaterial = OnlineLibraryMaterial::with(['type', 'subjects', 'reviews.user'])->find($materialId);
            $this->selectedMaterial->increment('view_count');

            $user->readingProgress()->updateOrCreate(
                ['material_id' => $materialId],
                ['last_page' => 1]
            );
        } finally {
            $this->isViewing = false;
        }
    }

    public function downloadMaterial($materialId)
    {
        $this->isDownloading = true;

        try {
            $material = OnlineLibraryMaterial::find($materialId);
            $material->increment('download_count');

            $fileUrl = Storage::disk('s3')->url(
                $material->file_path,
                now()->addMinutes(30)
            );

            return response()->streamDownload(function() use ($fileUrl) {
                echo file_get_contents($fileUrl);
            }, basename($material->file_path));
        } finally {
            $this->isDownloading = false;
        }
    }

    public function toggleFavorite($materialId)
    {
        $this->isTogglingFavorite = true;

        try {
            $user = User::whereId(Auth::id())->first();
            $favorite = $user->favorites()->where('material_id', $materialId)->first();

            if ($favorite) {
                $favorite->delete();
            } else {
                $user->favorites()->create(['material_id' => $materialId]);
            }
        } finally {
            $this->isTogglingFavorite = false;
        }
    }

    public function submitReview()
    {
        $this->isSubmittingReview = true;

        try {
            $this->validate([
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:1000',
            ]);
            $user = User::whereId(Auth::id())->first();
            $user->reviews()->updateOrCreate(
                ['material_id' => $this->selectedMaterial->id],
                ['rating' => $this->rating, 'review' => $this->review]
            );

            $this->reset(['rating', 'review']);
            $this->selectedMaterial->refresh();
        } finally {
            $this->isSubmittingReview = false;
        }
    }

    public function updateReadingProgress($page)
    {
        $user = User::whereId(Auth::id())->first();
        $user->readingProgress()->updateOrCreate(
            ['material_id' => $this->selectedMaterial->id],
            ['last_page' => $page]
        );
    }
}
