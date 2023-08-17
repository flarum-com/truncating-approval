<?php

/*
 * This file is part of flarum-com/truncating-approval.
 *
 * Copyright (c) 2023 Flarum Commercial Team.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FlarumCom\TruncatingApproval\Access;

use Closure;
use Flarum\Discussion\Discussion;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder;

class ScopePrivatePostVisibility
{
    /**
     * @param Builder $query
     * @param User $actor
     */
    public function __invoke(User $actor, Builder $query)
    {
        // All statements need to be wrapped in an orWhere, since we're adding a
        // subset of private posts that should be visible, not restricting the visible
        // set.
        $query->orWhere(function ($query) use ($actor) {
            // Show private posts if they require approval and they are
            // authored by the current user, or the current user has permission to
            // approve posts.
            $query->where('posts.awaiting_truncating_approval', true);

            if (!$actor->hasPermission('discussion.approveTruncatingApprovalPosts')) {
                $query->where(function (Builder $query) use ($actor) {
                    $query->where('posts.user_id', $actor->id)
                        ->orWhereExists($this->discussionWhereCanApprovePosts($actor));
                });
            }
        });
    }

    /**
     * Looks if the actor has permission to approve posts,
     * within the discussion which the post is a part of.
     */
    private function discussionWhereCanApprovePosts(User $actor): Closure
    {
        return function ($query) use ($actor) {
            $query->selectRaw('1')
                ->from('discussions')
                ->whereColumn('discussions.id', 'posts.discussion_id')
                ->where(function ($query) use ($actor) {
                    $query->whereRaw('1 != 1')->orWhere(function ($query) use ($actor) {
                        Discussion::query()->setQuery($query)->whereVisibleTo($actor, 'approveTruncatingApprovalPosts');
                    });
                });
        };
    }
}
