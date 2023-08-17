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

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Lock\Event\DiscussionWasLocked;
use FlarumCom\TruncatingApproval\Event\PostWasRejected;

class LockDiscussionAfterRejected
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var Dispatcher $events
     */
    protected $events;

    public function __construct(SettingsRepositoryInterface $settings, Dispatcher $events)
    {
        $this->settings = $settings;
        $this->events = $events;
    }

    /**
     * @param PostWasRejected $event
     */
    public function handle(PostWasRejected $event)
    {
        if (!class_exists(DiscussionWasLocked::class)) {
            // Lock is not installed
            return;
        }

        if ((bool) $this->settings->get('flarum-com-truncating-approval.lock-after-rejection') === false) {
            return;
        }

        $post = $event->post;
        $actor = $event->actor;

        if ($post->number !== 1) {
            return;
        }

        $discussion = $post->discussion;

        if ((bool) $post->discussion->is_locked === true) {
            return;
        }

        $discussion->is_locked = true;

        $discussion->save();

        $discussion->raise(new DiscussionWasLocked($discussion, $actor));
    }
}
