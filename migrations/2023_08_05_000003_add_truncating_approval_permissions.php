<?php

/*
 * This file is part of flarum-com/truncating-approval.
 *
 * Copyright (c) Flarum Commercial.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Flarum\Database\Migration;
use Flarum\Group\Group;

return Migration::addPermissions([
    'discussion.approveTruncatingApprovalPosts' => Group::MODERATOR_ID,
    'discussion.bypassRestrictedBbcodes' => Group::MODERATOR_ID
]);
