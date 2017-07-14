window.Vue = require('vue');

const instagram = Vue.component('instagram', require('./components/Instagram.vue'));

const vm = new Vue({
    el: '.app-container',
    components: {
        instagram
    }
});

window.vm = vm;