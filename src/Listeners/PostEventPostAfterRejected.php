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
use FlarumCom\TruncatingApproval\Event\PostWasRejected;
use FlarumCom\TruncatingApproval\Post\TruncatingApprovalRejectedPost;

class PostEventPostAfterRejected
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param PostWasRejected $event
     */
    public function handle(PostWasRejected $event)
    {
        if ((bool) $this->settings->get('flarum-com-truncating-approval.eventpost-after-reject') === false) {
            return;
        }

        $post = $event->post;
        $actor = $event->actor;
        $reason = $event->reason;

        TruncatingApprovalRejectedPost::reply(
            $post->discussion->id,
            $actor->id,
            $post->id,
            $reason
        )->save();
    }
}
