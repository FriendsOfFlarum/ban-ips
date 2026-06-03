import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import User from 'flarum/common/models/User';
import Badge from 'flarum/common/components/Badge';

export default function addBannedBadge(): void {
  extend(User.prototype, 'badges', function (this: User, items) {
    if (this.isBanned()) {
      items.add(
        'banned',
        Badge.component({
          icon: 'fas fa-gavel',
          type: 'banned',
          label: app.translator.trans('fof-ban-ips.forum.user_badge.banned_tooltip'),
        })
      );
    }
  });
}
