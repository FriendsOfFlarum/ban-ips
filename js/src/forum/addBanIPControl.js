import { extend } from 'flarum/extend';
import PostControls from 'flarum/utils/PostControls';
import UserControls from 'flarum/utils/UserControls';
import Button from 'flarum/components/Button';

import BanIPModal from './components/BanIPModal';
import UnbanIPModal from './components/UnbanIPModal';

export default () => {
    extend(PostControls, 'userControls', function(items, post) {
        const isBanned = post.user().isBanned();
        const prefix = isBanned ? 'un' : '';

        // Removes ability to ban thyself and also does permission check.
        if (!post.canBanIP() || post.isHidden() || post.user() === app.session.user || post.contentType() !== 'comment') return;

        items.add(
            `${prefix}ban`,
            Button.component({
                children: app.translator.trans(`fof-ban-ips.forum.${prefix}ban_ip_button`),
                className: 'Button Button--link',
                icon: 'fas fa-gavel',
                onclick: () => app.modal.show(isBanned ? new UnbanIPModal({ post }) : new BanIPModal({ post })),
            })
        );
    });

    extend(UserControls, 'moderationControls', function(items, user) {
        if (user.canBanIP() || user === app.session.user) return;

        const isBanned = user.isBanned();
        const prefix = isBanned ? 'un' : '';

        items.add(
            `${prefix}ban`,
            Button.component({
                children: app.translator.trans(`fof-ban-ips.forum.user_controls.${prefix}ban_button`),
                icon: 'fas fa-gavel',
                onclick: () => app.modal.show(isBanned ? new UnbanIPModal({ user }) : new BanIPModal({ user })),
            })
        );
    });
};
