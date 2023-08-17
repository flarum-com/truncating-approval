import EventPost from 'flarum/forum/components/EventPost';

export class TruncatingApprovalRejectedPost extends EventPost {
  static initAttrs(attrs) {
    super.initAttrs(attrs);

    attrs.postId = attrs.post.content().postId;
    attrs.reason = attrs.post.content().reason;
  }

  icon() {
    return 'fas fa-tag';
  }

  descriptionKey() {
    return 'flarum-com-truncating-approval.forum.rejected_event_post.description';
  }

  descriptionData() {
    return { reason: this.attrs.reason };
  }
}
