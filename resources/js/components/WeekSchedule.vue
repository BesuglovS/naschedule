<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Копировать расписание параллели
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
                    <tr>
                        <td>Неделя источник</td>
                        <td>&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="fromWeek" v-model="fromWeek" id="fromWeek">
                                <option v-for="i in weekCount" :selected="i === fromWeek" :value="i">{{i}} ({{weeks[i]}})</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    <tr>
                    <tr>
                        <td>Неделя назначение</td>
                        <td>&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="toWeek" v-model="toWeek" id="toWeek">
                                <option v-for="i in weekCount" :selected="i === toWeek" :value="i">{{i}} ({{weeks[i]}})</option>
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
                <button type="submit" @click="copy();" style="font-size: 2em;" class="button is-primary">Копировать</button>

                <div v-if="copyLoading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
                    Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </div>
            </div>
        </div>

        <div style="border: 1px solid black; border-radius: 10px; margin-top: 2em; padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Удалить расписание параллели
            </div>
            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr style="padding-bottom: 1em;">
                        <td>Параллель</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="deleteFacultyId" v-model="deleteFacultyId" id="facultyId2">
                                <option v-for="faculty in faculties" :value="faculty.id">{{faculty.name}}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    <tr>
                        <td>Неделя</td>
                        <td>&nbsp;</td>
                        <td>
                            <select style="margin-right: 1em; font-size: 1.5em; width: 310px;" name="deleteWeek" v-model="deleteWeek" id="deleteWeek">
                                <option v-for="i in weekCount" :selected="i === fromWeek" :value="i">{{i}} ({{weeks[i]}})</option>
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
                                    @click="removeDowToggled(dow);"
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
                <button type="submit" @click="removeWeek();" style="font-size: 2em;" class="button is-primary">Удалить</button>

                <div v-if="deleteLoading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
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

    export default {
        name: "WeekSchedule",
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
                fromWeek: this.currentWeek,
                toWeek: this.currentWeek,
                deleteWeek: this.currentWeek,
                facultyId: -1,
                deleteFacultyId: -1,
                selectedDows: [1,2,3,4,5,6],
                removeSelectedDows: [1,2,3,4,5,6],
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                showOKWindow: false,
                copyLoading: false,
                deleteLoading: false,
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
            removeDowToggled(dow) {
                let index = this.removeSelectedDows.indexOf(dow);
                if (index > -1) {
                    this.removeSelectedDows.splice(index, 1);
                } else {
                    this.removeSelectedDows.push(dow);
                }
            },
            copy() {
                this.copyLoading = true;

                axios.post('/copyWeekSchedule?facultyId=' + this.facultyId +
                    '&fromWeek=' + this.fromWeek +
                    '&toWeek=' + this.toWeek +
                    '&dows=' + this.selectedDows.join('|'))
                    .then(response => {
                        this.copyLoading = false;
                        this.showOKWindow = true;
                    });
            },
            removeWeek() {
                this.deleteLoading = true;

                axios.post('/deleteWeekSchedule?facultyId=' + this.deleteFacultyId +
                    '&week=' + this.deleteWeek +
                    '&dows=' + this.removeSelectedDows.join('|'))
                    .then(response => {
                        this.deleteLoading = false;
                        this.showOKWindow = true;
                    });
            },
        },
        mounted() {
            if (this.faculties.length !== 0) {
                this.facultyId = this.faculties[0].id;
                this.deleteFacultyId = this.faculties[0].id;
            }

            if (this.currentWeek < 1) {
                this.fromWeek = 1;
                this.toWeek = 1;
                this.deleteWeek = 1;
            }
            if (this.currentWeek > this.weekCount) {
                this.fromWeek = this.weekCount;
                this.toWeek = this.weekCount;
                this.deleteWeek = this.weekCount;
            }
        }
    }
</script>

<style scoped>

</style>
