import Model from 'flarum/Model';
import mixin from 'flarum/utils/mixin';

export default class BanIP extends mixin(Model, {
    userId: Model.attribute('userId'),
    postId: Model.attribute('postId'),
    ipAddress: Model.attribute('ipAddress'),
    createdAt: Model.attribute('createdAt', Model.transformDate),
}) {}