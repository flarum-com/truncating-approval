<?php

/*
 * This file is part of flarum-com/truncating-approval.
 *
 * Copyright (c) 2023 Flarum Commercial Team.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FlarumCom\TruncatingApproval\Formatter;

use Flarum\Http\RequestUtil;
use Flarum\Post\CommentPost;
use Flarum\Settings\SettingsRepositoryInterface;
use FlarumCom\TruncatingApproval\XmlUtils;
use Psr\Http\Message\ServerRequestInterface;
use s9e\TextFormatter\Renderer;

class RemoveRestrictedBbcodes
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(
        Renderer $parser,
        mixed $context,
        string $xml,
        ?ServerRequestInterface $request
    ): string {
        if (!($context instanceof CommentPost)) {
            return $xml;
        }

        $actor = RequestUtil::getActor($request);
        $discussion = $context->discussion;

        if ($context->awaiting_truncating_approval === false) {
            return $xml;
        }

        // If the post author is the current user, or the current user can approve
        if ($context->user->id === $actor->id || $actor->can('discussion.approveTruncatingApprovalPosts', $discussion)) {
            return $xml;
        }

        $restrictedTags = json_decode($this->settings->get('flarum-com-truncating-approval.restricted_bbcodes'), true, 2);

        $xml = XmlUtils::stripXmlTagsFromXmlString($restrictedTags, $xml);

        return $xml;
    }
}
