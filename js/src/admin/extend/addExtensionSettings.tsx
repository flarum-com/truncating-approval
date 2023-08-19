import app from 'flarum/admin/app';
import RestrictedBbcodes from '../components/settings/RestrictedBbcodes';

export function addExtensionSettings() {
  app.extensionData
    .for('flarum-com-truncating-approval')
    .registerSetting({
      default: false,
      type: 'checkbox',
      setting: 'flarum-com-truncating-approval.lock-after-rejection',
      label: app.translator.trans('flarum-com-truncating-approval.admin.settings.lock_after_rejection.label'),
      help: app.translator.trans('flarum-com-truncating-approval.admin.settings.lock_after_rejection.help'),
    })
    .registerSetting({
      default: false,
      type: 'checkbox',
      setting: 'flarum-com-truncating-approval.eventpost-after-reject',
      label: app.translator.trans('flarum-com-truncating-approval.admin.settings.eventpost_after_rejection.label'),
      help: app.translator.trans('flarum-com-truncating-approval.admin.settings.eventpost_after_rejection.help'),
    })
    .registerSetting(() => {
      return <RestrictedBbcodes />;
    })
    .registerSetting(() => {
      if (!('flarum-tags' in flarum.extensions)) return null;

      return (
        <p>
          <strong>{app.translator.trans('flarum-com-truncating-approval.admin.settings.tag_note')}</strong>
        </p>
      );
    });
}
