import { extend } from 'flarum/extend';
import PermissionGrid from 'flarum/components/PermissionGrid';

export default () => {
    extend(PermissionGrid.prototype, 'moderateItems', items => {
        items.add('viewBannedIPList', {
            icon: 'fas fa-ban',
            label: app.translator.trans('fof-ban-ips.admin.permissions.view_banned_ip_list_label'),
            permission: 'fof.ban-ips.viewBannedIPList',
        });

        items.add('banIP', {
            icon: 'fas fa-ban',
            label: app.translator.trans('fof-ban-ips.admin.permissions.ban_ip_label'),
            permission: 'fof.ban-ips.banIP',
        });
    });
};
