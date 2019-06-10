import Button from 'flarum/components/Button';
import Alert from 'flarum/components/Alert';
import punctuateSeries from 'flarum/helpers/punctuateSeries';
import username from 'flarum/helpers/username';

import BanIPModal from './BanIPModal';

export default class UnbanIPModal extends BanIPModal {
    title() {
        return app.translator.trans('fof-ban-ips.lib.modal.unban_title');
    }

    content() {
        const otherUsers = this.otherUsers[this.banOption()];
        const usernames = otherUsers && otherUsers.map(u => (u && u.displayName()) || app.translator.trans('core.lib.username.deleted_text'));

        if (this.bannedIPs) {
            return (
                <div className="Modal-body">
                    {Alert.component({
                        children: app.translator.trans('fof-ban-ips.lib.modal.unbanned_ips', { ips: punctuateSeries(this.bannedIPs) }),
                        dismissible: false,
                        type: 'success',
                    })}
                </div>
            );
        }

        return (
            <div className="Modal-body">
                <p>{app.translator.trans('fof-ban-ips.lib.modal.unban_ip_confirmation')}</p>

                <div className="Form-group">
                    {this.banOptions.map(key => (
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
                                {app.translator.trans(`fof-ban-ips.lib.modal.unban_options_${key}_ip`, {
                                    user: this.user,
                                    ip: this.address || (this.post && this.post.ipAddress()),
                                })}
                            </label>
                        </div>
                    ))}
                </div>

                {otherUsers
                    ? otherUsers.length
                        ? Alert.component({
                              children: app.translator.transChoice('fof-ban-ips.lib.modal.unban_ip_users', usernames.length, {
                                  users: punctuateSeries(usernames),
                              }),
                              dismissible: false,
                          })
                        : Alert.component({
                              children: app.translator.trans('fof-ban-ips.lib.modal.unban_ip_no_users'),
                              dismissible: false,
                              type: 'success',
                          })
                    : ''}

                {otherUsers && <br />}

                <div className="Form-group">
                    <Button className="Button Button--primary" type="submit" loading={this.loading}>
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

        this.loading = true;

        if (typeof this.otherUsers[this.banOption()] === 'undefined') return this.getOtherUsers();

        const attrs = {};

        if (this.banOption() === 'only') {
            attrs.address = this.address || this.post.ipAddress();

            const bannedIP = this.post ? this.post.bannedIP() : app.store.getBy('banned_ips', 'address', this.address);

            bannedIP
                .delete()
                .then(this.done.bind(this, bannedIP))
                .catch(this.onerror.bind(this));
        } else if (this.banOption() === 'all') {
            app.request({
                data: {
                    data: {
                        attributes: attrs,
                    },
                },
                url: `${app.forum.attribute('apiUrl')}${this.user.apiEndpoint()}/unban`,
                method: 'POST',
                errorHandler: this.onerror.bind(this),
            })
                .then(this.done.bind(this))
                .catch(this.onerror.bind(this));
        }
    }

    getOtherUsers() {
        const data = {};

        if (this.banOption() === 'only') data.ip = this.address || this.post.ipAddress();

        let url = `${app.forum.attribute('apiUrl')}/fof/ban-ips/check-users`;

        if (this.user) url += `/${this.user.id()}`;

        app.request({
            data,
            url: url,
            method: 'GET',
            errorHandler: this.onerror.bind(this),
        })
            .then(res => {
                const data = app.store.pushPayload(res);

                this.otherUsers[this.banOption()] = data.filter(e => e.bannedIPs().length === 1);
                this.loading = false;

                m.lazyRedraw();
            })
            .catch(() => {})
            .then(this.loaded.bind(this));
    }

    done(bannedIP) {
        this.loading = false;

        if (this.post) delete this.post.data.relationships.banned_ip;

        if (this.user && !this.user.data.relationships) {
            if (bannedIP instanceof app.store.models.banned_ips) {
                this.user.data.relationships.banned_ips = {
                    data: this.user.data.relationships.banned_ips.data.filter(e => e.id !== bannedIP.id()),
                };
                this.user.data.attributes.isBanned = this.user.data.relationships.banned_ips.data.length !== 0;
            } else if (!bannedIP) {
                this.user.data.relationships.banned_ips.data = [];
                this.user.data.attributes.isBanned = false;
            }
        }

        if (bannedIP && bannedIP.data) {
            this.bannedIPs = bannedIP.data.map(b => b.attributes.address);
            this.loading = false;

            m.lazyRedraw();
        }
    }

    hide() {
        super.hide();

        location.reload();
    }
}
