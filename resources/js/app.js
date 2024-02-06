 require('./bootstrap');

 import Vue from 'vue';
 import VueRouter from 'vue-router';
 import Vuetify from 'vuetify';

 window.Vue = require('vue').default;
 Vue.use(Vuetify);

 Vue.component('nav-component', require('./components/NavComponent.vue').default);
 Vue.component('padre-component', require('./components/BienvenidaComponent.vue').default);
 Vue.component('section1-component', require('./components/Section1Component.vue').default);
 Vue.component('section3-component', require('./components/Section3Component.vue').default);
 Vue.component('slider-component', require('./components/SliderComponent.vue').default);
 Vue.component('footer-component', require('./components/FooterComponent.vue').default);
 Vue.component('flyer-component', require('./components/FlyerComponent.vue').default);
 var _URL_BASE_ = window.location.protocol + '//' + window.location.host;

 Vue.use(VueRouter)
 Vue.prototype.$_URL_BASE_ = _URL_BASE_;
 import Slider from './components/InicioComponent.vue';
 // paginas internas
 import Pages from './components/paginas_internas/page/PagesComponent.vue';
 import ubicanos from './components/UbicanosComponent.vue';
 import blogs from './components/BlogsComponent.vue';
 import error404 from './components/Erro404Component.vue';
 const routes = [{
             path: '/',
             component: Slider
         },
         {
             path: '/page/*',
             component: Pages
         },
         {
             path: '/ubicanos',
             component: ubicanos
         },
         {
            path: '/blogs',
            component: blogs
        },
         {
             path: '*',
             component: error404
         },
     ]
     // const router = new VueRouter({routes,mode: 'history',history: true});
 const router = new VueRouter({ routes });
 const app = new Vue({
     el: '#app',
     router: router,
     vuetify: new Vuetify(),
     methods: {
         navegar: function(ruta) {
             alert('HOLA MUNDA' + ruta);
         },
     }
 });
