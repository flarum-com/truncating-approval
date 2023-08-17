import app from 'flarum/forum/app';

import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Post from 'flarum/common/models/Post';
import Stream from 'flarum/common/utils/Stream';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';

import type Mithril from 'mithril';
import Button from 'flarum/common/components/Button';
import extractText from 'flarum/common/utils/extractText';

interface RejectContentModalAttrs extends IInternalModalAttrs {
  post: Post;
  onsubmit: (reason: string) => Promise<void>;
}

interface RejectContentModalState {
  reason: string;
}

export class RejectContentModal extends Modal<RejectContentModalAttrs, RejectContentModalState> {
  state = {
    reason: Stream<string>(''),
    loadingPreview: Stream<boolean>(true),
    preview: Stream<string | null>(null),
  };

  className(): string {
    return 'TruncatingApproval-RejectContentModal';
  }

  title() {
    return app.translator.trans('flarum-com-truncating-approval.forum.reject_content_modal.title');
  }

  content() {
    return (
      <>
        <div class="Modal-body">
          <p>{app.translator.trans('flarum-com-truncating-approval.forum.reject_content_modal.body')}</p>

          <div class="Form-group">
            <p class="TruncatingApproval-PostPreviewLabel">
              {app.translator.trans('flarum-com-truncating-approval.forum.reject_content_modal.preview')}
            </p>
            <div class="TruncatingApproval-PostPreview">
              <div className="Post-body">{!this.state.loadingPreview() ? m.trust(this.state.preview()) : <LoadingIndicator />}</div>
            </div>
          </div>

          <div className="Form-group">
            <label>
              {app.translator.trans('flarum-com-truncating-approval.forum.reject_content_modal.reason_label')}
              <textarea className="FormControl" bidi={this.state.reason} disabled={this.loading} />
            </label>
            <p className="helpText">{app.translator.trans('flarum-com-truncating-approval.forum.reject_content_modal.reason_help')}</p>
          </div>
        </div>
        <div className="Modal-footer">
          <Button className="Button" onclick={this.hide.bind(this)} disabled={this.loading}>
            {app.translator.trans('flarum-com-truncating-approval.forum.reject_content_modal.cancel_button')}
          </Button>
          <Button className="Button Button--danger" icon="fas fa-dumpster-fire" type="submit" loading={this.state.loadingPreview() || this.loading}>
            {app.translator.trans('flarum-com-truncating-approval.forum.reject_content_modal.confirm_truncation_button')}
          </Button>
        </div>
      </>
    );
  }

  async onsubmit(e: SubmitEvent): Promise<void> {
    this.loading = true;
    m.redraw();

    e.preventDefault();

    if (this.state.reason().trim().length === 0) {
      this.alertAttrs = {
        type: 'error',
        content: extractText(app.translator.trans('flarum-com-truncating-approval.forum.reject_content_modal.errors.reason_required_alert')),
      };
      this.element.querySelector('textarea')?.focus();
      this.loaded();
      return;
    }

    await this.attrs.onsubmit(this.state.reason());
    this.hide();
  }

  oncreate(vnode: Mithril.VnodeDOM<RejectContentModalAttrs, RejectContentModalState>): void {
    super.oncreate(vnode);

    this.getPreview();
  }

  async getPreview() {
    try {
      const resp = await fetch(`${app.forum.attribute('apiUrl')}/truncating-approval-preview/${this.attrs.post.id()}`);
      const data = await resp.json();

      this.state.preview(data.html);
    } finally {
      this.state.loadingPreview(false);
      m.redraw();
    }
  }
}
