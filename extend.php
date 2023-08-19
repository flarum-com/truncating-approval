<?php

/*
 * This file is part of flarum-com/truncating-approval.
 *
 * Copyright (c) 2023 Flarum Commercial Team.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FlarumCom\TruncatingApproval;

use Flarum\Api\Serializer\BasicDiscussionSerializer;
use Flarum\Api\Serializer\PostSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Extend;
use Flarum\Post\Post;
use Flarum\Tags\Api\Serializer\TagSerializer;
use Flarum\Tags\Tag;
use Flarum\Tags\Event\Creating as TagCreating;
use Flarum\Tags\Event\Saving as TagSaving;
use FlarumCom\TruncatingApproval\Event\PostWasRejected;
use FlarumCom\TruncatingApproval\Post\TruncatingApprovalRejectedPost;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/less/admin.less'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\Settings())
        ->default('flarum-com-truncating-approval.lock-after-rejection', false)
        ->default('flarum-com-truncating-approval.eventpost-after-reject', false)
        ->default('flarum-com-truncating-approval.restricted-bbcodes', ''),

    (new Extend\ApiSerializer(BasicDiscussionSerializer::class))
        ->attribute('awaitingTruncatingApproval', function ($serializer, Discussion $discussion) {
            return $discussion->awaiting_truncating_approval;
        }),

    (new Extend\ApiSerializer(PostSerializer::class))
        ->attribute('awaitingTruncatingApproval', function ($serializer, Post $post) {
            return (bool) $post->awaiting_truncating_approval;
        })->attribute('canApproveTruncatingApproval', function (PostSerializer $serializer, Post $post) {
            return (bool) $serializer->getActor()->can('approveTruncatingApprovalPosts', $post->discussion);
        }),

    (new Extend\ApiSerializer(TagSerializer::class))
        ->attributes(function (TagSerializer $serializer, Tag $tag, array $attributes) {
            $attributes['usesTruncatingApproval'] = (bool) $tag->uses_truncating_approval;

            return $attributes;
        }),

    // Discussions should be approved by default
    (new Extend\Model(Discussion::class))
        ->default('awaiting_truncating_approval', false)
        ->cast('awaiting_truncating_approval', 'bool'),

    // Posts should be approved by default
    (new Extend\Model(Post::class))
        ->default('awaiting_truncating_approval', false)
        ->cast('awaiting_truncating_approval', 'bool'),

    (new Extend\Event())
        ->listen(TagCreating::class, Listeners\TagCreating::class)
        ->listen(TagSaving::class, Listeners\TagEditing::class)
        ->listen(PostWasRejected::class, Listeners\LockDiscussionAfterRejected::class)
        ->listen(PostWasRejected::class, Listeners\PostEventPostAfterRejected::class)
        ->subscribe(Listeners\PostWasApprovedOrRejectedHandler::class)
        ->subscribe(Listeners\ApproveRejectContent::class)
        ->subscribe(Listeners\UnapprovePostsWithForbiddenBbcodes::class),

    (new Extend\Policy())
        ->modelPolicy(Tag::class, Access\TagPolicy::class),

    (new Extend\ModelVisibility(Post::class))
        ->scope(Access\ScopePrivatePostVisibility::class, 'viewPrivate'),

    (new Extend\ModelVisibility(Discussion::class))
        ->scope(Access\ScopePrivateDiscussionVisibility::class, 'viewPrivate'),

    (new Extend\ModelPrivate(Discussion::class))
        ->checker([Listeners\UnapprovePostsWithForbiddenBbcodes::class, 'markUnapprovedContentAsPrivate']),

    (new Extend\ModelPrivate(CommentPost::class))
        ->checker([Listeners\UnapprovePostsWithForbiddenBbcodes::class, 'markUnapprovedContentAsPrivate']),

    (new Extend\Formatter())
        ->render(Formatter\RemoveRestrictedBbcodes::class),

    (new Extend\Post())
        ->type(TruncatingApprovalRejectedPost::class),

    (new Extend\Routes('api'))
        ->get('/truncating-approval-preview/{id}', 'flarum-com.truncating-approval.preview-post', Api\Controller\PreviewTruncatedPost::class),
];
