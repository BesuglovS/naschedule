<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Поменять время занятий <img :src="'./assets/img/green-checkmark.png'" style="height:25px;" />
            </div>
            <div class="container" style="align-items: center; display: flex; justify-content: center; flex-direction: column; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr style="padding-bottom: 1em;">
                        <td>Начальная дата</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" v-model="fromCalendarId" >
                                <option v-for="calendar in calendars" :value="calendar.id">{{reformatDate(calendar.date)}}</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>

                    <tr style="padding-bottom: 1em;">
                        <td>Конечная дата</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;"  v-model="toCalendarId" >
                                <option v-for="calendar in calendars" :value="calendar.id">{{reformatDate(calendar.date)}}</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>

                    <tr>
                        <td colspan="3" style="text-align: center;">
                            <input style="transform: scale(2);" type="checkbox" v-model="newLessons">&nbsp;&nbsp;&nbsp;&nbsp;Создавать новые уроки
                        </td>
                    </tr>
                </table>

                <div style="display: flex; flex-direction: row; ">
                    <table style="margin-top: 1.5em;">
                        <tr>
                            <td colspan="2" style="text-align: center; padding-right: 1em;">
                                <button :disabled="loading" @click="setDefaultSet(1);" style="font-size: 1em; margin-bottom: 1em;" class="button is-primary">С обычных (40) на онлайн (30)</button>
                            </td>
                            <td colspan="2" style="text-align: center;">
                                <button :disabled="loading" @click="setDefaultSet(2);" style="font-size: 1em;" class="button is-primary">С онлайн (30) на обычные (40)</button>
                            </td>
                        </tr>
                        <tr v-for="ringIndex in 9">
                            <td style="width: 75px; text-align: center; vertical-align: middle;">
                                <input style="transform: scale(2);" type="checkbox" v-model="moveEnabled[ringIndex-1]">
                            </td>
                            <td style="padding-top: 0.5em; padding-bottom: 0.5em;">
                                <select style="font-size: 2em;" v-model="ringFromIds[ringIndex-1]">
                                    <option v-for="ring in rings" :value="ring.id">{{ring.time.substr(0,5)}}</option>
                                </select>
                            </td>
                            <td style="padding-top: 0.5em; padding-bottom: 0.5em; width:75px; text-align:center;">
                                =>
                            </td>
                            <td style="padding-top: 0.5em; padding-bottom: 0.5em;">
                                <select style="font-size: 2em;" v-model="ringToIds[ringIndex-1]">
                                    <option v-for="ring in rings" :value="ring.id">{{ring.time.substr(0,5)}}</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <div>
                        <table class="table td-center is-bordered" style="margin-left: 6em; margin-top: 3em;">
                            <tr v-for="query in queryProgress">
                                <td>{{query.date}}</td>
                                <td>
                                    <template v-if="query.loading">
                                        Загрузка <img :src="'./assets/img/loading.gif'" style="height:25px;" />
                                    </template>
                                    <template v-if="!query.loading">
                                        <img :src="'./assets/img/green-checkmark.png'" style="height:25px;" />  Готово
                                    </template>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button :disabled="loading" @click="setNewRings();" style="font-size: 2em;" class="button is-primary">Поменять время</button>

                <div v-if="loading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
                    Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </div>
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
        name: "NewRings",
        props: [
            'calendars',
            'rings',
            'ringidpairs1',
            'ringidpairs2',
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                calendarList: Object.values(this.calendars),
                fromCalendarId: -1,
                toCalendarId: -1,
                showOKWindow: false,
                loading: false,
                ringFromIds: [-1, -1, -1, -1, -1, -1, -1, -1, -1],
                ringToIds: [-1, -1, -1, -1, -1, -1, -1, -1, -1],
                moveEnabled: [true, true, true, true, true, true, true, true, true],
                queryProgress: [],
                queueIndex: -1,
                calendarIds: [],
                calendarsById: {},
                ringString: '',
                newLessons: false,
            }
        },
        methods: {
            setNewRings() {
                let ringStringArray = [];
                for(let i = 1; i <= 9; i++) {
                    if (this.moveEnabled[i-1]) {
                        ringStringArray.push(this.ringFromIds[i-1] + "*" + this.ringToIds[i-1]);
                    }
                }
                this.ringString = ringStringArray.join('|');

                let fromDate = moment(this.calendarList.filter(c => c.id === this.fromCalendarId)[0].date, 'YYYY-MM-DD');
                let toDate = moment(this.calendarList.filter(c => c.id === this.toCalendarId)[0].date, 'YYYY-MM-DD');

                this.calendarIds = [];
                this.calendarsById = {};
                var currDate = moment(fromDate).startOf('day');
                do {
                    if (currDate.diff(toDate) <= 0) {
                        let dateString = currDate.clone().format('YYYY-MM-DD');
                        let dateCalendars = this.calendarList.filter(c => c.date === dateString);
                        if (dateCalendars.length !== 0) {
                            this.calendarIds.push(dateCalendars[0].id);
                            this.calendarsById[dateCalendars[0].id] = dateCalendars[0];
                        }
                    }
                    currDate.add(1, 'days')
                } while(currDate.diff(toDate) <= 0);

                this.queueIndex = 0;
                this.queryProgress = [];
                this.executeNextQueue();

            },
            executeNextQueue() {
                let calendarId = this.calendarIds[this.queueIndex];
                this.loading = true;
                this.queryProgress.push(
                    {
                        date: this.reformatDate(this.calendarsById[calendarId].date),
                        loading: true
                    });
                axios
                    .post('/changeRings?fromCalendarId=' + calendarId +
                        '&toCalendarId=' + calendarId +
                        '&ringIds=' + this.ringString +
                        '&newLessons=' + this.newLessons)
                    .then(response => {
                        this.queryProgress[this.queueIndex].loading = false;
                        this.queueIndex++;
                        if (this.queueIndex < this.calendarIds.length) {
                            this.executeNextQueue();
                        } else {
                            this.loading = false;
                        }
                    });
            },
            reformatDate(date) {
                return date.substr(8,2) + '.' + date.substr(5,2) + '.' + date.substr(0,4);
            },
            setDefaultSet(setIndex) {
                switch(setIndex) {
                    case 1:
                        this.moveEnabled =[true, true, true, true, true, true, true, true, true];
                        let sourceIds1 = Object.keys(this.ringidpairs1);
                        for(let i = 0; i < sourceIds1.length; i++) {
                            this.ringFromIds[i] = sourceIds1[i];
                            this.ringToIds[i] = this.ringidpairs1[sourceIds1[i]];
                        }
                        break;
                    case 2:
                        this.moveEnabled =[true, true, true, true, true, true, true, true, true];
                        let sourceIds2 = Object.keys(this.ringidpairs2);
                        for(let i = 0; i < sourceIds2.length; i++) {
                            this.ringFromIds[i] = sourceIds2[i];
                            this.ringToIds[i] = this.ringidpairs2[sourceIds2[i]];
                        }
                        break;
                }
            },
        },
        mounted() {
            let today = moment().format('YYYY-MM-DD');
            if (this.calendarList.length !== 0) {
                let c = this.calendarList.filter(c => c.date === today);

                if (c.length !== 0) {
                    this.fromCalendarId = c[0].id;
                    this.toCalendarId = c[0].id;
                } else {
                    this.fromCalendarId = this.calendarList[0].id;
                    this.toCalendarId = this.calendarList[0].id;
                }
            }

            this.setDefaultSet(1);
        }
    }
</script>

<style scoped>

</style>
