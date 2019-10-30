<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <span style="margin-right: 1em;">
                            Экзамены группы
                        </span>

                        <button @click="loadExams();"
                                style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                class="button is-primary">R
                        </button>
                    </div>

                    <div class="card-body">
                        <select v-model="studentGroupId" @change="loadExams()">
                            <option v-for="sg in groupsSorted" :value="sg.id">{{sg.name}}</option>
                        </select>

                        <div style="text-align: center;" v-if="exams.length === 0 && ! firstLoad && ! this.loading">Экзаменов нет</div>

                        <div style="text-align: center;" v-if="this.loading">Загрузка...</div>

                        <table v-if="exams.length !== 0" style="margin-top: 2em;" class="table td-center is-bordered">
                            <tr v-for="exam in exams">
                                <td>
                                    <a :href="'/exams/' + exam.id">{{exam.DisciplineName}}</a>
                                    <br />
                                    {{exam.TeacherFIO}}
                                </td>

                                <td>{{exam.StudentGroupName}}</td>

                                <td>{{exam.ConsultationDateTime | formatDateTime | emptyIf2020Date}}</td>

                                <td>{{exam.ConsultationAuditoriumName}}</td>

                                <td>{{exam.ExamDateTime | formatDateTime | emptyIf2020Date}}</td>

                                <td>{{exam.ExamAuditoriumName}}</td>

                                <td><a :href="'/exams/' + exam.id + '/edit'" class="button is-primary">Редактировать</a></td>

                                <td>
                                    <form method="POST" :action="'/exams/' + exam.id">
                                        <input type="hidden" name="_token" :value="csrf">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="button is-danger">Удалить</button>
                                    </form>
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
        name: "groupSession",
        props: {
            'studentGroups': Object,
            'groupId': Number,
        },
        data() {
          return {
              groups: this.studentGroups,
              exams: [],
              csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              firstLoad: true,
              loading: false,
              studentGroupId: this.groupId,
          }
        },
        methods: {
            loadExams() {
                const moment = require('moment');

                this.exams = [];
                this.loading = true;
                this.firstLoad = false;
                axios
                    .get('/api.php?action=groupExams&groupId=' + this.studentGroupId + '&internal=1')
                    .then(response => {
                        let data = response.data[this.studentGroupId].Exams;
                        this.exams = data.sort((a,b) => {
                            let num1 = parseInt(a.StudentGroupName.split(' ')[0]);
                            let num2 = parseInt(b.StudentGroupName.split(' ')[0]);

                            if (num1 === num2) {
                                if (a.StudentGroupName === b.StudentGroupName) {
                                    let aDate = (a.ConsultationDateTime !== '2020-01-01 00:00:00') ?
                                        moment(a.ConsultationDateTime, 'YYYY-MM-DD HH:mm:ss') :
                                        ((a.ExamDateTime !== '2020-01-01 00:00:00') ?
                                            moment(a.ExamDateTime, 'YYYY-MM-DD HH:mm:ss') :
                                            moment('2100-01-01 00:00:00', 'YYYY-MM-DD HH:mm:ss')
                                        );

                                    let bDate = (b.ConsultationDateTime !== '2020-01-01 00:00:00') ?
                                        moment(b.ConsultationDateTime, 'YYYY-MM-DD HH:mm:ss') :
                                        ((b.ExamDateTime !== '2020-01-01 00:00:00') ?
                                                moment(b.ExamDateTime, 'YYYY-MM-DD HH:mm:ss') :
                                                moment('2100-01-01 00:00:00', 'YYYY-MM-DD HH:mm:ss')
                                        );

                                    if (aDate === bDate) return 0;
                                    return aDate < bDate ? -1 : 1;
                                }
                                return (a.StudentGroupName < b.StudentGroupName) ? -1 : 1;
                            }
                            else
                            {
                                return (num1 < num2) ? -1 : 1;
                            }
                        });
                        this.loading = false;
                    });
            }
        },
        mounted() {
            if (this.studentGroupId === -1)
            {
                if (this.groups.length !== 0) {
                    this.studentGroupId = this.groupsSorted[0].id;
                    this.loadExams();
                }
            } else {
                this.loadExams();
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
    .isPrimary {
        background-color: #7957d5;
        border-color: transparent;
        color: white;
    }
</style>
