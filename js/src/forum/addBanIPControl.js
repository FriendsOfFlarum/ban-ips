import {extend} from 'flarum/extend';
import app from 'flarum/app';
import PostControls from 'flarum/utils/PostControls';
import Button from 'flarum/components/Button';

import User from 'flarum/models/User';
import Model from 'flarum/Model';


export default function () {
  User.prototype.canIPblock = Model.attribute('canIPblock');
  extend(PostControls, 'userControls', function (items, post) {
    const user = app.session.user;
    if (typeof user !== 'undefined' && user.canIPblock()) {
      const postIP = post.data.attributes.ipAddress;
      const banlist = JSON.parse(app.forum.attribute('fof-ban-ips.ips'));
      // TODO: Check for permission AND if ip is already in banlist
      if (!banlist.includes(postIP)) {
        items.add('banip', Button.component({
            children: app.translator.trans('fof-ban-ips.forum.button') + ": " + post.data.attributes.ipAddress,
            className: 'Button Button--link',
            icon: 'fas fa-ban',
            onclick: () => {
              if (!confirm(app.translator.trans('fof-ban-ips.forum.ban-ip-confirmation'))) return;

              app.request({
                url: `${app.forum.attribute('apiUrl')}/posts/${post.id()}/banip`,
                method: 'POST',
              }).then(() => window.location.reload());
            },
          })
        );
      } else {
        items.add('banip', Button.component({
          children: app.translator.trans('fof-ban-ips.forum.already-banned') + ": " + post.data.attributes.ipAddress,
          className: 'Button Button--link banip-banned',
          icon: 'fas fa-ban',
        }));
      }
    }
  });
}
