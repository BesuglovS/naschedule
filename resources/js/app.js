require('./bootstrap');
import moment from 'moment'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faEdit } from '@fortawesome/free-solid-svg-icons'
import { faPlusSquare } from '@fortawesome/free-solid-svg-icons'
import { faSpinner } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

window.Vue = require('vue');

library.add(faEdit);
library.add(faPlusSquare);
library.add(faSpinner);
Vue.component('font-awesome-icon', FontAwesomeIcon);

Vue.component('group-session', require('./components/GroupSession.vue').default);
Vue.component('teacher-schedule', require('./components/TeacherSchedule').default);
Vue.component('teacher-group-schedule', require('./components/TeacherGroupSchedule').default);
Vue.component('group-schedule', require('./components/GroupSchedule').default);
Vue.component('faculty-schedule', require('./components/FacultySchedule').default);
Vue.component('building-events', require('./components/BuildingEvents').default);
Vue.component('discipline-list', require('./components/DisciplineList').default);
Vue.component('discipline-hours', require('./components/DisciplineHours').default);
Vue.component('teacher-hours', require('./components/TeacherHours').default);
Vue.component('lesson-log-events', require('./components/LessonLogEvents').default);
Vue.component('week-schedule', require('./components/WeekSchedule').default);
Vue.component('teachers-collisions', require('./components/TeachersCollisions').default);
Vue.component('put-auds', require('./components/PutAuds').default);
Vue.component('blank-auds', require('./components/BlankAuds').default);
Vue.component('new-rings', require('./components/NewRings').default);
Vue.component('lle', require('./components/lle').default);
Vue.component('lleTeacher', require('./components/lleTeacher').default);
Vue.component('faculty-teachers', require('./components/FacultyTeachers').default);
Vue.component('fill-blank-auds', require('./components/FillBlankAuds').default);
Vue.component('teacher-got-ill', require('./components/TeacherGotIll').default);
Vue.component('teacher-building-transfers', require('./components/TeacherBuildingTransfers').default);


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


