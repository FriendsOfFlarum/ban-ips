import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import Alert from 'flarum/common/components/Alert';
import punctuateSeries from 'flarum/common/helpers/punctuateSeries';
import username from 'flarum/common/helpers/username';
import Stream from 'flarum/common/utils/Stream';

export default class BanIPModal extends Modal {
    oninit(vnode) {
        super.oninit(vnode);

        this.address = Stream('');
        this.reason = Stream('');

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
                    <input
                        type="text"
                        className="FormControl"
                        bidi={this.address}
                        required
                        pattern="^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$"
                    />
                </div>

                <div className="Form-group">
                    <label className="label">{app.translator.trans('fof-ban-ips.lib.modal.reason_label')}</label>
                    <input type="text" className="FormControl" bidi={this.reason} />
                </div>

                {usersBanned
                    ? usersBanned.length
                        ? Alert.component(
                              {
                                  dismissible: false,
                              },
                              app.translator.transChoice('fof-ban-ips.lib.modal.ban_ip_users', usernames.length, {
                                  users: punctuateSeries(usernames),
                              })
                          )
                        : Alert.component(
                              {
                                  dismissible: false,
                                  type: 'success',
                              },
                              app.translator.trans('fof-ban-ips.admin.modal.ban_ip_no_users')
                          )
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
                            ? app.translator.trans('fof-ban-ips.lib.modal.ban_button')
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

        app.store.createRecord('banned_ips').save(attrs).then(this.hide.bind(this), this.onerror.bind(this), this.loaded.bind(this));
    }

    getOtherUsers() {
        const data = {
            ip: this.address(),
        };

        app.request({
            params: data,
            url: `${app.forum.attribute('apiUrl')}/fof/ban-ips/check-users`,
            method: 'GET',
        })
            .then((res) => {
                this.usersBanned[this.address()] = res.data.map((e) => app.store.pushObject(e));

                m.redraw();
            })
            .then(this.loaded.bind(this))
            .catch((e) => {
                this.onerror(e);
                this.loading = false;
            });
    }
}
