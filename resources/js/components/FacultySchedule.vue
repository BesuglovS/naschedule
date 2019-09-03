<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12" style="display:flex;">
                <div class="card">
                    <div class="card-header">
                        <span style="margin-right: 1em;">
                            Расписание параллели
                        </span>

                        <button @click="loadFacultySchedule();"
                                style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                class="button isPrimary">R
                        </button>
                    </div>

                    <div class="card-body">
                        <select v-model="facId" @change="facultyChanged()">
                            <option v-for="faculty in facultiesSorted" :value="faculty.id">{{faculty.name}}</option>
                        </select>

                        Недели: {{combineWeeksToRange(this.selectedWeeks)}}

                        <div id="groupSchedule" style="margin-top: 1em;">
                            <div style="text-align: center;">
                                Недели:
                                <button @click="loadFullFacultySchedule();"
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
                                    'is-danger': selectedWeeks.includes(week) }"
                                >{{week}}</button>

                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="severalWeeks" @change="severalWeeksSwitchFlipped();" class="custom-control-input" id="customSwitch1">
                                    <label class="custom-control-label" for="customSwitch1">Несколько недель</label>
                                </div>
                            </div>

                            <div v-if="loading === true" style="font-size: 2em; text-align: center">
                                Загрузка расписания <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                            </div>

                            <table v-if="loading === false" style="margin-top: 2em; " class="table td-center is-bordered">
                                <tr>
                                    <td></td>
                                    <td v-for="groupSchedule in facultySchedule">
                                        <strong>
                                            {{groupSchedule.groupName}}
                                        </strong>
                                    </td>
                                </tr>

                                <template v-for="dow in 6">
                                    <tr>
                                        <td></td>
                                        <td v-for="groupIndex in facultySchedule.length">
                                            <strong>{{dowRu[dow-1]}}</strong>
                                            <template v-if="Object.keys(facultyDisciplines).length > 0">
                                                <a @click.prevent="newDows = []; newDows.push(dow); newRingIds = []; newGroupId = facultySchedule[groupIndex].groupId; askForNew();" href="#">
                                                    <font-awesome-icon icon="plus-square" />
                                                </a>
                                            </template>
                                        </td>
                                    </tr>
                                    <tr v-for="ring in scheduleRings[dow]">
                                        <td>
                                            <strong>
                                                {{ring}}
                                            </strong>
                                        </td>
                                        <td v-for="groupSchedule in facultySchedule">
                                            <a v-if="Object.keys(facultyDisciplines).length > 0 && groupSchedule['lessons'][dow][ring] === undefined"
                                               @click.prevent="newDows = []; newDows.push(dow); setNewRingId(ring); newGroupId = groupSchedule.groupId; askForNew();" href="#">
                                                <font-awesome-icon icon="plus-square" />
                                            </a>
                                            <template v-if="groupSchedule['lessons'][dow][ring] !== undefined">
                                                <template v-for="tfd in sortTfd(groupSchedule['lessons'][dow][ring])">
                                                    <table style="width: 100%; text-align: center; border:none !important;">
                                                        <tr>
                                                            <td style="border:none;"><a @click.prevent="askForEdit(groupSchedule['lessons'][dow][ring][tfd], dow, ring);" href="#"><font-awesome-icon icon="edit" /></a></td>
                                                            <td style="border:none;" v-if="Object.keys(facultyDisciplines).length > 0 &&
                                                                 tfd === sortTfd(groupSchedule['lessons'][dow][ring])[0]">
                                                                <a v-if="" @click.prevent="newDows = []; newDows.push(dow); setNewRingId(ring); newGroupId = groupSchedule.groupId; askForNew();" href="#">
                                                                    <font-awesome-icon icon="plus-square" />
                                                                </a>
                                                            </td>
                                                            <td style="border:none;">
                                                                <button type="button" @click="askForDelete(groupSchedule['lessons'][dow][ring][tfd].lessons);" class="close">×</button>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <strong>{{groupSchedule['lessons'][dow][ring][tfd]["lessons"][0]["discName"]}}</strong><br />
                                                    {{groupSchedule['lessons'][dow][ring][tfd]["lessons"][0]["teacherFIO"]}} <br />
                                                    <template v-for="auditorium in
                                                        Object.keys(groupSchedule['lessons'][dow][ring][tfd]['weeksAndAuds'])
                                                            .sort((a,b) => {
                                                            let aMin = Math.min(...groupSchedule['lessons'][dow][ring][tfd]['weeksAndAuds'][a]);
                                                            let bMin = Math.min(...groupSchedule['lessons'][dow][ring][tfd]['weeksAndAuds'][b]);

                                                            if (aMin === bMin) return 0;
                                                            return aMin < bMin ? -1 : 1;
                                                        })
                                                    ">
                                                        {{combineWeeksToRange(groupSchedule['lessons'][dow][ring][tfd]["weeksAndAuds"][auditorium])}} - {{auditorium}}<br />
                                                    </template>
                                                    <template v-if="tfd !== Object.keys(groupSchedule['lessons'][dow][ring])
                                                        .sort((a,b) => {
                                                            let aMin = Math.min(...Object.values(groupSchedule['lessons'][dow][ring][a]['weeksAndAuds']).flat());
                                                            let bMin = Math.min(...Object.values(groupSchedule['lessons'][dow][ring][b]['weeksAndAuds']).flat());

                                                            if (aMin === bMin) {
                                                                let aGroupName = groupSchedule['lessons'][dow][ring][a]['lessons'][0]['groupName'];
                                                                let bGroupName = groupSchedule['lessons'][dow][ring][b]['lessons'][0]['groupName'];

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
                                                        [Object.keys(groupSchedule['lessons'][dow][ring]).length-1]">
                                                        <hr>
                                                    </template>
                                                </template>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-left: 1em;">
                    <div class="card-header">Дисциплины параллели</div>

                    <div class="card-body" style="text-align: center;">
                        <div v-if="loading === true" style="font-size: 2em; text-align: center">
                            Загрузка дисциплин <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                        </div>

                        <div v-if="loadingDisciplines === false" v-for="(disciplineData, disciplineName) in facultyDisciplines">

                            <button
                                @click="facultyDisciplineClicked(disciplineName)"
                                :class="{'isPrimary': disciplineName !== facultyDisciplineSelectedName,
                                        'isDanger': disciplineName === facultyDisciplineSelectedName }"
                                style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;" >
                                {{disciplineName}}
                                <br />
                                <span style="font-size:0.6em;">
                                    <p v-for="teacherInfo in disciplineData.GroupsAndTeachers">
                                        {{teacherInfo}}
                                    </p>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <modal v-if="showNewWindow">
            <template v-slot:header>
                <span style="font-size: 2em;">Новые уроки</span>

                <span v-if="newTfdBusyLoading === true">
                    <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </span>

                <span v-if="newTfdBusyLoading === false" style="font-size: 2em; color: red;">
<!--                    {{WeeksToStringOrEmpty(teacherBusyArrays(newDows, newRingIds), '-')}}-->
                </span>

                <select v-model="groupDisciplineSelected" @change="newTfdChanged(groupDisciplineSelected)">
                    <option v-for="gd in newDisciplines" :value="gd">
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
                                <option v-for="aud in newLessonsAllWeeksAudsFree()"
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
    </div>
</template>

<script>
    import modal from './Modal';
    import moment from "moment";

    export default {
        name: "FacultySchedule",
        props: {
            'auditoriums': Array,
            'faculties': Array,
            'facultyId': Number,
            'weekCount': Number,
        },
        components: {
            'modal' : modal
        },
        data() {
            return {
                facultiesList: this.faculties,
                facId: this.facultyId,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                firstLoad: true,
                loading: false,
                weeksCount: this.weekCount,
                scheduleRings: {},
                selectedWeeks: [],
                severalWeeks: true,
                facultySchedule: {},
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                facultyDisciplines: {},
                facultyDisciplineSelectedName: "",
                loadingDisciplines: false,
                showNewWindow: false,
                newSelectedWeeks: [],
                newWeeksAuds: {},
                newDows: [],
                newRingIds: [],
                newTfdBusyLoading: false,
                allRings: [],
                groupDisciplineSelected: {},
                newSingleAudId: -1,
                addLoading: false,
                newGroupId: -1,
                newDisciplines: [],
                deletePrompt: false,
                showEditWindow: false,
                lessonsDataToEdit: {},
                editDow: "",
                editRing: "",
                editSelectedWeeks: [],
                editWeeksAuds: {},
                editFreeAuds: {},
                editDisciplineTeacherSchedule: {},
            }
        },
        methods: {
            facultyChanged() {
                this.loadFacultySchedule();

                this.loadingDisciplines = true;
                axios
                    .get('/disciplinesByFacultyInfo?facultyId=' + this.facId)
                    .then(response => {
                        this.facultyDisciplines = response.data;
                        this.loadingDisciplines = false;

                        if (typeof this.facultyDisciplines.length === "undefined") {
                            this.facultyDisciplineClicked(Object.keys(this.facultyDisciplines)[0]);
                        }
                    });
            },
            loadFacultySchedule() {
                this.loading = true;
                this.firstLoad = false;

                let apiUrl = '/api.php?action=facultyWeeksSchedule&facultyId=' + this.facId + '&weeks=' + this.selectedWeeks.join('|');

                if (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)
                {
                    let weeksString = this.range(1, this.weekCount).join('|');

                    apiUrl = '/api.php?action=facultyWeeksSchedule&facultyId=' + this.facId + '&weeks=' + weeksString;
                }

                axios
                    .get(apiUrl)
                    .then(response => {
                        let data = response.data;

                        for(let i = 0; i < data.length; i++) {

                            let groupLessons = data[i]['lessons'];

                            this.scheduleRings = {};
                            for(let dow = 1; dow <= 6; dow++) {
                                let dowGroupRings = Object.keys(groupLessons[dow]);

                                let dowRings = (this.scheduleRings[dow] !== undefined) ? this.scheduleRings[dow] : [];
                                dowGroupRings
                                    .forEach(item => {
                                        if (dowRings.indexOf(item) === -1)
                                            dowRings.push(item);
                                    });

                                this.scheduleRings[dow] = dowRings;
                            }

                        }

                        for(let dow = 1; dow <= 6; dow++) {
                            this.scheduleRings[dow].sort((a,b) => {
                                let aMinutes = parseInt(a.substr(0,2)) * 60 + parseInt(a.substr(3,2));
                                let bMinutes = parseInt(b.substr(0,2)) * 60 + parseInt(b.substr(3,2));
                                return aMinutes < bMinutes ? -1 : 1;
                            });
                        }

                        this.facultySchedule = data;
                        this.loading = false;
                    });
            },
            facultyDisciplineClicked(disciplineName) {
                this.facultyDisciplineSelectedName = disciplineName;
            },
            loadFullFacultySchedule() {
                this.severalWeeks = true;
                this.selectedWeeks = [];
                this.selectedWeeks.push(-1);
                this.loadFacultySchedule();
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
            severalWeeksSwitchFlipped() {
                if (!this.severalWeeks) {
                    let min = 1;
                    if (!(this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)) {
                        min = Math.min(...this.selectedWeeks);
                    }
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(min);
                    this.loadFacultySchedule();
                }
            },
            weekToggled(week) {
                if (!this.severalWeeks) {
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(week);
                    this.loadFacultySchedule();
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
                            this.loadFacultySchedule()
                        }

                        if (week > this.selectedWeeks[0]) {
                            for(let i = this.selectedWeeks[0]+1; i <= week; i++) {
                                this.selectedWeeks.push(i);
                            }
                            this.loadFacultySchedule()
                        }

                        return;
                    }

                    if (event.ctrlKey)
                    {
                        this.selectedWeeks = [];
                        this.selectedWeeks.push(week);
                        this.loadFacultySchedule();

                        return;
                    }

                    if (!this.selectedWeeks.includes(week))
                    {
                        this.selectedWeeks.push(week);
                    }
                    else
                    {
                        let index = this.selectedWeeks.indexOf(week);
                        console.log();
                        this.selectedWeeks.splice(index, 1);
                    }
                    this.loadFacultySchedule();
                }
            },
            sortTfd(lessons) {
                return Object.keys(lessons)
                    .sort((a,b) => {
                        let aMin = Math.min(...Object.values(lessons[a]['weeksAndAuds']).flat());
                        let bMin = Math.min(...Object.values(lessons[b]['weeksAndAuds']).flat());

                        if (aMin === bMin) {
                            let aGroupName = lessons[a]['lessons'][0]['groupName'];
                            let bGroupName = lessons[b]['lessons'][0]['groupName'];

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
                    });
            },
            setNewRingId(ring) {
                let ra = this.allRings.filter(r => ring === r.Time.substr(0,5));
                if (ra.length > 0) {
                    this.newRingIds = [];
                    this.newRingIds.push(ra[0].RingId);
                }
            },
            WeeksToStringOrEmpty(weeks, empty) {
                if (empty === undefined) empty = "";
                return (weeks.length === 0) ? empty : this.combineWeeksToRange(weeks);
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
            newWeekToggled(week) {
                if (this.newSelectedWeeks.length === 1 && event.shiftKey) {
                    if (week < this.newSelectedWeeks[0]) {
                        for(let i = week; i < this.newSelectedWeeks[0]; i++) {
                            if (!this.newSelectedWeeks.includes(i) && this.newWeeksAuds[i] === -1 && this.auditoriums.length > 0) {
                                this.newWeeksAuds[i] = this.auditoriumsSorted[0].id;
                            }
                            this.newSelectedWeeks.push(i);
                        }
                    }

                    if (week > this.newSelectedWeeks[0]) {
                        for(let i = this.newSelectedWeeks[0]+1; i <= week; i++) {
                            if (!this.newSelectedWeeks.includes(i) && this.newWeeksAuds[i] === -1 && this.auditoriums.length > 0) {
                                this.newWeeksAuds[i] = this.auditoriumsSorted[0].id;
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
                        this.newWeeksAuds[week] = this.auditoriumsSorted[0].id;
                    }

                    return;
                }

                if (!this.newSelectedWeeks.includes(week))
                {
                    if (this.newWeeksAuds[week] === -1 && this.auditoriums.length > 0) {
                        this.newWeeksAuds[week] = this.auditoriumsSorted[0].id;
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
                        this.loadFacultySchedule();
                    });

            },
            askForNew() {
                this.newSelectedWeeks = [];
                for(let i = 1; i <= this.weeksCount; i++) {
                    this.newWeeksAuds[i] = -1;
                }

                let result = [];
                Object.keys(this.facultyDisciplines).forEach(name => {
                    this.facultyDisciplines[name].Disciplines
                        .filter(d => d.GroupsInfo[this.newGroupId])
                        .forEach(d => {
                            result.push(d);
                        });
                });

                result = result.sort((a, b) => {
                    if (a.disciplineName === b.disciplineName) {
                        let numA = parseInt(a.studentGroupName.split(' ')[0]);
                        let numB = parseInt(b.studentGroupName.split(' ')[0]);

                        if (numA === numB) {
                            if (a.studentGroupName === b.studentGroupName) return 0;
                            return (a.studentGroupName < b.studentGroupName) ? -1 : 1;
                        }
                        else
                        {
                            return (numA < numB) ? -1 : 1;
                        }
                    }

                    return a.disciplineName < b.disciplineName ? -1 : 1;
                });

                this.newDisciplines = result;

                if (result.length !== 0) {
                    for(let i = 0; i < result.length; i++) {
                        if (result[i].disciplineName === this.facultyDisciplineSelectedName) {
                            this.groupDisciplineSelected = result[i];
                            break;
                        }
                    }
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
            newTfdChanged(groupDisciplineSelected) {
                this.newTfdBusyLoading = true;

                axios
                    .get('/api.php?action=teacherWeeksSchedule&teacherId=' + groupDisciplineSelected.teacherId + '&compactResult')
                    .then(response => {
                        this.disciplineTeacherSchedule = response.data;
                        this.newTfdBusyLoading = false;
                    });
            },
            newLessonsAllWeeksAudsFree() {
                let first = true;
                let resultIds = [];

                for(let dowIndex = 0; dowIndex < this.newDows.length; dowIndex++) {
                    let dow = this.newDows[dowIndex];
                    for(let weekIndex = 0; weekIndex < this.newSelectedWeeks.length; weekIndex++) {
                        let week = this.newSelectedWeeks[weekIndex];
                        for(let ringIdIndex = 0; ringIdIndex < this.newRingIds.length; ringIdIndex++) {
                            let ringId = this.newRingIds[ringIdIndex];
                            if (first) {
                                resultIds = this.freeAuds[dow][week][ringId];
                                first = false;
                            } else {
                                for(let ri = 0; ri < resultIds.length; ri++)
                                {
                                    if (this.freeAuds[dow] !== undefined &&
                                        this.freeAuds[dow][week][ringId] !== undefined &&
                                        this.freeAuds[dow][week][ringId].indexOf(resultIds[ri]) === -1) {
                                        resultIds.splice(ri,1);
                                        ri--;
                                    }
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
            newRingToggled(ring) {
                if (this.newRingIds.includes(ring.RingId)) {
                    if (this.newRingIds.length === 1) return;
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
            askForDelete(lessons) {
                this.lessonsToDelete = lessons;
                this.deletePrompt = true;
            },
            deleteLessons() {
                let IdsString = this.lessonsToDelete.map(l => l.lessonId).join('|');

                let destroyUrl = '/lessonsDestroyByIds?Ids=' + IdsString;

                axios
                    .post(destroyUrl)
                    .then(response => {
                        this.loadFacultySchedule();
                    });
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
            editLessonsWeeksAudsFree(week) {
                let resultIds = [];

                let dow = this.lessonsDataToEdit["lessons"][0].dow;
                let ringId = this.lessonsDataToEdit["lessons"][0].ringId;

                resultIds = (this.editFreeAuds[dow][week][ringId] !== undefined)
                    ? this.editFreeAuds[dow][week][ringId]
                    : [];

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
                        this.loadFacultySchedule();
                    });
            },
            timeFromRingId(ringId) {
                let ringArray = this.allRings.filter(r => r.RingId === ringId);

                return (ringArray.length !== 0) ? ringArray[0].time.substr(0,5) : "";
            },
            ringFromTime(time) {
                let ringArray = this.allRings.filter(r => r.Time.substr(0,5) === time);

                return (ringArray.length !== 0) ? ringArray[0] : "";
            },
            getAuditoriumByName(name) {
                let auds = this.auditoriums.filter(a => a.name === name);
                return (auds.length > 0) ? auds[0] : {};
            },
            getRingFromAllRingsByFiveTime(time) {
                let ra = this.allRings.filter(r => time === r.Time.substr(0,5));
                return (ra.length > 0) ? ra[0] : null;
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

            if (this.facId === -1)
            {
                if (this.facultiesList.length !== 0) {
                    this.facId = this.facultiesSorted[0].id;
                    this.selectedWeeks = [-1];
                    this.facultyChanged();
                }
            } else {
                this.selectedWeeks = [-1];
                this.facultyChanged();
            }
        },
        computed: {
            facultiesSorted() {
                let result = [];
                for (var index in this.facultiesList) {
                    let faculty = this.facultiesList[index];
                    result.push(faculty);
                }

                result.sort((a,b) => {
                    let aso = a.sorting_order;
                    let bso = b.sorting_order;

                    if (aso === bso) return 0;
                    return (aso < bso) ? -1 : 1;
                });

                return result;
            },
            auditoriumsSorted() {
                return this.auditoriums
                    .sort((a,b) => {
                        if (a.name === b.name) return 0;
                        return a.name < b.name ? -1 : 1;
                    })
            },
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
</style>
