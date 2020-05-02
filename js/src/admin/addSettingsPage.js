import { extend } from 'flarum/extend';
import AdminNav from 'flarum/components/AdminNav';
import AdminLinkButton from 'flarum/components/AdminLinkButton';

import SettingsPage from './components/SettingsPage';

export default () => {
    app.routes['fof-ban-ips'] = { path: '/fof/ban-ips', component: SettingsPage.component() };

    app.extensionSettings['fof-ban-ips'] = () => m.route(app.route('fof-ban-ips'));

    extend(AdminNav.prototype, 'items', (items) => {
        items.add(
            'fof-ban-ips',
            AdminLinkButton.component({
                href: app.route('fof-ban-ips'),
                icon: 'fas fa-gavel',
                children: app.translator.trans('fof-ban-ips.admin.nav.title'),
                description: app.translator.trans('fof-ban-ips.admin.nav.desc'),
            })
        );
    });
};
