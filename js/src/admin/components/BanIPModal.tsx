import app from 'flarum/admin/app';
import FormModal, { IFormModalAttrs } from 'flarum/common/components/FormModal';
import Button from 'flarum/common/components/Button';
import Alert from 'flarum/common/components/Alert';
import Form from 'flarum/common/components/Form';
import ItemList from 'flarum/common/utils/ItemList';
import punctuateSeries from 'flarum/common/helpers/punctuateSeries';
import username from 'flarum/common/helpers/username';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
import type User from 'flarum/common/models/User';
import type RequestError from 'flarum/common/utils/RequestError';
import type { ApiPayloadPlural } from 'flarum/common/Store';
import type BannedIP from '../../common/models/BannedIP';

// Matches an IPv4 address, IPv6 address, or hostname. Used to validate the
// address field before submission.
const IP_OR_HOST_PATTERN =
  '^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\\-]*[a-zA-Z0-9])\\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\\-]*[A-Za-z0-9])$|^\\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)(\\.(25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)(\\.(25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)(\\.(25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)(\\.(25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)(\\.(25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)(\\.(25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)(\\.(25[0-5]|2[0-4]\\d|1\\d\\d|[1-9]?\\d)){3}))|:)))(%.+)?\\s*$';

export default class BanIPModal extends FormModal<IFormModalAttrs> {
  protected address!: Stream<string>;
  protected reason!: Stream<string>;
  protected usersBanned!: Record<string, (User | null)[] | undefined>;

  oninit(vnode: Mithril.Vnode<IFormModalAttrs, this>) {
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
    return (
      <div className="Modal-body">
        <Form>{this.fields().toArray()}</Form>
      </div>
    );
  }

  fields() {
    const items = new ItemList<Mithril.Children>();

    const usersBanned = this.usersBanned[this.address()];
    const usernames = usersBanned && usersBanned.map((u) => username(u));

    items.add('help', <p>{app.translator.trans('fof-ban-ips.lib.modal.ban_ip_confirmation')}</p>, 100);

    items.add(
      'address',
      <div className="Form-group">
        <label className="label">{app.translator.trans('fof-ban-ips.lib.modal.address_label')}</label>
        <input type="text" className="FormControl" bidi={this.address} required pattern={IP_OR_HOST_PATTERN} />
      </div>,
      90
    );

    items.add(
      'reason',
      <div className="Form-group">
        <label className="label">{app.translator.trans('fof-ban-ips.lib.modal.reason_label')}</label>
        <input type="text" className="FormControl" bidi={this.reason} />
      </div>,
      80
    );

    if (usersBanned) {
      items.add(
        'usersBanned',
        usersBanned.length
          ? Alert.component(
              { dismissible: false },
              app.translator.trans('fof-ban-ips.lib.modal.ban_ip_users', {
                users: punctuateSeries(usernames!),
                count: usernames!.length,
              })
            )
          : Alert.component({ dismissible: false, type: 'success' }, app.translator.trans('fof-ban-ips.admin.modal.ban_ip_no_users')),
        70
      );
    }

    items.add(
      'submit',
      <div className="Form-group Form-controls">
        <Button
          className="Button Button--primary"
          type="submit"
          loading={this.loading}
          disabled={!!app.store.getBy<BannedIP>('banned_ips', 'address', this.address())}
        >
          {usernames ? app.translator.trans('fof-ban-ips.lib.modal.ban_button') : app.translator.trans('fof-ban-ips.lib.modal.check_button')}
        </Button>
      </div>,
      -10
    );

    return items;
  }

  async onsubmit(e: SubmitEvent) {
    e.preventDefault();

    if (!this.address()) return;

    this.loading = true;

    // The first submit only resolves who would be affected; the admin then
    // confirms with a second submit.
    if (typeof this.usersBanned[this.address()] === 'undefined') {
      await this.getOtherUsers();

      return;
    }

    try {
      await app.store.createRecord('banned_ips').save({
        address: this.address(),
        reason: this.reason(),
      });

      this.hide();
    } catch (error) {
      this.onerror(error as RequestError);
    } finally {
      this.loaded();
    }
  }

  async getOtherUsers() {
    try {
      const response = await app.request<ApiPayloadPlural>({
        params: { ipAddress: this.address() },
        url: `${app.forum.attribute<string>('apiUrl')}/banned_ips/check-users`,
        method: 'GET',
      });

      this.usersBanned[this.address()] = response.data.map((data) => app.store.pushObject<User>(data));
    } catch (error) {
      this.onerror(error as RequestError);
    } finally {
      this.loaded();
    }
  }
}
