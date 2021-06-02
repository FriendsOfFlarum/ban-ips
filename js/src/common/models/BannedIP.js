import Model from 'flarum/common/Model';
import mixin from 'flarum/common/utils/mixin';

export default class BannedIP extends mixin(Model, {
    creator: Model.hasOne('creator'),
    address: Model.attribute('address'),
    reason: Model.attribute('reason'),
    createdAt: Model.attribute('createdAt', Model.transformDate),
    deletedAt: Model.attribute('deletedAt', Model.transformDate),
}) {
    apiEndpoint() {
        return `/fof/ban-ips${this.exists ? `/${this.id()}` : ''}`;
    }
}
