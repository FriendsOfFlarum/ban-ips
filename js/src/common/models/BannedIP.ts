import Model from 'flarum/common/Model';
import type User from 'flarum/common/models/User';

export default class BannedIP extends Model {
  creator = Model.hasOne<User>('creator');
  user = Model.hasOne<User | null>('user');
  address = Model.attribute<string>('address');
  reason = Model.attribute<string | null>('reason');
  createdAt = Model.attribute<Date | undefined, string | undefined>('createdAt', Model.transformDate);
  deletedAt = Model.attribute<Date | null | undefined, string | null | undefined>('deletedAt', Model.transformDate);

  apiEndpoint(): string {
    return `/fof/ban-ips${this.exists ? `/${this.id()}` : ''}`;
  }
}
