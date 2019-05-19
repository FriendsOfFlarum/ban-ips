import Model from 'flarum/Model';
import mixin from 'flarum/utils/mixin';

export default class BannedIP extends mixin(Model, {
    creator: Model.hasOne('user'),
    user: Model.hasOne('user'),
    address: Model.attribute('address'),
    reason: Model.attribute('reason'),
    createdAt: Model.attribute('createdAt', Model.transformDate),
    deletedAt: Model.attribute('deletedAt', Model.transformDate),
}) {
    apiEndpoint() {
        return `/fof/ban-ips/bans${this.exists ? `/${this.id()}` : ''}`;
    }
}
