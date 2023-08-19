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

use Flarum\Post\CommentPost;
use Flarum\Post\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use FlarumCom\TruncatingApproval\Event\PostWasApproved;
use FlarumCom\TruncatingApproval\Event\PostWasRejected;
use FlarumCom\TruncatingApproval\XmlUtils;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\Dispatcher as EventsDispatcher;
use Illuminate\Support\Arr;

class ApproveRejectContent
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
        $events->listen(Saving::class, [$this, 'approveRejectTruncatingApprovalPost']);
    }

    public function approveRejectTruncatingApprovalPost(Saving $event)
    {
        $attributes = $event->data['attributes'];
        $post = $event->post;

        $isApproved = Arr::get($attributes, 'truncatingApprove', null);

        if ($isApproved !== null) {
            $event->actor->assertCan('discussion.approveTruncatingApprovalPosts', $post);

            if (!($post instanceof CommentPost)) {
                throw new \InvalidArgumentException('Only comment posts can be approved or rejected.');
            }

            $isApproved = (bool) $isApproved;
            $post->awaiting_truncating_approval = false;

            if ($isApproved) {
                $post->afterSave(function ($post) use ($event) {
                    $this->events->dispatch(new PostWasApproved($post, $event->actor));
                });
            } else {
                $reason = Arr::get($attributes, 'truncatingRejectReason', null);

                if ($reason === null || trim($reason) === '') {
                    throw new \InvalidArgumentException('A reason must be provided when rejecting a post.');
                }

                /** @var SettingsRepositoryInterface */
                $settings = resolve(SettingsRepositoryInterface::class);

                $forbiddenCodes = json_decode($settings->get('flarum-com-truncating-approval.restricted_bbcodes'), true, 2);
                $truncatedXml = XmlUtils::stripXmlTagsFromXmlString($forbiddenCodes, $post->getParsedContentAttribute());

                $post->setParsedContentAttribute($truncatedXml);

                $post->afterSave(function ($post) use ($event, $reason) {
                    $this->events->dispatch(new PostWasRejected($post, $event->actor, trim($reason)));
                });
            }
        }
    }
}
