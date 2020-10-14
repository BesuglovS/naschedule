<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Загрузить расписание на Trello
            </div>
            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr style="padding-bottom: 1em;">
                        <td>Параллель</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="facultyId" v-model="facultyId" id="facultyId">
                                <option v-for="faculty in faculties" :value="faculty.id">{{faculty.name}}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Неделя</td>
                        <td>&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="week" v-model="week" id="week">
                                <option v-for="i in weekCount" :selected="i === week" :value="i">{{i}} ({{weeks[i]}})</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    <tr>
                    <tr>
                        <td>День недели</td>
                        <td>&nbsp;</td>
                        <td>
                            <button v-for="dow in 6" style="margin-right:0.5em; margin-bottom: 0.5em;"
                                    @click="dowToggled(dow);"
                                    :class="{'button': true,
                                                'is-primary': !selectedDows.includes(dow),
                                                'is-danger': selectedDows.includes(dow) }">
                                {{dowRu[dow-1]}}
                            </button>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button type="submit" @click="upload();" style="font-size: 2em;" class="button is-primary">Загрузить</button>

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
    import modal from "./Modal";

    export default {
        name: "TrelloExport",
        props: [
            'faculties',
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
                facultyId: -1,
                selectedDows: [1,2,3,4,5,6],
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                showOKWindow: false,
                loading: false,
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
            upload() {
                this.loading = true;

                axios
                    .post('/trelloExport?facultyId=' + this.facultyId + '&week=' + this.week + '&dows=' + this.selectedDows.join('|'))
                    .then(response => {
                        let dt = response.data;
                        this.loading = false;
                    });
            },
        },
        mounted() {
            if (this.faculties.length !== 0) {
                this.facultyId = this.faculties[0].id;
                this.deleteFacultyId = this.faculties[0].id;
            }

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
