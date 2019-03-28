import app from 'flarum/app';
import {extend} from 'flarum/extend';
import addBanIPControl from "./addBanIPControl";

app.initializers.add('fof-ban-ips', () => {
 addBanIPControl();
});
