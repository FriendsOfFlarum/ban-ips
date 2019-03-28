import app from 'flarum/app';
import {extend} from 'flarum/extend';
import CommentPost from 'flarum/components/CommentPost';
import Button from 'flarum/components/Button';

import User from 'flarum/models/User';
import Model from 'flarum/Model';

app.initializers.add('fof-ban-ips', () => {
  User.prototype.canIPblock = Model.attribute('canIPblock');
  extend(CommentPost.prototype, 'actionItems', function (items) {
    const user = app.session.user;
    if (typeof user !== 'undefined' && user.canIPblock()) {
      const post = this.props.post;
      const postIP = post.data.attributes.ipAddress;
      const banlist = JSON.parse(app.forum.attribute('fof-ban-ips.ips'));
      // TODO: Check for permission AND if ip is already in banlist
      if (!banlist.includes(postIP)) {
        items.add(
          'banip',
          Button.component({
            children: app.translator.trans('fof-ban-ips.forum.button') + ": " + post.data.attributes.ipAddress,
            className: 'Button Button--link',
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
        }));
      }
    }
  });
});
