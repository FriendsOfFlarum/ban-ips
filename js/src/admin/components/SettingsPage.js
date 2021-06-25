import Button from 'flarum/common/components/Button';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import Placeholder from 'flarum/common/components/Placeholder';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';

import BanIPModal from './BanIPModal';
import SettingsPageItem from './SettingsPageItem';

export default class SettingsPage extends ExtensionPage {
    oninit(vnode) {
        super.oninit(vnode);

        this.loading = true;

        this.page = 0;
        this.pageSize = 20;
    }

    oncreate(vnode) {
        super.oncreate(vnode);

        this.refresh();
    }

    content() {
        let next, prev;

        if (this.nextResults === true) {
            next = Button.component({
                className: 'Button Button--PageList-next',
                icon: 'fas fa-angle-right',
                onclick: this.loadNext.bind(this),
            });
        }

        if (this.prevResults === true) {
            prev = Button.component({
                className: 'Button Button--PageList-prev',
                icon: 'fas fa-angle-left',
                onclick: this.loadPrev.bind(this),
            });
        }

        return (
            <div className="BannedIPsPage">
                <div className="BannedIPsPage-header">
                    <div className="container">
                        {Button.component(
                            {
                                className: 'Button Button--primary',
                                icon: 'fas fa-plus',
                                onclick: () => app.modal.show(BanIPModal),
                            },
                            app.translator.trans('fof-ban-ips.admin.page.create_button')
                        )}
                    </div>
                </div>
                <br />
                <div className="BannedIpsPage-table">
                    <div className="container">
                        {this.loading ? (
                            LoadingIndicator.component()
                        ) : app.store.all('banned_ips').length ? (
                            <table style={{ width: '100%', textAlign: 'left' }} className="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{app.translator.trans('fof-ban-ips.admin.page.creator_label')}</th>
                                        <th>{app.translator.trans('fof-ban-ips.admin.page.user_label')}</th>
                                        <th>{app.translator.trans('fof-ban-ips.admin.page.address_label')}</th>
                                        <th>{app.translator.trans('fof-ban-ips.admin.page.reason_label')}</th>
                                        <th>{app.translator.trans('fof-ban-ips.admin.page.date_label')}</th>
                                        <th />
                                    </tr>
                                </thead>
                                <tbody>
                                    {app.store
                                        .all('banned_ips')
                                        .slice(this.page, this.page + this.pageSize)
                                        .map((bannedIP) => SettingsPageItem.component({ bannedIP }))}
                                </tbody>
                            </table>
                        ) : (
                            <div>{Placeholder.component({ text: app.translator.trans('fof-ban-ips.admin.empty_text') })}</div>
                        )}
                    </div>
                </div>
                <div>
                    {next}
                    {prev}
                </div>
            </div>
        );
    }

    refresh() {
        return this.loadResults().then(this.parseResults.bind(this));
    }

    /**
     * Load a new page of Pages results.
     *
     * @param {Integer} page number.
     * @return {Promise}
     */
    loadResults() {
        const offset = this.page * this.pageSize;

        return app.store.find('fof/ban-ips', { page: { offset, limit: this.pageSize } });
    }

    /**
     * Load the next page of results.
     *
     * @public
     */
    loadNext() {
        if (this.nextResults === true) {
            this.page++;
            this.refresh();
        }
    }

    /**
     * Load the previous page of results.
     *
     * @public
     */
    loadPrev() {
        if (this.prevResults === true) {
            this.page--;
            this.refresh();
        }
    }

    /**
     * Parse results and append them to the page list.
     *
     * @param {Page[]} results
     * @return {Page[]}
     */
    parseResults(results) {
        this.loading = false;

        this.nextResults = !!results.payload.links.next;
        this.prevResults = !!results.payload.links.prev;

        m.redraw();
    }
}
