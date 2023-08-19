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

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Flags\Flag;
use Flarum\Post\CommentPost;
use Flarum\Post\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use FlarumCom\TruncatingApproval\XmlUtils;
use Illuminate\Contracts\Events\Dispatcher;

class UnapprovePostsWithForbiddenBbcodes
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
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Saving::class, [$this, 'unapprovePostsWithForbiddenBbcodes']);
    }

    /**
     * @param Saving $event
     */
    public function unapprovePostsWithForbiddenBbcodes(Saving $event)
    {
        $post = $event->post;

        if (!($post instanceof CommentPost)) {
            return;
        }

        if ($event->actor->can('bypassRestrictedBbcodes', $post->discussion)) {
            if ($post->awaiting_truncating_approval === null) {
                $post->awaiting_truncating_approval = false;
            }

            return;
        }

        $post->afterSave(function ($post) {
            if (!$this->postHasForbiddenBbcodes($post)) {
                if ($post->awaiting_truncating_approval === true) {
                    $post->awaiting_truncating_approval = false;
                    $post->save();
                }

                if ($post->number === 1 && $post->discussion->awaiting_truncating_approval === true) {
                    $post->discussion->awaiting_truncating_approval = false;
                    $post->discussion->save();
                }

                return;
            }

            $post->awaiting_truncating_approval = true;
            $post->save();

            if ($post->number === 1) {
                $post->discussion->awaiting_truncating_approval = true;
                $post->discussion->save();
            }

            if ($post->flags()->where('type', 'truncatingApproval')->exists()) {
                // A flag already exists for this post, so we don't need to create a new one.
                $post->flags()->where('type', 'truncatingApproval')->update(['created_at' => Carbon::now()]);
                return;
            }

            $flag = new Flag;

            $flag->post_id = $post->id;
            $flag->type = 'truncatingApproval';
            $flag->created_at = Carbon::now();

            $flag->save();
        });
    }

    protected function postHasForbiddenBbcodes(CommentPost $post): bool
    {
        /** @var array */
        $forbiddenCodes = json_decode($this->settings->get('flarum-com-truncating-approval.restricted_bbcodes'), true, 2);

        $content = XmlUtils::parseXmlToArray($post->getParsedContentAttribute());

        return $this->checkParsedXmlForBbcodes($content, $forbiddenCodes);
    }

    /**
     * @return boolean `true` if the post contains any of the forbidden bbcodes
     */
    protected function checkParsedXmlForBbcodes(array $data, array $restrictedCodes): bool
    {
        $flattened = XmlUtils::getXmlTagsFromArrayRecursive($data);

        $flattened = array_map(fn ($x) => strtoupper($x), $flattened);
        $restrictedCodes = array_map(fn ($x) => strtoupper($x), $restrictedCodes);

        $matches = array_intersect(array_keys($flattened), $restrictedCodes);

        if (count($matches) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param Discussion|CommentPost $instance
     * @return bool|null
     */
    public static function markUnapprovedContentAsPrivate($instance)
    {
        if ($instance->awaiting_truncating_approval) {
            return true;
        }
    }
}
