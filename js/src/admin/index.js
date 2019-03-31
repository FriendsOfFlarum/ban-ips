import { extend } from 'flarum/extend';
import app from 'flarum/app';
import PermissionGrid from 'flarum/components/PermissionGrid';

app.initializers.add('ban-ips', () => {
  extend(PermissionGrid.prototype, 'moderateItems', items => {
    items.add('viewBannedIPList', {
      icon: 'fas fa-ban',
      label: app.translator.trans('fof-ban-ips.admin.permissions.view_banned_ip_list_label'),
      permission: 'fof.banips.viewBannedIPList'
    });
    items.add('BanIP', {
      icon: 'fas fa-ban',
      label: app.translator.trans('fof-ban-ips.admin.permissions.ban_ip_label'),
      permission: 'fof.banips.banIP'
    });
  });
});
