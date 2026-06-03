import app from 'flarum/forum/app';
import addBanIPControl from './addBanIPControl';
import addBannedBadge from './addBannedBadge';

export { default as extend } from './extend';

app.initializers.add('fof/ban-ips', () => {
  addBanIPControl();
  addBannedBadge();
});

// Expose compat API
import extCompat from '../common/compat';
import { compat } from '@flarum/core/forum';

Object.assign(compat, extCompat);
