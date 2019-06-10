import Model from 'flarum/Model';

import BannedIP from '../common/models/BannedIP';
import addPermissions from './addPermissions';
import addSettingsPage from './addSettingsPage';

app.initializers.add('fof/ban-ips', () => {
    app.store.models.banned_ips = BannedIP;

    app.store.models.users.prototype.bannedIPs = Model.hasMany('banned_ips');

    addPermissions();
    addSettingsPage();
});
