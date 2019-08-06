require('./bootstrap');
import moment from 'moment'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faEdit } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

window.Vue = require('vue');

library.add(faEdit);
Vue.component('font-awesome-icon', FontAwesomeIcon);

Vue.component('group-session', require('./components/GroupSession.vue').default);
Vue.component('teacher-schedule', require('./components/TeacherSchedule').default);
Vue.component('group-schedule', require('./components/GroupSchedule').default);
Vue.component('faculty-schedule', require('./components/FacultySchedule').default);
Vue.component('building-events', require('./components/BuildingEvents').default);


import Buefy from 'buefy'
import 'buefy/dist/buefy.css'
Vue.use(Buefy);

Vue.filter('formatDateTime', function(value) {
   if (value) {
       return moment(String(value), 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY HH:mm');
   }
});

Vue.filter('formatOnlyDate', function(value) {
    if (value) {
        return moment(String(value), 'YYYY-MM-DD').format('DD.MM.YYYY');
    }
});

Vue.filter('emptyIf2020Date', function(value) {
    if (value) {
        return (value === '01.01.2020 00:00') ? '' : value;
    }
});

const app = new Vue({
    el: '#app',
});


