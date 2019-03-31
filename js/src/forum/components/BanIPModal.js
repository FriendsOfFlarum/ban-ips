import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';

export default class BanIPModal extends Modal {
  init() {
    super.init();
  }

  className() {
    return 'BanIPModal Modal--small';
  }

  title() {
    return app.translator.trans('fof-ban-ips.forum.ban_ip_modal.title')
  }

  content() {
    return (
      <div className="Modal-body">
        <p>
          {app.translator.trans('fof-ban-ips.forum.ban_ip_modal.ban_ip_confirmation')}
        </p>
        <div className="Form-group">
          <Button
            className="Button Button--primary Button--block"
            type="submit"
            loading={this.loading}>
            {app.translator.trans('fof-ban-ips.forum.ban_ip_modal.submit_button')}
          </Button>
        </div>
      </div>
    )
  }
}
