/// <reference types="flarum/@types/translator-icu-rich" />
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
import type BannedIP from '../../common/models/BannedIP';
export interface IChangeReasonModalAttrs extends IInternalModalAttrs {
    item: BannedIP;
}
export default class ChangeReasonModal<CustomAttrs extends IChangeReasonModalAttrs = IChangeReasonModalAttrs> extends Modal<CustomAttrs> {
    protected item: BannedIP;
    protected reason: Stream<string | null>;
    oninit(vnode: Mithril.Vnode<CustomAttrs, this>): void;
    className(): string;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    content(): JSX.Element;
    onsubmit(e: SubmitEvent): void;
}
