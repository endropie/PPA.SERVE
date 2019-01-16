
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap'); 
window.Vue = require('vue');

import Vuex from 'vuex'
Vue.use(Vuex)

import Auth from './modules/auth'
Vue.use(Auth)

import GlobalMix from './modules/mix-global'
Vue.mixin(GlobalMix)

import Vuetify from 'vuetify'
Vue.use(Vuetify)

import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import locale from 'element-ui/lib/locale/lang/en';
Vue.use(ElementUI, { locale })

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);
const components = require.context('./components/', true, /\.(js|vue)$/i);
components.keys().map(key => {
  const name = key.match(/\w+/)[0];
  return Vue.component(name, components(key).default)
});

const layouts = require.context('./views/layouts/', true, /\.(js|vue)$/i);
layouts.keys().map(key => {
  const name = key.match(/\w+/)[0];
  return Vue.component(name, layouts(key).default)
});
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import router from './modules/routers';

const app = new Vue({
    el: '#app',
    router,
});
