import app from 'flarum/admin/app';

import { extendTagEditModal } from './extend/extendTagEditModal';
import { addExtensionPermissions } from './extend/addExtensionPermissions';
import { addExtensionSettings } from './extend/addExtensionSettings';

app.initializers.add('flarum-com/truncating-approval', () => {
  extendTagEditModal();
  addExtensionPermissions();
  addExtensionSettings();
});
