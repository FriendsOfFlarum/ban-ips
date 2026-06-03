import Extend from 'flarum/common/extenders';
import commonExtend from '../common/extend';
import Post from 'flarum/common/models/Post';
import BannedIP from 'src/common/models/BannedIP';
import User from 'flarum/common/models/User';

export default [
  ...commonExtend,

  new Extend.Model(Post) //
    .attribute<boolean>('canBanIP')
    .attribute<string | null>('ipAddress')
    .hasOne<BannedIP>('banned_ip'),

  new Extend.Model(User) //
    .attribute<boolean>('canBanIP')
    .attribute<boolean>('isBanned'),
];
