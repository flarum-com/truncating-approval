import app from 'flarum/admin/app';

import Component, { ComponentAttrs } from 'flarum/common/Component';
import Button from 'flarum/common/components/Button';

import saveSettings from 'flarum/admin/utils/saveSettings';

import Stream from 'flarum/common/utils/Stream';
import extractText from 'flarum/common/utils/extractText';

import type Mithril from 'mithril';
import ItemList from 'flarum/common/utils/ItemList';

interface Attrs extends ComponentAttrs {}

export default class RestrictedBbcodes extends Component<Attrs> {
  private static RESTRICTED_BBCODES_KEY = 'flarum-com-truncating-approval.restricted_bbcodes';

  loading: boolean = false;

  shownErrors = {
    oneOrMoreBlanks: false,
  };

  get savedRestrictedBbcodes(): string[] {
    try {
      const data = JSON.parse(app.data.settings[RestrictedBbcodes.RESTRICTED_BBCODES_KEY]);

      if (!Array.isArray(data)) throw new Error('Invalid data type');

      return data;
    } catch (e) {
      return [];
    }
  }

  get isDirty(): boolean {
    const current = JSON.stringify(this.restrictedBbcodes.map((s) => s()));
    const saved = JSON.stringify(this.savedRestrictedBbcodes);

    console.log(current, saved);

    return current !== saved;
  }

  restrictedBbcodes: Stream<string>[] = [];

  view(vnode: Mithril.Vnode<Attrs, this>) {
    return (
      <fieldset class="TruncatingApproval-restrictedBbcodes">
        <legend class="sr-only">{app.translator.trans('flarum-com-truncating-approval.admin.settings.restricted_bbcodes.label')}</legend>
        <p class="TruncatingApproval-restrictedBbcodesLabel" aria-hidden="true">
          {app.translator.trans('flarum-com-truncating-approval.admin.settings.restricted_bbcodes.label')}
        </p>

        <div class="TruncatingApproval-restrictedBbcodesList">
          {this.restrictedBbcodes.length === 0 && (
            <p class="TruncatingApproval-restrictedBbcodesListEmpty">
              {app.translator.trans('flarum-com-truncating-approval.admin.settings.restricted_bbcodes.empty_text')}
            </p>
          )}

          <div className="TruncatingApproval-restrictedBbcodesListGrid">
            {this.restrictedBbcodes.map((bbcode, index) => (
              <RestrictedBbcode value={bbcode} onRemove={() => this.restrictedBbcodes.splice(index, 1)} disabled={this.loading} />
            ))}
          </div>
        </div>

        <div className="TruncatingApproval-restrictedBbcodesButtons">
          <Button class="Button" icon="fas fa-plus" onclick={this.newBbcode.bind(this)} loading={this.loading}>
            {app.translator.trans('flarum-com-truncating-approval.admin.settings.restricted_bbcodes.add_new_button')}
          </Button>

          <Button class="Button Button--primary" onclick={this.save.bind(this)} disabled={!this.isDirty} loading={this.loading}>
            {app.translator.trans('flarum-com-truncating-approval.admin.settings.restricted_bbcodes.save_button')}
          </Button>
        </div>

        {this.errors().toArray()}
      </fieldset>
    );
  }

  oncreate(vnode: Mithril.VnodeDOM<Attrs, this>): void {
    super.oncreate(vnode);

    this.restrictedBbcodes = this.savedRestrictedBbcodes.map((s) => Stream(s));

    m.redraw();
  }

  newBbcode() {
    this.restrictedBbcodes.push(Stream(''));
  }

  validate(): boolean {
    let success = true;

    if (this.restrictedBbcodes.some((s) => (s() as string).trim() === '')) {
      this.shownErrors.oneOrMoreBlanks = true;

      success = false;
    } else {
      this.shownErrors.oneOrMoreBlanks = false;
    }

    return success;
  }

  async save() {
    this.loading = true;
    m.redraw();

    if (!this.validate()) {
      this.loading = false;
      m.redraw();

      return;
    }

    await saveSettings({
      [RestrictedBbcodes.RESTRICTED_BBCODES_KEY]: JSON.stringify(this.restrictedBbcodes),
    });

    this.loading = false;
    m.redraw();
  }

  errors() {
    const items = new ItemList();

    if (this.shownErrors.oneOrMoreBlanks) {
      items.add(
        'one_or_more_blanks',
        <div class="TruncatingApproval-restrictedBbcodesError">
          <p>{app.translator.trans('flarum-com-truncating-approval.admin.settings.restricted_bbcodes.errors.one_or_more_blanks')}</p>
        </div>
      );
    }

    return items;
  }
}

interface RestrictedBbcodeAttrs extends ComponentAttrs {
  value: Stream<string>;
  onRemove: () => void;
  disabled?: boolean;
}

class RestrictedBbcode extends Component<RestrictedBbcodeAttrs> {
  view(vnode: Mithril.Vnode<RestrictedBbcodeAttrs, this>) {
    return (
      <div className="TruncatingApproval-restrictedBbcodesListItem">
        <label>
          <span className="sr-only">{app.translator.trans('flarum-com-truncating-approval.admin.settings.restricted_bbcodes.item_label')}</span>
          <input
            className="FormControl"
            bidi={vnode.attrs.value}
            disabled={this.attrs.disabled ?? false}
            placeholder={extractText(app.translator.trans('flarum-com-truncating-approval.admin.settings.restricted_bbcodes.item_label'))}
          />
        </label>

        <Button
          class="Button Button--icon Button--danger"
          icon="fas fa-trash"
          onclick={vnode.attrs.onRemove}
          aria-label={extractText(app.translator.trans('flarum-com-truncating-approval.admin.settings.restricted_bbcodes.remove_button'))}
          disabled={this.attrs.disabled ?? false}
        />
      </div>
    );
  }
}
