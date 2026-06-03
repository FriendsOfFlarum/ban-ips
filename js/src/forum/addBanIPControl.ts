import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import PostControls from 'flarum/forum/utils/PostControls';
import UserControls from 'flarum/forum/utils/UserControls';
import Button from 'flarum/common/components/Button';

// The ban/unban modals are only needed once a moderator decides to act, so they
// are lazy-loaded into their own chunks rather than shipped in the forum bundle.
const BanIPModal = () => import('../common/components/BanIPModal');
const UnbanIPModal = () => import('../common/components/UnbanIPModal');

export default function addBanIPControl(): void {
  extend(PostControls, 'userControls', function (items, post) {
    const user = post && post.user();

    if (!post || !user) return;

    const isBanned = user.isBanned();
    const prefix = isBanned ? 'un' : '';

    // Removes ability to ban thyself and also does permission check.
    if (!post.canBanIP() || post.isHidden() || user === app.session.user || post.contentType() !== 'comment') return;

    items.add(
      `${prefix}ban`,
      Button.component(
        {
          icon: 'fas fa-gavel',
          onclick: () => app.modal.show(isBanned ? UnbanIPModal : BanIPModal, { post }),
        },
        app.translator.trans(`fof-ban-ips.forum.${prefix}ban_ip_button`)
      )
    );
  });

  extend(UserControls, 'moderationControls', function (items, user) {
    if (!user.canBanIP() || user === app.session.user) return;

    const isBanned = user.isBanned();
    const prefix = isBanned ? 'un' : '';

    items.add(
      `${prefix}ban`,
      Button.component(
        {
          icon: 'fas fa-gavel',
          onclick: () => app.modal.show(isBanned ? UnbanIPModal : BanIPModal, { user }),
        },
        app.translator.trans(`fof-ban-ips.forum.user_controls.${prefix}ban_button`)
      )
    );
  });
}
