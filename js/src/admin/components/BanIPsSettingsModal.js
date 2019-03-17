import app from 'flarum/app';
import SettingsModal from 'flarum/components/SettingsModal';
import saveSettings from 'flarum/utils/saveSettings';

const setting = 'fof-ban-ips.ips';
const translationPrefix = 'fof-ban-ips.admin.settings.';

export default class BanIPsSettingsModal extends SettingsModal {
    constructor()
    {
        super();
        const banlistString = app.data.settings[setting];
        this.banlist = typeof banlistString !== 'undefined' ? JSON.parse(banlistString) : [];
    }
    className()
    {
        return 'KeyboardShortcutsSettingsModal Modal--medium';
    }
	title() {
        return app.translator.trans(translationPrefix + 'title');
    }

    form() {
        return [
            m('table#ip-list-table', [
                    m('tr', [
                        m('td', app.translator.trans(translationPrefix + 'delete')),
                        m('td', app.translator.trans(translationPrefix + 'ip')),
                    ]),
                    this.banlist.map((i) => {
                        return m('tr#element'+i, [
                                    m('td.td-ip',
                                        m('a',{
                                            onclick: () => {
                                                this.banlist.splice(this.banlist.indexOf(i), 1);
                                                m.render(document.getElementById("element"+i), "");
                                            } 
                                            }, m('i', {
                                                    className: "fas fa-trash"
                                                }
                                            ,'')
                                        ),
                                    ),
                                    m('td.td-del', i),
                                ]
                            )
                    })
                ]
            ),
        ];
    }
    submitButton() {
        return m('Button', {
            type: "submit",
            className: "Button Button--primary",
            onclick: saveSettings({'fof-ban-ips.ips': JSON.stringify(this.banlist)}),
        }, "Save");
  }
}