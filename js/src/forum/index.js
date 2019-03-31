import app from 'flarum/app';
import Model from 'flarum/Model';
import User from 'flarum/models/User';

import addBanIPControl from "./addBanIPControl";

app.initializers.add('fof-ban-ips', () => {
  app.store.models.posts.prototype.canBanIP = Model.attribute('canBanIP');

  addBanIPControl();
});
