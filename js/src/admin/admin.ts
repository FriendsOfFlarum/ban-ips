import '../common/common';

// Only the components that are part of the always-rendered settings page are
// eagerly bundled. The modals (BanIPModal, ChangeReasonModal, UnbanIPModal) are
// lazy-loaded on demand from their respective controls.
import './components/SettingsPage';
import './components/SettingsPageItem';
