import app from 'flarum/forum/app';
import addBanIPControl from './addBanIPControl';
import addBannedBadge from './addBannedBadge';

export { default as extend } from './extend';

app.initializers.add('fof/ban-ips', () => {
  addBanIPControl();
  addBannedBadge();
});

// Allow flarum to discover modules
import '../common/common';
