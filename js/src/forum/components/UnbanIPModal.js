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
                                {app.translator.trans(`fof-ban-ips.forum.modal.unban_options_${key}_ip`, {
                                    user: this.user,
                                    ip: this.post && this.post.ipAddress(),
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
                              children: app.translator.trans('fof-ban-ips.forum.modal.unban_ip_no_users'),
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
            attrs.address = this.post.ipAddress();

            const bannedIP = this.post.bannedIP();

            bannedIP
                .delete()
                .then(this.done.bind(this, bannedIP))
                .then(this.hide.bind(this), this.onerror.bind(this), this.loaded.bind(this));
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
                .then(this.hide.bind(this))
                .catch(() => {})
                .then(this.loaded.bind(this));
        }
    }

    getOtherUsers() {
        const data = {};

        if (this.banOption() === 'only') data.ip = this.post.ipAddress();

        app.request({
            data,
            url: `${app.forum.attribute('apiUrl')}/fof/ban-ips/check-users/${this.user.id()}`,
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
        if (bannedIP) {
            delete this.post.data.relationships.banned_ip;

            this.user.data.relationships.banned_ips.data = this.user.data.relationships.banned_ips.data.filter(e => e.id !== bannedIP.id());
            this.user.data.attributes.isBanned = false;
        } else {
            this.user.data.relationships.banned_ips.data = [];
            this.user.data.attributes.isBanned = false;

            if (this.post) delete this.post.data.relationships.banned_ip;
        }
    }
}