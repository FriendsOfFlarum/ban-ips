import Model from 'flarum/Model';
import Forum from 'flarum/models/Forum';

import BannedIP from '../common/models/BannedIP';
import addPermissions from './addPermissions';
import addSettingsPage from './addSettingsPage';

app.initializers.add('fof/ban-ips', () => {
    app.store.models.banned_ips = BannedIP;

    Forum.prototype.bannedIPs = Model.hasMany('banned_ips');

    addPermissions();
    addSettingsPage();
});
