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
                                style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                class="button is-primary">R
                        </button>
                    </div>

                    <div class="card-body">
                        <select v-model="buildingId" @change="loadBuildingEvents()">
                            <option v-for="building in buildings" :value="building.id">{{building.name}}</option>
                        </select>

                        Недели: {{combineWeeksToRange(this.selectedWeeks)}}

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
                                <button @click="loadFullBuildingEvents();"
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

                            <div v-if="scheduleRings.length === 0 && loading === false" style="font-size: 2em; text-align: center">
                                Занятий нет
                            </div>

                            <table v-if="scheduleRings.length !== 0 && loading === false" style="overflow-y: auto; font-size: 0.6em; margin-top: 2em;" class="table table-responsive td-center is-bordered">
                                <tr>
                                    <td></td>
                                    <td v-for="(auditoriumName, auditoriumId) in scheduleAuditoriums">{{auditoriumName}}</td>
                                </tr>

                                <tr v-for="ring in ringsSorted">
                                    <td>{{ring.time}}</td>
                                    <td v-for="(auditoriumName, auditoriumId) in scheduleAuditoriums"
                                        :class="{
                                            'redBorder': redBorder(buildingEvents[ring.id][auditoriumId]),
                                            'yellowBorder': yellowBorder(buildingEvents[ring.id][auditoriumId]) }"
                                    >
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
                                                    <strong>{{buildingEvents[ring.id][auditoriumId][tfd]["lessons"][0]["studentGroupName"]}}</strong><br />
                                                    {{combineWeeksToRange(buildingEvents[ring.id][auditoriumId][tfd]["weeksAndAuds"]
                                                        [Object.keys(buildingEvents[ring.id][auditoriumId][tfd]["weeksAndAuds"])[0]])}}
                                                </div>

                                                <div v-if="buildingEvents[ring.id][auditoriumId][tfd]['events'] !== undefined">
                                                    <strong>{{buildingEvents[ring.id][auditoriumId][tfd]["events"][0]["name"]}}</strong><br />
                                                    {{combineWeeksToRange(buildingEvents[ring.id][auditoriumId][tfd]["weeksAndAuds"]
                                                    [Object.keys(buildingEvents[ring.id][auditoriumId][tfd]["weeksAndAuds"])[0]])}}
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "BuildingEvents",
        props: [
            'buildings',
            'weekCount'
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
                severalWeeks: true,
                buildingEvents: {},
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                selectedDow: 1
            }
        },
        methods: {
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
                    .get(apiUrl + '&internal=1')
                    .then(response => {
                        let data = response.data;

                        this.loading = false;

                        this.buildingEvents = data.schedule;
                        this.scheduleAuditoriums = data.auditoriums;
                        this.scheduleRings = data.rings;
                    });
            },
            loadFullBuildingEvents() {
                this.severalWeeks = true;
                this.selectedWeeks = [];
                this.selectedWeeks.push(-1);
                this.loadBuildingEvents();
            },
            redBorder(events) {
                if (events === undefined || Object.keys(events).length === 1) return false;

                var teacherFIOs = [];
                for (var tfdId in events) {
                    var item = events[tfdId];
                    if (item['lessons'] !== undefined) {
                        if (!teacherFIOs.includes(item['lessons'][0]['teacherFio'])) {
                            teacherFIOs.push(item['lessons'][0]['teacherFio']);
                        }
                    } else {
                        return true;
                    }
                }

                return (teacherFIOs.length > 1);
            },
            yellowBorder(events) {
                if (events === undefined || Object.keys(events).length === 1) return false;

                var teacherFIOs = [];
                for (var tfdId in events) {
                    var item = events[tfdId];
                    if (item['lessons'] !== undefined) {
                        if (!teacherFIOs.includes(item['lessons'][0]['teacherFio'])) {
                            teacherFIOs.push(item['lessons'][0]['teacherFio']);
                        }
                    } else {
                        return true;
                    }
                }

                return (Object.keys(events).length > 1 && teacherFIOs.length === 1);
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
        },
        mounted() {
            if (this.buildingId === -1)
            {
                if (this.buildingsList.length !== 0) {
                    this.buildingId = this.buildingsList[0].id;
                    this.selectedWeeks = [-1];
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
    .redBorder {
        border: 3px solid #ee222c !important;
        background-color: rgba(241, 11, 30, 0.31);
    }

    .yellowBorder {
        border: 3px solid #8a860b !important;
        background-color: rgba(255, 254, 19, 0.31);
    }
</style>
