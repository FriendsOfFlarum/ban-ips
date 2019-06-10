module.exports =
/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./forum.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./forum.js":
/*!******************!*\
  !*** ./forum.js ***!
  \******************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _src_forum__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./src/forum */ "./src/forum/index.js");
/* empty/unused harmony star reexport */

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _inheritsLoose; });
function _inheritsLoose(subClass, superClass) {
  subClass.prototype = Object.create(superClass.prototype);
  subClass.prototype.constructor = subClass;
  subClass.__proto__ = superClass;
}

/***/ }),

/***/ "./src/common/models/BannedIP.js":
/*!***************************************!*\
  !*** ./src/common/models/BannedIP.js ***!
  \***************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return BannedIP; });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_Model__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/Model */ "flarum/Model");
/* harmony import */ var flarum_Model__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_Model__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_utils_mixin__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/utils/mixin */ "flarum/utils/mixin");
/* harmony import */ var flarum_utils_mixin__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_utils_mixin__WEBPACK_IMPORTED_MODULE_2__);




var BannedIP =
/*#__PURE__*/
function (_mixin) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(BannedIP, _mixin);

  function BannedIP() {
    return _mixin.apply(this, arguments) || this;
  }

  var _proto = BannedIP.prototype;

  _proto.apiEndpoint = function apiEndpoint() {
    return "/fof/ban-ips" + (this.exists ? "/" + this.id() : '');
  };

  return BannedIP;
}(flarum_utils_mixin__WEBPACK_IMPORTED_MODULE_2___default()(flarum_Model__WEBPACK_IMPORTED_MODULE_1___default.a, {
  creator: flarum_Model__WEBPACK_IMPORTED_MODULE_1___default.a.hasOne('creator'),
  user: flarum_Model__WEBPACK_IMPORTED_MODULE_1___default.a.hasOne('user'),
  address: flarum_Model__WEBPACK_IMPORTED_MODULE_1___default.a.attribute('address'),
  reason: flarum_Model__WEBPACK_IMPORTED_MODULE_1___default.a.attribute('reason'),
  createdAt: flarum_Model__WEBPACK_IMPORTED_MODULE_1___default.a.attribute('createdAt', flarum_Model__WEBPACK_IMPORTED_MODULE_1___default.a.transformDate),
  deletedAt: flarum_Model__WEBPACK_IMPORTED_MODULE_1___default.a.attribute('deletedAt', flarum_Model__WEBPACK_IMPORTED_MODULE_1___default.a.transformDate)
}));



/***/ }),

/***/ "./src/forum/addBanIPControl.js":
/*!**************************************!*\
  !*** ./src/forum/addBanIPControl.js ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_extend__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/extend */ "flarum/extend");
/* harmony import */ var flarum_extend__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_extend__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_utils_PostControls__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/utils/PostControls */ "flarum/utils/PostControls");
/* harmony import */ var flarum_utils_PostControls__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_utils_PostControls__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_utils_UserControls__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/utils/UserControls */ "flarum/utils/UserControls");
/* harmony import */ var flarum_utils_UserControls__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_utils_UserControls__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/components/Button */ "flarum/components/Button");
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Button__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _components_BanIPModal__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/BanIPModal */ "./src/forum/components/BanIPModal.js");
/* harmony import */ var _components_UnbanIPModal__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/UnbanIPModal */ "./src/forum/components/UnbanIPModal.js");






/* harmony default export */ __webpack_exports__["default"] = (function () {
  Object(flarum_extend__WEBPACK_IMPORTED_MODULE_0__["extend"])(flarum_utils_PostControls__WEBPACK_IMPORTED_MODULE_1___default.a, 'userControls', function (items, post) {
    var isBanned = post.user().isBanned();
    var prefix = isBanned ? 'un' : ''; // Removes ability to ban thyself and also does permission check.

    if (!post.canBanIP() || post.isHidden() || post.user() === app.session.user || post.contentType() !== 'comment') return;
    items.add(prefix + "ban", flarum_components_Button__WEBPACK_IMPORTED_MODULE_3___default.a.component({
      children: app.translator.trans("fof-ban-ips.forum." + prefix + "ban_ip_button"),
      className: 'Button Button--link',
      icon: 'fas fa-gavel',
      onclick: function onclick() {
        return app.modal.show(isBanned ? new _components_UnbanIPModal__WEBPACK_IMPORTED_MODULE_5__["default"]({
          post: post
        }) : new _components_BanIPModal__WEBPACK_IMPORTED_MODULE_4__["default"]({
          post: post
        }));
      }
    }));
  });
  Object(flarum_extend__WEBPACK_IMPORTED_MODULE_0__["extend"])(flarum_utils_UserControls__WEBPACK_IMPORTED_MODULE_2___default.a, 'moderationControls', function (items, user) {
    if (user.canBanIP() || user === app.session.user) return;
    var isBanned = user.isBanned();
    var prefix = isBanned ? 'un' : '';
    items.add(prefix + "ban", flarum_components_Button__WEBPACK_IMPORTED_MODULE_3___default.a.component({
      children: app.translator.trans("fof-ban-ips.forum.user_controls." + prefix + "ban_button"),
      icon: 'fas fa-gavel',
      onclick: function onclick() {
        return app.modal.show(isBanned ? new _components_UnbanIPModal__WEBPACK_IMPORTED_MODULE_5__["default"]({
          user: user
        }) : new _components_BanIPModal__WEBPACK_IMPORTED_MODULE_4__["default"]({
          user: user
        }));
      }
    }));
  });
});

/***/ }),

/***/ "./src/forum/addBannedBadge.js":
/*!*************************************!*\
  !*** ./src/forum/addBannedBadge.js ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_extend__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/extend */ "flarum/extend");
/* harmony import */ var flarum_extend__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_extend__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var flarum_models_User__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/models/User */ "flarum/models/User");
/* harmony import */ var flarum_models_User__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_models_User__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_components_Badge__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/components/Badge */ "flarum/components/Badge");
/* harmony import */ var flarum_components_Badge__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Badge__WEBPACK_IMPORTED_MODULE_2__);



/* harmony default export */ __webpack_exports__["default"] = (function () {
  Object(flarum_extend__WEBPACK_IMPORTED_MODULE_0__["extend"])(flarum_models_User__WEBPACK_IMPORTED_MODULE_1___default.a.prototype, 'badges', function (items) {
    if (this.isBanned()) {
      items.add('banned', flarum_components_Badge__WEBPACK_IMPORTED_MODULE_2___default.a.component({
        icon: 'fas fa-gavel',
        type: 'banned',
        label: app.translator.trans('fof-ban-ips.forum.user_badge.banned_tooltip')
      }));
    }
  });
});

/***/ }),

/***/ "./src/forum/components/BanIPModal.js":
/*!********************************************!*\
  !*** ./src/forum/components/BanIPModal.js ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return BanIPModal; });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_components_Modal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/components/Modal */ "flarum/components/Modal");
/* harmony import */ var flarum_components_Modal__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Modal__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/components/Button */ "flarum/components/Button");
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Button__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_components_Alert__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/components/Alert */ "flarum/components/Alert");
/* harmony import */ var flarum_components_Alert__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Alert__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/helpers/punctuateSeries */ "flarum/helpers/punctuateSeries");
/* harmony import */ var flarum_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var flarum_helpers_username__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! flarum/helpers/username */ "flarum/helpers/username");
/* harmony import */ var flarum_helpers_username__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(flarum_helpers_username__WEBPACK_IMPORTED_MODULE_5__);







var BanIPModal =
/*#__PURE__*/
function (_Modal) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(BanIPModal, _Modal);

  function BanIPModal() {
    return _Modal.apply(this, arguments) || this;
  }

  var _proto = BanIPModal.prototype;

  _proto.init = function init() {
    this.post = this.props.post;
    this.user = this.props.user || this.post.user();
    this.banOptions = !!this.post ? ['only', 'all'] : ['all'];
    this.banOption = m.prop(this.banOptions[0]);
    this.reason = m.prop('');
    this.otherUsers = {};
    this.loading = false;
  };

  _proto.className = function className() {
    return 'Modal--medium';
  };

  _proto.title = function title() {
    return app.translator.trans('fof-ban-ips.lib.modal.title');
  };

  _proto.content = function content() {
    var _this = this;

    var otherUsersBanned = this.otherUsers[this.banOption()];
    var usernames = otherUsersBanned && otherUsersBanned.map(function (u) {
      return u && u.displayName() || app.translator.trans('core.lib.username.deleted_text');
    });
    return m("div", {
      className: "Modal-body"
    }, m("p", null, app.translator.trans('fof-ban-ips.lib.modal.ban_ip_confirmation')), m("div", {
      className: "Form-group"
    }, this.banOptions.map(function (key) {
      return m("div", null, m("input", {
        type: "radio",
        name: "ban-option",
        id: "ban-option-" + key,
        checked: _this.banOption() === key,
        onclick: _this.banOption.bind(_this, key)
      }), "\xA0", m("label", {
        htmlFor: "ban-option-" + key
      }, app.translator.trans("fof-ban-ips.forum.modal.ban_options_" + key + "_ip", {
        user: _this.user,
        ip: _this.post && _this.post.ipAddress()
      })));
    })), m("div", {
      className: "Form-group"
    }, m("label", {
      className: "label"
    }, "Reason"), m("input", {
      type: "text",
      className: "FormControl",
      bidi: this.reason
    })), otherUsersBanned ? otherUsersBanned.length ? flarum_components_Alert__WEBPACK_IMPORTED_MODULE_3___default.a.component({
      children: app.translator.transChoice('fof-ban-ips.lib.modal.ban_ip_users', usernames.length, {
        users: flarum_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_4___default()(usernames)
      }),
      dismissible: false
    }) : flarum_components_Alert__WEBPACK_IMPORTED_MODULE_3___default.a.component({
      children: app.translator.trans('fof-ban-ips.forum.modal.ban_ip_no_users'),
      dismissible: false,
      type: 'success'
    }) : '', otherUsersBanned && m("br", null), m("div", {
      className: "Form-group"
    }, m(flarum_components_Button__WEBPACK_IMPORTED_MODULE_2___default.a, {
      className: "Button Button--primary",
      type: "submit",
      loading: this.loading
    }, usernames ? app.translator.trans('fof-ban-ips.lib.modal.submit_button') : app.translator.trans('fof-ban-ips.lib.modal.check_button'))));
  };

  _proto.onsubmit = function onsubmit(e) {
    var _this2 = this;

    e.preventDefault();
    this.loading = true;
    if (typeof this.otherUsers[this.banOption()] === 'undefined') return this.getOtherUsers();
    var attrs = {
      reason: this.reason(),
      userId: this.user.id()
    };

    if (this.banOption() === 'only') {
      attrs.address = this.post.ipAddress();
      app.store.createRecord('banned_ips').save(attrs).then(this.done.bind(this)).then(this.hide.bind(this), this.onerror.bind(this), this.loaded.bind(this));
    } else if (this.banOption() === 'all') {
      app.request({
        data: {
          data: {
            attributes: attrs
          }
        },
        url: "" + app.forum.attribute('apiUrl') + this.user.apiEndpoint() + "/ban",
        method: 'POST',
        errorHandler: this.onerror.bind(this)
      }).then(function (res) {
        return app.store.pushPayload(res).forEach(_this2.done.bind(_this2));
      }).then(this.hide.bind(this))["catch"](function () {}).then(this.loaded.bind(this));
    }
  };

  _proto.getOtherUsers = function getOtherUsers() {
    var _this3 = this;

    var data = {};
    if (this.banOption() === 'only') data.ip = this.post.ipAddress();
    app.request({
      data: data,
      url: app.forum.attribute('apiUrl') + "/fof/ban-ips/check-users/" + this.user.id(),
      method: 'GET',
      errorHandler: this.onerror.bind(this)
    }).then(function (res) {
      _this3.otherUsers[_this3.banOption()] = res.data.map(function (e) {
        return app.store.pushObject(e);
      }).filter(function (e) {
        return e.bannedIPs().length === 0;
      });
      _this3.loading = false;
    })["catch"](function () {}).then(this.loaded.bind(this));
  };

  _proto.done = function done(bannedIP) {
    var obj = {
      type: 'banned_ips',
      id: bannedIP.id()
    };

    if (this.post) {
      this.post.data.relationships.banned_ip = {
        data: obj
      };
    }

    this.user.data.relationships.banned_ips.data.push(obj);
    this.user.data.attributes.isBanned = true;
  };

  return BanIPModal;
}(flarum_components_Modal__WEBPACK_IMPORTED_MODULE_1___default.a);



/***/ }),

/***/ "./src/forum/components/UnbanIPModal.js":
/*!**********************************************!*\
  !*** ./src/forum/components/UnbanIPModal.js ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return UnbanIPModal; });
/* harmony import */ var _babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/esm/inheritsLoose */ "./node_modules/@babel/runtime/helpers/esm/inheritsLoose.js");
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! flarum/components/Button */ "flarum/components/Button");
/* harmony import */ var flarum_components_Button__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Button__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var flarum_components_Alert__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! flarum/components/Alert */ "flarum/components/Alert");
/* harmony import */ var flarum_components_Alert__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(flarum_components_Alert__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var flarum_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! flarum/helpers/punctuateSeries */ "flarum/helpers/punctuateSeries");
/* harmony import */ var flarum_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(flarum_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var flarum_helpers_username__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! flarum/helpers/username */ "flarum/helpers/username");
/* harmony import */ var flarum_helpers_username__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(flarum_helpers_username__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _BanIPModal__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./BanIPModal */ "./src/forum/components/BanIPModal.js");







var UnbanIPModal =
/*#__PURE__*/
function (_BanIPModal) {
  Object(_babel_runtime_helpers_esm_inheritsLoose__WEBPACK_IMPORTED_MODULE_0__["default"])(UnbanIPModal, _BanIPModal);

  function UnbanIPModal() {
    return _BanIPModal.apply(this, arguments) || this;
  }

  var _proto = UnbanIPModal.prototype;

  _proto.title = function title() {
    return app.translator.trans('fof-ban-ips.lib.modal.unban_title');
  };

  _proto.content = function content() {
    var _this = this;

    var otherUsers = this.otherUsers[this.banOption()];
    var usernames = otherUsers && otherUsers.map(function (u) {
      return u && u.displayName() || app.translator.trans('core.lib.username.deleted_text');
    });
    return m("div", {
      className: "Modal-body"
    }, m("p", null, app.translator.trans('fof-ban-ips.lib.modal.unban_ip_confirmation')), m("div", {
      className: "Form-group"
    }, this.banOptions.map(function (key) {
      return m("div", null, m("input", {
        type: "radio",
        name: "ban-option",
        id: "ban-option-" + key,
        checked: _this.banOption() === key,
        onclick: _this.banOption.bind(_this, key)
      }), "\xA0", m("label", {
        htmlFor: "ban-option-" + key
      }, app.translator.trans("fof-ban-ips.forum.modal.unban_options_" + key + "_ip", {
        user: _this.user,
        ip: _this.post && _this.post.ipAddress()
      })));
    })), otherUsers ? otherUsers.length ? flarum_components_Alert__WEBPACK_IMPORTED_MODULE_2___default.a.component({
      children: app.translator.transChoice('fof-ban-ips.lib.modal.unban_ip_users', usernames.length, {
        users: flarum_helpers_punctuateSeries__WEBPACK_IMPORTED_MODULE_3___default()(usernames)
      }),
      dismissible: false
    }) : flarum_components_Alert__WEBPACK_IMPORTED_MODULE_2___default.a.component({
      children: app.translator.trans('fof-ban-ips.forum.modal.unban_ip_no_users'),
      dismissible: false,
      type: 'success'
    }) : '', otherUsers && m("br", null), m("div", {
      className: "Form-group"
    }, m(flarum_components_Button__WEBPACK_IMPORTED_MODULE_1___default.a, {
      className: "Button Button--primary",
      type: "submit",
      loading: this.loading
    }, usernames ? app.translator.trans('fof-ban-ips.lib.modal.submit_button') : app.translator.trans('fof-ban-ips.lib.modal.check_button'))));
  };

  _proto.onsubmit = function onsubmit(e) {
    e.preventDefault();
    this.loading = true;
    if (typeof this.otherUsers[this.banOption()] === 'undefined') return this.getOtherUsers();
    var attrs = {};

    if (this.banOption() === 'only') {
      attrs.address = this.post.ipAddress();
      var bannedIP = this.post.bannedIP();
      bannedIP["delete"]().then(this.done.bind(this, bannedIP)).then(this.hide.bind(this), this.onerror.bind(this), this.loaded.bind(this));
    } else if (this.banOption() === 'all') {
      app.request({
        data: {
          data: {
            attributes: attrs
          }
        },
        url: "" + app.forum.attribute('apiUrl') + this.user.apiEndpoint() + "/unban",
        method: 'POST',
        errorHandler: this.onerror.bind(this)
      }).then(this.done.bind(this)).then(this.hide.bind(this))["catch"](function () {}).then(this.loaded.bind(this));
    }
  };

  _proto.getOtherUsers = function getOtherUsers() {
    var _this2 = this;

    var data = {};
    if (this.banOption() === 'only') data.ip = this.post.ipAddress();
    app.request({
      data: data,
      url: app.forum.attribute('apiUrl') + "/fof/ban-ips/check-users/" + this.user.id(),
      method: 'GET',
      errorHandler: this.onerror.bind(this)
    }).then(function (res) {
      var data = app.store.pushPayload(res);
      _this2.otherUsers[_this2.banOption()] = data.filter(function (e) {
        return e.bannedIPs().length === 1;
      });
      _this2.loading = false;
      m.lazyRedraw();
    })["catch"](function () {}).then(this.loaded.bind(this));
  };

  _proto.done = function done(bannedIP) {
    if (bannedIP) {
      delete this.post.data.relationships.banned_ip;
      this.user.data.relationships.banned_ips.data = this.user.data.relationships.banned_ips.data.filter(function (e) {
        return e.id !== bannedIP.id();
      });
      this.user.data.attributes.isBanned = false;
    } else {
      this.user.data.relationships.banned_ips.data = [];
      this.user.data.attributes.isBanned = false;
      if (this.post) delete this.post.data.relationships.banned_ip;
    }
  };

  return UnbanIPModal;
}(_BanIPModal__WEBPACK_IMPORTED_MODULE_5__["default"]);



/***/ }),

/***/ "./src/forum/index.js":
/*!****************************!*\
  !*** ./src/forum/index.js ***!
  \****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var flarum_Model__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! flarum/Model */ "flarum/Model");
/* harmony import */ var flarum_Model__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(flarum_Model__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _addBanIPControl__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./addBanIPControl */ "./src/forum/addBanIPControl.js");
/* harmony import */ var _common_models_BannedIP__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../common/models/BannedIP */ "./src/common/models/BannedIP.js");
/* harmony import */ var _addBannedBadge__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./addBannedBadge */ "./src/forum/addBannedBadge.js");




app.initializers.add('fof/ban-ips', function () {
  app.store.models.posts.prototype.canBanIP = flarum_Model__WEBPACK_IMPORTED_MODULE_0___default.a.attribute('canBanIP');
  app.store.models.posts.prototype.ipAddress = flarum_Model__WEBPACK_IMPORTED_MODULE_0___default.a.attribute('ipAddress');
  app.store.models.posts.prototype.bannedIP = flarum_Model__WEBPACK_IMPORTED_MODULE_0___default.a.hasOne('banned_ip');
  app.store.models.users.prototype.canBanIP = flarum_Model__WEBPACK_IMPORTED_MODULE_0___default.a.attribute('canBanIP');
  app.store.models.users.prototype.isBanned = flarum_Model__WEBPACK_IMPORTED_MODULE_0___default.a.attribute('isBanned');
  app.store.models.users.prototype.bannedIPs = flarum_Model__WEBPACK_IMPORTED_MODULE_0___default.a.hasMany('banned_ips');
  app.store.models.banned_ips = _common_models_BannedIP__WEBPACK_IMPORTED_MODULE_2__["default"];
  Object(_addBanIPControl__WEBPACK_IMPORTED_MODULE_1__["default"])();
  Object(_addBannedBadge__WEBPACK_IMPORTED_MODULE_3__["default"])();
});

/***/ }),

/***/ "flarum/Model":
/*!**********************************************!*\
  !*** external "flarum.core.compat['Model']" ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['Model'];

/***/ }),

/***/ "flarum/components/Alert":
/*!*********************************************************!*\
  !*** external "flarum.core.compat['components/Alert']" ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Alert'];

/***/ }),

/***/ "flarum/components/Badge":
/*!*********************************************************!*\
  !*** external "flarum.core.compat['components/Badge']" ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Badge'];

/***/ }),

/***/ "flarum/components/Button":
/*!**********************************************************!*\
  !*** external "flarum.core.compat['components/Button']" ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Button'];

/***/ }),

/***/ "flarum/components/Modal":
/*!*********************************************************!*\
  !*** external "flarum.core.compat['components/Modal']" ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['components/Modal'];

/***/ }),

/***/ "flarum/extend":
/*!***********************************************!*\
  !*** external "flarum.core.compat['extend']" ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['extend'];

/***/ }),

/***/ "flarum/helpers/punctuateSeries":
/*!****************************************************************!*\
  !*** external "flarum.core.compat['helpers/punctuateSeries']" ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['helpers/punctuateSeries'];

/***/ }),

/***/ "flarum/helpers/username":
/*!*********************************************************!*\
  !*** external "flarum.core.compat['helpers/username']" ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['helpers/username'];

/***/ }),

/***/ "flarum/models/User":
/*!****************************************************!*\
  !*** external "flarum.core.compat['models/User']" ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['models/User'];

/***/ }),

/***/ "flarum/utils/PostControls":
/*!***********************************************************!*\
  !*** external "flarum.core.compat['utils/PostControls']" ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['utils/PostControls'];

/***/ }),

/***/ "flarum/utils/UserControls":
/*!***********************************************************!*\
  !*** external "flarum.core.compat['utils/UserControls']" ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['utils/UserControls'];

/***/ }),

/***/ "flarum/utils/mixin":
/*!****************************************************!*\
  !*** external "flarum.core.compat['utils/mixin']" ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = flarum.core.compat['utils/mixin'];

/***/ })

/******/ });
//# sourceMappingURL=forum.js.map