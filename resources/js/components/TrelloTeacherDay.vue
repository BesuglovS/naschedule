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
                                <option v-for="dt in dates" :selected="dt === dateSelected" :value="dt">{{dt}}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr style="padding-bottom: 1em;">
                        <td>Учитель</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1em; width: 310px;" v-model="teacherId">
                                <option v-for="teacher in teachersList" :selected="teacher.id === teacherId" :value="teacher.id">{{teacher.fio}}</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button type="submit" @click="loadLessons();" style="font-size: 1em; margin-right: 1em;" class="button is-primary">Загрузить</button>

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
                            <span v-html="lesson.desc" style="white-space: pre-wrap; word-wrap:break-word;">{{lesson.desc}}</span>
                            <span v-if="lesson.desc == ''" style="font-size: 2em;">Описание отсутствует</span>
                        </td>
                    </tr>
                    <tr v-for="comment in lesson.comments">
                        <td v-html="comment.data.text"  colspan="7" style="text-align: left !important; white-space: pre-wrap; word-wrap:break-word;">
                            {{comment.data.text}}
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
        name: "TrelloTeacherDay",
        props: [
            'teachers',
            'dates',
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                dateSelected: null,
                teacherId: null,
                showOKWindow: false,
                loading: false,
                lessons: [],
                teachersList: Object.values(this.teachers).sort((a,b) => { return a.fio < b.fio ? -1 : 1;})
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
            loadLessons() {
                this.loading = true;
                axios
                    .get('/trelloLoadTeacher?date=' + this.dateSelected + '&teacherId=' + this.teacherId)
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

                                const URLMatcher = /(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-ZА-Яа-я0-9+&@#\/%=~_|$?!:,.]*\)|[-A-ZА-Яа-я0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-ZА-Яа-я0-9+&@#\/%=~_|$?!:,.]*\)|[A-ZА-Яа-я0-9+&@#\/%=~_|$])/igm

                                item.desc = item.desc.replace(URLMatcher, match => `<a href="${match}">` + match.substr(0,25) + (match.length > 25 ? '...':'') + `</a>`);

                                Object.values(item.comments).forEach(comment => {
                                    comment.data.text = comment.data.text.replace(URLMatcher, match => `<a href="${match}">` + match.substr(0,25) + (match.length > 25 ? '...':'') + `</a>`);
                                });
                            });

                            this.lessons.sort((a,b) => {
                                let aValue = a.time.substr(0,2)*60 + a.time.substr(3,2);
                                let bValue = b.time.substr(0,2)*60 + b.time.substr(3,2);

                                return (aValue < bValue) ? -1 : 1;
                            });

                            this.loading = false;
                        }
                    );
            },
        },
        mounted() {
            if (this.teachersList.length !== 0) {
                this.teacherId = this.teachersList[0].id;
            }

            let dts = this.dates;

            if (dts !== 0) {
                let minDate = {};
                let minDiff = 1000000000000;

                let today = moment();
                let todayString = today.format('DD.MM.YYYY');

                let c = dts.filter(d => d === todayString);

                if (c.length !== 0) {
                    this.dateSelected = c[0];
                } else {
                    for(let i = 0; i < dts.length; i++) {
                        let dateMoment = moment(this.dates[i], "DD.MM.YYYY");
                        let diff = Math.abs(today.diff(dateMoment));

                        if (diff < minDiff) {
                            minDiff = diff;
                            minDate = this.dates[i];
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
