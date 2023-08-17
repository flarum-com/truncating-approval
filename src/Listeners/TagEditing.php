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

use Flarum\Tags\Event\Saving;
use Illuminate\Support\Arr;

class TagEditing
{
    public function handle(Saving $event)
    {
        $attributes = Arr::get($event->data, 'attributes', []);

        if (isset($attributes['usesTruncatingApproval'])) {
            $event->tag->uses_truncating_approval = $attributes['usesTruncatingApproval'];
        }

        return $event;
    }
}
