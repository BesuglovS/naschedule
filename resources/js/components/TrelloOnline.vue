<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Проверить онлайн на Trello
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
                                <option v-for="(weekString, weekNumber) in weeks" :selected="weekNumber === week" :value="weekNumber">{{weekString}} ({{weekNumber}})</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <button type="submit" @click.prevent="trelloAnalyse()" style="font-size: 2em;" class="button is-primary">Анализ</button>

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
                    <td v-if="!byGradeNoEmpty" style="text-align: center;">Пустое описание</td>
                    <td v-if="!byGradeNoEmpty" style="text-align: center;">%</td>
                </tr>
                <tr v-for="(item, key) in this.byGrade">
                    <td>{{key}} <a href="#" @click.prevent="showGradeList(key)">(список)</a></td>
                    <td><a href="#" @click.prevent="showGradeOnline(key, item)">{{item.online}}</a></td>
                    <td :style="'background-image: linear-gradient(to right, rgba(0, 150, 0, 0.3) 0%, rgba(0, 175, 0, 0.3) 17%, rgba(0, 190, 0, 0.3) 33%, rgba(82, 210, 82, 0.3) 67%, rgba(131, 230, 131, 0.3) 83%, rgba(180, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.onlinePercent.toFixed(2) + '% 100%;'">
                        {{(item.onlinePercent.toFixed(2))}}
                    </td>
                    <td><a href="#" @click.prevent="showGradeOffline(key, item)">{{item.offline}}</a></td>
                    <td :style="'background-image: linear-gradient(to right, rgba(150, 150, 0, 0.3) 0%, rgba(175, 175, 0, 0.3) 17%, rgba(190, 190, 0, 0.3) 33%, rgba(210, 210, 82, 0.3) 67%, rgba(230, 230, 131, 0.3) 83%, rgba(221, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.offlinePercent.toFixed(2) + '% 100%;'">
                        {{(item.offlinePercent.toFixed(2))}}
                    </td>
                    <td v-if="!byGradeNoEmpty ">
                        <a href="#" @click.prevent="showGradeEmpty(key, item)">{{item.empty}}</a>
                    </td>
                    <td v-if="!byGradeNoEmpty" :style="'background-image: linear-gradient(to right, rgba(150, 0, 0, 0.3) 0%, rgba(175, 0, 0, 0.3) 17%, rgba(190, 0, 0, 0.3) 33%, rgba(210, 0, 82, 0.3) 67%, rgba(230, 0, 131, 0.3) 83%, rgba(221, 0, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.emptyPercent.toFixed(2) + '% 100%;'">
                        {{(item.emptyPercent.toFixed(2))}}
                    </td>
                </tr>
            </table>

            <table v-if="this.byGroup.length !== 0" class="table td-center is-bordered" style="margin-top: 1em;">
                <tr>
                    <td style="text-align: center;">Класс</td>
                    <td style="text-align: center;">Онлайн</td>
                    <td style="text-align: center;">%</td>
                    <td style="text-align: center;">Не онлайн</td>
                    <td style="text-align: center;">%</td>
                    <td v-if="!byGroupNoEmpty" style="text-align: center;">Пустое описание</td>
                    <td v-if="!byGroupNoEmpty" style="text-align: center;">%</td>
                </tr>
                <tr v-for="item in this.byGroup">
                    <td>{{item.groupName}} <a href="#" @click.prevent="showGroupList(item)">(список)</a></td>
                    <td><a href="#" @click.prevent="showGroupOnline(item)">{{item.online}}</a></td>
                    <td :style="'background-image: linear-gradient(to right, rgba(0, 150, 0, 0.3) 0%, rgba(0, 175, 0, 0.3) 17%, rgba(0, 190, 0, 0.3) 33%, rgba(82, 210, 82, 0.3) 67%, rgba(131, 230, 131, 0.3) 83%, rgba(180, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.onlinePercent.toFixed(2) + '% 100%;'">
                        {{(item.onlinePercent.toFixed(2))}}
                    </td>
                    <td><a href="#" @click.prevent="showGroupOffline(item)">{{item.offline}}</a></td>
                    <td :style="'background-image: linear-gradient(to right, rgba(150, 150, 0, 0.3) 0%, rgba(175, 175, 0, 0.3) 17%, rgba(190, 190, 0, 0.3) 33%, rgba(210, 210, 82, 0.3) 67%, rgba(230, 230, 131, 0.3) 83%, rgba(221, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.offlinePercent.toFixed(2) + '% 100%;'">
                        {{(item.offlinePercent.toFixed(2))}}
                    </td>
                    <td v-if="!byGroupNoEmpty "><a href="#" @click.prevent="showGroupEmpty(item)">{{item.empty}}</a></td>
                    <td v-if="!byGroupNoEmpty" :style="'background-image: linear-gradient(to right, rgba(150, 0, 0, 0.3) 0%, rgba(175, 0, 0, 0.3) 17%, rgba(190, 0, 0, 0.3) 33%, rgba(210, 0, 82, 0.3) 67%, rgba(230, 0, 131, 0.3) 83%, rgba(221, 0, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.emptyPercent.toFixed(2) + '% 100%;'">
                        {{(item.emptyPercent.toFixed(2))}}
                    </td>
                </tr>
            </table>

            <table v-if="this.byTeacherFio.length !== 0" class="table td-center is-bordered" style="margin-top: 1em;">
                <tr>
                    <td style="text-align: center;">ФИО преподавателя</td>
                    <td style="text-align: center;">Онлайн</td>
                    <td style="text-align: center;">%</td>
                    <td style="text-align: center;">Не онлайн</td>
                    <td style="text-align: center;">%</td>
                    <td v-if="!byTeacherNoEmpty" style="text-align: center;">Пустое описание</td>
                    <td v-if="!byTeacherNoEmpty" style="text-align: center;">%</td>
                </tr>
                <tr v-for="item in this.byTeacherFio">
                    <td>
                        <a href="#" @click.prevent="ShowTeacherData(item.teacherFio)">{{item.teacherFio}}</a>
                        <a href="#" @click.prevent="showTeacherList(item)">(список)</a>
                    </td>
                    <td><a href="#" @click.prevent="showTeacherOnline(item)">{{item.online}}</a></td>
                    <td :style="'background-image: linear-gradient(to right, rgba(0, 150, 0, 0.3) 0%, rgba(0, 175, 0, 0.3) 17%, rgba(0, 190, 0, 0.3) 33%, rgba(82, 210, 82, 0.3) 67%, rgba(131, 230, 131, 0.3) 83%, rgba(180, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.onlinePercent.toFixed(2) + '% 100%;'">
                        {{(item.onlinePercent.toFixed(2))}}
                    </td>
                    <td><a href="#" @click.prevent="showTeacherOffline(item)">{{item.offline}}</a></td>
                    <td :style="'background-image: linear-gradient(to right, rgba(150, 150, 0, 0.3) 0%, rgba(175, 175, 0, 0.3) 17%, rgba(190, 190, 0, 0.3) 33%, rgba(210, 210, 82, 0.3) 67%, rgba(230, 230, 131, 0.3) 83%, rgba(221, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.offlinePercent.toFixed(2) + '% 100%;'">
                        {{(item.offlinePercent.toFixed(2))}}
                    </td>
                    <td v-if="!byTeacherNoEmpty"><a href="#" @click.prevent="showTeacherEmpty(item)">{{item.empty}}</a></td>
                    <td v-if="!byTeacherNoEmpty" :style="'background-image: linear-gradient(to right, rgba(150, 0, 0, 0.3) 0%, rgba(175, 0, 0, 0.3) 17%, rgba(190, 0, 0, 0.3) 33%, rgba(210, 0, 82, 0.3) 67%, rgba(230, 0, 131, 0.3) 83%, rgba(221, 0, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.emptyPercent.toFixed(2) + '% 100%;'">
                        {{(item.emptyPercent.toFixed(2))}}
                    </td>
                </tr>
            </table>
        </div>

        <transition v-if="this.showTeacherWindow" name="modal">
            <div class="modal-mask">
                <div class="modal-wrapper">
                    <div class="modal-container">
                        <div class="modal-header">
                            <div style="text-align: center; font-size: 1.5em;">
                                {{this.teacherData.teacherFio}} - {{this.facultyNameLoaded}}
                            </div>
                        </div>

                        <div class="modal-body">
                            <div style="width: 100%; text-align: center;">
                                <table id="modalTable" class="is-bordered modalTable">
                                    <tr>
                                        <td style="text-align: center;">Группа</td>
                                        <td style="text-align: center;">Онлайн</td>
                                        <td style="text-align: center;">%</td>
                                        <td style="text-align: center;">Не онлайн</td>
                                        <td style="text-align: center;">%</td>
                                        <td v-if="!teacherByGradeNoEmpty" style="text-align: center;">Пустое описание</td>
                                        <td v-if="!teacherByGradeNoEmpty" style="text-align: center;">%</td>
                                    </tr>
                                    <tr v-for="(item, key) in this.teacherData.byGroup">
                                        <td>{{key}}</td>
                                        <td>{{item.online}}</td>
                                        <td :style="'background-image: linear-gradient(to right, rgba(0, 150, 0, 0.3) 0%, rgba(0, 175, 0, 0.3) 17%, rgba(0, 190, 0, 0.3) 33%, rgba(82, 210, 82, 0.3) 67%, rgba(131, 230, 131, 0.3) 83%, rgba(180, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.onlinePercent.toFixed(2) + '% 100%;'">
                                            {{(item.onlinePercent.toFixed(2))}}
                                        </td>
                                        <td>{{item.offline}}</td>
                                        <td :style="'background-image: linear-gradient(to right, rgba(150, 150, 0, 0.3) 0%, rgba(175, 175, 0, 0.3) 17%, rgba(190, 190, 0, 0.3) 33%, rgba(210, 210, 82, 0.3) 67%, rgba(230, 230, 131, 0.3) 83%, rgba(221, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.offlinePercent.toFixed(2) + '% 100%;'">
                                            {{(item.offlinePercent.toFixed(2))}}
                                        </td>
                                        <td v-if="!teacherByGradeNoEmpty">{{item.empty}}</td>
                                        <td v-if="!teacherByGradeNoEmpty" :style="'background-image: linear-gradient(to right, rgba(150, 0, 0, 0.3) 0%, rgba(175, 0, 0, 0.3) 17%, rgba(190, 0, 0, 0.3) 33%, rgba(210, 0, 82, 0.3) 67%, rgba(230, 0, 131, 0.3) 83%, rgba(221, 0, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + item.emptyPercent.toFixed(2) + '% 100%;'">
                                            {{(item.emptyPercent.toFixed(2))}}
                                        </td>
                                    </tr>

                                    <tr style="font-weight: 700;" v-if="Object.keys(this.teacherData.byGroup).length > 1">
                                        <td>Итого</td>
                                        <td>{{this.teacherData.online}}</td>
                                        <td :style="'background-image: linear-gradient(to right, rgba(0, 150, 0, 0.3) 0%, rgba(0, 175, 0, 0.3) 17%, rgba(0, 190, 0, 0.3) 33%, rgba(82, 210, 82, 0.3) 67%, rgba(131, 230, 131, 0.3) 83%, rgba(180, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + this.teacherData.onlinePercent.toFixed(2) + '% 100%;'">
                                            {{(this.teacherData.onlinePercent.toFixed(2))}}
                                        </td>
                                        <td>{{this.teacherData.offline}}</td>
                                        <td :style="'background-image: linear-gradient(to right, rgba(150, 150, 0, 0.3) 0%, rgba(175, 175, 0, 0.3) 17%, rgba(190, 190, 0, 0.3) 33%, rgba(210, 210, 82, 0.3) 67%, rgba(230, 230, 131, 0.3) 83%, rgba(221, 221, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + this.teacherData.offlinePercent.toFixed(2) + '% 100%;'">
                                            {{(this.teacherData.offlinePercent.toFixed(2))}}
                                        </td>
                                        <td v-if="!teacherByGradeNoEmpty">{{this.teacherData.empty}}</td>
                                        <td v-if="!teacherByGradeNoEmpty" :style="'background-image: linear-gradient(to right, rgba(150, 0, 0, 0.3) 0%, rgba(175, 0, 0, 0.3) 17%, rgba(190, 0, 0, 0.3) 33%, rgba(210, 0, 82, 0.3) 67%, rgba(230, 0, 131, 0.3) 83%, rgba(221, 0, 180, 0.3) 100%); background-repeat: no-repeat; background-size: ' + this.teacherData.emptyPercent.toFixed(2) + '% 100%;'">
                                            {{(this.teacherData.emptyPercent.toFixed(2))}}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="modal-footer" style="text-align: center;">
                            <button style="width: 200px; font-size: 1em; margin-top: 1em;" @click.prevent="showTeacherWindow = false" class="button is-primary">
                                Закрыть
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <transition v-if="this.showModalList" name="modal2">
            <div class="modal-mask">
                <div class="modal-wrapper">
                    <div class="modal-container">
                        <div class="modal-header">
                            <div style="text-align: center; font-size: 1.5em;">
                                {{this.modalTitle}}
                            </div>
                        </div>

                        <div class="modal-body">
                            <div style="width: 100%; text-align: center;">
                                <table class="is-bordered modalTable">
                                    <tr>
                                        <td v-if="modalDateVisible">Дата</td>
                                        <td v-if="modalDowVisible">День недели</td>
                                        <td v-if="modalTimeVisible">Время</td>
                                        <td v-if="modalDiscVisible">Предмет</td>
                                        <td v-if="modalGroupVisible">Класс</td>
                                        <td v-if="modalTeacherFioVisible">ФИО учителя</td>
                                    </tr>
                                    <tr v-for="item in this.modalLessonList">
                                        <td v-if="modalDateVisible">{{item.date}}</td>
                                        <td v-if="modalDowVisible">{{item.dow}}</td>
                                        <td v-if="modalTimeVisible">{{item.time}}</td>
                                        <td v-if="modalDiscVisible">{{item.discName}}</td>
                                        <td v-if="modalGroupVisible">{{item.groupName}}</td>
                                        <td v-if="modalTeacherFioVisible">{{item.teacherFio}}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="modal-footer" style="text-align: center;">
                            <button style="width: 200px; font-size: 1em; margin-top: 1em;" @click.prevent="showModalList = false" class="button is-primary">
                                Закрыть
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>

<script>
    export default {
        name: "TrelloOnline",
        props: [
            'faculties',
            'weekCount',
            'weeks',
            'currentWeek'
        ],
        components: {
        },
        data() {
            return {
                facultyId: -1,
                week: this.currentWeek,
                showTeacherWindow: false,
                loading: false,
                byGrade: [],
                byGroup: [],
                byTeacherFio: [],
                byGradeNoEmpty: false,
                byGroupNoEmpty: false,
                byTeacherNoEmpty: false,
                teacherData: { online: 0, offline: 4, empty: 0, byGroup: {} },
                teacherByGradeNoEmpty: true,
                facultyNameLoaded: '',
                weekLoaded: -1,
                showModalList: false,
                modalTitle: '',
                modalLessonList: [],
                modalDateVisible: true,
                modalDowVisible: true,
                modalTimeVisible: true,
                modalDiscVisible: true,
                modalGroupVisible: true,
                modalTeacherFioVisible: true
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
                this.facultyNameLoaded = this.faculties.filter(f => f.id === this.facultyId)[0].name;
                this.weekLoaded = this.week;

                axios
                    .get('/trelloOnlineAction?week=' + this.week + '&facultyId=' + this.facultyId)
                    .then(response => {
                        this.byGrade = response.data.byGrade;
                        this.byGradeNoEmpty = true;

                        let total  = { empty: 0, emptyPercent:0, offline:0, offlinePercent:0, online:0, onlinePercent:0 };

                        Object.values(this.byGrade).forEach(item => {
                            total.online += item.online;
                            total.offline += item.offline;
                            total.empty += item.empty;

                            item.onlinePercent = item.online * 100 / (item.online + item.offline + item.empty);
                            item.offlinePercent = item.offline * 100 / (item.online + item.offline + item.empty);
                            item.emptyPercent = item.empty * 100 / (item.online + item.offline + item.empty);
                            if (item.emptyPercent !== 0) this.byGradeNoEmpty = false;
                        });

                        total.onlinePercent = total.online * 100 / (total.online + total.offline + total.empty);
                        total.offlinePercent = total.offline * 100 / (total.online + total.offline + total.empty);
                        total.emptyPercent = total.empty * 100 / (total.online + total.offline + total.empty);
                        if (this.facultyId === 0) {
                            this.byGrade['Итого'] = total;
                        }

                        this.byGroup = response.data.byGroup;
                        this.byGroupNoEmpty = true;

                        this.byGroup.forEach(item => {
                            item.onlinePercent = item.online * 100 / (item.online + item.offline + item.empty);
                            item.offlinePercent = item.offline * 100 / (item.online + item.offline + item.empty);
                            item.emptyPercent = item.empty * 100 / (item.online + item.offline + item.empty);
                            if (item.emptyPercent !== 0) this.byGroupNoEmpty = false;
                        });

                        this.byGroup = this.byGroup.sort((a,b) => {
                            let aSplit = a.groupName.split(' ');
                            let bSplit = b.groupName.split(' ');
                            let aGrade = parseInt(aSplit[0]);
                            let bGrade = parseInt(bSplit[0]);
                            if (aGrade === bGrade) {
                                return aSplit[1] > bSplit[1] ? 1 : -1;
                            } else {
                                return aGrade > bGrade ? 1 : -1;
                            }
                        });

                        this.byTeacherFio = response.data.byTeacherFio;
                        this.byTeacherNoEmpty = true;

                        this.byTeacherFio.forEach(item => {
                            item.onlinePercent = item.online * 100 / (item.online + item.offline + item.empty);
                            item.offlinePercent = item.offline * 100 / (item.online + item.offline + item.empty);
                            item.emptyPercent = item.empty * 100 / (item.online + item.offline + item.empty);
                            if (item.emptyPercent !== 0) this.byTeacherNoEmpty = false;
                        });

                        this.byTeacherFio = this.byTeacherFio.sort((a,b) => {
                                if (a.onlinePercent.toFixed(2).toString() ===
                                    b.onlinePercent.toFixed(2).toString()) {
                                    if (a.online === b.online) {
                                        if (a.emptyPercent.toFixed(2).toString() ===
                                            b.emptyPercent.toFixed(2).toString()) {
                                            return a.teacherFio < b.teacherFio ? -1 : 1;
                                        } else {
                                            return a.emptyPercent < b.emptyPercent ? -1 : 1;
                                        }
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
            ShowTeacherData(teacherFio) {
                let teacherDataArray = this.byTeacherFio.filter(item => item.teacherFio === teacherFio);
                if (teacherDataArray.length > 0) {
                    let data = teacherDataArray[0];

                    this.teacherByGradeNoEmpty = true;
                    for (let [key, item] of Object.entries(data.byGroup)) {
                        item.onlinePercent = item.online * 100 / (item.online + item.offline + item.empty);
                        item.offlinePercent = item.offline * 100 / (item.online + item.offline + item.empty);
                        item.emptyPercent = item.empty * 100 / (item.online + item.offline + item.empty);
                        if (item.emptyPercent !== 0) this.teacherByGradeNoEmpty = false;
                    }

                    this.teacherData = data;
                    this.showTeacherWindow = true;
                }
            },
            showGradeList(grade) {
                this.modalTitle = 'Список уроков / ' + grade + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = this.byGrade[grade].lessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = true;
                this.modalTeacherFioVisible = true;
            },
            showGroupList(group) {
                this.modalTitle = 'Список уроков / ' + group.groupName + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = group.lessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = false;
                this.modalTeacherFioVisible = true;
            },
            showTeacherList(teacher) {
                this.modalTitle = 'Список уроков / ' + teacher.teacherFio + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = teacher.lessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = true;
                this.modalTeacherFioVisible = false;
            },
            showGradeOnline(key, item) {
                this.modalTitle = 'Список онлайн уроков / ' + key + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = item.onlineLessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = true;
                this.modalTeacherFioVisible = true;
            },
            showGradeOffline(key, item) {
                this.modalTitle = 'Список оффлайн уроков / ' + key + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = item.offlineLessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = true;
                this.modalTeacherFioVisible = true;
            },
            showGradeEmpty(key, item) {
                this.modalTitle = 'Список уроков с пустым описанием / ' + key + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = item.emptyLessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = true;
                this.modalTeacherFioVisible = true;
            },
            showGroupOnline(item) {
                this.modalTitle = 'Список онлайн уроков / ' + item.groupName + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = item.onlineLessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = false;
                this.modalTeacherFioVisible = true;
            },
            showGroupOffline(item) {
                this.modalTitle = 'Список оффлайн уроков / ' + item.groupName + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = item.offlineLessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = false;
                this.modalTeacherFioVisible = true;
            },
            showGroupEmpty(item) {
                this.modalTitle = 'Список уроков с пустым описанием / ' + item.groupName + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = item.emptyLessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = false;
                this.modalTeacherFioVisible = true;
            },
            showTeacherOnline(item) {
                this.modalTitle = 'Список онлайн уроков / ' + item.teacherFio + ' / ' + this.facultyNameLoaded + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = item.onlineLessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = true;
                this.modalTeacherFioVisible = false;
            },
            showTeacherOffline(item) {
                this.modalTitle = 'Список оффлайн уроков / ' + item.teacherFio + ' / ' + this.facultyNameLoaded + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = item.offlineLessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = true;
                this.modalTeacherFioVisible = false;
            },
            showTeacherEmpty(item) {
                this.modalTitle = 'Список уроков с пустым описанием / ' + item.teacherFio + ' / ' + this.facultyNameLoaded + ' / ' + this.weeks[this.weekLoaded] + ' (' + this.weekLoaded + ')';
                this.modalLessonList = item.emptyLessons;
                this.showModalList = true;

                this.modalDateVisible = true;
                this.modalDowVisible = true;
                this.modalTimeVisible = true;
                this.modalDiscVisible = true;
                this.modalGroupVisible = true;
                this.modalTeacherFioVisible = false;
            },
        },
        mounted() {
            this.faculties.unshift({
                dean_signing_schedule:" ",
                dean_signing_session_schedule:" ",
                id:0,
                letter:"Все",
                name:"Все",
                schedule_signing_title:" ",
                session_signing_title:" ",
                sorting_order:0
            });
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
    .modal-mask {
        position: fixed;
        z-index: 9998;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        display: table;
        transition: opacity .3s ease;
    }

    .modal-wrapper {
        display: table-cell;
        vertical-align: middle;
    }

    .modal-container {
        width: 90%;
        max-width: 920px;
        margin: 0px auto;
        padding: 20px 30px;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        transition: all .3s ease;
        font-family: Helvetica, Arial, sans-serif;
    }

    .modal-header h3 {
        margin-top: 0;
        color: #42b983;
    }

    .modal-body {
        margin: 20px 0;
        max-height: calc(100vh - 210px);
        overflow-y: auto;
    }

    .modal-enter .modal-container,
    .modal-leave-active .modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }

    table.modalTable {
        width: 100%;
        max-width: 900px;
    }

    table.modalTable td, table.modalTable th {
        font-size: 1em;
        padding: 1em;
        text-align: center !important;
    }
</style>
