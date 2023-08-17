<?php

namespace FlarumCom\TruncatingApproval\Api\Controller;

use Flarum\Http\RequestUtil;
use Flarum\Post\CommentPost;
use Flarum\Settings\SettingsRepositoryInterface;
use FlarumCom\TruncatingApproval\XmlUtils;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PreviewTruncatedPost implements RequestHandlerInterface
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $postId = Arr::get($request->getQueryParams(), 'id');

        /**
         * @var CommentPost
         */
        $post = CommentPost::with('discussion')->whereVisibleTo($actor)->findOrFail($postId);

        if ($post->type !== 'comment') {
            throw new \InvalidArgumentException('Only comments can be truncated.');
        }

        $actor->assertCan('discussion.approveTruncatingApprovalPosts', $post->discussion);

        /** @var array */
        $forbiddenCodes = json_decode($this->settings->get('flarum-com-truncating-approval.restricted_bbcodes'), true, 2);

        $newXml = XmlUtils::stripXmlTagsFromXmlString($forbiddenCodes, $post->getParsedContentAttribute());
        $newHtml = CommentPost::getFormatter()->render($newXml, $post, $request);

        return new JsonResponse(['html' => $newHtml], 200, [], JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT);
    }
}
