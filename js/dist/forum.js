(()=>{var t={n:n=>{var s=n&&n.__esModule?()=>n.default:()=>n;return t.d(s,{a:s}),s},d:(n,s)=>{for(var a in s)t.o(s,a)&&!t.o(n,a)&&Object.defineProperty(n,a,{enumerable:!0,get:s[a]})},o:(t,n)=>Object.prototype.hasOwnProperty.call(t,n),r:t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})}},n={};(()=>{"use strict";t.r(n);const s=flarum.core.compat["common/app"];var a=t.n(s);const e=flarum.core.compat["common/Model"];var o=t.n(e);const r=flarum.core.compat["common/extend"],i=flarum.core.compat["forum/utils/PostControls"];var p=t.n(i);const d=flarum.core.compat["forum/utils/UserControls"];var u=t.n(d);const l=flarum.core.compat["common/components/Button"];var h=t.n(l);function c(t,n){return c=Object.setPrototypeOf||function(t,n){return t.__proto__=n,t},c(t,n)}function b(t,n){t.prototype=Object.create(n.prototype),t.prototype.constructor=t,c(t,n)}const f=flarum.core.compat["common/components/Modal"];var y=t.n(f);const _=flarum.core.compat["common/components/Alert"];var v=t.n(_);const O=flarum.core.compat["common/utils/Stream"];var g=t.n(O);const P=flarum.core.compat["common/helpers/punctuateSeries"];var B=t.n(P),I=function(t){function n(){return t.apply(this,arguments)||this}b(n,t);var s=n.prototype;return s.oninit=function(n){if(t.prototype.oninit.call(this,n),this.address=this.attrs.address,this.post=this.attrs.post,this.user=this.attrs.user||this.post&&this.post.user(),!this.user&&this.address){var s=app.store.getBy("banned_ips","address",this.address);s&&(this.user=s.user())}this.banOptions=[],(this.post&&this.post.ipAddress()||this.address)&&this.banOptions.push("only"),this.user&&this.banOptions.push("all"),this.banOption=g()(this.banOptions[0]),this.reason=g()(""),this.otherUsers={},this.loading=!1},s.className=function(){return"Modal--medium"},s.title=function(){return app.translator.trans("fof-ban-ips.lib.modal.title")},s.content=function(){var t=this,n=this.otherUsers[this.banOption()],s=n&&n.map((function(t){return t&&t.displayName()||app.translator.trans("core.lib.username.deleted_text")}));return m("div",{className:"Modal-body"},m("p",null,app.translator.trans("fof-ban-ips.lib.modal.ban_ip_confirmation")),m("div",{className:"Form-group"},this.banOptions.map((function(n){return m("div",null,m("input",{type:"radio",name:"ban-option",id:"ban-option-"+n,checked:t.banOption()===n,onclick:t.banOption.bind(t,n)})," ",m("label",{htmlFor:"ban-option-"+n},app.translator.trans("fof-ban-ips.forum.modal.ban_options_"+n+"_ip",{user:t.user,ip:t.address||t.post&&t.post.ipAddress()})))}))),m("div",{className:"Form-group"},m("label",{className:"label"},app.translator.trans("fof-ban-ips.lib.modal.reason_label")),m("input",{type:"text",className:"FormControl",bidi:this.reason})),n?n.length?v().component({dismissible:!1},app.translator.trans("fof-ban-ips.lib.modal.ban_ip_users",{users:B()(s),count:s.length})):v().component({dismissible:!1,type:"success"},app.translator.trans("fof-ban-ips.forum.modal.ban_ip_no_users")):"",n&&m("br",null),m("div",{className:"Form-group"},m(h(),{className:"Button Button--primary",type:"submit",loading:this.loading},s?app.translator.trans("fof-ban-ips.lib.modal.ban_button"):app.translator.trans("fof-ban-ips.lib.modal.check_button"))))},s.onsubmit=function(t){var n=this;if(t.preventDefault(),this.loading=!0,void 0===this.otherUsers[this.banOption()])return this.getOtherUsers();var s={reason:this.reason(),userId:this.user.id()};"only"===this.banOption()?(s.address=this.post.ipAddress(),app.store.createRecord("banned_ips").save(s).then(this.hide.bind(this)).catch(this.onerror.bind(this)).then(this.loaded.bind(this))):"all"===this.banOption()&&app.request({body:{data:{attributes:s}},url:""+app.forum.attribute("apiUrl")+this.user.apiEndpoint()+"/ban",method:"POST",errorHandler:this.onerror.bind(this)}).then((function(t){return app.store.pushPayload(t).forEach(n.done.bind(n))})).then(this.hide.bind(this)).catch((function(){})).then(this.loaded.bind(this))},s.getOtherUsers=function(){var t=this,n={};"only"===this.banOption()&&(n.ip=this.address||this.post.ipAddress()),app.request({params:n,url:app.forum.attribute("apiUrl")+"/fof/ban-ips/check-users/"+this.user.id(),method:"GET",errorHandler:this.onerror.bind(this)}).then((function(n){t.otherUsers[t.banOption()]=n.data.map((function(t){return app.store.pushObject(t)})).filter((function(t){return 0===t.bannedIPs().length})),t.loading=!1})).catch((function(){})).then(this.loaded.bind(this))},s.done=function(t){var n={type:"banned_ips",id:t.id()};this.post&&(this.post.data.relationships.banned_ip={data:n}),this.user.data.relationships.banned_ips||(this.user.data.relationships.banned_ips={data:[]}),this.user.data.relationships.banned_ips.data.push(n),this.user.data.attributes.isBanned=!0,app.store.pushObject(this.user.data)},n}(y());flarum.core.compat["common/helpers/username"];var U=function(t){function n(){return t.apply(this,arguments)||this}b(n,t);var s=n.prototype;return s.title=function(){return app.translator.trans("fof-ban-ips.lib.modal.unban_title")},s.content=function(){var t=this,n=this.otherUsers[this.banOption()],s=n&&n.map((function(t){return t&&t.displayName()||app.translator.trans("core.lib.username.deleted_text")}));return this.bannedIPs?m("div",{className:"Modal-body"},v().component({dismissible:!1,type:"success"},app.translator.trans("fof-ban-ips.lib.modal.unbanned_ips",{ips:B()(this.bannedIPs)}))):m("div",{className:"Modal-body"},m("p",null,app.translator.trans("fof-ban-ips.lib.modal.unban_ip_confirmation")),m("div",{className:"Form-group"},this.banOptions.map((function(n){return m("div",null,m("input",{type:"radio",name:"ban-option",id:"ban-option-"+n,checked:t.banOption()===n,onclick:t.banOption.bind(t,n)})," ",m("label",{htmlFor:"ban-option-"+n},app.translator.trans("fof-ban-ips.lib.modal.unban_options_"+n+"_ip",{user:t.user,ip:t.address||t.post&&t.post.ipAddress()})))}))),n?n.length?v().component({dismissible:!1},app.translator.trans("fof-ban-ips.lib.modal.unban_ip_users",{users:B()(s),count:s.length})):v().component({dismissible:!1,type:"success"},app.translator.trans("fof-ban-ips.lib.modal.unban_ip_no_users")):"",n&&m("br",null),m("div",{className:"Form-group"},m(h(),{className:"Button Button--primary",type:"submit",loading:this.loading},s?app.translator.trans("fof-ban-ips.lib.modal.unban_button"):app.translator.trans("fof-ban-ips.lib.modal.check_button"))))},s.onsubmit=function(t){if(t.preventDefault(),this.loading=!0,void 0===this.otherUsers[this.banOption()])return this.getOtherUsers();var n={};if("only"===this.banOption()){n.address=this.address||this.post.ipAddress();var s=this.post?this.post.bannedIP():app.store.getBy("banned_ips","address",this.address);s.delete().then(this.done.bind(this,s)).catch(this.onerror.bind(this)).then(this.hide.bind(this))}else"all"===this.banOption()&&app.request({body:{data:{attributes:n}},url:""+app.forum.attribute("apiUrl")+this.user.apiEndpoint()+"/unban",method:"POST",errorHandler:this.onerror.bind(this)}).then(this.done.bind(this)).catch(this.onerror.bind(this)).then(this.hide.bind(this))},s.getOtherUsers=function(){var t=this,n={};"only"===this.banOption()&&(n.ip=this.address||this.post.ipAddress(),n.skipValidation=!0);var s=app.forum.attribute("apiUrl")+"/fof/ban-ips/check-users";this.user&&(s+="/"+this.user.id()),app.request({params:n,url:s,method:"GET",errorHandler:this.onerror.bind(this)}).then((function(n){var s=app.store.pushPayload(n);t.otherUsers[t.banOption()]=s.filter((function(t){return 1===t.bannedIPs().length})),t.loading=!1,m.redraw()})).catch((function(){})).then(this.loaded.bind(this))},s.done=function(t){this.loading=!1,this.post&&delete this.post.data.relationships.banned_ip,!this.user||this.user.data.relationships||t?this.user&&t instanceof app.store.models.banned_ips&&(this.user.data.relationships.banned_ips={data:this.user.data.relationships.banned_ips.data.filter((function(n){return n.id!==t.id()}))},this.user.data.attributes.isBanned=0!==this.user.data.relationships.banned_ips.data.length):(this.user.data.relationships.banned_ips.data=[],this.user.data.attributes.isBanned=!1),t&&Array.isArray(t.data)&&(this.bannedIPs=t.data.map((function(t){return t.attributes.address})),this.loading=!1,m.redraw())},s.hide=function(){t.prototype.hide.call(this),this.attrs.redraw||location.reload()},n}(I);const A=flarum.core.compat["common/utils/mixin"];var N=function(t){function n(){return t.apply(this,arguments)||this}return b(n,t),n.prototype.apiEndpoint=function(){return"/fof/ban-ips"+(this.exists?"/"+this.id():"")},n}(t.n(A)()(o(),{creator:o().hasOne("creator"),user:o().hasOne("user"),address:o().attribute("address"),reason:o().attribute("reason"),createdAt:o().attribute("createdAt",o().transformDate),deletedAt:o().attribute("deletedAt",o().transformDate)}));const M=flarum.core.compat["common/models/User"];var k=t.n(M);const x=flarum.core.compat["common/components/Badge"];var j=t.n(x);const S={"fof/ban-ips/components/BanIPModal":I,"fof/ban-ips/components/UnbanIPModal":U,"fof/ban-ips/models/BannedIP":N},F=flarum.core;a().initializers.add("fof/ban-ips",(function(){a().store.models.posts.prototype.canBanIP=o().attribute("canBanIP"),a().store.models.posts.prototype.ipAddress=o().attribute("ipAddress"),a().store.models.posts.prototype.bannedIP=o().hasOne("banned_ip"),a().store.models.users.prototype.canBanIP=o().attribute("canBanIP"),a().store.models.users.prototype.isBanned=o().attribute("isBanned"),a().store.models.users.prototype.bannedIPs=o().hasMany("banned_ips"),a().store.models.banned_ips=N,(0,r.extend)(p(),"userControls",(function(t,n){if(n&&n.user()){var s=n.user().isBanned(),a=s?"un":"";n.canBanIP()&&!n.isHidden()&&n.user()!==app.session.user&&"comment"===n.contentType()&&t.add(a+"ban",h().component({icon:"fas fa-gavel",onclick:function(){return app.modal.show(s?U:I,{post:n})}},app.translator.trans("fof-ban-ips.forum."+a+"ban_ip_button")))}})),(0,r.extend)(u(),"moderationControls",(function(t,n){if(n.canBanIP()&&n!==app.session.user){var s=n.isBanned(),a=s?"un":"";t.add(a+"ban",h().component({icon:"fas fa-gavel",onclick:function(){return app.modal.show(s?U:I,{user:n})}},app.translator.trans("fof-ban-ips.forum.user_controls."+a+"ban_button")))}})),(0,r.extend)(k().prototype,"badges",(function(t){this.isBanned()&&t.add("banned",j().component({icon:"fas fa-gavel",type:"banned",label:app.translator.trans("fof-ban-ips.forum.user_badge.banned_tooltip")}))}))})),Object.assign(F.compat,S)})(),module.exports=n})();
//# sourceMappingURL=forum.js.map