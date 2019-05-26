import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';
import Alert from 'flarum/components/Alert';
import punctuateSeries from 'flarum/helpers/punctuateSeries';
import username from 'flarum/helpers/username';

export default class BanIPModal extends Modal {
    init() {
        this.post = this.props.post;
        this.user = this.post.user();

        this.banOptions = ['only', 'all'];
        this.banOption = m.prop(this.banOptions[0]);
        this.reason = m.prop('');

        this.otherUsersBanned = {};

        this.loading = false;
    }

    className() {
        return 'Modal--medium';
    }

    title() {
        return app.translator.trans('fof-ban-ips.lib.modal.title');
    }

    content() {
        const otherUsersBanned = this.otherUsersBanned[this.banOption()];
        const usernames =
            otherUsersBanned && otherUsersBanned.map(u => (u && u.displayName()) || app.translator.trans('core.lib.username.deleted_text'));

        return (
            <div className="Modal-body">
                <p>{app.translator.trans('fof-ban-ips.forum.ban_ip_confirmation')}</p>

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
                                {app.translator.trans(`fof-ban-ips.forum.modal.ban_options_${key}_ip`, {
                                    user: this.user,
                                    ip: this.post.ipAddress(),
                                })}
                            </label>
                        </div>
                    ))}
                </div>

                <div className="Form-group">
                    <label className="label">Reason</label>
                    <input type="text" className="FormControl" bidi={this.reason} />
                </div>

                {otherUsersBanned
                    ? otherUsersBanned.length
                        ? Alert.component({
                              children: app.translator.trans('fof-ban-ips.lib.modal.ban_ip_users', { users: punctuateSeries(usernames) }),
                              dismissible: false,
                          })
                        : Alert.component({
                              children: app.translator.trans('fof-ban-ips.forum.modal.ban_ip_no_users'),
                              dismissible: false,
                              type: 'success',
                          })
                    : ''}

                {otherUsersBanned && <br />}

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

        if (typeof this.otherUsersBanned[this.banOption()] === 'undefined') return this.getOtherUsers();

        const attrs = {
            userId: this.user.id(),
            reason: this.reason(),
        };

        if (this.banOption() === 'only') {
            attrs.address = this.post.ipAddress();

            app.store
                .createRecord('banned_ips')
                .save(attrs)
                .then(this.hide.bind(this), this.onerror.bind(this), this.loaded.bind(this));
        } else if (this.banOption() === 'all') {
            app.request({
                data: {
                    data: {
                        attributes: attrs,
                    },
                },
                url: `${app.forum.attribute('apiUrl')}/fof/ban-ips/bans/chunk`,
                method: 'POST',
                errorHandler: this.onerror.bind(this),
            })
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
                this.otherUsersBanned[this.banOption()] = res.data.map(e => app.store.pushObject(e));
                this.loading = false;

                m.lazyRedraw();
            })
            .catch(() => {})
            .then(this.loaded.bind(this));
    }
}
