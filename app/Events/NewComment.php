<?php
// app/Events/NewComment.php

namespace App\Events;

use App\Models\RecipeComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewComment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;

    public function __construct(RecipeComment $comment)
    {
        $this->comment = $comment->load('user.profile');
    }

    public function broadcastOn()
    {
        // Channel name: recipe.{recipe_id}
        return new Channel('recipe.' . $this->comment->recipe_id);
    }

    public function broadcastAs()
    {
        return 'new-comment';
    }

    public function broadcastWith()
    {
        return [
            'comment' => [
                'id' => $this->comment->id,
                'content' => $this->comment->content,
                'parent_id' => $this->comment->parent_id,
                'created_at' => $this->comment->created_at->toISOString(),
                'user' => [
                    'id' => $this->comment->user->id,
                    'username' => $this->comment->user->username,
                    'profile' => $this->comment->user->profile ? [
                        'name' => $this->comment->user->profile->name,
                        'image_path' => $this->comment->user->profile->image_path
                    ] : null
                ]
            ]
        ];
    }
}