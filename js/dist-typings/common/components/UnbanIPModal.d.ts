import ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';
import type { ApiPayloadPlural } from 'flarum/common/Store';
import BanIPModal from './BanIPModal';
import type BannedIP from '../models/BannedIP';
export default class UnbanIPModal extends BanIPModal {
    protected bannedIPs?: string[];
    title(): string | any[];
    className(): string;
    content(): JSX.Element;
    fields(): ItemList<Mithril.Children>;
    confirmationText(): string | any[];
    optionLabel(key: string): any[];
    usersWarning(items: ItemList<Mithril.Children>): void;
    submitLabel(): string | any[];
    onsubmit(e: SubmitEvent): Promise<void>;
    getOtherUsers(): Promise<void>;
    done(bannedIP?: BannedIP | ApiPayloadPlural): void;
    hide(): void;
}
