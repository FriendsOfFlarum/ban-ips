import BanIPModal from './components/BanIPModal';
import ChangeReasonModal from './components/ChangeReasonModal';
import SettingsPage from './components/SettingsPage';
import SettingsPageItem from './components/SettingsPageItem';
declare const _default: {
    'fof/ban-ips/components/BanIPModal': typeof import("../common/components/BanIPModal").default;
    'fof/ban-ips/components/UnbanIPModal': typeof import("../common/components/UnbanIPModal").default;
    'fof/ban-ips/models/BannedIP': typeof import("../common/models/BannedIP").default;
} & {
    'fof/ban-ips/components/BanIPModal': typeof BanIPModal;
    'fof/ban-ips/components/ChangeReasonModal': typeof ChangeReasonModal;
    'fof/ban-ips/components/SettingsPage': typeof SettingsPage;
    'fof/ban-ips/components/SettingsPageItem': typeof SettingsPageItem;
};
export default _default;
