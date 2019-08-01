<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Расписание преподавателя</div>

                    <div class="card-body">
                        <select v-model="selectedTeacherId" @change="loadFullTeacherSchedule();">
                            <option v-for="teacher in teachersSorted" :value="teacher.id">{{teacher.fio}}</option>
                        </select>

                        Недели: {{combineWeeksToRange(this.selectedWeeks)}}

                        <div style="text-align: center;" v-if="this.loading">Загрузка...</div>

                        <div id="teachersSchedule" style="margin-top: 1em;">
                            <div style="text-align: center;">
                                Недели:
                                <button @click="loadFullTeacherSchedule();"
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

                            <div v-if="
                                (teacherSchedule[1] && teacherSchedule[1].length === 0) &&
                                (teacherSchedule[2] && teacherSchedule[2].length === 0) &&
                                (teacherSchedule[3] && teacherSchedule[3].length === 0) &&
                                (teacherSchedule[4] && teacherSchedule[4].length === 0) &&
                                (teacherSchedule[5] && teacherSchedule[5].length === 0) &&
                                (teacherSchedule[6] && teacherSchedule[6].length === 0)
                            " style="text-align: center; font-size: 30px">
                                Занятий нет
                            </div>

                            <table v-if=" !(
                                (teacherSchedule[1] && teacherSchedule[1].length === 0) &&
                                (teacherSchedule[2] && teacherSchedule[2].length === 0) &&
                                (teacherSchedule[3] && teacherSchedule[3].length === 0) &&
                                (teacherSchedule[4] && teacherSchedule[4].length === 0) &&
                                (teacherSchedule[5] && teacherSchedule[5].length === 0) &&
                                (teacherSchedule[6] && teacherSchedule[6].length === 0))
                            "

                                style="margin-top: 2em;" class="table td-center is-bordered">
                                <tr>
                                    <td></td>
                                    <td v-if="teacherSchedule[1] && teacherSchedule[1].length !== 0">
                                        <strong>Понедельник</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{teacherSchedule[1]
                                                [Object.keys(teacherSchedule[1])[0]]
                                                [Object.keys(teacherSchedule[1][Object.keys(teacherSchedule[1])[0]])]
                                                ["lessons"][0]
                                                ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="teacherSchedule[2] && teacherSchedule[2].length !== 0">
                                        <strong>Вторник</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{teacherSchedule[2]
                                            [Object.keys(teacherSchedule[2])[0]]
                                            [Object.keys(teacherSchedule[2][Object.keys(teacherSchedule[2])[0]])]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="teacherSchedule[3] && teacherSchedule[3].length !== 0">
                                        <strong>Среда</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{teacherSchedule[3]
                                            [Object.keys(teacherSchedule[3])[0]]
                                            [Object.keys(teacherSchedule[3][Object.keys(teacherSchedule[3])[0]])]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="teacherSchedule[4] && teacherSchedule[4].length !== 0">
                                        <strong>Четверг</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{teacherSchedule[4]
                                            [Object.keys(teacherSchedule[4])[0]]
                                            [Object.keys(teacherSchedule[4][Object.keys(teacherSchedule[4])[0]])]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="teacherSchedule[5] && teacherSchedule[5].length !== 0">
                                        <strong>Пятница</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{teacherSchedule[5]
                                            [Object.keys(teacherSchedule[5])[0]]
                                            [Object.keys(teacherSchedule[5][Object.keys(teacherSchedule[5])[0]])]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="teacherSchedule[6] && teacherSchedule[6].length !== 0">
                                        <strong>Суббота</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{teacherSchedule[6]
                                            [Object.keys(teacherSchedule[6])[0]]
                                            [Object.keys(teacherSchedule[6][Object.keys(teacherSchedule[6])[0]])]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                </tr>

                                <tr v-for="ring in this.scheduleRings">
                                    <td><strong>{{ring}}</strong></td>
                                    <td v-if="Object.keys(teacherSchedule[dow]).length !== 0" v-for="dow in 6">
                                        <div style="border: none;" v-if="teacherSchedule[dow][ring] !== undefined">
                                            <template v-for="tfd in
                                                Object.keys(teacherSchedule[dow][ring])
                                                    .sort((a,b) => {
                                                            let aMin = Math.min(...Object.values(teacherSchedule[dow][ring][a]['weeksAndAuds']).flat());
                                                            let bMin = Math.min(...Object.values(teacherSchedule[dow][ring][b]['weeksAndAuds']).flat());

                                                            if (aMin === bMin) {
                                                                let aGroupName = teacherSchedule[dow][ring][a]['lessons'][0]['groupName'];
                                                                let bGroupName = teacherSchedule[dow][ring][b]['lessons'][0]['groupName'];

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
                                                <strong>{{teacherSchedule[dow][ring][tfd]["lessons"][0]["groupName"]}}</strong><br />
                                                {{teacherSchedule[dow][ring][tfd]["lessons"][0]["discName"]}} <br />
                                                <template v-for="auditorium in
                                                    Object.keys(teacherSchedule[dow][ring][tfd]['weeksAndAuds'])
                                                        .sort((a,b) => {
                                                            let aMin = Math.min(...teacherSchedule[dow][ring][tfd]['weeksAndAuds'][a]);
                                                            let bMin = Math.min(...teacherSchedule[dow][ring][tfd]['weeksAndAuds'][b]);

                                                            if (aMin === bMin) return 0;
                                                            return aMin < bMin ? -1 : 1;
                                                        })
                                                ">
                                                    {{combineWeeksToRange(teacherSchedule[dow][ring][tfd]["weeksAndAuds"][auditorium])}} - {{auditorium}}<br />
                                                </template>
                                                <template v-if="tfd !== Object.keys(teacherSchedule[dow][ring])
                                                    .sort((a,b) => {
                                                            let aMin = Math.min(...Object.values(teacherSchedule[dow][ring][a]['weeksAndAuds']).flat());
                                                            let bMin = Math.min(...Object.values(teacherSchedule[dow][ring][b]['weeksAndAuds']).flat());

                                                            if (aMin === bMin) {
                                                                let aGroupName = teacherSchedule[dow][ring][a]['lessons'][0]['groupName'];
                                                                let bGroupName = teacherSchedule[dow][ring][b]['lessons'][0]['groupName'];

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
                                                    [Object.keys(teacherSchedule[dow][ring]).length-1]">
                                                    <hr>
                                                </template>
                                            </template>
                                        </div>
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
        name: "TeacherSchedule",
        props: {
            'teachers': Object,
            'weekCount': Number,
        },
        data() {
            return {
                teacherList: this.teachers,
                selectedTeacherId: -1,
                teacherSchedule: {},
                firstLoad: true,
                loading: false,
                weeksCount: this.weekCount,
                scheduleRings: [],
                selectedWeeks: [],
                severalWeeks: true
            }
        },
        methods: {
            cl(text) {
              console.log(text);
            },
            loadTeacherSchedule() {
                let apiUrl = '/api.php?action=teacherWeeksSchedule&teacherId=' + this.selectedTeacherId + '&weeks=' + this.selectedWeeks.join('|') + '&compactResult';

                if (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)
                {
                    let weeksString = this.range(1, this.weekCount).join('|');

                    apiUrl = '/api.php?action=teacherWeeksSchedule&teacherId=' + this.selectedTeacherId + '&weeks=' + weeksString + '&compactResult';
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

                        this.scheduleRings = rings;

                        rings.sort((a,b) => {
                            let aMinutes = parseInt(a.substr(0,2)) * 60 + parseInt(a.substr(3,2));
                            let bMinutes = parseInt(b.substr(0,2)) * 60 + parseInt(b.substr(3,2));
                            return aMinutes < bMinutes ? -1 : 1;
                        });

                        this.teacherSchedule = data;
                    });
            },
            range(start, end) {
                return Array(end - start + 1).fill().map((_, idx) => start + idx)
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
            weekToggled(week) {
                if (!this.severalWeeks) {
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(week);
                    this.loadTeacherSchedule();
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
                            this.loadTeacherSchedule()
                        }

                        if (week > this.selectedWeeks[0]) {
                            for(let i = this.selectedWeeks[0]+1; i <= week; i++) {
                                this.selectedWeeks.push(i);
                            }
                            this.loadTeacherSchedule()
                        }

                        return;
                    }

                    if (event.ctrlKey)
                    {
                        this.selectedWeeks = [];
                        this.selectedWeeks.push(week);
                        this.loadTeacherSchedule();

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
                    this.loadTeacherSchedule();
                }
            },
            loadFullTeacherSchedule() {
                this.severalWeeks = true;
                this.selectedWeeks = [];
                this.selectedWeeks.push(-1);
                this.loadTeacherSchedule();
            },
            severalWeeksSwitchFlipped() {
                if (!this.severalWeeks) {
                    let min = 1;
                    if (!(this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)) {
                        min = Math.min(...this.selectedWeeks);
                    }
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(min);
                    this.loadTeacherSchedule();
                }
            }
        },
        mounted() {
            if (this.selectedTeacherId === -1)
            {
                if (this.teacherList.length !== 0) {
                    this.selectedTeacherId = this.teachersSorted[0].id;
                    this.selectedWeeks = [-1];
                    this.loadTeacherSchedule();
                }
            } else {
                this.selectedWeeks = [-1];
                this.loadTeacherSchedule();
            }
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
