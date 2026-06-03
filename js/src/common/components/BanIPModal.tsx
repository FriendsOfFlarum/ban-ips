import app from 'flarum/common/app';
import FormModal, { IFormModalAttrs } from 'flarum/common/components/FormModal';
import Button from 'flarum/common/components/Button';
import Alert from 'flarum/common/components/Alert';
import Form from 'flarum/common/components/Form';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import punctuateSeries from 'flarum/common/helpers/punctuateSeries';
import type Mithril from 'mithril';
import type Post from 'flarum/common/models/Post';
import type User from 'flarum/common/models/User';
import type RequestError from 'flarum/common/utils/RequestError';
import type { ModelIdentifier, SavedModelData } from 'flarum/common/Model';
import type { ApiPayloadPlural } from 'flarum/common/Store';
import type BannedIP from '../models/BannedIP';

export interface IBanIPModalAttrs extends IFormModalAttrs {
  address?: string;
  post?: Post;
  user?: User;
  redraw?: boolean;
}

export default class BanIPModal extends FormModal<IBanIPModalAttrs> {
  protected address?: string;
  protected post?: Post;
  protected user?: User;
  protected banOptions!: string[];
  protected banOption!: Stream<string>;
  protected reason!: Stream<string>;
  protected otherUsers!: Record<string, (User | null)[] | undefined>;

  oninit(vnode: Mithril.Vnode<IBanIPModalAttrs, this>) {
    super.oninit(vnode);

    this.address = this.attrs.address;
    this.post = this.attrs.post;
    this.user = this.attrs.user || (this.post && this.post.user()) || undefined;

    if (!this.user && this.address) {
      const bannedIP = app.store.getBy<BannedIP>('banned_ips', 'address', this.address);

      if (bannedIP) this.user = bannedIP.user() || undefined;
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
    return 'BanIPModal Modal--medium';
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

    items.add('help', <p>{this.confirmationText()}</p>, 100);

    items.add(
      'banOptions',
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
            <label htmlFor={`ban-option-${key}`}>{this.optionLabel(key)}</label>
          </div>
        ))}
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

    this.usersWarning(items);

    items.add(
      'submit',
      <div className="Form-group Form-controls">
        <Button className="Button Button--primary" type="submit" loading={this.loading}>
          {this.submitLabel()}
        </Button>
      </div>,
      -10
    );

    return items;
  }

  /**
   * The confirmation text shown at the top of the modal. Overridden by the
   * unban modal.
   */
  confirmationText() {
    return app.translator.trans('fof-ban-ips.lib.modal.ban_ip_confirmation');
  }

  /**
   * The label for a given ban-scope radio option ("only this IP" / "all IPs").
   * Overridden by the unban modal to use its own wording.
   */
  optionLabel(key: string) {
    return app.translator.trans(`fof-ban-ips.forum.modal.ban_options_${key}_ip`, {
      user: this.user,
      ip: this.address || (this.post && this.post.ipAddress()),
    });
  }

  /**
   * Add an alert describing which other users a ban/unban would affect, once the
   * server has been asked. Overridden by the unban modal to use its own wording.
   */
  usersWarning(items: ItemList<Mithril.Children>) {
    const otherUsersBanned = this.otherUsers[this.banOption()];

    if (!otherUsersBanned) return;

    const usernames = otherUsersBanned.map((u) => (u && u.displayName()) || app.translator.trans('core.lib.username.deleted_text'));

    items.add(
      'otherUsers',
      otherUsersBanned.length
        ? Alert.component(
            { dismissible: false },
            app.translator.trans('fof-ban-ips.lib.modal.ban_ip_users', {
              users: punctuateSeries(usernames),
              count: usernames.length,
            })
          )
        : Alert.component({ dismissible: false, type: 'success' }, app.translator.trans('fof-ban-ips.forum.modal.ban_ip_no_users')),
      70
    );
  }

  /**
   * The label of the submit button. Switches to a "check" action until the
   * affected users have been resolved.
   */
  submitLabel() {
    return this.otherUsers[this.banOption()]
      ? app.translator.trans('fof-ban-ips.lib.modal.ban_button')
      : app.translator.trans('fof-ban-ips.lib.modal.check_button');
  }

  async onsubmit(e: SubmitEvent) {
    e.preventDefault();

    this.loading = true;

    // The first submit only resolves who would be affected; the operator then
    // confirms with a second submit.
    if (typeof this.otherUsers[this.banOption()] === 'undefined') {
      await this.getOtherUsers();

      return;
    }

    const attrs: { reason: string; userId?: string; address?: string | null } = {
      reason: this.reason(),
      userId: this.user?.id(),
    };

    try {
      if (this.banOption() === 'only') {
        attrs.address = this.post!.ipAddress();

        await app.store.createRecord('banned_ips').save(attrs);
      } else {
        const response = await app.request<ApiPayloadPlural>({
          body: { data: { attributes: attrs } },
          url: `${app.forum.attribute<string>('apiUrl')}${(this.user as unknown as { apiEndpoint(): string }).apiEndpoint()}/ban`,
          method: 'POST',
        });

        app.store.pushPayload<BannedIP[]>(response).forEach(this.done.bind(this));
      }

      this.hide();
    } catch (error) {
      this.onerror(error as RequestError);
    } finally {
      this.loaded();
    }
  }

  async getOtherUsers() {
    const params: { ipAddress?: string | null } = {};

    if (this.banOption() === 'only') {
      params.ipAddress = this.address || this.post!.ipAddress();
    }

    try {
      const response = await app.request<ApiPayloadPlural>({
        params,
        url: `${app.forum.attribute<string>('apiUrl')}/banned_ips/check-users/${this.user!.id()}`,
        method: 'GET',
      });

      this.otherUsers[this.banOption()] = response.data
        .map((data) => app.store.pushObject<User>(data))
        .filter((user): user is User => {
          const bannedIPs = user?.banned_ips();

          return !!bannedIPs && bannedIPs.length === 0;
        });
    } catch (error) {
      this.onerror(error as RequestError);
    } finally {
      this.loaded();
    }
  }

  done(bannedIP: BannedIP) {
    const obj: ModelIdentifier = {
      type: 'banned_ips',
      id: bannedIP.id()!,
    };

    if (this.post) {
      this.post.data.relationships!.banned_ip = {
        data: obj,
      };
    }

    if (!this.user!.data.relationships!.banned_ips)
      this.user!.data.relationships!.banned_ips = {
        data: [],
      };

    (this.user!.data.relationships!.banned_ips.data as ModelIdentifier[]).push(obj);
    this.user!.data.attributes!.isBanned = true;

    app.store.pushObject(this.user!.data as SavedModelData);
  }
}
