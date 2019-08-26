<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Расписание нескольких преподавателей</div>

                    <div class="card-body">
                        <table style="width:100%;">
                            <tr>
                                <td style="width:35%;">
                                    <select style="width:100%;" multiple v-model="selectedTeacherIds">
                                        <option v-for="teacher in teachersSorted" :value="teacher.id">{{teacher.fio}}</option>
                                    </select>
                                </td>
                                <td style="width:45%;">
                                    <button @click="loadTeachersSchedule(this.selectedTeacherIds);"
                                            style="width: 90%; margin: 1em;" class="button is-primary">
                                        Загрузить расписание выбранных преподавателей на неделю
                                    </button>
                                </td>
                                <td style="width:20%; text-align: center;">
                                    Недели: {{combineWeeksToRange(this.selectedWeeks)}}
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top: 1em;">
                            Группы преподавателей
                            <table style="width:100%;">
                                <tr>
                                    <td style="width:50%;">
                                        <select style="width:100%;" v-model="selectedTeacherGroupId">
                                            <option v-for="teacherGroup in teacherGroups" :value="teacherGroup.id">{{teacherGroup.name}}</option>
                                        </select>
                                    </td>
                                    <td style="width:30%;">
                                        <button @click="loadTeacherGroupSchedule();" style="width: 80%; margin: 1em;" class="button is-primary">
                                            Загрузить расписание группы на неделю
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div id="teachersSchedule" style="margin-top: 1em;">
                            <div style="text-align: center;">
                                Недели:
                                <button @click="allWeeksClicked();"
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

                            <div style="text-align: center; margin-bottom:1em;">
                                <button v-for="dow in 6"
                                        @click="DowToggled(dow);"
                                        style="margin-right:1em; margin-bottom: 0.5em; text-align:center;"
                                        :class="{'button': true,
                                    'is-primary': !Dows.includes(dow),
                                    'is-danger': Dows.includes(dow)}">
                                    {{dowRu[dow-1]}}
                                </button>
                            </div>

                            <div v-if="loading === true" style="font-size: 2em; text-align: center">
                                Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                            </div>

                            <table v-if="loading === false" class="table td-center is-bordered">
                                <tr>
                                    <td></td>
                                    <td v-for="ring in teachersScheduleRings[Dows[0]]">
                                        {{ring.time.substr(0,5)}}
                                    </td>
                                </tr>

                                <tr v-for="teacherId in loadedTeacherIds">
                                    <td>{{teacherById[teacherId].fio}}</td>
                                    <td v-for="ring in teachersScheduleRings[Dows[0]]">
                                        <template v-if="teachersSchedule[teacherId][Dows[0]][ring.time.substr(0,5)] !== undefined">
                                            <template v-for="tfdId in
                                            Object.keys(teachersSchedule[teacherId][Dows[0]][ring.time.substr(0,5)])
                                                .sort((a,b) => {
                                                    let aMin = Math.min(...Object.values(teachersSchedule[teacherId][Dows[0]][ring.time.substr(0,5)][a]['weeksAndAuds']).flat());
                                                    let bMin = Math.min(...Object.values(teachersSchedule[teacherId][Dows[0]][ring.time.substr(0,5)][b]['weeksAndAuds']).flat());

                                                    if (aMin === bMin) {
                                                        let aGroupName = teachersSchedule[teacherId][Dows[0]][ring.time.substr(0,5)][a]['lessons'][0]['groupName'];
                                                        let bGroupName = teachersSchedule[teacherId][Dows[0]][ring.time.substr(0,5)][b]['lessons'][0]['groupName'];

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
                                                })">
                                                <p>
                                                    {{teachersSchedule[teacherId][Dows[0]][ring.time.substr(0,5)][tfdId]["lessons"][0].groupName}}
                                                </p>
                                                <p style="font-size:0.6em;">
                                                    ({{combineWeeksToRange(Object.values(teachersSchedule[teacherId][Dows[0]][ring.time.substr(0,5)][tfdId]["weeksAndAuds"]).flat())}})
                                                </p>
                                            </template>
                                        </template>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "TeacherGroupSchedule",
        props: [
            'teachers',
            'teacherGroups',
            'weekCount',
            'rings',
        ],
        data() {
            return {
                teacherList: this.teachers,
                selectedTeacherIds: [],
                loadedTeacherIds: [],
                teachersSchedule: {},
                firstLoad: true,
                loading: false,
                weeksCount: this.weekCount,
                scheduleRings: [],
                selectedWeeks: [],
                severalWeeks: true,
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                Dows: [],
                teachersScheduleRings: [],
                teacherById: {},
                loading: false,
                selectedTeacherGroupId: -1,
            }
        },
        methods: {
            loadTeachersSchedule(teacherIds) {
                this.loading = true;

                let weeks = (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1) ?
                    this.range(1, this.weekCount).join('|') :
                    this.selectedWeeks.join('|');

                axios
                    .get('/api.php?action=teachersWeeksSchedule&teacherIds=' + teacherIds.join('|') + '&weeks=' + weeks)
                    .then(response => {
                        let teachersRings = {1: [], 2: [], 3: [], 4: [], 5: [], 6: [], 7: []};
                        Object.keys(response.data).forEach((teacherId) => {
                            for(let dow = 1; dow <= 7; dow++) {
                                Object.keys(response.data[teacherId][dow]).map(time => {
                                    let ring = this.getRingFromAllRingsByFiveTime(time);
                                    if (teachersRings[dow].filter(r => (r.id === ring.id)).length === 0) {
                                        teachersRings[dow].push(ring);
                                    }
                                });
                            }
                        });

                        for(let dow = 1; dow <= 7; dow++) {
                            teachersRings[dow] = this.sortRings(teachersRings[dow]);
                        }

                        this.teachersScheduleRings = teachersRings;
                        this.loadedTeacherIds = [...teacherIds];
                        this.teachersSchedule = response.data;

                        this.loading = false;
                    });
            },
            loadTeacherGroupSchedule() {
                this.loading = true;
                axios
                    .get('/teacherTeacherGroups/teacherGroupTeachers/' + this.selectedTeacherGroupId)
                    .then(response => {
                        let teacherIds = response.data.map(teacher => teacher.id);
                        this.loadTeachersSchedule(teacherIds);
                    });
            },
            cl(text) {
                console.log(text);
            },
            range(start, end) {
                return Array(end - start + 1).fill().map((_, idx) => start + idx)
            },
            getRingFromAllRingsByFiveTime(time) {
                let ra = this.rings.filter(r => time === r.time.substr(0,5));
                return (ra.length > 0) ? ra[0] : null;
            },
            sortRings(rings) {
                return rings.sort((a,b) => {
                    let aHour = parseInt(a.time.substr(0, 2));
                    let aMin = parseInt(a.time.substr(3, 2));
                    let bHour = parseInt(b.time.substr(0, 2));
                    let bMin = parseInt(b.time.substr(3, 2));
                    let aValue = aHour*60 + aMin;
                    let bValue = bHour*60 + bMin;

                    if (aValue === bValue) return 0;
                    return (aValue < bValue) ? -1 : 1;
                });
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
            allWeeksClicked() {
                this.severalWeeks = true;
                this.selectedWeeks = [];
                this.selectedWeeks.push(-1);
            },
            weekToggled(week) {
                if (!this.severalWeeks) {
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(week);
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
                        }

                        if (week > this.selectedWeeks[0]) {
                            for(let i = this.selectedWeeks[0]+1; i <= week; i++) {
                                this.selectedWeeks.push(i);
                            }
                        }

                        return;
                    }

                    if (event.ctrlKey)
                    {
                        this.selectedWeeks = [];
                        this.selectedWeeks.push(week);

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
                }
            },
            severalWeeksSwitchFlipped() {
                if (!this.severalWeeks) {
                    let min = 1;
                    if (!(this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)) {
                        min = Math.min(...this.selectedWeeks);
                    }
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(min);
                }
            },
            DowToggled(dow) {
                this.Dows = [];
                this.Dows.push(dow);
            },
        },
        mounted() {
            if (this.selectedTeacherId === -1)
            {
                if (this.teacherList.length !== 0) {
                    this.selectedTeacherIds = [];
                    this.selectedTeacherIds.push(this.teachersSorted[0].id);
                    this.selectedWeeks = [-1];
                }
            } else {
                this.selectedWeeks = [-1];
            }

            if (this.teacherGroups.length !== 0) {
                this.selectedTeacherGroupId = this.teacherGroups[0].id;
            }

            let teacherKeys = Object.keys(this.teachers);
            for(let i = 0; i < teacherKeys.length; i++) {
                this.teacherById[this.teachers[i].id] = this.teachers[i];
            }

            this.Dows = [];
            this.Dows.push(1);
        },
        computed: {
            teachersSorted() {
                let result = [];
                for (var index in this.teacherList) {
                    let teacher = this.teacherList[index];
                    result.push(teacher);
                }

                result.sort((a,b) => {
                    if (a.fio === b.fio) return 0;
                    return a.fio < b.fio ? -1 : 1;
                });

                return result;
            }
        }
    }
</script>

<style scoped>
    table th, table td {
        display: table-cell;
        vertical-align: middle;
    }
</style>
