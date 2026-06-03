/// <reference types="mithril" />
/// <reference types="flarum/@types/translator-icu-rich" />
import type { ApiPayloadPlural } from 'flarum/common/Store';
import BanIPModal from './BanIPModal';
import type BannedIP from '../models/BannedIP';
export default class UnbanIPModal extends BanIPModal {
    protected bannedIPs?: string[];
    title(): import("@askvortsov/rich-icu-message-formatter").NestedStringArray;
    className(): string;
    content(): JSX.Element;
    onsubmit(e: SubmitEvent): void;
    getOtherUsers(): void;
    done(bannedIP?: BannedIP | ApiPayloadPlural): void;
    hide(): void;
}
