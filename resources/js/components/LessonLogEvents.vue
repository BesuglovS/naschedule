<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12" style="display:flex;">
                <div class="card">
                    <div class="card-header">Изменения расписания группы</div>

                    <div class="card-body">
                        <select v-model="studentGroupId" @change="loadGroupEvents()">
                            <option v-for="sg in groupsSorted" :value="sg.id">{{sg.name}}</option>
                        </select>

                        Недели: {{combineWeeksToRange(this.selectedWeeks)}}

                        <div style="margin-top: 1em;">
                            <div style="text-align: center;">
                                Недели:
                                <button @click="loadFullGroupEvents();"
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
                            </div>

                            <div v-if="loading === true" style="font-size: 2em; text-align: center">
                                Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                            </div>

                            <table v-if="loading === false" style="margin-top: 2em;" class="table td-center is-bordered">
                                <tr>
                                    <td>Дата изменения</td>
                                    <td>Старый урок</td>
                                    <td>Новый урок</td>
                                </tr>

                                <tr v-for="event in this.groupLessonLogEvents">
                                    <td style="vertical-align: middle;">{{reformatDateTime(event.lessonLogEventDateTime)}}</td>
                                    <td>
                                        <template v-if="event.lessonOldId !== null">
                                            <p>{{reformatDate(event.lessonOldCalendarDate)}} @ {{event.lessonOldRingTime.substr(0,5)}} = {{event.lessonOldStudentGroupName}}</p>
                                            <p>{{event.lessonOldDisciplineName}}</p>
                                            <p>{{event.lessonOldTeacherFio}}</p>
                                            <p>{{event.lessonOldAuditoriumName}}</p>
                                        </template>
                                    </td>
                                    <td>
                                        <template v-if="event.lessonNewId !== null">
                                            <p>{{reformatDate(event.lessonNewCalendarDate)}} @ {{event.lessonNewRingTime.substr(0,5)}} = {{event.lessonNewStudentGroupName}}</p>
                                            <p>{{event.lessonNewDisciplineName}}</p>
                                            <p>{{event.lessonNewTeacherFio}}</p>
                                            <p>{{event.lessonNewAuditoriumName}}</p>
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
    import modal from './Modal';
    import moment from "moment";

    export default {
        name: "LessonLogEvents",
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
                groupLessonLogEvents: [],
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                firstLoad: true,
                loading: false,
                studentGroupId: this.groupId,
                weeksCount: this.weekCount,
                selectedWeeks: [],
                severalWeeks: true,
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
            }
        },
        methods: {
            loadGroupEvents() {
                let apiUrl = '/lessonLogEventsByGroup?groupId=' + this.studentGroupId + '&weeks=' + this.selectedWeeks.join('|');

                if (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)
                {
                    let weeksString = this.range(1, this.weekCount).join('|');

                    apiUrl = '/lessonLogEventsByGroup?groupId=' + this.studentGroupId + '&weeks=' + weeksString;
                }

                axios.get(apiUrl)
                    .then(response => {
                        this.groupLessonLogEvents = response.data;
                    });
            },
            loadFullGroupEvents() {
                this.severalWeeks = true;
                this.selectedWeeks = [];
                this.selectedWeeks.push(-1);
                this.loadGroupEvents();
            },
            reformatDateTime(date) {
                return moment(date, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY HH:mm:ss');
            },
            reformatDate(date) {
                return moment(date, 'YYYY-MM-DD').format('DD.MM.YYYY');
            },
            WeeksToStringOrEmpty(weeks, empty) {
                if (empty === undefined) empty = "";
                return (weeks.length === 0) ? empty : this.combineWeeksToRange(weeks);
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
                    this.loadGroupEvents();
                }
            },
            weekToggled(week) {
                if (!this.severalWeeks) {
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(week);
                    this.loadGroupEvents();
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
                            this.loadGroupEvents()
                        }

                        if (week > this.selectedWeeks[0]) {
                            for(let i = this.selectedWeeks[0]+1; i <= week; i++) {
                                this.selectedWeeks.push(i);
                            }
                            this.loadGroupEvents()
                        }

                        return;
                    }

                    if (event.ctrlKey)
                    {
                        this.selectedWeeks = [];
                        this.selectedWeeks.push(week);
                        this.loadGroupEvents();

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
                    this.loadGroupEvents();
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
                    this.loadGroupEvents();
                }
            } else {
                this.selectedWeeks = [-1];
                this.loadGroupEvents();
            }
        },
        computed: {
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
</style>

