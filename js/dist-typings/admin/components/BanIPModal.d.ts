import FormModal, { IFormModalAttrs } from 'flarum/common/components/FormModal';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
import type User from 'flarum/common/models/User';
export default class BanIPModal extends FormModal<IFormModalAttrs> {
    protected address: Stream<string>;
    protected reason: Stream<string>;
    protected usersBanned: Record<string, (User | null)[] | undefined>;
    oninit(vnode: Mithril.Vnode<IFormModalAttrs, this>): void;
    className(): string;
    title(): string | any[];
    content(): JSX.Element;
    fields(): ItemList<Mithril.Children>;
    onsubmit(e: SubmitEvent): Promise<void>;
    getOtherUsers(): Promise<void>;
}
