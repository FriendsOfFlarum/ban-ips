import app from 'flarum/common/app';
import Model from 'flarum/common/Model';
import { extend } from 'flarum/common/extend';
import ForumApplication from 'flarum/forum/ForumApplication';
import addBanIPControl from './addBanIPControl';
import BannedIP from '../common/models/BannedIP';
import addBannedBadge from './addBannedBadge';
import restrictedIPAlert from './restrictedIPAlert';

app.initializers.add('fof/ban-ips', () => {
    app.store.models.posts.prototype.canBanIP = Model.attribute('canBanIP');
    app.store.models.posts.prototype.ipAddress = Model.attribute('ipAddress');
    app.store.models.posts.prototype.bannedIP = Model.hasOne('banned_ip');

    app.store.models.users.prototype.canBanIP = Model.attribute('canBanIP');
    //app.store.models.users.prototype.isBanned = Model.attribute('isBanned');
    //app.store.models.users.prototype.bannedIPs = Model.hasMany('banned_ips');

    app.store.models.banned_ips = BannedIP;

    addBanIPControl();
    //addBannedBadge();
    extend(ForumApplication.prototype, 'mount', function () {
        restrictedIPAlert(this);
    });
});

// Expose compat API
import extCompat from '../common/compat';
import { compat } from '@flarum/core/forum';

Object.assign(compat, extCompat);
