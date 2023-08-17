import app from 'flarum/admin/app';
import { extend } from 'flarum/common/extend';
import Stream from 'flarum/common/utils/Stream';
import Model from 'flarum/common/Model';

import EditTagModal from 'flarum/tags/components/EditTagModal';
import Tag from 'flarum/tags/models/Tag';

import type Mithril from 'mithril';
import type ItemList from 'flarum/common/utils/ItemList';

export function extendTagEditModal() {
  Tag.prototype.usesTruncatingApproval = Model.attribute('usesTruncatingApproval');

  extend(EditTagModal.prototype, 'oninit', function (this: EditTagModal) {
    this.usesTruncatingApproval = Stream(this.tag.usesTruncatingApproval() || false);
  });

  extend(EditTagModal.prototype, 'fields', function (this: EditTagModal, items: ItemList<Mithril.Children>) {
    items.add(
      'truncatingApproval',
      <div class="Form-group">
        <div>
          <label className="checkbox">
            <input type="checkbox" bidi={this.usesTruncatingApproval} />
            {app.translator.trans('flarum-com-truncating-approval.admin.tags.edit_modal.uses_truncating_approval')}{' '}
          </label>
        </div>
      </div>
    );
  });

  extend(EditTagModal.prototype, 'submitData', function (this: EditTagModal, data: Record<string, unknown>) {
    data.usesTruncatingApproval = this.usesTruncatingApproval();
  });
}
