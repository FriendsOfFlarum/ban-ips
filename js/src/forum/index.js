import app from 'flarum/forum/app';
import Model from 'flarum/common/Model';
import addBanIPControl from './addBanIPControl';
import BannedIP from '../common/models/BannedIP';
import addBannedBadge from './addBannedBadge';

app.initializers.add('fof/ban-ips', () => {
  app.store.models.posts.prototype.canBanIP = Model.attribute('canBanIP');
  app.store.models.posts.prototype.ipAddress = Model.attribute('ipAddress');
  app.store.models.posts.prototype.bannedIP = Model.hasOne('banned_ip');

  app.store.models.users.prototype.canBanIP = Model.attribute('canBanIP');
  app.store.models.users.prototype.isBanned = Model.attribute('isBanned');
  app.store.models.users.prototype.bannedIPs = Model.hasMany('banned_ips');

  app.store.models.banned_ips = BannedIP;

  addBanIPControl();
  addBannedBadge();
});

// Expose compat API
import extCompat from '../common/compat';
import { compat } from '@flarum/core/forum';

Object.assign(compat, extCompat);
