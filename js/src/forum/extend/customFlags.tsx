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
      <Button className="Button" icon="fas fa-check" onclick={PostControls.truncatingApproveAction.bind(this, true)}>
        {app.translator.trans('flarum-com-truncating-approval.forum.post.dismiss_flag_button')}
      </Button>
    );

    items.add(
      'truncatingApprove-truncate',
      <Button className="Button Button--danger" icon="fas fa-dumpster-fire" onclick={() => PostControls.truncatingRejectAction(this)}>
        {app.translator.trans('flarum-com-truncating-approval.forum.post.truncate_button')}
      </Button>
    );
  });

  PostControls.truncatingRejectAction = async function (postComponent: PostComponent) {
    return app.modal.show(RejectContentModal, {
      post: postComponent.attrs.post,
      onsubmit: async (reason: string) => await PostControls.truncatingApproveAction.call(postComponent, false, reason),
    });
  };

  PostControls.truncatingApproveAction = async function (this: PostComponent, truncatingApprove: boolean, reason?: string) {
    const body: Record<string, unknown> = { truncatingApprove };

    if (reason) {
      body.truncatingRejectReason = reason;
    }

    const post = this.attrs.post;

    const save = post.save(body);
    post.pushAttributes({ awaitingTruncatingApproval: false });

    if (post.number() === 1) {
      post.discussion().pushAttributes({ awaitingTruncatingApproval: false });
    }

    await save;

    this.dismissFlag();
  };
}
