import type BannedIP from '../common/models/BannedIP';

declare module 'flarum/common/models/User' {
  export default interface User {
    canBanIP(): boolean;
    isBanned(): boolean;
    banned_ips(): (BannedIP | undefined)[] | false;
  }
}

declare module 'flarum/common/models/Post' {
  export default interface Post {
    canBanIP(): boolean;
    ipAddress(): string | null;
    bannedIP(): BannedIP | false;
  }
}
