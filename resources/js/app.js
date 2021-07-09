/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./bootstrap');
window.Vue = require('vue');
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import CKEditor from 'ckeditor4-vue';
// Make BootstrapVue available throughout your project
Vue.use(BootstrapVue)
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin)
Vue.use( CKEditor );
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('clone-component', require('./components/CloneComponent.vue').default);
Vue.component('rawDisplayer', require('./components/raw-displayer.vue').default);
Vue.component('handle-component', require('./components/handleComponent.vue').default);
Vue.component('handle-component-get', require('./components/handleComponentGet.vue').default);
Vue.component('xmp-component', require('./components/xmpComponent.vue').default);
Vue.component('calendar-component', require('./components/CalendarComponent.vue').default);
Vue.component('profile-component', require('./components/Profile.vue').default);
Vue.component('profile-editor-component', require('./components/profile-editorComponent.vue').default);
Vue.component('gallery', require('./components/Gallery.vue').default);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.prototype.$eventBus = new Vue(); // Global event bus
Vue.prototype.$bus = new Vue();

const app = new Vue({
    el: '#app',
});
