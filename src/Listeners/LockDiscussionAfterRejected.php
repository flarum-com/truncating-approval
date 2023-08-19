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

use Flarum\Extension\ExtensionManager;
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

    /**
     * @var ExtensionManager $extensionManager
     */
    protected $extensionManager;

    public function __construct(SettingsRepositoryInterface $settings, Dispatcher $events, ExtensionManager $extensionManager)
    {
        $this->settings = $settings;
        $this->events = $events;
        $this->extensionManager = $extensionManager;
    }

    /**
     * @param PostWasRejected $event
     */
    public function handle(PostWasRejected $event)
    {
        if (!class_exists(DiscussionWasLocked::class) || !$this->extensionManager->isEnabled('flarum-lock')) {
            // Lock is not installed/enabled
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
