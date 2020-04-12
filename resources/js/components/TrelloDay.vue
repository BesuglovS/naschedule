<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Просмотр расписания c Trello
            </div>
            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr>
                        <td>День</td>
                        <td>&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1em; width: 310px;" v-model="dateSelected">
                                <option v-for="dt in dates" :selected="dt.date === dateSelected" :value="dt.date">{{dt.date}}</option>
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
                        <td>{{lesson.date}}</td>
                        <td>{{lesson.dow}}</td>
                        <td>{{lesson.time}}</td>
                        <td style="text-align: left !important;">{{lesson.discName}}</td>
                        <td>{{lesson.groupName}}</td>
                        <td>{{lesson.teacherFio}}</td>
                        <td><a  target=”_blank” :href="lesson.url">Trello</a></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align: left !important;">
                            {{lesson.desc}}
                            <span v-if="lesson.desc == ''" style="font-size: 2em;">Описание отсутствует</span>
                        </td>
                    </tr>
                    <tr v-for="comment in lesson.comments">
                        <td colspan="7" style="text-align: left !important;">{{comment.data.text}}</td>
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
        name: "TrelloDay",
        props: [
            'groups',
            'dates',
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                dateSelected: null,
                studentGroupId: null,
                showOKWindow: false,
                loading: false,
                lessons: []
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
                axios
                    .get('/trelloLoadGroup?date=' + this.dateSelected + '&groupId=' + this.studentGroupId)
                    .then(response => {
                        this.lessons = response.data;

                        Object.values(this.lessons).forEach(item => {
                            let mainSplit = item.name.split(" - ");
                            let dtSplit = mainSplit[0].split(" ");
                            let groupLeftLeftBracket = mainSplit[1].lastIndexOf('(');
                            let groupRightLeftBracket = mainSplit[1].lastIndexOf(')');
                            let discName = mainSplit[1].substr(0, groupLeftLeftBracket - 1);
                            let groupName = mainSplit[1].substr(groupLeftLeftBracket + 1, groupRightLeftBracket - groupLeftLeftBracket - 1);
                            item.date = dtSplit[0];
                            item.dow = dtSplit[1];
                            item.time = dtSplit[2];
                            item.discName = discName;
                            item.groupName = groupName;
                            item.teacherFio = mainSplit[2];
                        });
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

            let dts = this.dates.map(d => d.date);

            if (dts !== 0) {
                let minDate = {};
                let minDiff = 1000000000000;

                let today = moment();
                let todayString = today.format('YYYY-MM-DD');

                let c = dts.filter(d => d === todayString);

                if (c.length !== 0) {
                    this.dateSelected = c[0];
                } else {
                    for(let i = 0; i < dts.length; i++) {
                        let dateMoment = moment(this.dates[i].date, "DD.MM.YYYY");
                        let diff = Math.abs(today.diff(dateMoment));

                        if (diff < minDiff) {
                            minDiff = diff;
                            minDate = this.dates[i].date;
                        }
                    }

                    this.dateSelected = minDate;
                }
            }

        }
    }
</script>

<style scoped>

</style>
