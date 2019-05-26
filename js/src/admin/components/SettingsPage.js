import Button from 'flarum/components/Button';
import Page from 'flarum/components/Page';

import username from 'flarum/helpers/username';
import BanIPModal from './BanIPModal';

export default class SettingsPage extends Page {
    init() {
        const bannedIPs = app.store.all('banned_ips');

        this.page = 0;
        this.pageSize = 20;
        this.pageNumber = Math.ceil(bannedIPs.length / this.pageSize);
    }

    view() {
        return (
            <div className="BannedIPsPage">
                <div className="BannedIPsPage-header">
                    <div className="container">
                        <p>{app.translator.trans('fof-ban-ips.admin.page.about_text')}</p>
                        {Button.component({
                            className: 'Button Button--primary',
                            icon: 'fas fa-plus',
                            children: app.translator.trans('fof-ban-ips.admin.page.create_button'),
                            onclick: () => app.modal.show(new BanIPModal()),
                        })}
                    </div>
                </div>
                <br />
                <div className="BannedIpsPage-table">
                    <div className="container">
                        <table style={{ width: '100%', textAlign: 'left' }} class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Creator</th>
                                    <th>User</th>
                                    <th>Address</th>
                                    <th>Reason</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                {app.store
                                    .all('banned_ips')
                                    .slice(this.page, this.page + this.pageSize)
                                    .map(b => (
                                        <tr>
                                            <td>{b.id()}</td>
                                            <td>{username(b.creator())}</td>
                                            <td>{username(b.user())}</td>
                                            <td>{b.address()}</td>
                                            <td>{b.reason()}</td>
                                            <td>{b.createdAt().toLocaleDateString()}</td>
                                        </tr>
                                    ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        );
    }
}
