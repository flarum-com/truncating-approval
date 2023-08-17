<?php

/*
 * This file is part of flarum-com/truncating-approval.
 *
 * Copyright (c) Flarum Commercial.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FlarumCom\TruncatingApproval\Listeners;

use Flarum\Flags\Event\Deleting as FlagDeleting;
use FlarumCom\TruncatingApproval\Event\PostWasApproved;
use FlarumCom\TruncatingApproval\Event\PostWasRejected;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\Dispatcher as EventsDispatcher;

class PostWasApprovedOrRejectedHandler
{
    /**
     * @var EventsDispatcher
     */
    protected $events;

    public function __construct(EventsDispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(PostWasApproved::class, [$this, 'handleApprovalOrRejection']);
        $events->listen(PostWasRejected::class, [$this, 'handleApprovalOrRejection']);
    }

    /**
     * @param PostWasApproved|PostWasRejected $event
     */
    public function handleApprovalOrRejection(PostWasApproved|PostWasRejected $event)
    {
        $post = $event->post;
        $discussion = $post->discussion;
        $user = $discussion->user;

        $discussion->refreshCommentCount();
        $discussion->refreshLastPost();

        if ($post->number === 1) {
            $discussion->awaiting_truncating_approval = false;

            $discussion->afterSave(function () use ($user) {
                $user->refreshDiscussionCount();
            });
        }

        $discussion->save();

        if ($discussion->user) {
            $user->refreshCommentCount();
            $user->save();
        }

        if ($post->user) {
            $post->user->refreshCommentCount();
            $post->user->save();
        }

        // Dismiss any flags that were raised for this post.

        /** @var Collection */
        $flags = $post->flags()->where('type', 'truncatingApproval')->get();

        foreach ($flags as $flag) {
            $this->events->dispatch(new FlagDeleting($flag, $event->actor, []));
        }

        $post->flags()->where('type', 'truncatingApproval')->delete();
    }
}
