<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Изменения расписания
            </div>
            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr style="padding-bottom: 1em;">
                        <td>Дата</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="facultyId" v-model="date" >
                                <option v-for="date in dates" :value="date">{{reformatDate(date)}}</option>
                            </select>
                        </td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select v-model="selectedChunk" @change="chunkChange();">
                                <option v-for="chunk in dateInfo.parts" :value="chunk">{{chunk.times}}</option>
                            </select>
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
                        <td>Дата изменения</td>
                        <td>Старый урок</td>
                        <td>Новый урок</td>
                        <td>Скрытый комментарий</td>
                    </tr>

                    <tr v-for="event in this.lessonLogEvents">
                        <td style="vertical-align: middle;">{{reformatDateTime(event.lessonLogEventDateTime)}}</td>
                        <td>
                            <template v-if="event.lessonOldId !== null">
                                <p>{{reformatDate(event.lessonOldCalendarDate)}} @ {{event.lessonOldRingTime.substr(0,5)}} = {{event.lessonOldStudentGroupName}}</p>
                                <p>{{dow(event.lessonOldCalendarDate)}}</p>
                                <p>{{event.lessonOldDisciplineName}}</p>
                                <p>{{event.lessonOldTeacherFio}}</p>
                                <p>{{event.lessonOldAuditoriumName}}</p>
                            </template>
                        </td>
                        <td>
                            <template v-if="event.lessonNewId !== null">
                                <p>{{reformatDate(event.lessonNewCalendarDate)}} @ {{event.lessonNewRingTime.substr(0,5)}} = {{event.lessonNewStudentGroupName}}</p>
                                <p>{{dow(event.lessonNewCalendarDate)}}</p>
                                <p>{{event.lessonNewDisciplineName}}</p>
                                <p>{{event.lessonNewTeacherFio}}</p>
                                <p>{{event.lessonNewAuditoriumName}}</p>
                            </template>
                        </td>
                        <td>
                            {{event.lessonLogEventHiddenComment}}
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
        name: "lle",
        props: [
            'dates',
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                date: '',
                showOKWindow: false,
                loading: false,
                dateInfo: {"total-count": 0, parts: []},
                selectedChunk: {offset:0, times:""},
                lessonLogEvents: [],
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
            }
        },
        methods: {
            loadDateInfo() {
                this.loading = true;
                axios
                    .get('/lleDateInfo?date=' + this.date)
                    .then(response => {
                        this.loading = false;
                        this.dateInfo = response.data;
                        if (this.dateInfo.parts.length !== 0) {
                            this.selectedChunk = this.dateInfo.parts[0];
                            this.loadLLE();
                        }
                    });
            },
            loadLLE() {
                this.loading = true;
                axios
                    .get('/lleEvents?date=' + this.date + '&offset=' + this.selectedChunk.offset)
                    .then(response => {
                        this.loading = false;
                        this.lessonLogEvents = response.data.events;
                    });
            },
            chunkChange() {
                this.loadLLE();
            },
            reformatDate(date) {
                return date.substr(8,2) + '.' + date.substr(5,2) + '.' + date.substr(0,4);
            },
            reformatDateTime(date) {
                return moment(date, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY HH:mm:ss');
            },
            dow(date) {
                let m = moment(date, "YYYY-MM-DD");
                return this.dowRu[m.format('E')-1];
            },
        },
        mounted() {
            let today = moment().format('YYYY-MM-DD');
            if (this.dates.length !== 0) {
                let c = this.dates.filter(d => d === today);

                if (c.length !== 0) {
                    this.date = c[0];
                } else {
                    this.date = this.dates[0];
                }

                this.loadDateInfo();
            }
        }
    }
</script>

<style scoped>

</style>
