<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Проверить онлайн на Trello
            </div>
            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr>
                        <td>Неделя</td>
                        <td>&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="week" v-model="week" id="week">
                                <option v-for="(weekString, weekNumber) in weeks" :selected="weekNumber === week" :value="weekNumber">{{weekString}} ({{weekNumber}})</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button type="submit" @click="trelloAnalyse();" style="font-size: 2em;" class="button is-primary">Анализ</button>

                <div v-if="loading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
                    Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </div>
            </div>
        </div>

        <div style="border: 1px solid black; border-radius: 10px; padding:1em;">
            <table v-if="this.byGrade.length !== 0" class="table td-center is-bordered" style="margin-top: 1em;">
                <tr>
                    <td style="text-align: center;">Параллель</td>
                    <td style="text-align: center;">Онлайн</td>
                    <td style="text-align: center;">%</td>
                    <td style="text-align: center;">Не онлайн</td>
                    <td style="text-align: center;">%</td>
                    <td style="text-align: center;">Пустое описание</td>
                    <td style="text-align: center;">%</td>
                </tr>
                <tr v-for="(item, key) in this.byGrade">
                    <td>{{key}}</td>
                    <td>{{item.online}}</td>
                    <td>{{(item.online * 100 / (item.online + item.offline + item.empty)).toFixed(2)}}</td>
                    <td>{{item.offline}}</td>
                    <td>{{(item.offline * 100 / (item.online + item.offline + item.empty)).toFixed(2)}}</td>
                    <td>{{item.empty}}</td>
                    <td>{{(item.empty * 100 / (item.online + item.offline + item.empty)).toFixed(2)}}</td>
                </tr>
            </table>

            <table v-if="this.byTeacherFio.length !== 0" class="table td-center is-bordered" style="margin-top: 1em;">
                <tr>
                    <td style="text-align: center;">ФИО преподавателя</td>
                    <td style="text-align: center;">Онлайн</td>
                    <td style="text-align: center;">%</td>
                    <td style="text-align: center;">Не онлайн</td>
                    <td style="text-align: center;">%</td>
                    <td style="text-align: center;">Пустое описание</td>
                    <td style="text-align: center;">%</td>
                </tr>
                <tr v-for="item in this.byTeacherFio">
                    <td>{{item.teacherFio}}</td>
                    <td>{{item.online}}</td>
                    <td :style="'background-image: linear-gradient(to right, rgba(0, 150, 0, 0.3) 0%, rgba(0, 175, 0, 0.3) 17%, rgba(0, 190, 0, 0.3) 33%, rgba(82, 210, 82, 0.3) 67%, rgba(131, 230, 131, 0.3) 83%, rgba(180, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.onlinePercent.toFixed(2) + '% 100%;'">
                        {{(item.onlinePercent.toFixed(2))}}
                    </td>
                    <td>{{item.offline}}</td>
                    <td :style="'background-image: linear-gradient(to right, rgba(150, 150, 0, 0.3) 0%, rgba(175, 175, 0, 0.3) 17%, rgba(190, 190, 0, 0.3) 33%, rgba(210, 210, 82, 0.3) 67%, rgba(230, 230, 131, 0.3) 83%, rgba(221, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.offlinePercent.toFixed(2) + '% 100%;'">
                        {{(item.offlinePercent.toFixed(2))}}
                    </td>
                    <td>{{item.empty}}</td>
                    <td :style="'background-image: linear-gradient(to right, rgba(150, 0, 0, 0.3) 0%, rgba(175, 0, 0, 0.3) 17%, rgba(190, 0, 0, 0.3) 33%, rgba(210, 0, 82, 0.3) 67%, rgba(230, 0, 131, 0.3) 83%, rgba(221, 0, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.emptyPercent.toFixed(2) + '% 100%;'">
                        {{(item.emptyPercent.toFixed(2))}}
                    </td>
                </tr>
            </table>
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

    export default {
        name: "TrelloOnline",
        props: [
            'weekCount',
            'weeks',
            'currentWeek'
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                week: this.currentWeek,
                showOKWindow: false,
                loading: false,
                byGrade: [],
                byTeacherFio: [],
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
            trelloAnalyse() {
                this.loading = true;
                axios
                    .get('/trelloOnlineAction?week=' + this.week)
                    .then(response => {
                        this.byGrade = response.data.byGrade;

                        this.byTeacherFio = response.data.byTeacherFio;

                        this.byTeacherFio.forEach(item => {
                            item.onlinePercent = item.online * 100 / (item.online + item.offline + item.empty);
                            item.offlinePercent = item.offline * 100 / (item.online + item.offline + item.empty);
                            item.emptyPercent = item.empty * 100 / (item.online + item.offline + item.empty);
                        });

                        this.byTeacherFio = this.byTeacherFio.sort((a,b) => {
                                if (a.onlinePercent.toFixed(2).toString() ===
                                    b.onlinePercent.toFixed(2).toString()) {
                                    if (a.online === b.online) {
                                        return a.teacherFio < b.teacherFio ? -1 : 1;
                                    } else {
                                        return a.online > b.online ? -1 : 1;
                                    }
                                } else {
                                    return (a.onlinePercent) > (b.onlinePercent) ? -1 : 1;
                                }
                            }
                        );

                        this.loading = false;
                    });
            },
        },
        mounted() {
            if (this.currentWeek < 1) {
                this.week = 1;
            }
            if (this.currentWeek > this.weekCount) {
                this.week = this.weekCount;
            }
        }
    }
</script>

<style scoped>

</style>
