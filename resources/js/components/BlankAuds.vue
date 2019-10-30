<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Занятия без аудиторий (5-11)
            </div>
            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr style="padding-bottom: 1em;">
                        <td>Дата</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="facultyId" v-model="calendarId" >
                                <option v-for="calendar in calendars" :value="calendar.id">{{reformatDate(calendar.date)}}</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button :disabled="loading" @click="putAuditoriums();" style="font-size: 2em;" class="button is-primary">Показать</button>

                <div v-if="loading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
                    Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </div>
            </div>
        </div>

        <table v-if="this.blankAudsLessons.length !== 0" class="table td-center is-bordered" style="margin-top: 1em;">
            <tr v-for="lesson in this.blankAudsLessons">
                <td>{{lesson.ringsTime.substr(0,5)}}</td>
                <td>{{lesson.studentGroupsName}}</td>
                <td style="text-align: left;">{{lesson.disciplinesName}}</td>
                <td>{{lesson.teachersFio}}</td>
                <td>{{lesson.auditoriumsName}}</td>
            </tr>
        </table>

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
        name: "BlankAuds",
        props: [
            'calendars',
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                calendarList: Object.values(this.calendars),
                calendarId: -1,
                showOKWindow: false,
                loading: false,
                blankAudsLessons: [],
            }
        },
        methods: {
            putAuditoriums() {
                let selectedDate = this.calendarList.filter(c => c.id === this.calendarId)[0].date;
                this.loading = true;
                axios
                    .get('/getBlankAuds?date=' + selectedDate + '&internal=1')
                    .then(response => {
                        this.blankAudsLessons = response.data;
                        this.loading = false;
                    });
            },
            reformatDate(date) {
                return date.substr(8,2) + '.' + date.substr(5,2) + '.' + date.substr(0,4);
            },

        },
        mounted() {
            let today = moment().format('YYYY-MM-DD');
            if (this.calendarList.length !== 0) {
                let c = this.calendarList.filter(c => c.date === today);

                if (c.length !== 0) {
                    this.calendarId = c[0].id;
                } else {
                    this.calendarId = this.calendarList[0].id;
                }
            }
        }
    }
</script>

<style scoped>

</style>
