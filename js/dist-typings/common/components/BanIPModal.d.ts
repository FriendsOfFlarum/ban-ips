/// <reference types="flarum/@types/translator-icu-rich" />
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
import type Post from 'flarum/common/models/Post';
import type User from 'flarum/common/models/User';
import type BannedIP from '../models/BannedIP';
export interface IBanIPModalAttrs extends IInternalModalAttrs {
    address?: string;
    post?: Post;
    user?: User;
    redraw?: boolean;
}
export default class BanIPModal<CustomAttrs extends IBanIPModalAttrs = IBanIPModalAttrs> extends Modal<CustomAttrs> {
    protected address?: string;
    protected post?: Post;
    protected user?: User;
    protected banOptions: string[];
    protected banOption: Stream<string>;
    protected reason: Stream<string>;
    protected otherUsers: Record<string, (User | null)[] | undefined>;
    oninit(vnode: Mithril.Vnode<CustomAttrs, this>): void;
    className(): string;
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    content(): JSX.Element;
    onsubmit(e: SubmitEvent): void;
    getOtherUsers(): void;
    done(bannedIP: BannedIP): void;
}
