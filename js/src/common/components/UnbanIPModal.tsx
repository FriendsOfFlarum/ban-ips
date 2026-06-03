import app from 'flarum/common/app';
import Alert from 'flarum/common/components/Alert';
import ItemList from 'flarum/common/utils/ItemList';
import punctuateSeries from 'flarum/common/helpers/punctuateSeries';
import type Mithril from 'mithril';
import type User from 'flarum/common/models/User';
import type RequestError from 'flarum/common/utils/RequestError';
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
    // Once the unban has completed, replace the form with a success summary of
    // the IPs that were removed.
    if (this.bannedIPs) {
      return (
        <div className="Modal-body">
          {Alert.component(
            { dismissible: false, type: 'success' },
            app.translator.trans('fof-ban-ips.lib.modal.unbanned_ips', { ips: punctuateSeries(this.bannedIPs) })
          )}
        </div>
      );
    }

    return super.content();
  }

  fields() {
    const items = super.fields();

    // Unbanning does not take a reason.
    items.remove('reason');

    return items;
  }

  confirmationText() {
    return app.translator.trans('fof-ban-ips.lib.modal.unban_ip_confirmation');
  }

  optionLabel(key: string) {
    return app.translator.trans(`fof-ban-ips.lib.modal.unban_options_${key}_ip`, {
      user: this.user,
      ip: this.address || (this.post && this.post.ipAddress()),
    });
  }

  usersWarning(items: ItemList<Mithril.Children>) {
    const otherUsers = this.otherUsers[this.banOption()];

    if (!otherUsers) return;

    const usernames = otherUsers.map((u) => (u && u.displayName()) || app.translator.trans('core.lib.username.deleted_text'));

    items.add(
      'otherUsers',
      otherUsers.length
        ? Alert.component(
            { dismissible: false },
            app.translator.trans('fof-ban-ips.lib.modal.unban_ip_users', {
              users: punctuateSeries(usernames),
              count: usernames.length,
            })
          )
        : Alert.component({ dismissible: false, type: 'success' }, app.translator.trans('fof-ban-ips.lib.modal.unban_ip_no_users')),
      70
    );
  }

  submitLabel() {
    return this.otherUsers[this.banOption()]
      ? app.translator.trans('fof-ban-ips.lib.modal.unban_button')
      : app.translator.trans('fof-ban-ips.lib.modal.check_button');
  }

  async onsubmit(e: SubmitEvent) {
    e.preventDefault();

    this.loading = true;

    if (typeof this.otherUsers[this.banOption()] === 'undefined') {
      await this.getOtherUsers();

      return;
    }

    try {
      if (this.banOption() === 'only') {
        const bannedIP = (this.post ? this.post.bannedIP() : app.store.getBy<BannedIP>('banned_ips', 'address', this.address)) as BannedIP;

        await bannedIP.delete();

        this.done(bannedIP);
      } else {
        const response = await app.request<ApiPayloadPlural>({
          body: { data: { attributes: {} } },
          url: `${app.forum.attribute<string>('apiUrl')}${(this.user as unknown as { apiEndpoint(): string }).apiEndpoint()}/unban`,
          method: 'POST',
        });

        this.done(response);
      }

      this.hide();
    } catch (error) {
      this.onerror(error as RequestError);
    } finally {
      this.loaded();
    }
  }

  async getOtherUsers() {
    const params: { ipAddress?: string | null; skipValidation?: boolean } = {};

    if (this.banOption() === 'only') {
      params.ipAddress = this.address || this.post!.ipAddress();
      params.skipValidation = true;
    }

    let url = `${app.forum.attribute<string>('apiUrl')}/banned_ips/check-users`;

    if (this.user) url += `/${this.user.id()}`;

    try {
      const response = await app.request<ApiPayloadPlural>({ params, url, method: 'GET' });

      this.otherUsers[this.banOption()] = app.store.pushPayload<User[]>(response).filter((user) => {
        const bannedIPs = user.banned_ips();

        return !!bannedIPs && bannedIPs.length === 1;
      });
    } catch (error) {
      this.onerror(error as RequestError);
    } finally {
      this.loaded();
    }
  }

  done(bannedIP?: BannedIP | ApiPayloadPlural) {
    this.loading = false;

    if (this.post && this.post.data.relationships) {
      delete this.post.data.relationships.banned_ip;
    }

    if (this.user && bannedIP instanceof app.store.models.banned_ips) {
      const relationships = this.user.data.relationships;

      // Only mutate the user's banned IPs relationship when it was actually loaded.
      // When unbanning a single IP from the admin panel, the associated user (if any) is
      // loaded as an included resource without its `banned_ips` relationship, so this would
      // otherwise throw "Cannot read property 'banned_ips' of undefined".
      if (relationships && relationships.banned_ips) {
        relationships.banned_ips = {
          data: (relationships.banned_ips.data as ModelIdentifier[]).filter((e) => e.id !== (bannedIP as BannedIP).id()),
        };

        if (this.user.data.attributes) {
          this.user.data.attributes.isBanned = (relationships.banned_ips.data as ModelIdentifier[]).length !== 0;
        }
      }
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
