<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <span style="margin-right: 1em;">
                            Занятость аудиторий корпуса
                        </span>

                        <button @click="loadBuildingEvents();"
                                style="white-space:normal !important; margin-left:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                class="button is-primary">R
                        </button>
                    </div>

                    <div class="card-body">
                        <select v-model="buildingId" @change="loadBuildingEvents()">
                            <option v-for="building in buildings" :value="building.id">{{building.name}}</option>
                        </select>

                        <div style="margin-top: 1em; text-align: center;">
                            <div>
                                <button v-for="dow in 6" style="margin-right:0.5em; margin-bottom: 0.5em;"
                                        @click="selectedDow = dow; loadBuildingEvents();"
                                        :class="{'button': true,
                                            'is-primary': selectedDow !== dow,
                                            'is-danger': selectedDow === dow }">
                                    {{dowRu[dow-1]}}
                                </button>
                            </div>

                            <div>
                                Недели:

                                <button @click="weekToggled(week)"
                                        v-for="week in this.weeksCount"
                                        style="margin-right:0.5em; margin-bottom: 0.5em;"
                                        :class="{'button': true,
                                    'is-primary': !selectedWeeks.includes(week),
                                    'is-danger': selectedWeeks.includes(week) }"
                                >{{week}}</button>
                            </div>

                            <div v-if="loading === true" style="font-size: 2em; text-align: center">
                                Загрузка ...
                            </div>

                            <div style="display: flex; flex-direction: column;">
                                <table v-if="loading === false" style="overflow-y: auto; font-size: 0.6em; margin-top: 2em;" class="table table-responsive td-center is-bordered">
                                    <tr>
                                        <td></td>
                                        <td v-for="(auditoriumName, auditoriumId) in scheduleAuditoriums">{{auditoriumName}}</td>
                                    </tr>

                                    <tr v-for="ring in ringsSorted">
                                        <td>{{ring.time}}</td>
                                        <td v-for="(auditoriumName, auditoriumId) in scheduleAuditoriums"
                                            :class="{
                                            'computerAudColor' : computerAudNames.includes(auditoriumName),
                                            'closedAudColor' : closedAudNames.includes(auditoriumName),
                                            'sameAudManyTeachers' : (((buildingEvents[ring.id] !== undefined) && (auditoriumId in buildingEvents[ring.id])) &&
                                                (Object.keys(buildingEvents[ring.id][auditoriumId]).length > 1) &&
                                                ((Object.values(buildingEvents[ring.id][auditoriumId]).map(item => item['lessons'][0].teacherFio).filter((v, i, a) => a.indexOf(v) === i)).length > 1)),
                                            'sameAudSameTeachers' : (((buildingEvents[ring.id] !== undefined) && (auditoriumId in buildingEvents[ring.id])) &&
                                                (Object.keys(buildingEvents[ring.id][auditoriumId]).length > 1) &&
                                                ((Object.values(buildingEvents[ring.id][auditoriumId]).map(item => item['lessons'][0].teacherFio).filter((v, i, a) => a.indexOf(v) === i)).length <= 1)),
                                            'possibleStreightAud': (teacherBlankRingIds.includes(parseInt(ring.id)) && (teacherPossibleAudIds.includes(parseInt(auditoriumId))))
                                                }">
                                            <template v-if="selectedLesson.ringsTime !== undefined && ring.time === selectedLesson.ringsTime.substr(0,5) && !closedAudNames.includes(auditoriumName)">
                                            <a @click.prevent="changeLessonAuditorium(auditoriumId);" href="#">
                                                <font-awesome-icon icon="plus-square" />
                                            </a>
                                            <br />
                                            </template>
                                            <template v-if="(buildingEvents[ring.id] !== undefined) && (auditoriumId in buildingEvents[ring.id])">
                                                <template v-for="tfd in
                                                    Object.keys(buildingEvents[ring.id][auditoriumId])
                                                        .sort((a,b) => {
                                                            let aMin = Math.min(...Object.values(buildingEvents[ring.id][auditoriumId][a]['weeksAndAuds']).flat());
                                                            let bMin = Math.min(...Object.values(buildingEvents[ring.id][auditoriumId][b]['weeksAndAuds']).flat());

                                                            if (aMin === bMin) return 0;
                                                            return aMin < bMin ? -1 : 1;
                                                        })
                                                ">
                                                    <div v-if="buildingEvents[ring.id][auditoriumId][tfd]['lessons'] !== undefined"
                                                         :title="
                                                        buildingEvents[ring.id][auditoriumId][tfd]['lessons'][0]['disciplineName'] + '@' +
                                                        buildingEvents[ring.id][auditoriumId][tfd]['lessons'][0]['teacherFio']
                                                    ">
                                                        <strong>{{buildingEvents[ring.id][auditoriumId][tfd]["lessons"][0]["studentGroupName"]}}</strong>
                                                    </div>

                                                    <div v-if="buildingEvents[ring.id][auditoriumId][tfd]['events'] !== undefined">
                                                        <strong>{{buildingEvents[ring.id][auditoriumId][tfd]["events"][0]["name"]}}</strong>
                                                    </div>


                                                    <template v-if="
                                                        Object.keys(buildingEvents[ring.id][auditoriumId])
                                                        .sort((a,b) => {
                                                            let aMin = Math.min(...Object.values(buildingEvents[ring.id][auditoriumId][a]['weeksAndAuds']).flat());
                                                            let bMin = Math.min(...Object.values(buildingEvents[ring.id][auditoriumId][b]['weeksAndAuds']).flat());

                                                            if (aMin === bMin) return 0;
                                                            return aMin < bMin ? -1 : 1;
                                                        }) [Object.keys(buildingEvents[ring.id][auditoriumId]).length-1] !== tfd
                                                    ">
                                                        <hr>
                                                    </template>
                                                </template>
                                            </template>
                                        </td>
                                    </tr>
                                </table>

                                <div class="card">
                                    <div class="card-header">
                                        <div>
                                            Занятия без аудитории
                                        </div>
                                        <button @click="loadBlankLessons();"
                                                style="white-space:normal !important; margin-left:1em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                                class="button isPrimary">R
                                        </button>
                                    </div>

                                    <div class="card-body" style="text-align: center; display: flex; flex-direction: row; flex-wrap: wrap; justify-content: space-between;">
                                        <template v-for="blankAudLesson in blankAuds">
                                            <button @click="lessonClicked(blankAudLesson)"
                                                    style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                                    :class="{
                                        'isPrimary': blankAudLesson.lessonId !== selectedLesson.lessonId,
                                        'isDanger': blankAudLesson.lessonId === selectedLesson.lessonId }">
                                                <table>
                                                    <tr>
                                                        <td>
                                                            <span style="font-size:2em;">
                                                                {{blankAudLesson.ringsTime.substr(0,5)}}
                                                            </span>
                                                        </td>
                                                        <td style="text-align: center;">
                                                            <span style="font-size:2em; padding-left:0.5em; padding-right:0.5em;">
                                                                {{FfromFIO(blankAudLesson.teachersFio)}}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span style="font-size:2em;">
                                                                {{blankAudLesson.studentGroupsName}}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" style="text-align: center;">
                                                            {{blankAudLesson.disciplinesName}}
                                                        </td>
                                                    </tr>
                                                </table>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from "moment";

    export default {
        name: "FillBlankAuds",
        props: [
            'buildings',
            'weekCount',
            'audsWithBuilding',
            'rings',
            'semesterStarts',
        ],
        data() {
            return {
                buildingsList: this.buildings,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                firstLoad: true,
                loading: false,
                buildingId: -1,
                weeksCount: this.weekCount,
                scheduleRings: [],
                scheduleAuditoriums: [],
                selectedWeeks: [],
                severalWeeks: false,
                buildingEvents: {},
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                selectedDow: 1,
                blankAuds: [],
                ssMoment: moment(this.semesterStarts, "YYYY-MM-DD").startOf('isoWeek'),
                selectedLesson: {lessonId: -1},
                computerAudNames: ['Ауд. 301', 'Ауд. 302', '10'],
                closedAudNames: ['Ауд. 111', 'Ауд. 123', 'Ауд. 124', 'Ауд. 129'],
                teacherBlankRingIds: [],
                teacherPossibleAudIds: [],
            }
        },
        methods: {
            changeLessonAuditorium(auditoriumId) {
                axios
                    .post('/changeLessonAud?lessonId=' + this.selectedLesson.lessonId +
                    '&auditoriumId=' + auditoriumId)
                .then(response => {
                    this.loadBuildingEvents();
                    this.loadBlankLessons();
                });
            },
            lessonClicked(lesson) {
                let tFio = lesson.teachersFio;
                let teacherLessons = this.blankAuds.filter(bal => bal.teachersFio === tFio);
                let teacherLessonRingIds = teacherLessons.map(l => l.ringsId);
                let audIds = this.audsWithBuilding[this.buildingId]
                    .filter(a => {
                        if (this.closedAudNames.includes(a.name)) return false;

                        for(let i = 0; i < teacherLessonRingIds.length; i++) {
                            if (this.buildingEvents[teacherLessonRingIds[i]] === undefined) return true;
                            if (this.buildingEvents[teacherLessonRingIds[i]][a.id] !== undefined) return false;
                        }
                        return true;
                    })
                    .map(a => a.id);

                this.teacherBlankRingIds = teacherLessonRingIds;
                this.teacherPossibleAudIds = audIds;

                this.selectedLesson = lesson;
            },
            DateFromWeekAndDow(week, dow) {
                let momentDate = this.ssMoment.clone().add(week-1, 'week').add(dow-1, 'day');
                return momentDate.format('YYYY-MM-DD');
            },
            cl(text) {
                console.log(text);
            },
            loadBuildingEvents() {
                this.buildingEvents = {};
                this.loading = true;
                this.firstLoad = false;

                let apiUrl = '/api.php?action=buildingEvents&dow=' + this.selectedDow + '&weeks=' + this.selectedWeeks.join('|') + '&buildingId=' + this.buildingId;

                if (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)
                {
                    let weeksString = this.range(1, this.weekCount).join('|');

                    apiUrl = '/api.php?action=buildingEvents&dow=' + this.selectedDow + '&weeks=' + weeksString + '&buildingId=' + this.buildingId;
                }

                axios
                    .get(apiUrl)
                    .then(response => {
                        let data = response.data;

                        this.loading = false;

                        this.buildingEvents = data.schedule;
                        this.lessonClicked(this.selectedLesson);

                        let allRings = {};
                        this.rings.forEach(item => {
                            allRings[item.id] = item.time.substr(0,5);
                        });
                        this.scheduleRings = allRings;

                        let buildingAuds = {};
                        if (this.audsWithBuilding[this.buildingId] !== undefined) {
                            this.audsWithBuilding[this.buildingId].forEach(item => {
                                if (!['-', '--', '---'].includes(item.name)) {
                                    buildingAuds[item.id] = item.name;
                                }
                            });
                        }
                        this.scheduleAuditoriums = buildingAuds;
                    });

                this.loadBlankLessons();
            },
            loadBlankLessons() {
                let date = this.DateFromWeekAndDow(this.selectedWeeks[0], this.selectedDow);

                axios
                    .get('/getBlankAuds?date=' + date)
                    .then(response => {
                        this.blankAuds = response.data.sort((a,b) => {
                            if (a.teachersFio === b.teachersFio) {
                                let aTime = parseInt(a.ringsTime.substr(0,2))*60 + parseInt(a.ringsTime.substr(3,2));
                                let bTime = parseInt(b.ringsTime.substr(0,2))*60 + parseInt(b.ringsTime.substr(3,2));

                                if (aTime === bTime) return 0;
                                return (aTime < bTime) ? -1 : 1;
                            }

                            return (a.teachersFio < b.teachersFio) ? -1 : 1;
                        });
                        if (this.blankAuds.length !== 0) {
                            this.lessonClicked(this.blankAuds[0]);
                        } else {
                            this.selectedLesson = {lessonId: -1};
                        }
                    });
            },
            loadFullBuildingEvents() {
                this.severalWeeks = true;
                this.selectedWeeks = [];
                this.selectedWeeks.push(-1);
                this.loadBuildingEvents();
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
                    this.loadBuildingEvents();
                }
            },
            weekToggled(week) {
                if (!this.severalWeeks) {
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(week);
                    this.loadBuildingEvents();
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
                            this.loadBuildingEvents()
                        }

                        if (week > this.selectedWeeks[0]) {
                            for(let i = this.selectedWeeks[0]+1; i <= week; i++) {
                                this.selectedWeeks.push(i);
                            }
                            this.loadBuildingEvents()
                        }

                        return;
                    }

                    if (event.ctrlKey)
                    {
                        this.selectedWeeks = [];
                        this.selectedWeeks.push(week);
                        this.loadBuildingEvents();

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
                    this.loadBuildingEvents();
                }
            },
            FfromFIO(fio) {
                return fio.split(' ')[0];
            },
        },
        mounted() {
            if (this.buildingId === -1)
            {
                if (this.buildingsList.length !== 0) {
                    this.buildingId = this.buildingsList[0].id;
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(1);
                    this.loadBuildingEvents();
                }
            } else {
                this.selectedWeeks = [-1];
                this.loadBuildingEvents();
            }
        },
        computed: {
            ringsSorted() {
                let result = [];
                for (var k in this.scheduleRings) {
                    if (this.scheduleRings.hasOwnProperty(k)) {
                        result.push({'id' : k, 'time' : this.scheduleRings[k]})
                    }
                }

                result.sort((a,b) => {
                    let aVal = a.time.substr(0,2) * 60 + parseInt(a.time.substr(3,2));
                    let bVal = b.time.substr(0,2) * 60 + parseInt(b.time.substr(3,2));

                    if (aVal === bVal) return 0;
                    return aVal < bVal ? -1 : 1;
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

    .computerAudColor {
        background-color: rgba(255,255,0,0.3);
    }

    .closedAudColor {
        background-color: rgba(255,0,0,0.3);
    }

    .sameAudManyTeachers {
        border: 3px solid #ff0000 !important;
    }

    .sameAudSameTeachers {
        border: 3px solid rgba(255,113,0,1.0) !important;
    }

    .possibleStreightAud {
        background-color: rgba(99,255,69,0.15);
    }
</style>
