import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';

import { RejectContentModal } from '../components/RejectContentModal';

import PostComponent from 'flarum/forum/components/Post';
import Post from 'flarum/common/models/Post';
import PostControls from 'flarum/forum/utils/PostControls';
import Button from 'flarum/common/components/Button';

import type Mithril from 'mithril';
import type ItemList from 'flarum/common/utils/ItemList';

export function customFlags() {
  override(PostComponent.prototype, 'flagReason', function (original, flag) {
    if (flag.type() === 'truncatingApproval') {
      return app.translator.trans('flarum-com-truncating-approval.forum.post.forbidden_formatting_detected');
    }

    return original(flag);
  });

  extend(PostComponent.prototype, 'flagActionItems', function (this: PostComponent, items: ItemList<Mithril.Children>) {
    items.setContent(
      'dismiss',
      <Button className="Button" icon="fas fa-check" onclick={PostControls.truncatingApproveAction.bind(this.attrs.post, true)}>
        {app.translator.trans('flarum-com-truncating-approval.forum.post.dismiss_flag_button')}
      </Button>
    );

    items.add(
      'truncatingApprove-truncate',
      <Button className="Button Button--danger" icon="fas fa-dumpster-fire" onclick={() => PostControls.truncatingRejectAction(this.attrs.post)}>
        {app.translator.trans('flarum-com-truncating-approval.forum.post.truncate_button')}
      </Button>
    );
  });

  PostControls.truncatingRejectAction = async function (post: Post) {
    return app.modal.show(RejectContentModal, {
      post,
      onsubmit: async (reason: string) => await PostControls.truncatingApproveAction.call(post, false, reason),
    });
  };

  PostControls.truncatingApproveAction = async function (this: Post, truncatingApprove: boolean, reason?: string) {
    const body: Record<string, unknown> = { truncatingApprove };

    if (reason) {
      body.truncatingRejectReason = reason;
    }

    const save = this.save(body);
    this.pushAttributes({ awaitingTruncatingApproval: false });

    if (this.number() === 1) {
      this.discussion().pushAttributes({ awaitingTruncatingApproval: false });
    }

    await save;
  };
}
