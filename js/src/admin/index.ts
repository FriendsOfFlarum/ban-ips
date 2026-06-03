import app from 'flarum/admin/app';
import SettingsPage from './components/SettingsPage';

export { default as extend } from './extend';

app.initializers.add('fof/ban-ips', () => {
  app.extensionData
    .for('fof-ban-ips')
    .registerPermission(
      {
        icon: 'fas fa-gavel',
        label: app.translator.trans('fof-ban-ips.admin.permissions.view_banned_ip_list_label'),
        permission: 'fof.ban-ips.viewBannedIPList',
      },
      'moderate'
    )
    .registerPermission(
      {
        icon: 'fas fa-gavel',
        label: app.translator.trans('fof-ban-ips.admin.permissions.ban_ip_label'),
        permission: 'fof.ban-ips.banIP',
      },
      'moderate'
    )
    .registerPage(SettingsPage);
});

// Expose compat API
import extCompat from './compat';
import { compat } from '@flarum/core/admin';

Object.assign(compat, extCompat);
