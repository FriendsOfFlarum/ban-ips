import { extend } from 'flarum/extend';
import PostControls from 'flarum/utils/PostControls';
import Button from 'flarum/components/Button';
import BanIPModal from './components/BanIPModal';

export default () => {
    extend(PostControls, 'userControls', function(items, post) {
        const postIP = post.ipAddress();
        const isBanned = post.user().isBanned();

        // Removes ability to ban thyself and also does permission check.
        if (!post.canBanIP() || post.isHidden() || post.user() === app.session.user || post.contentType() !== 'comment') return;

        items.add(
            'banip',
            Button.component({
                children: app.translator.trans(!isBanned ? 'fof-ban-ips.forum.ban_ip_button' : 'fof-ban-ips.forum.already_banned_ip_button'),
                className: 'Button Button--link',
                icon: 'fas fa-ban',
                disabled: isBanned,
                onclick: () => app.modal.show(new BanIPModal({ post })),
            })
        );
    });
};
