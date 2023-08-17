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

use Flarum\Tags\Event\Creating;
use Illuminate\Support\Arr;

class TagCreating
{
    public function handle(Creating $event)
    {
        $event->tag->uses_truncating_approval = Arr::get($event->data, 'attributes.usesTruncatingApproval');

        return $event;
    }
}