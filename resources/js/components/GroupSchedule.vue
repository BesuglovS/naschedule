<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Расписание группы</div>

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
                                    'is-danger': selectedWeeks.includes(week) }"
                                >{{week}}</button>

                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="severalWeeks" @change="severalWeeksSwitchFlipped();" class="custom-control-input" id="customSwitch1">
                                    <label class="custom-control-label" for="customSwitch1">Несколько недель</label>
                                </div>
                            </div>

                            <div v-if="loading === true" style="font-size: 2em; text-align: center">
                                Загрузка ...
                            </div>

                            <div v-if="
                            (groupSchedule[1] && groupSchedule[1].length === 0) &&
                            (groupSchedule[2] && groupSchedule[2].length === 0) &&
                            (groupSchedule[3] && groupSchedule[3].length === 0) &&
                            (groupSchedule[4] && groupSchedule[4].length === 0) &&
                            (groupSchedule[5] && groupSchedule[5].length === 0) &&
                            (groupSchedule[6] && groupSchedule[6].length === 0) && loading === false
                        " style="text-align: center; font-size: 30px">
                                Занятий нет
                            </div>

                            <table v-if=" !(
                            (groupSchedule[1] && groupSchedule[1].length === 0) &&
                            (groupSchedule[2] && groupSchedule[2].length === 0) &&
                            (groupSchedule[3] && groupSchedule[3].length === 0) &&
                            (groupSchedule[4] && groupSchedule[4].length === 0) &&
                            (groupSchedule[5] && groupSchedule[5].length === 0) &&
                            (groupSchedule[6] && groupSchedule[6].length === 0)) && loading === false
                        "

                                   style="margin-top: 2em;" class="table td-center is-bordered">
                                <tr>
                                    <td></td>
                                    <td v-if="groupSchedule[1] && groupSchedule[1].length !== 0">
                                        <strong>Понедельник</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{groupSchedule[1]
                                            [Object.keys(groupSchedule[1])[0]]
                                            [Object.keys(groupSchedule[1][Object.keys(groupSchedule[1])[0]])[0]]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="groupSchedule[2] && groupSchedule[2].length !== 0">
                                        <strong>Вторник</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{groupSchedule[2]
                                            [Object.keys(groupSchedule[2])[0]]
                                            [Object.keys(groupSchedule[2][Object.keys(groupSchedule[2])[0]])[0]]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="groupSchedule[3] && groupSchedule[3].length !== 0">
                                        <strong>Среда</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{groupSchedule[3]
                                            [Object.keys(groupSchedule[3])[0]]
                                            [Object.keys(groupSchedule[3][Object.keys(groupSchedule[3])[0]])[0]]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="groupSchedule[4] && groupSchedule[4].length !== 0">
                                        <strong>Четверг</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{groupSchedule[4]
                                            [Object.keys(groupSchedule[4])[0]]
                                            [Object.keys(groupSchedule[4][Object.keys(groupSchedule[4])[0]])[0]]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="groupSchedule[5] && groupSchedule[5].length !== 0">
                                        <strong>Пятница</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{groupSchedule[5]
                                            [Object.keys(groupSchedule[5])[0]]
                                            [Object.keys(groupSchedule[5][Object.keys(groupSchedule[5])[0]])[0]]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                    <td v-if="groupSchedule[6] && groupSchedule[6].length !== 0">
                                        <strong>Суббота</strong>
                                        <template v-if="!this.severalWeeks">
                                            <br />
                                            {{groupSchedule[6]
                                            [Object.keys(groupSchedule[6])[0]]
                                            [Object.keys(groupSchedule[6][Object.keys(groupSchedule[6])[0]])[0]]
                                            ["lessons"][0]
                                            ["date"] | formatOnlyDate
                                            }}
                                        </template>
                                    </td>
                                </tr>

                                <tr v-for="ring in this.scheduleRings">
                                    <td><strong>{{ring}}</strong></td>
                                    <td v-if="Object.keys(groupSchedule[dow]).length !== 0" v-for="dow in 6">
                                        <div style="border: none;" v-if="groupSchedule[dow][ring] !== undefined">
                                            <template v-for="tfd in
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
                                                <strong>{{groupSchedule[dow][ring][tfd]["lessons"][0]["groupName"]}}</strong><br />
                                                {{groupSchedule[dow][ring][tfd]["lessons"][0]["discName"]}} <br />
                                                {{groupSchedule[dow][ring][tfd]["lessons"][0]["teacherFIO"]}} <br />
                                                <template v-for="auditorium in
                                                    Object.keys(groupSchedule[dow][ring][tfd]['weeksAndAuds'])
                                                        .sort((a,b) => {
                                                            let aMin = Math.min(...groupSchedule[dow][ring][tfd]['weeksAndAuds'][a]);
                                                            let bMin = Math.min(...groupSchedule[dow][ring][tfd]['weeksAndAuds'][b]);

                                                            if (aMin === bMin) return 0;
                                                            return aMin < bMin ? -1 : 1;
                                                        })
                                                ">
                                                    {{combineWeeksToRange(groupSchedule[dow][ring][tfd]["weeksAndAuds"][auditorium])}} - {{auditorium}}<br />
                                                </template>
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
        name: "GroupSchedule",
        props: {
            'studentGroups': Object,
            'groupId': Number,
            'weekCount': Number,
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
                groupSchedule: {}
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

                        this.scheduleRings = rings;

                        rings.sort((a,b) => {
                            let aMinutes = parseInt(a.substr(0,2)) * 60 + parseInt(a.substr(3,2));
                            let bMinutes = parseInt(b.substr(0,2)) * 60 + parseInt(b.substr(3,2));
                            return aMinutes < bMinutes ? -1 : 1;
                        });

                        this.loading = false;

                        this.groupSchedule = data;
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
            severalWeeksSwitchFlipped() {
                if (!this.severalWeeks) {
                    let min = 1;
                    if (!(this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)) {
                        min = Math.min(...this.selectedWeeks);
                    }
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(min);
                    this.loadGroupSchedule();
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
                        console.log();
                        this.selectedWeeks.splice(index, 1);
                    }
                    this.loadGroupSchedule();
                }
            },
        },
        mounted() {
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
            groupsSorted() {
                let result = [];
                for (var index in this.groups) {
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

</style>
