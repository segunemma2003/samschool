<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Comments extends Component
{
    public Model $model;
    public $newComment = '';
    public $replyTo = null;
    public $editComment = null;
    public $editContent = '';
    public $showReplies = [];

    protected $rules = [
        'newComment' => 'required|string',
        'editContent' => 'required|string',
    ];

    public function mount(Model $model)
    {
        $this->model = $model;
    }

    public function toggleReplies($commentId)
    {
        if (isset($this->showReplies[$commentId])) {
            unset($this->showReplies[$commentId]);
        } else {
            $this->showReplies[$commentId] = true;
        }
    }

    public function startReply($commentId)
    {
        $this->replyTo = $commentId;
        $this->editComment = null;
        $this->newComment = '';
    }

    public function startEdit($comment)
    {
        $this->editComment = $comment['id'];
        $this->editContent = $comment['content'];
        $this->replyTo = null;
    }

    public function postComment()
    {
        $this->validate(['newComment' => 'required|string']);

        $this->model->comments()->create([
            'content' => $this->newComment,
            'user_id' => Auth::id(),
            'parent_id' => $this->replyTo,
        ]);

        $this->reset(['newComment', 'replyTo']);
        $this->model->refresh();
    }

    public function updateComment()
    {
        $this->validate(['editContent' => 'required|string']);

        $comment = Comment::findOrFail($this->editComment);

        if ($comment->user_id === Auth::id()) {
            $comment->update(['content' => $this->editContent]);
            $this->reset(['editComment', 'editContent']);
            $this->model->refresh();
        }
    }

    public function deleteComment($commentId)
    {
        $comment = Comment::findOrFail($commentId);

        if ($comment->user_id === Auth::id()) {
            $comment->delete();
            $this->model->refresh();
        }
    }

    public function render()
    {
        return view('livewire.comments', [
            'comments' => $this->model->comments()
                ->with(['user', 'replies.user'])
                ->whereNull('parent_id')
                ->latest()
                ->get()
        ]);
    }
}
