
import Alert from 'flarum/common/components/Alert';

export default function restictedIPAlert(app) {
    const restrictedIP = app.forum.attribute('ipRestricted');

    if (!restrictedIP) { return; }

    class ContainedAlert extends Alert {
        view(vnode) {
            const vdom = super.view(vnode);
            return { ...vdom, children: [<div className="container">{vdom.children}</div>] };
        }
    }

    m.mount($('<div/>').insertBefore('#content')[0], {
        view: () => (
            <ContainedAlert dismissible={false}>
                {app.translator.trans('fof-ban-ips.forum.alert.restricted_ip')}
            </ContainedAlert>
        ),
    });
}
