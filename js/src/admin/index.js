import app from 'flarum/app';
import Model from 'flarum/Model';

import BannedIP from '../common/models/BannedIP';
import SettingsPage from './components/SettingsPage';

app.initializers.add('fof/ban-ips', () => {
    app.store.models.banned_ips = BannedIP;

    app.store.models.users.prototype.bannedIPs = Model.hasMany('banned_ips');

    app.extensionData.for('fof-ban-ips')
    .registerPermission({
      icon: 'fas fa-gavel',
      label: app.translator.trans('fof-ban-ips.admin.permissions.view_banned_ip_list_label'),
      permission: 'fof.ban-ips.viewBannedIPList',
    }, 'moderate')
    .registerPermission({
      icon: 'fas fa-gavel',
      label: app.translator.trans('fof-ban-ips.admin.permissions.ban_ip_label'),
      permission: 'fof.ban-ips.banIP',
    }, 'moderate')
    .registerPage(SettingsPage);
});

// Expose compat API
import extCompat from './compat';
import { compat } from '@flarum/core/admin';

Object.assign(compat, extCompat);
