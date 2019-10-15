<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Болезнь преподавателя
            </div>
            <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr>
                        <td style="vertical-align: middle;">ФИО</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td style="vertical-align: middle;">
                            <select style="margin-right: 1em; font-size: 1.5em; width: 520px;" name="facultyId" v-model="teacherId" >
                                <option v-for="teacher in teachersSorted" :value="teacher.id">{{teacher.fio}}</option>
                            </select>

                            <button :disabled="loading" @click="loadIllInfo();" style="font-size: 1em; margin-top: 0.5em;" class="button is-primary">Загрузить информацию</button>
                        </td>
                        <td style="padding-left:0.5em; padding-right:0.5em;vertical-align: middle;">Даты<br />(С / ПО)</td>
                        <td style="vertical-align: middle;">
                            <div style="margin-bottom:0.5em;">
                                <select style="margin-right: 1em; font-size: 1.5em; width: 220px;" v-model="calendarFromId" >
                                    <option v-for="calendar in calendars" :value="calendar.id">{{reformatDate(calendar.date)}}</option>
                                </select>
                            </div>

                            <div>
                                <select style="margin-right: 1em; font-size: 1.5em; width: 220px;" v-model="calendarToId" >
                                    <option v-for="calendar in calendars" :value="calendar.id">{{reformatDate(calendar.date)}}</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>

            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <div v-if="loading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
                    Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </div>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <table v-if="loading === false" style="margin-top: 2em;" class="table td-center is-bordered">
                    <tr>
                        <td><strong>Отменяемый урок</strong></td>
                        <td><strong>Варианты корректировки</strong></td>
                    </tr>

                    <tr v-for="lesson in illInfo">
                        <td>
                            {{reformatDate(lesson.calendarsDate)}}  - {{lesson.ringsTime.substr(0,5)}} <br />
                            {{lesson.disciplinesName}} ({{lesson.studentGroupsName}})
                        </td>
                        <td>
                            <div>
                                Нет уроков других преподавателей раньше в этот день
                                <template v-if="!lesson.earlierLessonsExists">
                                    <img :src="'./assets/img/green-checkmark.png'" style="height:25px;" />
                                    <button @click="deleteLesson(lesson);" style="font-size: 1em;" class="button btn-sm is-primary">Убрать урок</button>
                                </template>
                                <template v-if="lesson.earlierLessonsExists">
                                    <img :src="'./assets/img/red-cross.png'" style="height:25px;" />
                                </template>
                            </div>

                            <div>
                                Нет уроков других преподавателей позже в этот день
                                <template v-if="!lesson.latterLessonsExists">
                                    <img :src="'./assets/img/green-checkmark.png'" style="height:25px;" />
                                    <button @click="deleteLesson(lesson);" style="font-size: 1em;" class="button btn-sm is-primary">Убрать урок</button>
                                </template>
                                <template v-if="lesson.latterLessonsExists">
                                    <img :src="'./assets/img/red-cross.png'" style="height:25px;" />
                                </template>
                            </div>

                            <div>
                                <template v-if="lesson.possibleFill == undefined">
                                    <strong>
                                        Не удалось подобрать занятия для замены.
                                    </strong>
                                </template>
                                <template v-if="lesson.possibleFill !== undefined">
                                    <div v-for="fillLesson in lesson.possibleFill">
                                        <table style="width:100%;">
                                            <tr>
                                                <td style="border: none; vertical-align: middle;">
                                                    Можно заменить уроком <br />
                                                    <strong>{{fillLesson.disciplinesName}} ({{fillLesson.teacherFio}})</strong> <br />
                                                    {{reformatDate(fillLesson.lessonForExchangeDate)}} {{fillLesson.lessonForExchangeTime.substr(0,5)}}
                                                    <div v-if="fillLesson.earlierTargetLessonsExists && fillLesson.latterTargetLessonsExists">
                                                        <img :src="'./assets/img/green-checkmark.png'" style="height:25px;" />
                                                        Перенос в окно
                                                    </div>
                                                    <div v-if="!fillLesson.earlierSourceLessonsExists">
                                                        <img :src="'./assets/img/green-checkmark.png'" style="height:25px;" />
                                                        Перенос самого раннего урока преподавателя
                                                    </div>
                                                    <div v-if="!fillLesson.latterSourceLessonsExists">
                                                        <img :src="'./assets/img/green-checkmark.png'" style="height:25px;" />
                                                        Перенос самого позднего урока преподавателя
                                                    </div>

                                                    <div v-if="fillLesson.earlierSourceLessonsExists && fillLesson.latterSourceLessonsExists">
                                                        <img :src="'./assets/img/red-cross.png'" style="height:25px;" />
                                                        В дне из которого осуществляется перенос у преподавателя есть более ранние и более поздние занятия.
                                                    </div>
                                                </td>
                                                <td style="border: none; vertical-align: middle;">
                                                    <button @click="replaceWithLesson(lesson, fillLesson);" style="font-size: 1em;" class="button btn-sm is-primary">Заменить окно уроком</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        <modal v-if="showOKWindow">
            <template v-slot:body>
                <div style="width: 100%; text-align: center;">
                    <button style="width: 400px; font-size: 2em;" @click="showOKWindow = false;" class="button is-primary">
                        Операция завершена
                    </button>
                </div>
            </template>
        </modal>

    </div>
</template>

<script>
    import modal from './Modal';
    import moment from "moment";

    export default {
        name: "TeacherGotIll",
        props: [
            'teachers',
            'weekCount',
            'currentWeek',
            'semesterStarts',
            'calendars',
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                teacherId: '',
                weeksCount: this.weekCount,
                showOKWindow: false,
                loading: false,
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                momentSemesterStarts: moment(this.semesterStarts, 'DD.MM.YYYY').startOf('isoweek'),
                calendarList: Object.values(this.calendars),
                calendarFromId: -1,
                calendarToId: -1,
                illInfo: {},
            }
        },
        methods: {
            loadIllInfo() {
                this.loading = true;
                axios
                    .get('/loadIllInfo?teacherId=' + this.teacherId +
                        '&calendarFromId=' + this.calendarFromId +
                        '&calendarToId=' + this.calendarToId)
                    .then(response => {
                        this.loading = false;
                        this.illInfo = response.data.sort((a,b) => {
                            let aMoment = moment(a.calendarsDate + ' ' + a.ringsTime, 'YYYY-MM-DD HH:mm:ss');
                            let bMoment = moment(b.calendarsDate + ' ' + b.ringsTime, 'YYYY-MM-DD HH:mm:ss');

                            if (aMoment === bMoment) return 0;
                            return (aMoment < bMoment) ? -1 : 1;
                        });
                    });

            },
            deleteLesson(lesson) {
                this.loading = true;
                axios
                    .post('lessonsDestroyByIds?Ids=' + lesson.id)
                    .then(response => {
                        this.loadIllInfo();
                    });
            },
            reformatDate(date) {
                return date.substr(8,2) + '.' + date.substr(5,2) + '.' + date.substr(0,4);
            },
            reformatDateTime(date) {
                return moment(date, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY HH:mm:ss');
            },
            range(start, end) {
                return Array(end - start + 1).fill().map((_, idx) => start + idx)
            },
            replaceWithLesson(lesson, fillLesson) {
                axios
                    .post('/removeLessonAndReplaceWithAnother' +
                        '?lessonToRemoveId=' + lesson.id +
                        '&lessonToMoveId=' + fillLesson.lessonForExchangeId)
                    .then(response => {
                        this.loadIllInfo();
                    });
            },
        },
        mounted() {
            if (this.teachers.length !== 0) {
                this.teacherId = this.teachersSorted[0].id;
            }

            let today = moment();
            let todayString = today.format('YYYY-MM-DD');
            let nearestCalendar = {};
            let minDiff = 1000000000;
            if (this.calendarList.length !== 0) {
                let c = this.calendarList.filter(c => c.date === todayString);

                if (c.length !== 0) {
                    this.calendarFromId = c[0].id;
                    this.calendarToId = c[0].id;
                } else {
                    for(let i = 0; i < this.calendarList.length; i++) {
                        let dateMoment = moment(this.calendarList[i].date, "YYYY-MM-DD");
                        let diff = today.diff(dateMoment);

                        if (diff < minDiff) {
                            minDiff = diff;
                            nearestCalendar = this.calendarList[i];
                        }
                    }

                    this.calendarFromId = nearestCalendar.id;
                    this.calendarToId = nearestCalendar.id;
                }

                this.loadIllInfo();
            }
        },
        computed: {
            teachersSorted() {
                let result = [];
                for (var index in this.teachers) {
                    let teacher = this.teachers[index];
                    result.push(teacher);
                }

                result.sort((a,b) => {
                    if (a.fio === b.fio) return 0;
                    return a.fio < b.fio ? -1 : 1;
                });

                return result;
            },
        },
    }
</script>

<style scoped>

</style>
