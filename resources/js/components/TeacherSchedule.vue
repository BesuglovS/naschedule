<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Расписание преподавателя</div>

                    <div class="card-body">
                        <select v-model="selectedTeacherId" @change="loadTeacherSchedule(selectedWeek)">
                            <option v-for="teacher in teachersSorted" :value="teacher.id">{{teacher.fio}}</option>
                        </select>

                        <div style="text-align: center;" v-if="this.loading">Загрузка...</div>

                        <div id="teachersSchedule" style="margin-top: 1em;">
                            <div style="text-align: center;">
                                Недели:
                                <button @click="loadTeacherSchedule(-1)"
                                        style="margin-right:0.5em; margin-bottom: 0.5em;"
                                        :class="{'button': true, 'is-primary': selectedWeek !== -1, 'is-danger': selectedWeek === -1 }"
                                >Все</button>
                                <button @click="loadTeacherSchedule(week)"
                                        v-for="week in this.weeksCount"
                                        style="margin-right:0.5em; margin-bottom: 0.5em;"
                                        :class="{'button': true, 'is-primary': selectedWeek !== week, 'is-danger': selectedWeek === week }"
                                >{{week}}</button>
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
                                    <td v-if="teacherSchedule[1] && teacherSchedule[1].length !== 0">Понедельник</td>
                                    <td v-if="teacherSchedule[2] && teacherSchedule[2].length !== 0">Вторник</td>
                                    <td v-if="teacherSchedule[3] && teacherSchedule[3].length !== 0">Среда</td>
                                    <td v-if="teacherSchedule[4] && teacherSchedule[4].length !== 0">Четверг</td>
                                    <td v-if="teacherSchedule[5] && teacherSchedule[5].length !== 0">Пятница</td>
                                    <td v-if="teacherSchedule[6] && teacherSchedule[6].length !== 0">Суббота</td>
                                </tr>

                                <tr v-for="ring in this.scheduleRings">
                                    <td>{{ring}}</td>
                                    <td v-if="Object.keys(teacherSchedule[dow]).length !== 0" v-for="dow in 6">
                                        <div style="border: none;" v-if="teacherSchedule[dow][ring] !== undefined">
                                            <template v-for="tfd in Object.keys(teacherSchedule[dow][ring])">
                                                {{teacherSchedule[dow][ring][tfd]["lessons"][0]["groupName"]}}<br />
                                                {{teacherSchedule[dow][ring][tfd]["lessons"][0]["discName"]}} <br />
                                                {{teacherSchedule[dow][ring][tfd]["lessons"][0]["teacherFIO"]}} <br />
                                                <template v-for="auditorium in Object.keys(teacherSchedule[dow][ring][tfd]['weeksAndAuds'])">
                                                    {{combineWeeksToRange(teacherSchedule[dow][ring][tfd]["weeksAndAuds"][auditorium])}} - {{auditorium}}<br />
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
                selectedWeek: 1
            }
        },
        methods: {
            loadTeacherSchedule(week) {
                this.selectedWeek = week;

                let apiUrl = '/api.php?action=teacherWeeksSchedule&teacherId=' + this.selectedTeacherId + '&weeks=' + week + '&compactResult';

                if (this.selectedWeek === -1)
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

                        window.ts1 = this.teacherSchedule[1];

                        this.teacherSchedule = data;
                    });
            },
            range(start, end) {
                return Array(end - start + 1).fill().map((_, idx) => start + idx)
            },
            combineWeeksToRange(ws) {
                let weeks = ws.slice(0);

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
                    let aVal = (a.toString().indexOf('-') === -1) ? a : a.toString().substr(0, a.indexOf('-'));
                    let bVal = (b.toString().indexOf('-') === -1) ? b : b.toString().substr(0, b.indexOf('-'));

                    if (aVal === bVal) return 0;
                    return (aVal < bVal) ? -1 : 1;
                });

                let stringResult = result.join(', ');

                return stringResult;
            }
        },
        mounted() {
            if (this.selectedTeacherId === -1)
            {
                if (this.teacherList.length !== 0) {
                    this.selectedTeacherId = this.teachersSorted[0].id;
                    this.loadTeacherSchedule(1);
                }
                else {
                    this.selectedTeacherId = 0;
                }
            } else {
                this.loadTeacherSchedule(1);
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

</style>
