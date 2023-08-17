import app from 'flarum/forum/app';

import { customFlags } from './extend/customFlags';
import Post from 'flarum/common/models/Post';
import { awaitingApprovalTooltip } from './extend/awaitingApprovalTooltip';

app.initializers.add('flarum-com/truncating-approval', () => {
  Post.prototype.awaitingTruncatingApproval = Post.attribute('awaitingTruncatingApproval');
  Post.prototype.canApproveTruncatingApproval = Post.attribute('canApproveTruncatingApproval');

  customFlags();
  awaitingApprovalTooltip();
});
