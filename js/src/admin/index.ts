import app from 'flarum/admin/app';
import SettingsPage from './components/SettingsPage';

export { default as extend } from './extend';

app.initializers.add('fof/ban-ips', () => {
  app.registry
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

// Allow flarum to discover modules
import './admin';
