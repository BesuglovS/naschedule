<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12" style="display:flex;">
                <div class="card">
                    <div class="card-header">
                        <span style="margin-right: 1em;">
                            Расписание группы
                        </span>

                        <button @click="loadGroupSchedule();"
                                style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                class="button is-primary">R
                        </button>
                    </div>

                    <div class="card-body">
                        <select v-model="studentGroupId" @change="loadGroupSchedule()">
                            <option v-for="sg in groupsSorted" :value="sg.id">{{sg.name}}</option>
                        </select>

                        Недели: {{combineWeeksToRange(this.selectedWeeks)}}

                        <div id="groupSchedule" style="margin-top: 1em;">
                            <div style="text-align: center;">
                                Недели:
                                <button @click="loadFullGroupSchedule();"
                                        style="margin-right:0.5em; margin-bottom: 0.5em;"
                                        :class="{'button': true,
                                    'is-primary': selectedWeeks.length !== 1 || (selectedWeeks.length > 0 && selectedWeeks[0] !== -1),
                                    'is-danger': selectedWeeks.length === 1 && selectedWeeks[0] === -1 }"
                                >Все</button>
                                <button @click="weekToggled(week)"
                                        v-for="week in this.weeksCount"
                                        style="margin-right:0.5em; margin-bottom: 0.5em;"
                                        :class="{'button': true,
                                    'is-primary': !selectedWeeks.includes(week),
                                    'is-danger': selectedWeeks.includes(week)}"
                                >{{week}}</button>

                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="severalWeeks" @change="severalWeeksSwitchFlipped();" class="custom-control-input" id="customSwitch1">
                                    <label class="custom-control-label" for="customSwitch1">Несколько недель</label>
                                </div>

                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="showEditTools" class="custom-control-input" id="customSwitch2">
                                    <label class="custom-control-label" for="customSwitch2">Показывать инструменты редактирования</label>
                                </div>

                                <div class="custom-control custom-switch">
                                    <input :disabled="severalWeeks" type="checkbox" @change="fastInputModeToggled" v-model="fastInputMode" class="custom-control-input" id="customSwitch3">
                                    <label class="custom-control-label" for="customSwitch3">Режим быстрого ввода</label>
                                </div>
                            </div>

                            <div v-if="loading === true" style="font-size: 2em; text-align: center">
                                Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                            </div>

                            <table v-if="loading === false" style="margin-top: 2em;" class="table td-center is-bordered">
                                <tr>
                                    <td></td>
                                    <td v-for="dow in 6">
                                        <strong>{{dowRu[dow-1]}}</strong>
                                        <template v-if="groupDisciplines.length > 0 && showEditTools && !fastInputMode">
                                            <a @click.prevent="newDows = []; newDows.push(dow); newRingIds = []; askForNew();" href="#"><font-awesome-icon icon="plus-square" /></a>
                                        </template>
                                    </td>
                                </tr>

                                <tr v-for="ring in this.scheduleRings">
                                    <td><strong>{{ring}}</strong></td>
                                    <td v-for="dow in 6">
                                        <table v-if="showEditTools" style="width: 100%; text-align: center; border:none !important;">
                                            <tr>
                                                <td style="border:none;">
                                                    <a v-if="groupDisciplines.length > 0" @click.prevent="newDows = []; newDows.push(dow); setNewRingId(ring); askForNew();" href="#">
                                                        <font-awesome-icon icon="plus-square" />
                                                    </a>
                                                </td>

                                                <td v-if="teacherBusy(disciplineTeacherSchedule, dow, ring) !== ''"
                                                    style="border:none; border-radius: 5px; background-color: #ffdddd; font-size: 0.6em; vertical-align: middle;">
                                                    {{teacherBusy(disciplineTeacherSchedule, dow, ring)}}
                                                </td>
                                            </tr>
                                        </table>
                                        <template v-if="Object.keys(groupSchedule[dow]).length !== 0">
                                            <div :class="{ 'smallFont': fastInputMode }" style="border: none;" v-if="groupSchedule[dow][ring] !== undefined">
                                                <template
                                                    v-for="tfd in
                                                    Object.keys(groupSchedule[dow][ring])
                                                    .sort((a,b) => {
                                                                let aMin = Math.min(...Object.values(groupSchedule[dow][ring][a]['weeksAndAuds']).flat());
                                                                let bMin = Math.min(...Object.values(groupSchedule[dow][ring][b]['weeksAndAuds']).flat());

                                                                if (aMin === bMin) {
                                                                    let aGroupName = groupSchedule[dow][ring][a]['lessons'][0]['groupName'];
                                                                    let bGroupName = groupSchedule[dow][ring][b]['lessons'][0]['groupName'];

                                                                    let numA = parseInt(aGroupName.split(' ')[0]);
                                                                    let numB = parseInt(bGroupName.split(' ')[0]);

                                                                    if (numA === numB) {
                                                                        if (aGroupName === bGroupName) return 0;
                                                                        return (aGroupName < bGroupName) ? -1 : 1;
                                                                    }
                                                                    else
                                                                    {
                                                                        return (numA < numB) ? -1 : 1;
                                                                    }
                                                                }

                                                                return aMin < bMin ? -1 : 1;
                                                        })
                                                ">
                                                    <table v-if="showEditTools && !fastInputMode" style="width: 100%; text-align: center; border:none !important;">
                                                        <tr>
                                                            <td style="border:none;"><a @click.prevent="askForEdit(groupSchedule[dow][ring][tfd], dow, ring);" href="#"><font-awesome-icon icon="edit" /></a></td>
                                                            <td style="border:none;">
                                                                <strong>{{groupSchedule[dow][ring][tfd]["lessons"][0]["groupName"]}}</strong>
                                                            </td>
                                                            <td style="border:none;">
                                                                <button type="button" @click="askForDelete(groupSchedule[dow][ring][tfd]['lessons']);" class="close">×</button>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <div
                                                        :class="{
                                                                'budget': groupSchedule[dow][ring][tfd]['lessons'][0]['disciplinesType'] === 1,
                                                                'vneur': groupSchedule[dow][ring][tfd]['lessons'][0]['disciplinesType'] === 2,
                                                                'plat': groupSchedule[dow][ring][tfd]['lessons'][0]['disciplinesType'] === 3}">
                                                        {{groupSchedule[dow][ring][tfd]["lessons"][0]["discName"]}}
                                                        <template v-if="fastInputMode">
                                                            (<strong>{{groupSchedule[dow][ring][tfd]["lessons"][0]["groupName"]}}</strong>)
                                                        </template>
                                                        <br />
                                                        {{groupSchedule[dow][ring][tfd]["lessons"][0]["teacherFIO"]}} <br />
                                                        <template v-if="!fastInputMode" v-for="auditorium in
                                                            Object.keys(groupSchedule[dow][ring][tfd]['weeksAndAuds'])
                                                                .sort((a,b) => {
                                                                    let  aMin = Math.min(...groupSchedule[dow][ring][tfd]['weeksAndAuds'][a]);
                                                                    let bMin = Math.min(...groupSchedule[dow][ring][tfd]['weeksAndAuds'][b]);

                                                                    if (aMin === bMin) return 0;
                                                                    return aMin < bMin ? -1 : 1;
                                                                })
                                                        ">
                                                            {{combineWeeksToRange(groupSchedule[dow][ring][tfd]["weeksAndAuds"][auditorium])}} - {{auditorium}}<br />
                                                        </template>
                                                    </div>

                                                    <template v-if="tfd !== Object.keys(groupSchedule[dow][ring])
                                                        .sort((a,b) => {
                                                                let aMin = Math.min(...Object.values(groupSchedule[dow][ring][a]['weeksAndAuds']).flat());
                                                                let bMin = Math.min(...Object.values(groupSchedule[dow][ring][b]['weeksAndAuds']).flat());

                                                                if (aMin === bMin) {
                                                                    let aGroupName = groupSchedule[dow][ring][a]['lessons'][0]['groupName'];
                                                                    let bGroupName = groupSchedule[dow][ring][b]['lessons'][0]['groupName'];

                                                                    let numA = parseInt(aGroupName.split(' ')[0]);
                                                                    let numB = parseInt(bGroupName.split(' ')[0]);

                                                                    if (numA === numB) {
                                                                        if (aGroupName === bGroupName) return 0;
                                                                        return (aGroupName < bGroupName) ? -1 : 1;
                                                                    }
                                                                    else
                                                                    {
                                                                        return (numA < numB) ? -1 : 1;
                                                                    }
                                                                }

                                                                return aMin < bMin ? -1 : 1;
                                                        })
                                                        [Object.keys(groupSchedule[dow][ring]).length-1]">
                                                        <hr>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-left: 1em;">
                    <div class="card-header">
                        <div>
                            Дисциплины группы
                        </div>
                        <button @click="reloadDisciplines();"
                                style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                class="button isPrimary">R
                        </button>
                    </div>

                    <div class="card-body" style="text-align: center;">
                        <div v-for="discipline in groupDisciplinesWithTeacher">
                            <button @click="disciplineClicked(discipline)"
                                    style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                    :class="{
                                        'isPrimary': discipline.disciplineId !== groupDisciplineSelected.disciplineId,
                                        'isDanger': discipline.disciplineId === groupDisciplineSelected.disciplineId }">
                                {{discipline.disciplineName}}
                                <template v-if="showGroup(studentGroupId, discipline.studentGroupName)">
                                    ({{discipline.studentGroupName}})
                                </template>
                                <br />
                                <span style="font-size:0.6em;">
                                    {{discipline.teacherFio}}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <modal v-if="deletePrompt">
            <template v-slot:header>Удаление уроков</template>
            <template v-slot:body>Вы уверены в том, что хотите удалить все эти уроки?</template>
            <template v-slot:footer>
                <button style="margin-right:0.5em;" @click="deletePrompt = false;" class="button is-primary">Нет</button>
                <button style="margin-right:0.5em;" @click="deletePrompt = false; deleteLessons();" class="button is-danger">Да</button>
            </template>
        </modal>

        <modal v-if="showEditWindow">
            <template v-slot:header>
                Редактирование уроков. Недели: {{combineWeeksToRange(editSelectedWeeks)}} |
                {{lessonsDataToEdit.lessons[0].discName}} + {{lessonsDataToEdit.lessons[0].teacherFIO}} @ {{lessonsDataToEdit.lessons[0].groupName}} |
                {{dowRu[editDow-1]}} + {{editRing.Time.substr(0,5)}}
                <span style="border:none; border-radius: 5px; background-color: #ffdddd; font-size: 1em; padding: 8px; vertical-align: middle;">
                    {{teacherBusy(editDisciplineTeacherSchedule, editDow, editRing.Time.substr(0,5), '-')}}
                </span>
            </template>
            <template v-slot:body>
                <div style="width: 100%; margin: 0; padding: 0; display: flex; flex-direction: row; flex-wrap: wrap;">

                    <div style="text-align: center; width: auto; margin-right: 1em; margin-bottom: 1em;" v-for="week in weeksCount">
                        <div>
                            <button style="margin-right:0.5em; margin-bottom: 0.5em;"
                                    @click="editWeekToggled(week)"
                                    :class="{'button': true,
                                    'is-primary': !editSelectedWeeks.includes(week),
                                    'is-danger': editSelectedWeeks.includes(week) }"
                            >{{week}}</button>
                        </div>

                        <div>
                            <select style="width: 90px; font-size: 1em;" v-model="editWeeksAuds[week]">
                                <option v-for="aud in editLessonsWeeksAudsFree(week)"
                                        v-bind:style="{ backgroundColor :
                                            (aud.id === editWeeksAuds[week]) ? ('#ddffdd') :
                                            ((aud.free) ? 'white' : '#ffdddd') }"
                                        :value="aud.id">
                                    {{aud.name}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

            </template>
            <template v-slot:footer>
                <button style="margin-right:0.5em;" @click="showEditWindow = false;" class="button is-primary">Отмена</button>
                <button style="margin-right:0.5em;" @click="saveLessonsEdit(); showEditWindow = false; " class="button is-danger">Сохранить</button>
            </template>
        </modal>

        <modal v-if="showNewWindow">
            <template v-slot:header>
                <span style="font-size: 2em;">Новые уроки</span>

                <span v-if="newTfdBusyLoading === true">
                    <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </span>

                <span v-if="newTfdBusyLoading === false" style="font-size: 2em; color: red;">
                    {{WeeksToStringOrEmpty(teacherBusyArrays(newDows, newRingIds), '-')}}
                </span>

                <select v-model="groupDisciplineSelected" @change="newTfdChanged(groupDisciplineSelected)">
                    <option v-for="gd in groupDisciplinesWithTeacher" :value="gd">
                        {{gd.disciplineName}} @ {{gd.studentGroupName}} = {{gd.teacherFio}}
                    </option>
                </select>
                <br />

            </template>
            <template v-slot:body>
                <table style="width: 100%;">
                    <tr>
                        <td style="width:50%;">
                            <div style="text-align: left;">
                                Недели: {{combineWeeksToRange(newSelectedWeeks)}}
                            </div>
                        </td>
                        <td style="text-align:center; width:50%;">
                            Аудитория для всех выбранных недель:
                            <select style="width: 90px; font-size: 1em;" v-model="newSingleAudId" @change="newSingleAudChanged();">
                                <option v-for="aud in newLessonsAllWeeksAudsFree"
                                        v-bind:style="{ backgroundColor : (aud.free) ? 'white' : '#ffdddd' }"
                                        :value="aud.id">
                                    {{aud.name}}
                                </option>
                            </select>
                        </td>
                    </tr>
                </table>
                <hr>

                <div style="width: 100%; margin: 0; padding: 0; display: flex; flex-direction: row; flex-wrap: wrap;">
                    <div v-for="week in weeksCount" style="text-align: center; width: auto; margin-right: 1em; margin-bottom: 1em;">
                        <div>
                            <button style="margin-right:0.5em; margin-bottom: 0.5em;"
                                    @click="newWeekToggled(week)"
                                    :class="{'button': true,
                                        'is-primary': !newSelectedWeeks.includes(week),
                                        'is-danger': newSelectedWeeks.includes(week) }"
                            >{{week}}</button>
                        </div>


                        <div>
                            <select style="width: 90px; font-size: 1em;" v-model="newWeeksAuds[week]">
                                <option v-for="aud in newLessonsWeeksAudsFree(week)" :value="aud.id"
                                        v-bind:style="{ backgroundColor : (aud.free) ? 'white' : '#ffdddd' }">
                                    {{aud.name}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>

                <table style="margin: 0 auto;">
                    <tr>
                        <td>
                            <button v-for="dow in 6"
                                    @click="newDowToggled(dow);"
                                    style="margin-right:1em; margin-bottom: 0.5em; text-align:center;"
                                    :class="{'button': true,
                                    'is-primary': !newDows.includes(dow),
                                    'is-danger': newDows.includes(dow)}">
                                {{dowRu[dow-1]}}
                            </button>
                        </td>
                        <td>
                            <button @click="newRingToggled(ring);"
                                    v-for="ring in allRings"
                                    style="margin-right:1em; margin-bottom: 0.5em; text-align:center;"
                                    :class="{'button': true,
                                    'is-primary': !newRingIds.includes(ring.RingId),
                                    'is-danger': newRingIds.includes(ring.RingId)}">
                                {{ring.Time.substr(0,5)}}
                            </button>
                        </td>
                    </tr>
                </table>
            </template>
            <template v-slot:footer>
                <button style="margin-right:0.5em;" @click="showNewWindow = false;" class="button is-primary">Отмена</button>
                <button style="margin-right:0.5em;" @click="saveLessonsNew();" class="button is-danger">
                    <template v-if="!addLoading">Добавить</template>
                    <template v-if="addLoading"><font-awesome-icon icon="spinner" /></template>
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
    import modal from './Modal';
    import moment from "moment";

    export default {
        name: "GroupSchedule",
        props: [
            'auditoriums',
            'studentGroups',
            'groupId',
            'weekCount',
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                groups: this.studentGroups,
                exams: [],
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                firstLoad: true,
                loading: false,
                studentGroupId: this.groupId,
                weeksCount: this.weekCount,
                scheduleRings: [],
                selectedWeeks: [],
                severalWeeks: true,
                groupSchedule: {},
                deletePrompt: false,
                lessonsToDelete: [],
                showEditWindow: false,
                lessonsDataToEdit: {},
                editDow: "",
                editRing: "",
                editSelectedWeeks: [],
                editWeeksAuds: {},
                groupDisciplines: [],
                groupDisciplineSelected: {},
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                showNewWindow: false,
                newSelectedWeeks: [],
                newWeeksAuds: {},
                newDows: [],
                newRingIds: [],
                allRings: [],
                addLoading: false,
                newSingleAudId: -1,
                freeAuds: {},
                editFreeAuds: {},
                disciplineTeacherSchedule: {},
                editDisciplineTeacherSchedule: {},
                newTfdBusyLoading: false,
                showEditTools: true,
                fastInputMode: false,
            }
        },
        methods: {
            loadGroupSchedule() {
                this.exams = [];
                this.loading = true;
                this.firstLoad = false;

                let apiUrl = '/api.php?action=weeksSchedule&groupId=' + this.studentGroupId + '&weeks=' + this.selectedWeeks.join('|') + '&compactResult';

                if (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)
                {
                    let weeksString = this.range(1, this.weekCount).join('|');

                    apiUrl = '/api.php?action=weeksSchedule&groupId=' + this.studentGroupId + '&weeks=' + weeksString + '&compactResult';
                }

                axios
                    .get(apiUrl)
                    .then(response => {
                        let data = response.data;

                        let rings1 = Object.keys(data[1]);
                        let rings2 = Object.keys(data[2]);
                        let rings3 = Object.keys(data[3]);
                        let rings4 = Object.keys(data[4]);
                        let rings5 = Object.keys(data[5]);
                        let rings6 = Object.keys(data[6]);

                        let rings = [];
                        rings1.concat(rings2, rings3, rings4, rings5, rings6)
                            .forEach(item =>{
                                if (rings.indexOf(item) === -1)
                                    rings.push(item);
                            });

                        rings.sort((a,b) => {
                            let aMinutes = parseInt(a.substr(0,2)) * 60 + parseInt(a.substr(3,2));
                            let bMinutes = parseInt(b.substr(0,2)) * 60 + parseInt(b.substr(3,2));
                            return aMinutes < bMinutes ? -1 : 1;
                        });

                        if (!this.fastInputMode) {
                            this.scheduleRings = rings;
                        } else {
                            this.scheduleRings = this.allRings.map(r => r.Time.substr(0,5));
                        }

                        this.loading = false;

                        this.groupSchedule = data;
                    });

                axios
                    .get('/disciplinesByGroupInfo?groupId=' + this.studentGroupId)
                    .then(response => {
                        this.groupDisciplines = response.data.filter(d => d.active === 1);

                        if (this.groupDisciplinesWithTeacher.length > 0) {
                            this.disciplineClicked(this.groupDisciplinesWithTeacher[0]);
                        }
                    });
            },
            reloadDisciplines() {
                let save =  this.groupDisciplineSelected;

                axios
                    .get('/disciplinesByGroupInfo?groupId=' + this.studentGroupId)
                    .then(response => {
                        this.groupDisciplines = response.data.filter(d => d.active === 1);

                        let saved = this.groupDisciplinesWithTeacher.filter(d => d.disciplineId === save.disciplineId);

                        if (saved.length !== 0) {
                            this.disciplineClicked(saved[0]);
                        }
                    });
            },
            teacherBusy(teacherSchedule, dow, ring, empty) {
                if (empty === undefined) empty = "";
                if (teacherSchedule[dow] === undefined || teacherSchedule[dow][ring] === undefined) {
                    return empty;
                }
                let result = [];

                for(let tfdId in teacherSchedule[dow][ring]) {
                    Object.values(teacherSchedule[dow][ring][tfdId]['weeksAndAuds']).flat()
                        .forEach(item =>{
                            if ((result.indexOf(item) === -1) && (this.selectedWeeks.includes(item) || (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)))
                                result.push(item);
                        });
                }
                return (result.length === 0) ? empty : this.combineWeeksToRange(result);
            },
            teacherBusyArrays(dowArray, ringArray) {
                let result = [];

                for(let i = 0; i < dowArray.length; i++) {
                    let dow = dowArray[i];

                    for(let j = 0; j < ringArray.length; j++) {
                        let ring = this.allRings.filter(r => r.RingId == ringArray[j])[0].Time.substr(0,5);

                        if (this.disciplineTeacherSchedule[dow] !== undefined && this.disciplineTeacherSchedule[dow][ring] !== undefined) {

                            for (let tfdId in this.disciplineTeacherSchedule[dow][ring]) {
                                Object.values(this.disciplineTeacherSchedule[dow][ring][tfdId]['weeksAndAuds']).flat()
                                    .forEach(item => {
                                        if(result.indexOf(item) === -1)
                                            result.push(item);
                                    });
                            }
                        }
                    }
                }

                return result;
            },
            WeeksToStringOrEmpty(weeks, empty) {
                if (empty === undefined) empty = "";
                return (weeks.length === 0) ? empty : this.combineWeeksToRange(weeks);
            },
            newTfdChanged(groupDisciplineSelected) {
                this.newTfdBusyLoading = true;

                axios
                    .get('/api.php?action=teacherWeeksSchedule&teacherId=' + groupDisciplineSelected.teacherId + '&compactResult')
                    .then(response => {
                        this.disciplineTeacherSchedule = response.data;
                        this.newTfdBusyLoading = false;
                    });
            },
            setNewRingId(ring) {
                let ra = this.allRings.filter(r => ring === r.Time.substr(0,5));
                if (ra.length > 0) {
                    this.newRingIds = [];
                    this.newRingIds.push(ra[0].RingId);
                }
            },
            getRingFromAllRingsByFiveTime(time) {
                let ra = this.allRings.filter(r => time === r.Time.substr(0,5));
                return (ra.length > 0) ? ra[0] : null;
            },
            newRingToggled(ring) {
                if (this.newRingIds.includes(ring.RingId)) {
                    let index = this.newRingIds.indexOf(ring.RingId);
                    this.newRingIds.splice(index,1);
                }
                else {
                    this.newRingIds.push(ring.RingId);
                }

                axios
                    .get('/api.php?action=freeAuditoriums' +
                        '&dows=' + this.newDows.join('|') +
                        '&ringIds=' + this.newRingIds.join('|'))
                    .then(response => {
                        this.freeAuds = response.data;
                    });
            },
            disciplineClicked(discipline) {
              this.groupDisciplineSelected = discipline;

              axios
                  .get('/api.php?action=teacherWeeksSchedule&teacherId=' + this.groupDisciplineSelected.teacherId + '&compactResult')
                  .then(response => {
                      this.disciplineTeacherSchedule = response.data;
                  });
            },
            askForNew() {
                if (this.fastInputMode) {
                    let tfdId = this.groupDisciplineSelected.tfdId;
                    let dows = this.newDows.join('|');
                    let weeks = this.selectedWeeks[0];
                    let ringIds = this.newRingIds[0];

                    let blankAudId1 = this.auditoriums[0].id;
                    let aud1 = this.auditoriums.filter(a => a.name === '-');
                    if (aud1.length !== 0) {
                        blankAudId1 = aud1[0].id;
                    }

                    let blankAudId2 = this.auditoriums[0].id;
                    let aud2 = this.auditoriums.filter(a => a.name === '--');
                    if (aud2.length !== 0) {
                        blankAudId2 = aud2[0].id;
                    }

                    let blankAudId3 = this.auditoriums[0].id;
                    let aud3 = this.auditoriums.filter(a => a.name === '---');
                    if (aud3.length !== 0) {
                        blankAudId3 = aud3[0].id;
                    }

                    let groupNameStart = Object.values(this.studentGroups).filter(g => g.id === this.studentGroupId)[0].name.split(' ')[0];
                    const start = ['1', '2', '3', '4'];
                    const five = ['5'];
                    const six = ['6'];
                    const finish = ['7', '8', '9', '10', '11'];

                    let blankAudId = this.auditoriums[0].id;

                    if (start.includes(groupNameStart)) {
                        blankAudId = blankAudId1;
                    }

                    if (finish.includes(groupNameStart)) {
                        blankAudId = blankAudId2;
                    }

                    if ((five.includes(groupNameStart)) && ([1,3,5].includes(Math.min(...this.newDows)))) {
                        blankAudId = blankAudId2;
                    }
                    if ((five.includes(groupNameStart)) && ([2,4,6].includes(Math.min(...this.newDows)))) {
                        blankAudId = blankAudId3;
                    }

                    if ((six.includes(groupNameStart)) && ([1,3,5].includes(Math.min(...this.newDows)))) {
                        blankAudId = blankAudId3;
                    }
                    if ((six.includes(groupNameStart)) && ([2,4,6].includes(Math.min(...this.newDows)))) {
                        blankAudId = blankAudId2;
                    }

                    let blankAudIdIl = blankAudId3;
                    let audIl = this.auditoriums.filter(a => a.name === 'Корп № 3 Спортивный зал');
                    if (audIl.length !== 0) {
                        blankAudIdIl = audIl[0].id;
                    }

                    if (this.groupDisciplineSelected.teacherFio === "Ильичев Евгений Борисович") {
                        blankAudId = blankAudIdIl;
                    }

                    let weeksAuds = this.selectedWeeks[0] + '@' + blankAudId;

                    this.loading = true;
                    axios
                        .post('/lessonsGroupScheduleAdd' +
                            '?tfdId=' + tfdId +
                            '&dows=' + dows +
                            '&weeks=' + weeks +
                            '&ringIds=' + ringIds +
                            '&weeksAuds=' + weeksAuds
                        )
                        .then(response => {
                            this.loadGroupSchedule();
                        });

                    return;
                }

                this.newSelectedWeeks = [];
                for(let i = 1; i <= this.weeksCount; i++) {
                    this.newWeeksAuds[i] = -1;
                }

                axios
                    .get('/api.php?action=freeAuditoriums' +
                        '&dows=' + this.newDows.join('|') +
                        '&ringIds=' + this.newRingIds.join('|'))
                    .then(response => {
                        this.freeAuds = response.data;
                        this.showNewWindow = true;
                    });
            },
            askForDelete(lessons) {
                this.lessonsToDelete = lessons;
                this.deletePrompt = true;
            },
            getAuditoriumByName(name) {
                let auds = this.auditoriums.filter(a => a.name === name);
                return (auds.length > 0) ? auds[0] : {};
            },
            newSingleAudChanged() {
                for(let i = 0; i < this.newSelectedWeeks.length; i++) {
                    this.newWeeksAuds[this.newSelectedWeeks[i]] = this.newSingleAudId;
                }
            },
            newDowToggled(dow) {
                if (this.newDows.includes(dow)) {
                    if (this.newDows.length !== 1) {
                        let index = this.newDows.indexOf(dow);
                        this.newDows.splice(index, 1);
                    }
                }
                else {
                    this.newDows.push(dow);
                }

                axios
                    .get('/api.php?action=freeAuditoriums' +
                        '&dows=' + this.newDows.join('|') +
                        '&ringIds=' + this.newRingIds.join('|'))
                    .then(response => {
                        this.freeAuds = response.data;
                    });
            },
            timeFromRingId(ringId) {
              let ringArray = this.allRings.filter(r => r.RingId == ringId);

              return (ringArray.length !== 0) ? ringArray[0].time.substr(0,5) : "";
            },
            ringFromTime(time) {
                let ringArray = this.allRings.filter(r => r.Time.substr(0,5) === time);

                return (ringArray.length !== 0) ? ringArray[0] : "";
            },
            askForEdit(lessonsData, dow, time) {
                this.editDow = dow;
                this.editRing = this.ringFromTime(time);

                let r = {};
                for(let i = 1; i <= this.weeksCount; i++) {
                    r[i] = -1;
                }

                for(let key in lessonsData["weeksAndAuds"])
                {
                    for(let i = 0; i < lessonsData["weeksAndAuds"][key].length; i++) {
                        r[lessonsData["weeksAndAuds"][key][i]] = this.getAuditoriumByName(key).id;
                    }
                }
                this.editWeeksAuds = r;

                axios
                    .get('/api.php?action=freeAuditoriums' +
                        '&dows=' + dow +
                        '&ringIds=' + this.getRingFromAllRingsByFiveTime(time).RingId)
                    .then(response => {
                        this.editFreeAuds = response.data;

                        this.editSelectedWeeks = Object.values(lessonsData['weeksAndAuds']).flat().sort((a,b) => {return a-b;});
                        this.lessonsDataToEdit = lessonsData;
                        this.showEditWindow = true;
                    });

                axios
                    .get('/api.php?action=teacherWeeksSchedule&teacherId=' + lessonsData.lessons[0].teacherId + '&compactResult')
                    .then(response => {
                        this.editDisciplineTeacherSchedule = response.data;
                    });
            },
            deleteLessons() {
                let IdsString = this.lessonsToDelete.map(l => l.lessonId).join('|');

                let destroyUrl = '/lessonsDestroyByIds?Ids=' + IdsString;

                axios
                    .post(destroyUrl)
                    .then(response => {
                        this.loadGroupSchedule();
                    });
            },
            saveLessonsNew() {
                this.addLoading = true;
                let tfdId = this.groupDisciplineSelected.tfdId;
                let dows = this.newDows.join('|');
                let weeks = this.newSelectedWeeks.join('|');
                let ringIds = this.newRingIds.join('|');
                let weeksAuds = this.newSelectedWeeks.map(w => w + '@' + this.newWeeksAuds[w]).join('|');

                axios
                    .post('/lessonsGroupScheduleAdd' +
                        '?tfdId=' + tfdId +
                        '&dows=' + dows +
                        '&weeks=' + weeks +
                        '&ringIds=' + ringIds +
                        '&weeksAuds=' + weeksAuds
                    )
                    .then(response => {
                        this.addLoading = false;
                        this.showNewWindow = false;
                        this.loadGroupSchedule();
                    });

            },
            saveLessonsEdit() {
                let oldWeeks = Object.values(this.lessonsDataToEdit['weeksAndAuds']).flat().sort((a,b) => {return a-b;});

                let r = {};

                for(let key in this.lessonsDataToEdit["weeksAndAuds"])
                {
                    for(let i = 0; i < this.lessonsDataToEdit["weeksAndAuds"][key].length; i++) {
                        r[this.lessonsDataToEdit["weeksAndAuds"][key][i]] = this.getAuditoriumByName(key).id;
                    }
                }
                let oldEditWeeksAuds = r;

                let weeksToAdd = this.editSelectedWeeks.filter(w => !oldWeeks.includes(w));
                let addString = weeksToAdd.map(w => w + '@' + this.editWeeksAuds[w]).join('|');

                let weeksRemoved = oldWeeks.filter(w => !this.editSelectedWeeks.includes(w));
                let removeString = weeksRemoved.join('|');

                let changedAuditoriumsString = oldWeeks
                    .filter(w =>
                        this.editSelectedWeeks.includes(w) &&
                        oldEditWeeksAuds[w] !== this.editWeeksAuds[w]
                    )
                    .map(w => w + '@' + this.editWeeksAuds[w])
                    .join('|');

                axios
                    .post('/lessonsWeeksAndAudsEdit' +
                        '?tfdId=' + this.lessonsDataToEdit["lessons"][0].tfdId +
                        '&ringId=' + this.lessonsDataToEdit["lessons"][0].ringId +
                        '&dow=' + this.lessonsDataToEdit["lessons"][0].dow +
                        '&add=' + addString +
                        '&remove=' + removeString +
                        '&changeAuditorium=' + changedAuditoriumsString)
                    .then(response => {
                        this.loadGroupSchedule();
                    });
            },
            loadFullGroupSchedule() {
                this.severalWeeks = true;
                this.selectedWeeks = [];
                this.selectedWeeks.push(-1);
                this.loadGroupSchedule();
            },
            combineWeeksToRange(ws) {
                let weeks = ws.slice(0);

                if (weeks.length === 1 && weeks[0] === -1) {
                    weeks = this.range(1, this.weekCount);
                }

                let min = Math.min(...weeks);
                let max = Math.max(...weeks);

                let result = [];

                let prev = false;
                let baseNum = max+3;
                for (let i = min-1; i <= max+1; i++) {
                    if ((prev === false) && (weeks.includes(i)))
                    {
                        baseNum = i;
                    }

                    if ((!weeks.includes(i)) && ((i - baseNum) > 2))
                    {
                        result.push(baseNum +  "-" + (i - 1).toString());
                        for (let k = baseNum; k < i; k++)
                        {
                            let index = weeks.indexOf(k);
                            if (index > -1) {
                                weeks.splice(index, 1);
                            }
                        }
                    }

                    if (!weeks.includes(i))
                        baseNum = max+3;
                    prev = weeks.includes(i);
                }

                prev = false;
                baseNum = max+3;
                for(let i = ((min % 2 === 1) ? (min-2) : (min-1)); i <= max+3; i = i + 2)
                {
                    if ((prev === false) && (weeks.includes(i)))
                    {
                        baseNum = i;
                    }
                    if ((!weeks.includes(i)) && ((i - baseNum) > 4))
                    {
                        result.push(baseNum + "-" + (i - 2).toString() + " (нечёт.)");
                        for (let k = baseNum; k < i; k = k + 2)
                        {
                            let index = weeks.indexOf(k);
                            if (index > -1) {
                                weeks.splice(index, 1);
                            }
                        }
                    }
                    if (!weeks.includes(i))
                        baseNum = max+3;
                    prev = weeks.includes(i);
                }

                prev = false;
                baseNum = max+3;
                for(let i = ((min % 2 === 0) ? (min-2) : (min-1)); i <= max+3; i = i + 2)
                {
                    if ((prev === false) && (weeks.includes(i)))
                    {
                        baseNum = i;
                    }
                    if ((!weeks.includes(i)) && ((i - baseNum) > 4))
                    {
                        result.push(baseNum +  "-" + (i - 2).toString() + " (чёт.)");
                        for (let k = baseNum; k < i; k = k + 2)
                        {
                            let index = weeks.indexOf(k);
                            if (index > -1) {
                                weeks.splice(index, 1);
                            }
                        }
                    }
                    if (!weeks.includes(i))
                        baseNum = max+3;
                    prev = weeks.includes(i);
                }

                for (let index = 0; index < weeks.length; index++) {
                    result.push(weeks[index]);
                }

                result.sort((a,b) => {
                    let aVal = parseInt((a.toString().indexOf('-') === -1) ? a : a.toString().substr(0, a.toString().indexOf('-')));
                    let bVal = parseInt((b.toString().indexOf('-') === -1) ? b : b.toString().substr(0, b.toString().indexOf('-')));

                    if (aVal === bVal) return 0;
                    return (aVal < bVal) ? -1 : 1;
                });

                let stringResult = result.join(', ');

                return stringResult;
            },
            range(start, end) {
                return Array(end - start + 1).fill().map((_, idx) => start + idx)
            },
            showGroup(studentGroupId, disciplineStudentGroupName) {
                let group = Object.values(this.groups).filter(g => g.id == studentGroupId)[0];

                return group.name !== disciplineStudentGroupName;
            },
            severalWeeksSwitchFlipped() {
                if (!this.severalWeeks) {
                    let min = 1;
                    if (!(this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)) {
                        min = Math.min(...this.selectedWeeks);
                    }
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(min);
                    this.loadGroupSchedule();
                } else {
                    this.fastInputMode = false;
                }
            },
            weekToggled(week) {
                if (!this.severalWeeks) {
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(week);
                    this.loadGroupSchedule();
                }
                else {
                    if (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1) {
                        this.selectedWeeks = [];
                    }

                    if (this.selectedWeeks.length === 1 && event.shiftKey) {
                        if (week < this.selectedWeeks[0]) {
                            for(let i = week; i < this.selectedWeeks[0]; i++) {
                                this.selectedWeeks.push(i);
                            }
                            this.loadGroupSchedule()
                        }

                        if (week > this.selectedWeeks[0]) {
                            for(let i = this.selectedWeeks[0]+1; i <= week; i++) {
                                this.selectedWeeks.push(i);
                            }
                            this.loadGroupSchedule()
                        }

                        return;
                    }

                    if (event.ctrlKey)
                    {
                        this.selectedWeeks = [];
                        this.selectedWeeks.push(week);
                        this.loadGroupSchedule();

                        return;
                    }

                    if (!this.selectedWeeks.includes(week))
                    {
                        this.selectedWeeks.push(week);
                    }
                    else
                    {
                        let index = this.selectedWeeks.indexOf(week);
                        this.selectedWeeks.splice(index, 1);
                    }
                    this.loadGroupSchedule();
                }
            },
            editWeekToggled(week) {
                if (this.editSelectedWeeks.length === 1 && event.shiftKey) {
                    if (week < this.editSelectedWeeks[0]) {
                        for(let i = week; i < this.editSelectedWeeks[0]; i++) {
                            if (!this.editSelectedWeeks.includes(i) && this.editWeeksAuds[i] === -1  && this.auditoriums.length > 0) {
                                this.editWeeksAuds[i] = this.auditoriumsSorted[0].id;
                            }
                            this.editSelectedWeeks.push(i);
                        }
                    }

                    if (week > this.editSelectedWeeks[0]) {
                        for(let i = this.editSelectedWeeks[0]+1; i <= week; i++) {
                            if (!this.editSelectedWeeks.includes(i) && this.editWeeksAuds[i] === -1 && this.auditoriums.length > 0) {
                                this.editWeeksAuds[i] = this.auditoriumsSorted[0].id;
                            }
                            this.editSelectedWeeks.push(i);
                        }
                    }

                    return;
                }

                if (event.ctrlKey)
                {
                    this.editSelectedWeeks = [];
                    this.editSelectedWeeks.push(week);
                    if (this.editWeeksAuds[week] === -1 && this.auditoriums.length > 0) {
                        this.editWeeksAuds[week] = this.auditoriumsSorted[0].id;
                    }

                    return;
                }

                if (!this.editSelectedWeeks.includes(week))
                {
                    if (this.editWeeksAuds[week] === -1 && this.auditoriums.length > 0) {
                        this.editWeeksAuds[week] = this.auditoriumsSorted[0].id;
                    }
                    this.editSelectedWeeks.push(week);
                }
                else
                {
                    let index = this.editSelectedWeeks.indexOf(week);
                    this.editSelectedWeeks.splice(index, 1);
                }
            },
            newWeekToggled(week) {
                let blankAudId1 = this.auditoriums[0].id;
                let aud1 = this.auditoriums.filter(a => a.name === '-');
                if (aud1.length !== 0) {
                    blankAudId1 = aud1[0].id;
                }

                let blankAudId2 = this.auditoriums[0].id;
                let aud2 = this.auditoriums.filter(a => a.name === '--');
                if (aud2.length !== 0) {
                    blankAudId2 = aud2[0].id;
                }

                let blankAudId3 = this.auditoriums[0].id;
                let aud3 = this.auditoriums.filter(a => a.name === '---');
                if (aud3.length !== 0) {
                    blankAudId3 = aud3[0].id;
                }

                let groupNameStart = Object.values(this.studentGroups).filter(g => g.id === this.studentGroupId)[0].name.split(' ')[0];
                console.log('groupNameStart');
                console.log(groupNameStart);
                const start = ['1', '2', '3', '4'];
                const five = ['5'];
                const six = ['6'];
                const finish = ['7', '8', '9', '10', '11'];

                let blankAudId = this.auditoriums[0].id;

                if (start.includes(groupNameStart)) {
                    blankAudId = blankAudId1;
                }

                if (finish.includes(groupNameStart)) {
                    blankAudId = blankAudId2;
                }

                if ((five.includes(groupNameStart)) && ([1,3,5].includes(Math.min(...this.newDows)))) {
                    blankAudId = blankAudId2;
                }
                if ((five.includes(groupNameStart)) && ([2,4,6].includes(Math.min(...this.newDows)))) {
                    blankAudId = blankAudId3;
                }

                if ((six.includes(groupNameStart)) && ([1,3,5].includes(Math.min(...this.newDows)))) {
                    blankAudId = blankAudId3;
                }
                if ((six.includes(groupNameStart)) && ([2,4,6].includes(Math.min(...this.newDows)))) {
                    blankAudId = blankAudId2;
                }

                let blankAudIdIl = blankAudId3;
                let audIl = this.auditoriums.filter(a => a.name === 'Корп № 3 Спортивный зал');
                if (audIl.length !== 0) {
                    blankAudIdIl = audIl[0].id;
                }

                if (this.groupDisciplineSelected.teacherFio === "Ильичев Евгений Борисович") {
                    blankAudId = blankAudIdIl;
                }

                if (this.newSelectedWeeks.length === 1 && event.shiftKey) {
                    if (week < this.newSelectedWeeks[0]) {
                        for(let i = week; i < this.newSelectedWeeks[0]; i++) {
                            if (!this.newSelectedWeeks.includes(i) && this.newWeeksAuds[i] === -1 && this.auditoriums.length > 0) {
                                this.newWeeksAuds[i] = blankAudId;
                            }
                            this.newSelectedWeeks.push(i);
                        }
                    }

                    if (week > this.newSelectedWeeks[0]) {
                        for(let i = this.newSelectedWeeks[0]+1; i <= week; i++) {
                            if (!this.newSelectedWeeks.includes(i) && this.newWeeksAuds[i] === -1 && this.auditoriums.length > 0) {
                                this.newWeeksAuds[i] = blankAudId;
                            }
                            this.newSelectedWeeks.push(i);
                        }
                    }

                    return;
                }

                if (event.ctrlKey)
                {
                    this.newSelectedWeeks = [];
                    this.newSelectedWeeks.push(week);
                    if (this.newWeeksAuds[week] === -1 && this.auditoriums.length > 0) {
                        this.newWeeksAuds[week] = blankAudId;
                    }

                    return;
                }

                if (!this.newSelectedWeeks.includes(week))
                {
                    if (this.newWeeksAuds[week] === -1 && this.auditoriums.length > 0) {
                        this.newWeeksAuds[week] = blankAudId;
                    }
                    this.newSelectedWeeks.push(week);
                }
                else
                {
                    let index = this.newSelectedWeeks.indexOf(week);
                    this.newSelectedWeeks.splice(index, 1);
                }
            },
            newLessonsWeeksAudsFree(week) {
                let first = true;
                let resultIds = [];

                for(let dowIndex = 0; dowIndex < this.newDows.length; dowIndex++) {
                    let dow = this.newDows[dowIndex];
                    if (this.freeAuds[dow] === undefined)
                        continue;

                    for(let ringIdIndex = 0; ringIdIndex < this.newRingIds.length; ringIdIndex++) {
                        let ringId = this.newRingIds[ringIdIndex];

                        if (first) {
                            resultIds = this.freeAuds[dow][week][ringId];
                            first = false;
                        } else {
                            for(let ri = 0; ri < resultIds.length; ri++)
                            {
                                if (this.freeAuds[dow][week][ringId] !== undefined && this.freeAuds[dow][week][ringId].indexOf(resultIds[ri]) === -1) {
                                    resultIds.splice(ri,1);
                                    ri--;
                                }
                            }
                        }
                    }
                }

                let audsInfo = [];
                for(let i=0; i < this.auditoriumsSorted.length; i++) {
                    let aud = this.auditoriumsSorted[i];

                    audsInfo.push({
                        'id': aud.id,
                        'name': aud.name,
                        'free': resultIds.includes(aud.id)
                    });
                }

                return audsInfo;
            },
            editLessonsWeeksAudsFree(week) {
                let resultIds = [];

                let dow = this.lessonsDataToEdit["lessons"][0].dow;
                let ringId = this.lessonsDataToEdit["lessons"][0].ringId;

                resultIds = (this.editFreeAuds !== undefined) ? this.editFreeAuds[dow][week][ringId] : [];

                let audsInfo = [];
                for (let i = 0; i < this.auditoriumsSorted.length; i++) {
                    let aud = this.auditoriumsSorted[i];

                    audsInfo.push({
                        'id': aud.id,
                        'name': aud.name,
                        'free': resultIds.includes(aud.id)
                    });
                }

                return audsInfo;
            },
            fastInputModeToggled() {
                if (this.fastInputMode) {
                    this.scheduleRings = this.allRings.map(r => r.Time.substr(0,5));
                }
            },
        },
        mounted() {
            axios
                .get('/api.php?action=list&listtype=rings')
                .then(response => {
                    this.allRings = response.data.sort((a,b) => {
                        if (a.Time === b.Time) return 0;

                        let aMoment = moment(a.Time,"HH:mm:ss");
                        let bMoment = moment(b.Time,"HH:mm:ss");

                        return aMoment < bMoment ? -1 : 1;
                    });
                });

            if (this.studentGroupId === -1)
            {
                if (this.groups.length !== 0) {
                    this.studentGroupId = this.groupsSorted[0].id;
                    this.selectedWeeks = [-1];
                    this.loadGroupSchedule();
                }
            } else {
                this.selectedWeeks = [-1];
                this.loadGroupSchedule();
            }
        },
        computed: {
            newLessonsAllWeeksAudsFree() {
                let first = true;
                let resultIds = [];

                for(let dowIndex = 0; dowIndex < this.newDows.length; dowIndex++) {
                    let dow = this.newDows[dowIndex];
                    for(let weekIndex = 0; weekIndex < this.newSelectedWeeks.length; weekIndex++) {
                        let week = this.newSelectedWeeks[weekIndex];
                        for(let ringIdIndex = 0; ringIdIndex < this.newRingIds.length; ringIdIndex++) {
                            let ringId = this.newRingIds[ringIdIndex];
                            if (this.freeAuds[dow] != undefined && week in this.freeAuds[dow]) {
                                if (first) {
                                    resultIds = this.freeAuds[dow][week][ringId];
                                    first = false;
                                } else {
                                    for (let ri = 0; ri < resultIds.length; ri++) {
                                        if (this.freeAuds[dow][week][ringId] !== undefined && this.freeAuds[dow][week][ringId].indexOf(resultIds[ri]) === -1) {
                                            resultIds.splice(ri, 1);
                                            ri--;
                                        }
                                    }
                                }
                            } else {
                                resultIds = [];
                            }
                        }
                    }
                }

                let audsInfo = [];
                for(let i=0; i < this.auditoriumsSorted.length; i++) {
                    let aud = this.auditoriumsSorted[i];

                    audsInfo.push({
                        'id': aud.id,
                        'name': aud.name,
                        'free': resultIds.includes(aud.id)
                    });
                }

                return audsInfo;
            },
            groupDisciplinesWithTeacher() {
              return this.groupDisciplines.filter(d => d.tfdId !== null);
            },
            auditoriumsSorted() {
              return this.auditoriums
                  .sort((a,b) => {
                      if (a.name === b.name) return 0;
                      return a.name < b.name ? -1 : 1;
                  })
            },
            groupsSorted() {
                let result = [];
                for (let index in this.groups) {
                    let group = this.groups[index];
                    result.push(group);
                }

                result.sort((a,b) => {
                    let num1 = parseInt(a.name.split(' ')[0]);
                    let num2 = parseInt(b.name.split(' ')[0]);

                    if (num1 === num2) {
                        if (a === b) return 0;
                        return (a.name < b.name) ? -1 : 1;
                    }
                    else
                    {
                        return (num1 < num2) ? -1 : 1;
                    }
                });

                return result;
            }
        }
    }
</script>

<style scoped>
    .isDanger {
        background-color: #ff3860;
        border-color: transparent;
        color: white;
    }

    .isPrimary {
        background-color: #7957d5;
        border-color: transparent;
        color: white;
    }

    .smallFont {
        font-size: 0.8em;
    }

    .budget {
        background-color: rgba(255,255,0,0.2);
    }

    .vneur {
        background-color: rgba(20,255,0,0.2);
    }

    .plat {
        background-color: rgba(23,67,255,0.2);
    }
</style>
