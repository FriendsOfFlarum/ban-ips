import Component, { ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';
import type BannedIP from '../../common/models/BannedIP';
export interface ISettingsPageItemAttrs extends ComponentAttrs {
    bannedIP: BannedIP;
}
export default class SettingsPageItem<CustomAttrs extends ISettingsPageItemAttrs = ISettingsPageItemAttrs> extends Component<CustomAttrs> {
    protected item: BannedIP;
    oninit(vnode: Mithril.Vnode<CustomAttrs, this>): void;
    view(): JSX.Element;
}
