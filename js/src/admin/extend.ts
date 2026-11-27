import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';
import commonExtend from '../common/extend';
import SettingsPage from './components/SettingsPage';

export default [
  ...commonExtend,

  new Extend.Admin()
    .permission(
      () => ({
        icon: 'fas fa-gavel',
        label: app.translator.trans('fof-ban-ips.admin.permissions.view_banned_ip_list_label'),
        permission: 'fof.ban-ips.viewBannedIPList',
      }),
      'moderate'
    )
    .permission(
      () => ({
        icon: 'fas fa-gavel',
        label: app.translator.trans('fof-ban-ips.admin.permissions.ban_ip_label'),
        permission: 'fof.ban-ips.banIP',
      }),
      'moderate'
    )
    .page(SettingsPage),
];
