window.Vue = require('vue');

const instagram = Vue.component('instagram', require('./components/Instagram.vue'));

const vm = new Vue({
    el: '.app-container'
});

// console.log(instagram, 'instagram');
// console.log(app, 'app');


window.vm = vm;