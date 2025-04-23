<?php

namespace App\Filament\App\Resources\ComplaintResource\Pages;

use App\Filament\App\Resources\ComplaintResource;
use App\Models\ComplaintReply;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ViewComplaint extends ViewRecord
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addReply')
                ->label('Add Reply')
                ->icon('heroicon-o-chat-bubble-left')
                ->modalHeading('Add Reply')
                ->modalDescription('Add a response to this complaint')
                ->form([
                    RichEditor::make('message')
                        ->required()
                        ->fileAttachmentsDisk('s3')
                        ->fileAttachmentsDirectory('complaint-replies')
                        ->fileAttachmentsVisibility('public'),
                    Toggle::make('is_admin')
                        ->label('Admin Reply')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $complaint = $this->getRecord();

                    ComplaintReply::create([
                        'complaint_id' => $complaint->id,
                        'user_id' => Auth::id(),
                        'message' => $data['message'],
                        'is_admin' => $data['is_admin'],
                    ]);

                    $this->notify('success', 'Reply added successfully');
                    $this->redirect(ComplaintResource::getUrl('view', ['record' => $complaint]));
                }),

            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Complaint Details')
                    ->schema([
                        TextEntry::make('subject')
                            ->label('Subject')
                            ->size('text-xl font-bold'),
                        TextEntry::make('user.name')
                            ->label('Created By'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'open' => 'warning',
                                'in_progress' => 'primary',
                                'resolved' => 'success',
                            }),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Message Thread')
                    ->schema([
                        // Use standard TextEntry with HTML rendering for the initial message
                        TextEntry::make('message')
                            ->label('Initial Message')
                            ->html(),

                        // Custom component to display the thread of replies
                        TextEntry::make('replies')
                            ->label('Replies')
                            ->html()
                            ->formatStateUsing(function ($state) {
                                // Check if $state is a collection or array before counting
                                if (empty($state) || (is_array($state) && count($state) === 0) ||
                                    (is_object($state) && method_exists($state, 'isEmpty') && $state->isEmpty())) {
                                    return new HtmlString('<div class="text-gray-500 italic">No replies yet.</div>');
                                }

                                $html = '<div class="space-y-4">';

                                // Make sure we're iterating over a collection/array
                                if (is_object($state) && method_exists($state, 'each')) {
                                    // It's a collection
                                    $state->each(function ($reply) use (&$html) {
                                        $html .= $this->formatReply($reply);
                                    });
                                } elseif (is_array($state)) {
                                    // It's an array
                                    foreach ($state as $reply) {
                                        $html .= $this->formatReply($reply);
                                    }
                                } else {
                                    // Not iterable, could be a single reply or something else
                                    return new HtmlString('<div class="text-gray-500 italic">Unable to display replies.</div>');
                                }

                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                    ])
            ]);
    }

    // Helper method to format a single reply
    private function formatReply($reply): string
    {
        if (!is_object($reply)) {
            return '';
        }

        $isAdmin = $reply->is_admin ?? false;
        $alignment = $isAdmin ? 'text-right' : 'text-left';
        $background = $isAdmin ? 'bg-primary-50' : 'bg-gray-50';
        $border = $isAdmin ? 'border-primary-200' : 'border-gray-200';

        $html = '<div class="'.$alignment.' mb-4">';
        $html .= '<div class="inline-block max-w-3xl p-4 rounded-lg '.$background.' border '.$border.'">';
        $html .= '<div class="flex items-center gap-2 mb-2">';
        $html .= '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M'.($isAdmin ? '9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : '17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z').'" /></svg>';

        // Get the user name safely
        $userName = '';
        if (isset($reply->user) && is_object($reply->user) && isset($reply->user->name)) {
            $userName = $reply->user->name;
        }

        $html .= '<span class="font-medium">'.($isAdmin ? 'Admin' : $userName).'</span>';

        // Format the date safely
        $dateFormatted = '';
        if (isset($reply->created_at)) {
            try {
                $dateFormatted = date('M j, Y g:i A', strtotime($reply->created_at));
            } catch (\Exception $e) {
                $dateFormatted = '(unknown date)';
            }
        }

        $html .= '<span class="text-sm text-gray-500">'.$dateFormatted.'</span>';
        $html .= '</div>';
        $html .= '<div class="prose max-w-none">'.($reply->message ?? '').'</div>';
        $html .= '</div></div>';

        return $html;
    }
}
