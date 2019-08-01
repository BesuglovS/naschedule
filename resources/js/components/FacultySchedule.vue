<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Расписание параллели</div>

                    <div class="card-body">
                        <select v-model="facId" @change="loadFacultySchedule()">
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

                            <table style="margin-top: 2em; " class="table td-center is-bordered">
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
                                        <td :colspan="facultySchedule.length+1">
                                            <strong>{{dowRu[dow-1]}}</strong>
                                        </td>
                                    </tr>
                                    <tr v-for="ring in scheduleRings[dow]">
                                        <td>
                                            <strong>
<!--                                                {{dowRu[dow-1]}} <br />-->
                                                {{ring}}
                                            </strong>
                                        </td>
                                        <td v-for="groupSchedule in facultySchedule">
                                            <template v-if="groupSchedule['lessons'][dow][ring] !== undefined">
                                                <template v-for="tfd in Object.keys(groupSchedule['lessons'][dow][ring])">
                                                    <template v-if="groupSchedule['lessons'][dow][ring][tfd]['lessons'][0]['groupName'] !== groupSchedule['groupName']">
                                                        {{groupSchedule['lessons'][dow][ring][tfd]["lessons"][0]["groupName"]}}<br />
                                                    </template>
                                                    <strong>{{groupSchedule['lessons'][dow][ring][tfd]["lessons"][0]["discName"]}}</strong><br />
                                                    {{groupSchedule['lessons'][dow][ring][tfd]["lessons"][0]["teacherFIO"]}} <br />
                                                    <template v-for="auditorium in Object.keys(groupSchedule['lessons'][dow][ring][tfd]['weeksAndAuds'])">
                                                        {{combineWeeksToRange(groupSchedule['lessons'][dow][ring][tfd]["weeksAndAuds"][auditorium])}} - {{auditorium}}<br />
                                                    </template>
                                                    <template v-if="tfd !== Object.keys(groupSchedule['lessons'][dow][ring])[Object.keys(groupSchedule['lessons'][dow][ring]).length-1]">
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
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "FacultySchedule",
        props: {
            'faculties': Array,
            'facultyId': Number,
            'weekCount': Number,
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
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье']
            }
        },
        methods: {
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
                    });
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
        },
        mounted() {
            if (this.facId === -1)
            {
                if (this.facultiesList.length !== 0) {
                    this.facId = this.facultiesSorted[0].id;
                    this.selectedWeeks = [-1];
                    this.loadFacultySchedule();
                }
            } else {
                this.selectedWeeks = [-1];
                this.loadFacultySchedule();
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
            }
        }
    }
</script>

<style scoped>

</style>
