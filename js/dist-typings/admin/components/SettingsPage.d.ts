import ExtensionPage, { ExtensionPageAttrs } from 'flarum/admin/components/ExtensionPage';
import type Mithril from 'mithril';
import type { ApiResponsePlural } from 'flarum/common/Store';
import type BannedIP from '../../common/models/BannedIP';
export default class SettingsPage<CustomAttrs extends ExtensionPageAttrs = ExtensionPageAttrs> extends ExtensionPage<CustomAttrs> {
    protected page: number;
    protected pageSize: number;
    protected nextResults?: boolean;
    protected prevResults?: boolean;
    oninit(vnode: Mithril.Vnode<CustomAttrs, this>): void;
    oncreate(vnode: Mithril.VnodeDOM<CustomAttrs, this>): void;
    content(): JSX.Element;
    refresh(): Promise<void>;
    /**
     * Load a new page of results.
     */
    loadResults(): Promise<ApiResponsePlural<BannedIP>>;
    /**
     * Load the next page of results.
     */
    loadNext(): void;
    /**
     * Load the previous page of results.
     */
    loadPrev(): void;
    /**
     * Parse results and append them to the page list.
     */
    parseResults(results: ApiResponsePlural<BannedIP>): void;
}
