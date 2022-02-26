import compat from '../common/compat';

import BanIPModal from './components/BanIPModal';
import ChangeReasonModal from './components/ChangeReasonModal';
import SettingsPage from './components/SettingsPage';
import SettingsPageItem from './components/SettingsPageItem';

export default Object.assign(compat, {
  'fof/ban-ips/components/BanIPModal': BanIPModal,
  'fof/ban-ips/components/ChangeReasonModal': ChangeReasonModal,
  'fof/ban-ips/components/SettingsPage': SettingsPage,
  'fof/ban-ips/components/SettingsPageItem': SettingsPageItem,
});
