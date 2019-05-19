import Model from 'flarum/Model';

import addBanIPControl from './addBanIPControl';
import BannedIP from '../common/models/BannedIP';

app.initializers.add('fof/ban-ips', () => {
    app.store.models.posts.prototype.canBanIP = Model.attribute('canBanIP');
    app.store.models.posts.prototype.ipAddress = Model.attribute('ipAddress');

    app.store.models.banned_ips = BannedIP;

    addBanIPControl();
});
