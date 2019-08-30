<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Количество часов в расписании по группам</div>

                    <div class="card-body">
                        <div>
                            Дисциплины класса:
                            <select style="margin-left: 1em;" v-model="studentGroupId" @change="loadGroupDisciplines()">
                                <option v-for="sg in groupsSorted" :value="sg.id">{{sg.name}}</option>
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

                            <div class="custom-control custom-switch">
                                <input type="checkbox" v-model="severalWeeks" @change="severalWeeksSwitchFlipped();" class="custom-control-input" id="customSwitch1">
                                <label class="custom-control-label" for="customSwitch1">Несколько недель</label>
                            </div>
                        </div>

                        <div v-if="errorMessage !== ''" class="alert alert-danger alert-block" style="margin-top: 1em;">
                            <button type="button" class="close" @click="errorMessage = ''" data-dismiss="alert">×</button>
                            <strong>{{ errorMessage }}</strong>
                        </div>

                        <div v-if="loading === true" style="font-size: 2em; text-align: center">
                            Загрузка ...
                        </div>

                        <div v-if="loading === false && groupDisciplines.length === 0" style="font-size: 2em; text-align: center">
                            Дисциплин нет
                        </div>

                        <table v-if="loading === false" style="margin: 10px" class="table td-center is-bordered disciplineTable">
                            <tr>
                                <td>Дисциплина + ФИО</td>
                                <td>Группа</td>
                                <td style="font-weight: 700;">Часы в неделю</td>

                                <td v-for="week in selectedWeeksSorted">{{week}}</td>

                            </tr>
                            <tr v-for="discipline in disciplinesSorted">
                                <td style="text-align: left !important;"><a :href="'/disciplines/' + discipline.DisciplineId">{{discipline.Name}}<br />{{discipline.teacherFio}}</a></td>

                                <td>{{discipline.groupName}}</td>

                                <td style="font-weight: 700;">
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
                                <td></td>
                                <td></td>
                                <td v-for="week in selectedWeeksSorted" style="font-weight:700;">
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
        name: "DisciplineHours",
        props: [
            'studentGroups',
            'groupId',
            'weekCount'
        ],
        data() {
            return {
                groups: this.studentGroups,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                loading: false,
                studentGroupId: -1,
                groupDisciplines: [],
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                attestation: ["нет", "зачёт", "экзамен", "зачёт и экзамен", "зачёт с оценкой"],
                errorMessage: "",
                copyStudentGroupId: -1,
                selectedWeeks: [],
                severalWeeks: true,
                weeksCount: this.weekCount,
            }
        },
        methods: {
            loadGroupDisciplines() {
                this.loading = true;
                axios
                    .get('/api.php?action=list&listtype=disciplines&groupId=' + this.studentGroupId + '&hoursByWeek')
                    .then(response => {
                        this.loading = false;
                        this.groupDisciplines = response.data;
                    });
            },
            allWeeksClicked() {
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

            if (this.groups.length !== 0) {
                let group = this.groupsSorted.filter(g => g.id === this.groupId);
                if ((this.groupId !== "-1") && (group.length !== 0)) {
                    this.studentGroupId = this.groupId;
                    this.copyStudentGroupId = this.groupsSorted[0].id;
                } else {
                    this.studentGroupId = this.groupsSorted[0].id;
                    this.copyStudentGroupId = this.groupsSorted[0].id;
                }

                this.loadGroupDisciplines();
            }
        },
        computed: {
            disciplinesSorted() {
                return this.groupDisciplines.sort((a,b) => {
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
            },
            totalWeekHours() {
                let result = {};
                for(let i = 1; i <= this.weekCount; i++) {
                    result[i] = 0;
                }

                for(let i = 0; i < this.groupDisciplines.length; i++) {
                    let discipline = this.groupDisciplines[i];

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

    .disciplineTable th, .disciplineTable td {
        border: 1px solid black !important;
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
</style>

