<?php

/*
 * This file is part of flarum-com/truncating-approval.
 *
 * Copyright (c) Flarum Commercial.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FlarumCom\TruncatingApproval\Event;

use Flarum\Post\Post;
use Flarum\User\User;

class PostWasRejected
{
    /**
     * The post that was rejected.
     *
     * @var Post
     */
    public $post;

    /**
     * @var User
     */
    public $actor;

    /**
     * @var string
     */
    public $reason;

    /**
     * @param Post $post
     * @param User $actor
     * @param string $reason
     */
    public function __construct(Post $post, User $actor, string $reason)
    {
        $this->post = $post;
        $this->actor = $actor;
        $this->reason = $reason;
    }
}
