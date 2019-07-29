<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Экзамены группы</div>

                    <div class="card-body">
                        <select v-model="selectedGroupId" @change="loadExams()" name="group_id">
                            <option v-for="sg in groupsSorted" :value="sg.id">{{sg.name}}</option>
                        </select>

<!--                        <button type="button" @click="loadExams" class="button is-primary">Загрузить экзамены</button>-->

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

                                <td>{{exam.ConsultationDateTime | formatDate | emptyIf2020Date}}</td>

                                <td>{{exam.ConsultationAuditoriumName}}</td>

                                <td>{{exam.ExamDateTime | formatDate | emptyIf2020Date}}</td>

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
            'groupId': Number
        },
        data() {
          return {
              groups: this.studentGroups,
              selectedGroupId: 0,
              exams: [],
              csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              firstLoad: true,
              loading: false,
              studentGroupId: this.groupId
          }
        },
        methods: {
            loadExams() {
                this.exams = [];
                this.loading = true;
                this.firstLoad = false;
                axios
                    .get('/api.php?action=groupExams&groupId=' + this.selectedGroupId)
                    .then(response => {
                        let data = response.data[this.selectedGroupId].Exams;
                        this.exams = data.sort((a,b) => {
                            let num1 = parseInt(a.StudentGroupName.split(' ')[0]);
                            let num2 = parseInt(b.StudentGroupName.split(' ')[0]);

                            if (num1 === num2) {
                                if (a.StudentGroupName === b.StudentGroupName) {
                                    if (a.DisciplineName === b.DisciplineName) return 0;
                                    return a.DisciplineName < b.DisciplineName ? -1 : 1;
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
            console.log(this.studentGroupId);
            if (this.studentGroupId === -1)
            {
                if (this.groups.length !== 0) {
                    this.selectedGroupId = this.groupsSorted[0].id;
                    this.loadExams();
                }
                else {
                    this.selectedGroupId = 0;
                }
            } else {
                this.selectedGroupId = this.studentGroupId;
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

</style>
