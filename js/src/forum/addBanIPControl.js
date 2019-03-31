import {extend} from 'flarum/extend';
import app from 'flarum/app';
import PostControls from 'flarum/utils/PostControls';
import Button from 'flarum/components/Button';

import BanIPModal from './components/BanIPModal';

import User from 'flarum/models/User';
import Model from 'flarum/Model';

export default function () {
  User.prototype.canBanIP = Model.attribute('canBanIP');

  extend(PostControls, 'userControls', function (items, post) {
    const postIP = post.data.attributes.ipAddress;
    // TODO: Get Banned IP Addresses from banned_ip_addresses table.
    const banList = false;

    // Removes ability to ban thyself and also does permission check.
    if (!post.canBanIP || post.isHidden() || post.user() === app.session.user || post.contentType() !== 'comment') return;

    items.add('banip', Button.component({
      children: app.translator.trans(banList ? 'fof-ban-ips.forum.ban_ip_button' : 'fof-ban-ips.forum.already_banned_ip_button'),
      className: 'Button Button--link',
      icon: 'fas fa-ban',
      title: postIP,
      onclick: () => {
        app.modal.show(new BanIPModal())
      }
    }));
  });
}
