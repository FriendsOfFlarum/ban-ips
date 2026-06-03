import Extend from 'flarum/common/extenders';
import User from 'flarum/common/models/User';
import BannedIP from './models/BannedIP';

export default [
  new Extend.Model(User) //
    .hasMany<BannedIP>('banned_ips'),

  new Extend.Store() //
    .add('banned_ips', BannedIP),
];
