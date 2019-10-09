<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Количество часов в расписании по учителям</div>

                    <div class="card-body">
                        <div>
                            Дисциплины преподавателя:
                            <select style="margin-left: 1em;" v-model="selectedTeacherId" @change="loadTeacherDisciplines()">
                                <option v-for="teacher in teachersSorted" :value="teacher.id">{{teacher.fio}}</option>
                            </select>
                        </div>


                        <div style="text-align: center; margin-top: 1em;">
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
                                'is-danger': selectedWeeks.includes(week)}"
                            >{{week}}</button>

                            <div style="display: flex; flex-direction: row;">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" v-model="severalWeeks" @change="severalWeeksSwitchFlipped();" class="custom-control-input" id="customSwitch1">
                                    <label class="custom-control-label" for="customSwitch1">Несколько недель</label>
                                </div>

                                <div class="custom-control custom-switch" style="margin-left: 1em;">
                                    <input type="checkbox" v-model="onlyActiveDisciplines"  class="custom-control-input" id="customSwitch2">
                                    <label class="custom-control-label" for="customSwitch2">Только активные дисциплины</label>
                                </div>
                            </div>

                        </div>

                        <div v-if="errorMessage !== ''" class="alert alert-danger alert-block" style="margin-top: 1em;">
                            <button type="button" class="close" @click="errorMessage = ''" data-dismiss="alert">×</button>
                            <strong>{{ errorMessage }}</strong>
                        </div>

                        <div v-if="loading === true" style="font-size: 2em; text-align: center">
                            Загрузка ...
                        </div>

                        <div v-if="loading === false && teacherDisciplines.length === 0" style="font-size: 2em; text-align: center">
                            Дисциплин нет
                        </div>

                        <table v-if="loading === false" style="margin: 10px" class="table td-center is-bordered blackborders">
                            <tr>
                                <td>Дисциплина + Группа</td>
                                <td style="font-weight: 700;">Часы в неделю</td>

                                <td v-for="week in selectedWeeksSorted">{{week}}</td>

                            </tr>
                            <tr v-for="discipline in disciplinesFiltered">
                                <td style="text-align: left !important;" :class="{
                                        'budget': discipline.type === 1,
                                        'vneur': discipline.type === 2,
                                        'plat': discipline.type === 3,
                                        'inactive': discipline.active === 0}">
                                    <a :href="'/disciplines/' + discipline.DisciplineId">{{discipline.Name}} <br />{{discipline.groupName}}</a>
                                </td>

                                <td style="font-weight: 700;" :class="{
                                        'budget': discipline.type === 1,
                                        'vneur': discipline.type === 2,
                                        'plat': discipline.type === 3,
                                        'inactive': discipline.active === 0}">
                                    {{discipline.AuditoriumHoursPerWeek}}
                                </td>

                                <td v-for="week in selectedWeeksSorted"
                                    :class="{
                                        'zero': discipline.hoursByWeek[week] !== parseInt(discipline.AuditoriumHoursPerWeek) && discipline.hoursByWeek[week] === 0,
                                        'less': discipline.hoursByWeek[week] !== 0 && discipline.hoursByWeek[week] < discipline.AuditoriumHoursPerWeek,
                                        'ok': discipline.hoursByWeek[week] === parseInt(discipline.AuditoriumHoursPerWeek),
                                        'more': discipline.hoursByWeek[week] !== 0 && discipline.hoursByWeek[week] > discipline.AuditoriumHoursPerWeek
                                }">
                                    {{discipline.hoursByWeek[week]}}
                                </td>
                            </tr>

                            <tr>
                                <td style="font-weight:700;">Итого</td>
                                <td>
                                    <strong>{{disciplinesFilteredHours.all}} <br />
                                    ({{disciplinesFilteredHours[1]}} / {{disciplinesFilteredHours[2]}} / {{disciplinesFilteredHours[3]}})</strong>
                                </td>
                                <td v-for="week in selectedWeeksSorted" style="font-weight:700; vertical-align:middle;">
                                    {{totalWeekHours[week]}}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "TeacherHours",
        props: [
            'studentGroups',
            'teachers',
            'teacherId',
            'weekCount'
        ],
        data() {
            return {
                groups: this.studentGroups,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                loading: false,
                selectedTeacherId: -1,
                teacherDisciplines: [],
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                attestation: ["нет", "зачёт", "экзамен", "зачёт и экзамен", "зачёт с оценкой"],
                errorMessage: "",
                copyStudentGroupId: -1,
                selectedWeeks: [],
                severalWeeks: true,
                weeksCount: this.weekCount,
                teacherList: this.teachers,
                onlyActiveDisciplines: true,
            }
        },
        methods: {
            loadTeacherDisciplines() {
                this.loading = true;
                axios
                    .get('/api.php?action=list&listtype=disciplines&teacherId=' + this.selectedTeacherId + '&hoursByWeek')
                    .then(response => {
                        this.loading = false;
                        this.teacherDisciplines = response.data;
                    });
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
            range(start, end) {
                return Array(end - start + 1).fill().map((_, idx) => start + idx)
            },
        },
        mounted() {
            this.allWeeksClicked();

            if (this.teachersSorted.length !== 0) {
                let teacher = this.teachersSorted.filter(g => g.id === this.teacherId);
                if ((this.teacherId !== "-1") && (teacher.length !== 0)) {
                    this.selectedTeacherId = teacherId;
                } else {
                    this.selectedTeacherId = this.teachersSorted[0].id;
                }

                this.loadTeacherDisciplines();
            }
        },
        computed: {
            disciplinesFiltered() {
                return this.onlyActiveDisciplines ?
                    this.disciplinesSorted.filter(d => d.active === 1) :
                    this.disciplinesSorted;
            },
            disciplinesFilteredHours() {
                return {
                    'all' : this.disciplinesFiltered.map(d => parseInt(d.AuditoriumHoursPerWeek)).reduce((a,b) => a + b, 0),
                    1 : this.disciplinesFiltered.filter(d => d.type === 1).map(d => parseInt(d.AuditoriumHoursPerWeek)).reduce((a,b) => a + b, 0),
                    2 : this.disciplinesFiltered.filter(d => d.type === 2).map(d => parseInt(d.AuditoriumHoursPerWeek)).reduce((a,b) => a + b, 0),
                    3 : this.disciplinesFiltered.filter(d => d.type === 3).map(d => parseInt(d.AuditoriumHoursPerWeek)).reduce((a,b) => a + b, 0)
                };
            },
            disciplinesSorted() {
                return this.teacherDisciplines.sort((a,b) => {
                    if (a.Name === b.Name) {
                        let num1 = parseInt(a.groupName.split(' ')[0]);
                        let num2 = parseInt(b.groupName.split(' ')[0]);

                        if (num1 === num2) {
                            if (a.groupName === b.groupName) return 0;
                            return a.groupName < b.groupName ? -1 : 1;
                        }
                        return (num1 < num2) ? -1 : 1;
                    } else {
                        return a.Name < b.Name ? -1 : 1;
                    }
                });
            },
            selectedWeeksSorted() {
                if (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1) {
                    return this.range(1, this.weekCount);
                }

                return this.selectedWeeks.sort((a,b) => {
                    if (a === b) return 0;
                    return a < b ? -1 : 1;
                });
            },
            teachersSorted() {
                return Object.keys(this.teacherList)
                    .map((key) => {
                        return this.teacherList[key]
                    })
                    .sort((a,b) => {
                        if (a.fio === b.fio) return 0;
                        return (a.fio < b.fio) ? -1 : 1;
                    });
            },
            totalWeekHours() {
                let result = {};
                for(let i = 1; i <= this.weekCount; i++) {
                    result[i] = 0;
                }

                for(let i = 0; i < this.teacherDisciplines.length; i++) {
                    let discipline = this.teacherDisciplines[i];

                    for(let week = 1; week <= this.weekCount; week++) {
                        result[week] += discipline.hoursByWeek[week];
                    }
                }

                return result;
            },
        }
    }
</script>

<style scoped>
    .more {
        background-color: #D3A5CE;
    }

    .ok {
        background-color: #B8E5AE;
    }

    .less {
        background-color: #F2B7A2;
    }

    .zero {
        background-color: #FF7F7F;
    }

    .blackborders th, .blackborders td {
        border: 1px solid black !important;
    }

    .budget {
        background-color: rgba(255,255,0,0.2);
    }

    .vneur {
        background-color: rgba(20,255,0,0.2);
    }

    .plat {
        background-color: rgba(23,67,255,0.2);
    }

    .inactive {
        background-color: rgba(33,33,33,0.2);
    }

</style>

