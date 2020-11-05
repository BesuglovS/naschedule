<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Просмотр расписания на один день
            </div>
            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr>
                        <td>День</td>
                        <td>&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1em; width: 310px;" v-model="selectedCalendarId">
                                <option v-for="dt in dates" :selected="dt.date === selectedCalendarId" :value="dt.id">{{dt.date}}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr style="padding-bottom: 1em;">
                        <td>Группа</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1em; width: 310px;" v-model="studentGroupId">
                                <option v-for="gr in groups" :selected="gr.groupId === studentGroupId" :value="gr.groupId">{{gr.name}}</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button type="submit" @click="loadGroup();" style="font-size: 1em; margin-right: 1em;" class="button is-primary">Загрузить</button>

                <div v-if="loading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
                    Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </div>
            </div>
        </div>

        <div v-if="this.lessons.length !== 0" style="border: 1px solid black; border-radius: 10px; padding:1em;">
            <table  class="table td-center is-bordered" style="margin-top: 1em;">
                <template v-for="lesson in lessons">
                    <tr>
                        <td>{{lesson.time}}</td>
                        <td style="text-align: left !important;">{{lesson.disc_name}}</td>
                        <td>{{lesson.group_name}}</td>
                        <td>{{lesson.fio}}</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align: left !important;">
                            <span v-html="lesson.description" style="white-space: pre-wrap; word-wrap:break-word; ">{{lesson.description}}</span>
                            <span v-if="lesson.description == ''" style="font-size: 2em;">Описание отсутствует</span>
                        </td>
                    </tr>
                </template>
            </table>
        </div>

        <div v-if="this.lessons.length == 0" style="border: 1px solid black; border-radius: 10px; padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em;">
                Пусто
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
import modal from "./Modal";
import moment from "moment";

export default {
    name: "GroupTrelloDay",
    props: [
        'groups',
        'calendars',
    ],
    components: {
        'modal' : modal
    },
    data() {
        return {
            selectedCalendarId: null,
            studentGroupId: null,
            showOKWindow: false,
            loading: false,
            lessons: [],
            dates: []
        }
    },
    methods: {
        dowToggled(dow) {
            let index = this.selectedDows.indexOf(dow);
            if (index > -1) {
                this.selectedDows.splice(index, 1);
            } else {
                this.selectedDows.push(dow);
            }
        },
        loadGroup() {
            this.loading = true;
            axios
                .get('/rGroupDay?groupId=' + this.studentGroupId + '&calendarId=' + this.selectedCalendarId)
                .then(response => {
                        this.lessons = response.data;

                        Object.values(this.lessons).forEach(item => {

                            const URLMatcher = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-ZА-Яа-я0-9+&@#\/%=~_|$?!:,.]*\)|[-A-ZА-Яа-я0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-ZА-Яа-я0-9+&@#\/%=~_|$?!:,.]*\)|[A-ZА-Яа-я0-9+&@#\/%=~_|$])/igm

                            item.description = item.description.replace(URLMatcher, match => `<a target="_blank" href="${match}">${match}</a>`);
                        });

                        this.loading = false;
                    }
                );
        },
        loadTeacher() {

        },
    },
    mounted() {
        if (this.groups.length !== 0) {
            this.studentGroupId = this.groups[0].groupId;
        }

        for (let i = 0; i < this.calendars.length; i++) {
            let date = {};
            date.id = this.calendars[i].id;
            date.date = this.calendars[i].date.substr(8,2) + '.' + this.calendars[i].date.substr(5,2) + '.' + this.calendars[i].date.substr(0,4);
            this.dates.push(date);
        }

        if (this.dates.length !== 0) {
            let minDate = {};
            let minDiff = 1000000000000;

            let today = moment();
            let todayString = today.format('DD.MM.YYYY');

            let c = this.dates.filter(d => d.date === todayString);

            if (c.length !== 0) {
                this.selectedCalendarId = c[0].id;
            } else {
                for(let i = 0; i < this.dates.length; i++) {
                    let dateMoment = moment(this.dates[i].date, "DD.MM.YYYY");
                    let diff = Math.abs(today.diff(dateMoment));

                    if (diff < minDiff) {
                        minDiff = diff;
                        minDate = this.dates[i];
                    }
                }

                this.selectedCalendarId = minDate.id;
            }
        }
    }
}
</script>

<style scoped>

</style>
