import FormModal, { IFormModalAttrs } from 'flarum/common/components/FormModal';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
import type Post from 'flarum/common/models/Post';
import type User from 'flarum/common/models/User';
import type BannedIP from '../models/BannedIP';
export interface IBanIPModalAttrs extends IFormModalAttrs {
    address?: string;
    post?: Post;
    user?: User;
    redraw?: boolean;
}
export default class BanIPModal extends FormModal<IBanIPModalAttrs> {
    protected address?: string;
    protected post?: Post;
    protected user?: User;
    protected banOptions: string[];
    protected banOption: Stream<string>;
    protected reason: Stream<string>;
    protected otherUsers: Record<string, (User | null)[] | undefined>;
    oninit(vnode: Mithril.Vnode<IBanIPModalAttrs, this>): void;
    className(): string;
    title(): string | any[];
    content(): JSX.Element;
    fields(): ItemList<Mithril.Children>;
    /**
     * The confirmation text shown at the top of the modal. Overridden by the
     * unban modal.
     */
    confirmationText(): string | any[];
    /**
     * The label for a given ban-scope radio option ("only this IP" / "all IPs").
     * Overridden by the unban modal to use its own wording.
     */
    optionLabel(key: string): any[];
    /**
     * Add an alert describing which other users a ban/unban would affect, once the
     * server has been asked. Overridden by the unban modal to use its own wording.
     */
    usersWarning(items: ItemList<Mithril.Children>): void;
    /**
     * The label of the submit button. Switches to a "check" action until the
     * affected users have been resolved.
     */
    submitLabel(): string | any[];
    onsubmit(e: SubmitEvent): Promise<void>;
    getOtherUsers(): Promise<void>;
    done(bannedIP: BannedIP): void;
}
