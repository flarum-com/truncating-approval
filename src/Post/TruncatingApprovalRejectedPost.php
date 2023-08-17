<?php

/*
 * This file is part of flarum-com/truncating-approval.
 *
 * Copyright (c) Flarum Commercial.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FlarumCom\TruncatingApproval\Post;

use Carbon\Carbon;
use Flarum\Post\AbstractEventPost;

class TruncatingApprovalRejectedPost extends AbstractEventPost
{
    /**
     * {@inheritdoc}
     */
    public static $type = 'truncatingApprovalRejected';

    /**
     * Create a new instance in reply to a discussion.
     *
     * @param int $discussionId
     * @param int $userId
     * @param int $postId
     * @param string $reason
     * @return static
     */
    public static function reply($discussionId, $userId, int $postId, string $reason)
    {
        $post = new static;

        $post->content = static::buildContent($postId, $reason);
        $post->created_at = Carbon::now();
        $post->discussion_id = $discussionId;
        $post->user_id = $userId;

        return $post;
    }

    /**
     * Build the content attribute.
     *
     * @return array
     */
    public static function buildContent(int $postId, string $reason)
    {
        return ['postId' => $postId, 'reason' => $reason];
    }
}
