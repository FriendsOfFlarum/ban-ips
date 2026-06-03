import Model from 'flarum/common/Model';
import type User from 'flarum/common/models/User';
export default class BannedIP extends Model {
    creator: () => false | User;
    user: () => false | User | null;
    address: () => string;
    reason: () => string | null;
    createdAt: () => Date | undefined;
    deletedAt: () => Date | null | undefined;
    apiEndpoint(): string;
}
