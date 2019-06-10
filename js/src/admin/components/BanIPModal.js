import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';
import Alert from 'flarum/components/Alert';
import punctuateSeries from 'flarum/helpers/punctuateSeries';
import username from 'flarum/helpers/username';

export default class BanIPModal extends Modal {
    init() {
        this.address = m.prop('');
        this.reason = m.prop('');

        this.usersBanned = {};

        this.loading = false;
    }

    className() {
        return 'Modal--medium';
    }

    title() {
        return app.translator.trans('fof-ban-ips.lib.modal.title');
    }

    content() {
        const usersBanned = this.usersBanned[this.address()];
        const usernames = usersBanned && usersBanned.map(username);

        return (
            <div className="Modal-body">
                <p>{app.translator.trans('fof-ban-ips.lib.modal.ban_ip_confirmation')}</p>

                <div className="Form-group">
                    <label className="label">{app.translator.trans('fof-ban-ips.lib.modal.address_label')}</label>
                    <input type="text" className="FormControl" bidi={this.address} required pattern="^([0-9]{1,3}\.){3}[0-9]{1,3}$" />
                </div>

                <div className="Form-group">
                    <label className="label">{app.translator.trans('fof-ban-ips.lib.modal.reason_label')}</label>
                    <input type="text" className="FormControl" bidi={this.reason} />
                </div>

                {usersBanned
                    ? usersBanned.length
                        ? Alert.component({
                              children: app.translator.transChoice('fof-ban-ips.lib.modal.ban_ip_users', usernames.length, {
                                  users: punctuateSeries(usernames),
                              }),
                              dismissible: false,
                          })
                        : Alert.component({
                              children: app.translator.trans('fof-ban-ips.admin.modal.ban_ip_no_users'),
                              dismissible: false,
                              type: 'success',
                          })
                    : ''}

                {usersBanned && <br />}

                <div className="Form-group">
                    <Button
                        className="Button Button--primary"
                        type="submit"
                        loading={this.loading}
                        disabled={app.store.getBy('banned_ips', 'address', this.address())}
                    >
                        {usernames
                            ? app.translator.trans('fof-ban-ips.lib.modal.submit_button')
                            : app.translator.trans('fof-ban-ips.lib.modal.check_button')}
                    </Button>
                </div>
            </div>
        );
    }

    onsubmit(e) {
        e.preventDefault();

        if (!this.address()) return;

        this.loading = true;

        if (typeof this.usersBanned[this.address()] === 'undefined') return this.getOtherUsers();

        const attrs = {
            address: this.address(),
            reason: this.reason(),
        };

        app.store
            .createRecord('banned_ips')
            .save(attrs)
            .then(this.hide.bind(this), this.onerror.bind(this), this.loaded.bind(this));
    }

    getOtherUsers() {
        const data = {
            ip: this.address(),
        };

        app.request({
            data,
            url: `${app.forum.attribute('apiUrl')}/fof/ban-ips/check-users`,
            method: 'GET',
            errorHandler: this.onerror.bind(this),
        })
            .then(res => {
                this.usersBanned[this.address()] = res.data.map(e => app.store.pushObject(e));
                this.loading = false;

                m.lazyRedraw();
            })
            .catch(() => {})
            .then(this.loaded.bind(this));
    }
}
