<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <span style="margin-right: 1em;">
                            Список дисциплин
                        </span>

                        <button @click="loadGroupDisciplines();"
                                style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                class="button is-primary">R
                        </button>
                    </div>

                    <div class="card-body">
                        Дисциплины класса:
                        <select v-model="studentGroupId" @change="loadGroupDisciplines()">
                            <option v-for="sg in groupsSorted" :value="sg.id">{{sg.name}}</option>
                        </select>

                        <span style="margin-left: 2em;">
                            Cкопировать дисциплины из группы
                            <select v-model="copyStudentGroupId">
                                <option v-for="sg in groupsSorted" :value="sg.id">{{sg.name}}</option>
                            </select>
                            <button @click="copyDisciplines()" class="button is-primary">Скопировать</button>
                        </span>

                        <div v-if="errorMessage !== ''" class="alert alert-danger alert-block" style="margin-top: 1em;">
                            <button type="button" class="close" @click="errorMessage = ''" data-dismiss="alert">×</button>
                            <strong>{{ errorMessage }}</strong>
                        </div>

                        <div style="display: flex; flex-direction: row;">

                        <div class="custom-control custom-switch">
                            <input type="checkbox" v-model="teachersSelect"  class="custom-control-input" id="customSwitch1">
                            <label class="custom-control-label" for="customSwitch1">Выбор преподавателей</label>
                        </div>

                        <div class="custom-control custom-switch" style="margin-left: 1em;">
                            <input type="checkbox" v-model="onlyActiveDisciplines"  class="custom-control-input" id="customSwitch2">
                            <label class="custom-control-label" for="customSwitch2">Только активные дисциплины</label>
                        </div>

                        </div>

                        <div v-if="loading === true" style="font-size: 2em; text-align: center">
                            Загрузка ...
                        </div>

                        <div v-if="loading === false && groupDisciplines.length === 0" style="font-size: 2em; text-align: center">
                            Дисциплин нет
                        </div>

                        <table v-if="loading === false" style="margin: 10px" class="table td-center is-bordered">
                            <tr>
                                <td>Дисциплина</td>
                                <td>ФИО преподавателя</td>
                                <td>Группа</td>
                                <td>Часы</td>
                                <td>Отчётность</td>
                                <td>Активная</td>
                                <td>Тип</td>
                                <td>Редактирование</td>
                                <td>Удаление</td>
                            </tr>
                            <tr v-for="discipline in disciplinesFiltered"
                                :class="{
                                        'budget': discipline.type === 1,
                                        'vneur': discipline.type === 2,
                                        'plat': discipline.type === 3,
                                        'elect': discipline.type === 4,
                                        'inactive': discipline.active === 0}"
                            >
                                <td style="text-align: left !important;"><a :href="'/disciplines/' + discipline.DisciplineId">{{discipline.Name}}</a></td>

                                <td>
                                    <template v-if="!teachersSelect">
                                        {{discipline.teacherFio}}
                                    </template>
                                    <template v-if="teachersSelect">
                                        <select v-model="discipline.teacherId"
                                                @change="changeTfdTeacher(discipline.tfdId, discipline.teacherId, discipline.DisciplineId);">
                                            <option v-for="teacher in teachersSorted" :value="teacher.id">{{teacher.fio}}</option>
                                        </select>
                                    </template>
                                </td>

                                <td>{{discipline.groupName}}</td>

                                <td>{{discipline.AuditoriumHoursPerWeek}}</td>

                                <td>{{attestation[discipline.Attestation]}}</td>

                                <td>
                                    <input v-model="discipline.active" type="checkbox" style="transform: scale(2);" disabled name="active" id="activeDiscipline" />
                                </td>

                                <td>
                                    <template v-if="discipline.type == 1">Бюджет</template>
                                    <template v-if="discipline.type == 2">Внеурочные</template>
                                    <template v-if="discipline.type == 3">Платные</template>
                                    <template v-if="discipline.type == 4">Электив</template>
                                </td>

                                <td><a :href="'/disciplines/' + discipline.DisciplineId + '/edit'" class="button is-primary">Редактировать</a></td>

                                <td>
                                    <button @click="deleteDiscipline(discipline)" class="button is-danger">Удалить</button>
                                </td>
                            </tr>
                        </table>

                        <div style="text-align: center">
                            <a :href="'/disciplines/create?studentGroupId=' + this.studentGroupId" class="button is-primary">Добавить дисциплину</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "DisciplineList",
        props: [
            'studentGroups',
            'teachers',
            'groupId',
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
                teacherList: this.teachers,
                teachersSelect: false,
                onlyActiveDisciplines: true,
            }
        },
        methods: {
            loadGroupDisciplines() {
                this.loading = true;
                axios
                    .get('/api.php?action=list&listtype=disciplines&groupId=' + this.studentGroupId + '&internal=1')
                    .then(response => {
                        this.loading = false;
                        this.groupDisciplines = response.data;
                    });
            },
            deleteDiscipline(discipline) {
                this.loading = true;

                let url = '/disciplines/' + discipline.DisciplineId.toString() + '/ ';

                axios.post(url, {"_method" : "DELETE"})
                    .then(response => {
                        if (response.data.error !== undefined) {
                            this.loading = false;
                            this.errorMessage = "Дисциплину нельзя удалить. Ей назначен преподаватель.";
                        } else {
                            this.loadGroupDisciplines();
                        }
                    });

            },
            changeTfdTeacher(tfdId, teacherId, disciplineId) {
                if (tfdId === null) {
                    axios.post('/teacherDisciplines/store?discipline_id=' + disciplineId + '&teacher_id=' + teacherId);
                }
                else {
                    axios.post('/teacherDisciplines/updateTfd?tfdId=' + tfdId + '&teacherId=' + teacherId);
                }
            },
            copyDisciplines() {
                this.loading = true;

                axios
                    .post('/disciplinesCopyFromGroupToGroup?sourceGroupId=' + this.copyStudentGroupId + '&destinationGroupId=' + this.studentGroupId)
                    .then(response => {
                        this.loadGroupDisciplines();
                    });
            },
        },
        mounted() {
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
            disciplinesFiltered() {
               return this.onlyActiveDisciplines ?
                   this.disciplinesSorted.filter(d => d.active === 1) :
                   this.disciplinesSorted;
            },
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
            }
        }
    }
</script>

<style scoped>
    .isPrimary {
        background-color: #7957d5;
        border-color: transparent;
        color: white;
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

    .elect {
        background-color: rgba(75,255,255,0.2);
    }

    .inactive {
        background-color: rgba(33,33,33,0.2);
    }
</style>
