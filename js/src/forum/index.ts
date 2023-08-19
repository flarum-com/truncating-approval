import app from 'flarum/forum/app';
import Extend from 'flarum/common/extenders';

import { customFlags } from './extend/customFlags';
import Post from 'flarum/common/models/Post';
import { awaitingApprovalTooltip } from './extend/awaitingApprovalTooltip';
import { TruncatingApprovalRejectedPost } from './components/TruncatingApprovalRejectedPost';

export const extend = [
  new Extend.Model(Post) //
    .attribute<boolean>('awaitingTruncatingApproval')
    .attribute<boolean>('canApproveTruncatingApproval'),

  new Extend.PostTypes() //
    .add('truncatingApprovalRejected', TruncatingApprovalRejectedPost),
];

app.initializers.add('flarum-com/truncating-approval', () => {
  customFlags();
  awaitingApprovalTooltip();
});
