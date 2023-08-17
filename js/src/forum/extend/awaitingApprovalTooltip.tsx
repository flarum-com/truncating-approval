import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';

import CommentPost from 'flarum/forum/components/CommentPost';
import Tooltip from 'flarum/common/components/Tooltip';
import icon from 'flarum/common/helpers/icon';
import extractText from 'flarum/common/utils/extractText';

import type ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

export function awaitingApprovalTooltip() {
  extend(CommentPost.prototype, 'headerItems', function (this: CommentPost, items: ItemList<Mithril.Children>) {
    if (!this.attrs.post.awaitingTruncatingApproval()) return;

    items.add(
      'awaitingTruncatingApprovalTooltip',
      <Tooltip text={extractText(app.translator.trans('flarum-com-truncating-approval.forum.post.awaiting_approval_tooltip'))}>
        <span class="Post-awaitingTruncatingApproval">
          {icon('fas fa-low-vision')} {app.translator.trans('flarum-com-truncating-approval.forum.post.awaiting_approval')}
        </span>
      </Tooltip>,
      -5
    );
  });
}
