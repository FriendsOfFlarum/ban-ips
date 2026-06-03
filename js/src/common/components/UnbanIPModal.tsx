import app from 'flarum/common/app';
import Button from 'flarum/common/components/Button';
import Alert from 'flarum/common/components/Alert';
import punctuateSeries from 'flarum/common/helpers/punctuateSeries';
import type User from 'flarum/common/models/User';
import type { ModelIdentifier, SavedModelData } from 'flarum/common/Model';
import type { ApiPayloadPlural } from 'flarum/common/Store';

import BanIPModal from './BanIPModal';
import type BannedIP from '../models/BannedIP';

export default class UnbanIPModal extends BanIPModal {
  protected bannedIPs?: string[];

  title() {
    return app.translator.trans('fof-ban-ips.lib.modal.unban_title');
  }

  className() {
    return 'UnbanIPModal Modal--medium';
  }

  content() {
    const otherUsers = this.otherUsers[this.banOption()];
    const usernames = otherUsers && otherUsers.map((u) => (u && u.displayName()) || app.translator.trans('core.lib.username.deleted_text'));

    if (this.bannedIPs) {
      return (
        <div className="Modal-body">
          {Alert.component(
            {
              dismissible: false,
              type: 'success',
            },
            app.translator.trans('fof-ban-ips.lib.modal.unbanned_ips', { ips: punctuateSeries(this.bannedIPs) })
          )}
        </div>
      );
    }

    return (
      <div className="Modal-body">
        <p>{app.translator.trans('fof-ban-ips.lib.modal.unban_ip_confirmation')}</p>

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
            ? Alert.component(
                {
                  dismissible: false,
                },
                app.translator.trans('fof-ban-ips.lib.modal.unban_ip_users', {
                  users: punctuateSeries(usernames!),
                  count: usernames!.length,
                })
              )
            : Alert.component(
                {
                  dismissible: false,
                  type: 'success',
                },
                app.translator.trans('fof-ban-ips.lib.modal.unban_ip_no_users')
              )
          : ''}

        {otherUsers && <br />}

        <div className="Form-group">
          <Button className="Button Button--primary" type="submit" loading={this.loading}>
            {usernames ? app.translator.trans('fof-ban-ips.lib.modal.unban_button') : app.translator.trans('fof-ban-ips.lib.modal.check_button')}
          </Button>
        </div>
      </div>
    );
  }

  onsubmit(e: SubmitEvent) {
    e.preventDefault();

    this.loading = true;

    if (typeof this.otherUsers[this.banOption()] === 'undefined') return this.getOtherUsers();

    const attrs: { address?: string | null } = {};

    if (this.banOption() === 'only') {
      attrs.address = this.address || this.post!.ipAddress();

      const bannedIP = (this.post ? this.post.bannedIP() : app.store.getBy<BannedIP>('banned_ips', 'address', this.address)) as BannedIP;

      bannedIP.delete().then(this.done.bind(this, bannedIP)).catch(this.onerror.bind(this)).then(this.hide.bind(this));
    } else if (this.banOption() === 'all') {
      app
        .request<ApiPayloadPlural>({
          body: {
            data: {
              attributes: attrs,
            },
          },
          url: `${app.forum.attribute<string>('apiUrl')}${(this.user as unknown as { apiEndpoint(): string }).apiEndpoint()}/unban`,
          method: 'POST',
          errorHandler: this.onerror.bind(this),
        })
        .then(this.done.bind(this))
        .catch(this.onerror.bind(this))
        .then(this.hide.bind(this));
    }
  }

  getOtherUsers() {
    const data: { ip?: string | null; skipValidation?: boolean } = {};

    if (this.banOption() === 'only') {
      data.ip = this.address || this.post!.ipAddress();
      data.skipValidation = true;
    }

    let url = `${app.forum.attribute<string>('apiUrl')}/fof/ban-ips/check-users`;

    if (this.user) url += `/${this.user.id()}`;

    app
      .request<ApiPayloadPlural>({
        params: data,
        url: url,
        method: 'GET',
        errorHandler: this.onerror.bind(this),
      })
      .then((res) => {
        const data = app.store.pushPayload<User[]>(res);

        this.otherUsers[this.banOption()] = data.filter((e) => {
          const bannedIPs = e.bannedIPs();

          return !!bannedIPs && bannedIPs.length === 1;
        });
        this.loading = false;

        m.redraw();
      })
      .catch(() => {})
      .then(this.loaded.bind(this));
  }

  done(bannedIP?: BannedIP | ApiPayloadPlural) {
    this.loading = false;

    if (this.post) delete this.post.data.relationships!.banned_ip;

    if (this.user && !this.user.data.relationships && !bannedIP) {
      this.user.data.relationships!.banned_ips.data = [];
      this.user.data.attributes!.isBanned = false;
    } else if (this.user && bannedIP instanceof app.store.models.banned_ips) {
      this.user.data.relationships!.banned_ips = {
        data: (this.user.data.relationships!.banned_ips.data as ModelIdentifier[]).filter((e) => e.id !== (bannedIP as BannedIP).id()),
      };
      this.user.data.attributes!.isBanned = (this.user.data.relationships!.banned_ips.data as ModelIdentifier[]).length !== 0;
    }

    if (bannedIP && Array.isArray((bannedIP as ApiPayloadPlural).data)) {
      this.bannedIPs = (bannedIP as ApiPayloadPlural).data.map((b) => (b as SavedModelData).attributes!.address as string);
      this.loading = false;

      m.redraw();
    }
  }

  hide() {
    super.hide();

    if (!this.attrs.redraw) {
      location.reload();
    }
  }
}
