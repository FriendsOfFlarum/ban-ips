import Model from 'flarum/Model';
import Forum from 'flarum/models/Forum';

import addBanIPControl from './addBanIPControl';
import BannedIP from '../common/models/BannedIP';
import addBannedBadge from './addBannedBadge';

app.initializers.add('fof/ban-ips', () => {
    app.store.models.posts.prototype.canBanIP = Model.attribute('canBanIP');
    app.store.models.posts.prototype.ipAddress = Model.attribute('ipAddress');
    app.store.models.users.prototype.isBanned = Model.attribute('isBanned');

    app.store.models.banned_ips = BannedIP;

    Forum.prototype.bannedIPs = Model.hasMany('banned_ips');

    addBanIPControl();
    addBannedBadge();
});
