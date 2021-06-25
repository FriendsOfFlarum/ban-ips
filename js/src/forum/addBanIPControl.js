import { extend } from 'flarum/common/extend';
import PostControls from 'flarum/forum/utils/PostControls';
import UserControls from 'flarum/forum/utils/UserControls';
import Button from 'flarum/common/components/Button';

import BanIPModal from '../common/components/BanIPModal';
import UnbanIPModal from '../common/components/UnbanIPModal';

export default () => {
    extend(PostControls, 'userControls', function (items, post) {
        if (!post || !post.user()) return;

        const isBanned = post.user().isBanned();
        const prefix = isBanned ? 'un' : '';

        // Removes ability to ban thyself and also does permission check.
        if (!post.canBanIP() || post.isHidden() || post.user() === app.session.user || post.contentType() !== 'comment') return;

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
                    onclick: () => app.modal.show(isBanned ? UnbanIPModal : BanIPModal, { post }),
                },
                app.translator.trans(`fof-ban-ips.forum.user_controls.${prefix}ban_button`)
            )
        );
    });
};
