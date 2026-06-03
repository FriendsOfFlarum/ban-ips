import app from 'flarum/admin/app';
import { IFormModalAttrs } from 'flarum/common/components/FormModal';
import FormModal from 'flarum/common/components/FormModal';
import Button from 'flarum/common/components/Button';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
import type BannedIP from '../../common/models/BannedIP';

export interface IChangeReasonModalAttrs extends IFormModalAttrs {
  item: BannedIP;
}

export default class ChangeReasonModal<CustomAttrs extends IChangeReasonModalAttrs = IChangeReasonModalAttrs> extends FormModal<CustomAttrs> {
  protected item!: BannedIP;
  protected reason!: Stream<string | null>;

  oninit(vnode: Mithril.Vnode<CustomAttrs, this>) {
    super.oninit(vnode);

    this.item = this.attrs.item;

    this.reason = Stream(this.item.reason());
  }

  className() {
    return 'Modal--medium';
  }

  title() {
    return app.translator.trans('fof-ban-ips.admin.modal.update_title');
  }

  content() {
    return (
      <div className="Modal-body">
        <div className="Form-group">
          <label className="label">{app.translator.trans('fof-ban-ips.lib.modal.reason_label')}</label>
          <input type="text" className="FormControl" bidi={this.reason} />
        </div>

        <div className="Form-group">
          <Button className="Button Button--primary" type="submit" loading={this.loading} disabled={this.reason() === this.item.reason()}>
            {app.translator.trans('fof-ban-ips.lib.modal.save_button')}
          </Button>
        </div>
      </div>
    );
  }

  onsubmit(e: SubmitEvent) {
    e.preventDefault();

    if (!this.reason()) return;

    this.loading = true;

    this.item
      .save({
        reason: this.reason(),
      })
      .then(this.hide.bind(this))
      .catch(this.onerror.bind(this))
      .then(this.loaded.bind(this));
  }
}
