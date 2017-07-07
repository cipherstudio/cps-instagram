window.Vue = require('vue');

Vue.component('instagram', require('./components/Instagram.vue'));

const app = new Vue({
    el: '.app-container'
});