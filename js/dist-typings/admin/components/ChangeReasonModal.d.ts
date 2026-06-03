import FormModal, { IFormModalAttrs } from 'flarum/common/components/FormModal';
import ItemList from 'flarum/common/utils/ItemList';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
import type BannedIP from '../../common/models/BannedIP';
export interface IChangeReasonModalAttrs extends IFormModalAttrs {
    item: BannedIP;
}
export default class ChangeReasonModal extends FormModal<IChangeReasonModalAttrs> {
    protected item: BannedIP;
    protected reason: Stream<string | null>;
    oninit(vnode: Mithril.Vnode<IChangeReasonModalAttrs, this>): void;
    className(): string;
    title(): string | any[];
    content(): JSX.Element;
    fields(): ItemList<Mithril.Children>;
    onsubmit(e: SubmitEvent): void;
}
