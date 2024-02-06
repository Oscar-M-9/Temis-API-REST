"use strict";

var _vue = _interopRequireDefault(require("vue"));

var _vueRouter = _interopRequireDefault(require("vue-router"));

var _vuetify = _interopRequireDefault(require("vuetify"));

var _InicioComponent = _interopRequireDefault(require("./components/InicioComponent.vue"));

var _PagesComponent = _interopRequireDefault(require("./components/paginas_internas/page/PagesComponent.vue"));

var _UbicanosComponent = _interopRequireDefault(require("./components/UbicanosComponent.vue"));

var _BlogsComponent = _interopRequireDefault(require("./components/BlogsComponent.vue"));

var _Erro404Component = _interopRequireDefault(require("./components/Erro404Component.vue"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

require('./bootstrap');

window.Vue = require('vue')["default"];

_vue["default"].use(_vuetify["default"]);

_vue["default"].component('nav-component', require('./components/NavComponent.vue')["default"]);

_vue["default"].component('padre-component', require('./components/BienvenidaComponent.vue')["default"]);

_vue["default"].component('section1-component', require('./components/Section1Component.vue')["default"]);

_vue["default"].component('section3-component', require('./components/Section3Component.vue')["default"]);

_vue["default"].component('slider-component', require('./components/SliderComponent.vue')["default"]);

_vue["default"].component('footer-component', require('./components/FooterComponent.vue')["default"]);

_vue["default"].component('flyer-component', require('./components/FlyerComponent.vue')["default"]);

var _URL_BASE_ = window.location.protocol + '//' + window.location.host;

_vue["default"].use(_vueRouter["default"]);

_vue["default"].prototype.$_URL_BASE_ = _URL_BASE_;
var routes = [{
  path: '/',
  component: _InicioComponent["default"]
}, {
  path: '/page/*',
  component: _PagesComponent["default"]
}, {
  path: '/ubicanos',
  component: _UbicanosComponent["default"]
}, {
  path: '/blogs',
  component: _BlogsComponent["default"]
}, {
  path: '*',
  component: _Erro404Component["default"]
}]; // const router = new VueRouter({routes,mode: 'history',history: true});

var router = new _vueRouter["default"]({
  routes: routes
});
var app = new _vue["default"]({
  el: '#app',
  router: router,
  vuetify: new _vuetify["default"](),
  methods: {
    navegar: function navegar(ruta) {
      alert('HOLA MUNDA' + ruta);
    }
  }
});