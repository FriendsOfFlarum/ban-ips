import { BooleanGambit } from 'flarum/common/query/IGambit';
export default class IPBannedGambit extends BooleanGambit {
    key(): string;
    canonicalKey(): string;
    filterKey(): string;
    enabled(): boolean;
}
