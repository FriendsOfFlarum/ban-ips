import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import Alert from 'flarum/common/components/Alert';
import Stream from 'flarum/common/utils/Stream';
import punctuateSeries from 'flarum/common/helpers/punctuateSeries';

export default class BanIPModal extends Modal {
    oninit(vnode) {
        super.oninit(vnode);

        this.address = this.attrs.address;
        this.post = this.attrs.post;
        this.user = this.attrs.user || (this.post && this.post.user());

        if (!this.user && this.address) {
            const bannedIP = app.store.getBy('banned_ips', 'address', this.address);

            if (bannedIP) this.user = bannedIP.user();
        }

        this.banOptions = [];

        if ((this.post && this.post.ipAddress()) || this.address) this.banOptions.push('only');
        if (this.user) this.banOptions.push('all');

        this.banOption = Stream(this.banOptions[0]);
        this.reason = Stream('');

        this.otherUsers = {};

        this.loading = false;
    }

    className() {
        return 'Modal--medium';
    }

    title() {
        return app.translator.trans('fof-ban-ips.lib.modal.title');
    }

    content() {
        const otherUsersBanned = this.otherUsers[this.banOption()];
        const usernames =
            otherUsersBanned && otherUsersBanned.map((u) => (u && u.displayName()) || app.translator.trans('core.lib.username.deleted_text'));

        return (
            <div className="Modal-body">
                <p>{app.translator.trans('fof-ban-ips.lib.modal.ban_ip_confirmation')}</p>

                <div className="Form-group">
                    {this.banOptions.map((key) => (
                        <div>
                            <input
                                type="radio"
                                name="ban-option"
                                id={`ban-option-${key}`}
                                checked={this.banOption() === key}
                                onclick={this.banOption.bind(this, key)}
                            />
                            &nbsp;
                            <label htmlFor={`ban-option-${key}`}>
                                {app.translator.trans(`fof-ban-ips.forum.modal.ban_options_${key}_ip`, {
                                    user: this.user,
                                    ip: this.address || (this.post && this.post.ipAddress()),
                                })}
                            </label>
                        </div>
                    ))}
                </div>

                <div className="Form-group">
                    <label className="label">{app.translator.trans('fof-ban-ips.lib.modal.reason_label')}</label>
                    <input type="text" className="FormControl" bidi={this.reason} />
                </div>

                {otherUsersBanned
                    ? otherUsersBanned.length
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
                              app.translator.trans('fof-ban-ips.forum.modal.ban_ip_no_users')
                          )
                    : ''}

                {otherUsersBanned && <br />}

                <div className="Form-group">
                    <Button className="Button Button--primary" type="submit" loading={this.loading}>
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

        this.loading = true;

        if (typeof this.otherUsers[this.banOption()] === 'undefined') return this.getOtherUsers();

        const attrs = {
            reason: this.reason(),
            userId: this.user.id(),
        };

        if (this.banOption() === 'only') {
            attrs.address = this.post.ipAddress();

            app.store.createRecord('banned_ips').save(attrs).then(this.hide.bind(this)).catch(this.onerror.bind(this)).then(this.loaded.bind(this));
        } else if (this.banOption() === 'all') {
            app.request({
                body: {
                    data: {
                        attributes: attrs,
                    },
                },
                url: `${app.forum.attribute('apiUrl')}${this.user.apiEndpoint()}/ban`,
                method: 'POST',
                errorHandler: this.onerror.bind(this),
            })
                .then((res) => app.store.pushPayload(res).forEach(this.done.bind(this)))
                .then(this.hide.bind(this))
                .catch(() => {})
                .then(this.loaded.bind(this));
        }
    }

    getOtherUsers() {
        const data = {};

        if (this.banOption() === 'only') data.ip = this.address || this.post.ipAddress();

        app.request({
            params: data,
            url: `${app.forum.attribute('apiUrl')}/fof/ban-ips/check-users/${this.user.id()}`,
            method: 'GET',
            errorHandler: this.onerror.bind(this),
        })
            .then((res) => {
                this.otherUsers[this.banOption()] = res.data.map((e) => app.store.pushObject(e)).filter((e) => e.bannedIPs().length === 0);
                this.loading = false;
            })
            .catch(() => {})
            .then(this.loaded.bind(this));
    }

    done(bannedIP) {
        const obj = {
            type: 'banned_ips',
            id: bannedIP.id(),
        };

        if (this.post) {
            this.post.data.relationships.banned_ip = {
                data: obj,
            };
        }

        if (!this.user.data.relationships.banned_ips)
            this.user.data.relationships.banned_ips = {
                data: [],
            };

        this.user.data.relationships.banned_ips.data.push(obj);
        this.user.data.attributes.isBanned = true;

        app.store.pushObject(this.user.data);
    }
}
