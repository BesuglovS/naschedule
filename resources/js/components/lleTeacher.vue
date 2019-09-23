<template>
    <div>
        <div style="border: 1px solid black; border-radius: 10px;  padding-bottom:1em;">
            <div style="text-align: center; font-size: 2em; font-weight: 700;">
                Изменения расписания
            </div>
            <div class="container" style="align-items: center; display: flex; flex-direction: column; justify-content: center; margin-top: 2em;">
                <table style="font-size: 1.5em;">
                    <tr>
                        <td style="vertical-align: middle;">ФИО</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td style="vertical-align: middle;">
                            <select @change="loadTeacherEvents();" style="margin-right: 1em; font-size: 1.5em; width: 820px;" name="facultyId" v-model="teacherId" >
                                <option v-for="teacher in teachersSorted" :value="teacher.id">{{teacher.fio}}</option>
                            </select>
                        </td>
                        <td style="padding: 0em 2em 0em 1em; font-size:2em;">
                            {{totalCount}}
                        </td>
                    </tr>
                </table>

                Недели: {{combineWeeksToRange(this.selectedWeeks)}}

                <div id="groupSchedule" style="margin-top: 1em;">
                    <div style="text-align: center;">
                        Недели:
                        <button @click="loadAllTeacherEvents();"
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

                        <div>
                            <button v-for="dow in 6" style="margin-right:0.5em; margin-bottom: 0.5em;"
                                    @click="selectedDow = dow; filterEvents();"
                                    :class="{'button': true,
                                            'is-primary': selectedDow !== dow,
                                            'is-danger': selectedDow === dow }">
                                {{dowRu[dow-1]}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <div v-if="loading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
                    Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
                </div>
            </div>

            <div class="container" style="align-items: center; display: flex; justify-content: center; margin-top: 1em;">
                <table v-if="loading === false" style="margin-top: 2em;" class="table td-center is-bordered">
                    <tr>
                        <td>Дата изменения</td>
                        <td>Старый урок</td>
                        <td>Новый урок</td>
                        <td>Скрытый комментарий</td>
                    </tr>

                    <tr v-for="event in this.lessonLogEvents">
                        <td style="vertical-align: middle; font-size: 2em;">{{reformatDateTime(event.lessonLogEventDateTime)}}</td>
                        <td>
                            <template v-if="event.lessonOldId !== null">
                                <p>{{reformatDate(event.lessonOldCalendarDate)}} @ {{event.lessonOldRingTime.substr(0,5)}} = {{event.lessonOldStudentGroupName}}</p>
                                <p>{{dowWeek(event.lessonOldCalendarDate)}}</p>
                                <p>{{event.lessonOldDisciplineName}}</p>
                                <p>{{event.lessonOldTeacherFio}}</p>
                                <p :class="{bigRed: event.lessonNewId !== null}">{{event.lessonOldAuditoriumName}}</p>
                            </template>
                            <template v-if="event.lessonOldId == null">
                                <p style="font-size: 2em; color: #2fb631">
                                    Занятия добавлены =>
                                </p>
                            </template>
                        </td>
                        <td>
                            <template v-if="event.lessonNewId !== null">
                                <p>{{reformatDate(event.lessonNewCalendarDate)}} @ {{event.lessonNewRingTime.substr(0,5)}} = {{event.lessonNewStudentGroupName}}</p>
                                <p>{{dowWeek(event.lessonNewCalendarDate)}}</p>
                                <p>{{event.lessonNewDisciplineName}}</p>
                                <p>{{event.lessonNewTeacherFio}}</p>
                                <p :class="{bigGreen: event.lessonOldId !== null}">{{event.lessonNewAuditoriumName}}</p>
                            </template>
                            <template v-if="event.lessonNewId == null">
                                <p style="font-size: 2em; color: #ff6740">
                                    <= Занятия удалены
                                </p>
                            </template>
                        </td>
                        <td>
                            {{event.lessonLogEventHiddenComment}}
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        <modal v-if="showOKWindow">
            <template v-slot:body>
                <div style="width: 100%; text-align: center;">
                    <button style="width: 400px; font-size: 2em;" @click="showOKWindow = false;" class="button is-primary">
                        Операция завершена
                    </button>
                </div>
            </template>
        </modal>

    </div>
</template>

<script>
    import modal from './Modal';
    import moment from "moment";

    export default {
        name: "lleTeacher",
        props: [
            'teachers',
            'weekCount',
            'currentWeek',
            'semesterStarts',
        ],
        components: {
            'modal' : modal
        },
        data() {
            return {
                teacherId: '',
                selectedWeeks: [],
                severalWeeks: true,
                weeksCount: this.weekCount,
                totalCount: "",
                showOKWindow: false,
                loading: false,
                selectedChunk: {offset:0, times:""},
                lessonLogEvents: [],
                weekLessonLogEvents: [],
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                momentSemesterStarts: moment(this.semesterStarts, 'DD.MM.YYYY').startOf('isoweek'),
                selectedDow: 1,
            }
        },
        methods: {
            loadTeacherEvents() {
                let apiUrl = '/lleByTeacher?teacherId=' + this.teacherId + '&weeks=' + this.selectedWeeks.join('|');

                if (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)
                {
                    let weeksString = this.range(1, this.weekCount).join('|');

                    apiUrl = '/lleByTeacher?teacherId=' + this.teacherId + '&weeks=' + weeksString;
                }

                this.lessonLogEvents = [];
                this.loading = true;
                axios.get(apiUrl)
                    .then(response => {
                        this.weekLessonLogEvents = response.data;
                        this.filterEvents();
                        this.loading = false;
                    });
            },
            filterEvents() {
                this.lessonLogEvents = this.weekLessonLogEvents.filter(item => {
                    console.log('item');
                    console.log(item);
                    if (item.lessonOldCalendarDate !== null) {
                        let oldMoment = moment(item.lessonOldCalendarDate, 'YYYY-MM-DD');
                        if (oldMoment.format('E') == this.selectedDow) {
                            return true;
                        }
                    }

                    if (item.lessonNewCalendarDate !== null) {
                        let newMoment = moment(item.lessonNewCalendarDate, 'YYYY-MM-DD');
                        if (newMoment.format('E') == this.selectedDow) {
                            return true;
                        }
                    }

                    return false;
                });
            },
            reformatDate(date) {
                return date.substr(8,2) + '.' + date.substr(5,2) + '.' + date.substr(0,4);
            },
            reformatDateTime(date) {
                return moment(date, 'YYYY-MM-DD HH:mm:ss').format('DD.MM.YYYY HH:mm:ss');
            },
            dowWeek(date) {
                let m = moment(date, "YYYY-MM-DD");

                let diff = m.diff(this.momentSemesterStarts, 'days');

                let week = Math.floor((diff / 7) + 1);

                return this.dowRu[m.format('E')-1] + ' (' + week + ')';
            },
            todaysDow() {
                let dow = parseInt(moment().format('E'));
                return dow;
            },
            weekToggled(week) {
                if (!this.severalWeeks) {
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(week);
                    this.loadTeacherEvents();
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
                            this.loadTeacherEvents()
                        }

                        if (week > this.selectedWeeks[0]) {
                            for(let i = this.selectedWeeks[0]+1; i <= week; i++) {
                                this.selectedWeeks.push(i);
                            }
                            this.loadTeacherEvents()
                        }

                        return;
                    }

                    if (event.ctrlKey)
                    {
                        this.selectedWeeks = [];
                        this.selectedWeeks.push(week);
                        this.loadTeacherEvents();

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

                    if (this.selectedWeeks.length === 0) {
                        this.lessonLogEvents = [];
                    } else {
                        this.loadTeacherEvents();
                    }
                }
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
                    this.loadTeacherEvents();
                }
            },
        },
        mounted() {
            this.selectedDow = this.todaysDow();

            if (this.teachers.length !== 0) {
                this.teacherId = this.teachersSorted[0].id;
                this.selectedWeeks = [];
                if (this.currentWeek > 0 && this.currentWeek <= this.weeksCount) {
                    this.selectedWeeks.push(this.currentWeek);
                } else {
                    this.selectedWeeks.push(1);
                }
                this.loadTeacherEvents();
            }
        },
        computed: {
            teachersSorted() {
                let result = [];
                for (var index in this.teachers) {
                    let teacher = this.teachers[index];
                    result.push(teacher);
                }

                result.sort((a,b) => {
                    if (a.fio === b.fio) return 0;
                    return a.fio < b.fio ? -1 : 1;
                });

                return result;
            },

        },
    }
</script>

<style scoped>
.bigGreen {
    font-size: 2em;
    color: #2fb631;
}

.bigRed {
    font-size: 2em;
    color: #ff6740;
}

</style>
