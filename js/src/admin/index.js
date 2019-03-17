import app from 'flarum/app';
import BanIPsSettingsModal from './components/BanIPsSettingsModal';

app.initializers.add('fof-ban-ips', () => {
  app.extensionSettings['fof-ban-ips'] = () => app.modal.show(new BanIPsSettingsModal());
});
