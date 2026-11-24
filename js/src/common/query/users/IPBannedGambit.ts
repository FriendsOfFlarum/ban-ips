import app from 'flarum/common/app';
import { BooleanGambit } from 'flarum/common/query/IGambit';

export default class IPBannedGambit extends BooleanGambit {
  key(): string {
    return app.translator.trans('fof-ban-ips.lib.gambits.users.ipbanned.key', {}, true);
  }

  canonicalKey(): string {
    return 'ipbanned';
  }

  filterKey(): string {
    return 'ipbanned';
  }

  enabled(): boolean {
    return !!app.session.user && !!app.forum.attribute<boolean>('canViewIPBans');
  }
}
