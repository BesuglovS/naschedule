<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Переходы преподавателей между корпусами
            </div>
            <div class="container" style="margin-top: 2em; text-align: center;">
                <select style="margin-right: 1em; font-size: 1.5em; width: 220px;" v-model="calendarId" >
                    <option v-for="calendar in calendars" :value="calendar.id">{{reformatDate(calendar.date)}}</option>
                </select>

                <button :disabled="loading" @click="loadTransfersInfo();" style="font-size: 1em; margin-top: 0.5em;" class="button is-primary">Загрузить информацию</button>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <div v-if="loading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
                    Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </div>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <table v-if="loading === false" style="margin-top: 2em;" class="table td-center is-bordered">
                    <tr>
                        <td colspan="2">Время</td>
                        <td colspan="2">Корпуса</td>
                        <td>ФИО преподавателя</td>
                        <td colspan="2">Занятия</td>
                    </tr>
                    <tr v-for="transfer in teacherTransfers">
                        <td style="vertical-align: middle;">{{transfer.timeFrom}}</td>
                        <td style="vertical-align: middle;">{{transfer.timeTo}}</td>
                        <td style="vertical-align: middle;">{{transfer.buildingFrom}}</td>
                        <td style="vertical-align: middle;">{{transfer.buildingTo}}</td>
                        <td style="vertical-align: middle;">{{transfer.teachersFio}}</td>
                        <td style="vertical-align: middle;">{{transfer.disciplineFrom}} ({{transfer.groupFrom}})</td>
                        <td style="vertical-align: middle;">{{transfer.disciplineTo}} ({{transfer.groupTo}})</td>
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
        name: "TeacherBuildingTransfers",
        props: [
            'calendars',
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                showOKWindow: false,
                loading: false,
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                calendarList: Object.values(this.calendars),
                calendarId: -1,
                teacherTransfers: [],
            }
        },
        methods: {
            loadTransfersInfo() {
                this.loading = true;
                axios
                    .get('/teacherBuildingDailyTransfers?calendarId=' + this.calendarId + '&internal=1')
                    .then(response => {
                        this.loading = false;
                        this.teacherTransfers = response.data;
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
        },
        mounted() {
            let today = moment();
            let todayString = today.format('YYYY-MM-DD');
            let nearestCalendar = {};
            let minDiff = 1000000000;
            if (this.calendarList.length !== 0) {
                let c = this.calendarList.filter(c => c.date === todayString);

                if (c.length !== 0) {
                    this.calendarId = c[0].id;
                } else {
                    for(let i = 0; i < this.calendarList.length; i++) {
                        let dateMoment = moment(this.calendarList[i].date, "YYYY-MM-DD");
                        let diff = today.diff(dateMoment);

                        if (diff < minDiff) {
                            minDiff = diff;
                            nearestCalendar = this.calendarList[i];
                        }
                    }

                    this.calendarId = nearestCalendar.id;
                }

                this.loadTransfersInfo();
            }
        },
    }
</script>

<style scoped>

</style>
