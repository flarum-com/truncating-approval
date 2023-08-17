import app from 'flarum/admin/app';

export function addExtensionPermissions() {
  app.extensionData
    .for('flarum-com-truncating-approval')
    .registerPermission(
      {
        permission: 'discussion.approveTruncatingApprovalPosts',
        icon: 'fas fa-check',
        label: app.translator.trans('flarum-com-truncating-approval.admin.permissions.approve_posts'),
      },
      'moderate',
      10
    )
    .registerPermission(
      {
        permission: 'discussion.bypassRestrictedBbcodes',
        icon: 'fas fa-dumpster-fire',
        label: app.translator.trans('flarum-com-truncating-approval.admin.permissions.bypass_restricted_formatting'),
      },
      'reply',
      10
    );
}
