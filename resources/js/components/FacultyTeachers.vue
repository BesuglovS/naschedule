<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <span style="margin-right: 1em;">
                            Преподаватели параллели
                        </span>

                        <button @click="loadFacultyDisciplines();"
                                style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                class="button isPrimary">R
                        </button>
                    </div>

                    <div class="card-body">
                        <select style="margin-bottom: 1.5em;" v-model="facultyId" @change="loadFacultyTeachers()">
                            <option v-for="faculty in facultiesSorted" :value="faculty.id">{{faculty.name}}</option>
                        </select>

                        <table class="table td-center is-bordered">
                            <tr>
                                <td></td>
                                <td v-for="group in facultyTeachers.groups" style="vertical-align: middle;">
                                    {{group.studentGroupsName}}
                                </td>
                            </tr>

                            <template v-for="disciplineName in Object.keys(facultyTeachers.disciplines).sort()">
                                <tr>
                                    <td style="vertical-align: middle;">{{disciplineName}}</td>
                                    <td v-for="group in facultyTeachers.groups" style="vertical-align: middle">
                                        <template v-if="facultyTeachers.disciplines[disciplineName][group.student_group_id] !== undefined"
                                            v-for="groupDiscipline in disciplinesSorted(facultyTeachers.disciplines[disciplineName][group.student_group_id])">
                                            {{fFromFio(groupDiscipline.teachersFio)}}
                                            <template v-if="groupDiscipline.studentGroupsName !== group.studentGroupsName">
                                                ({{groupDiscipline.studentGroupsName}})
                                            </template>
                                            <br />
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import modal from './Modal';

    export default {
        name: "FacultyTeachers",
        props: {
            'faculties': Array,
        },
        components: {
            'modal' : modal
        },
        data() {
            return {
                facultyId: -1,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                firstLoad: true,
                loading: false,
                facultyTeachers: {groups: [], disciplines: {}},
            }
        },
        methods: {
            loadFacultyTeachers() {
                this.loading = true;
                this.firstLoad = false;

                axios.get('/facTeachers?facultyId=' + this.facultyId + '&internal=1')
                    .then(response => {
                        this.facultyTeachers = response.data;
                    });
            },
            disciplinesSorted(list) {
                return (list === undefined) ? undefined : list.sort((a,b) => {
                    if (a.studentGroupsName === b.studentGroupsName) return 0;
                    return (a.studentGroupsName < b.studentGroupsName) ? -1 : 1;
                });
            },
            fFromFio(fio) {
                return (fio === null) ? null : fio.split(" ")[0];
            },
        },
        mounted() {
            if (this.faculties.length !== 0) {
                this.facultyId = this.faculties[0].id;
                this.loadFacultyTeachers();
            }
        },
        computed: {
            facultiesSorted() {
                let result = [];
                for (var index in this.faculties) {
                    let faculty = this.faculties[index];
                    result.push(faculty);
                }

                result.sort((a,b) => {
                    let aso = a.sorting_order;
                    let bso = b.sorting_order;

                    if (aso === bso) return 0;
                    return (aso < bso) ? -1 : 1;
                });

                return result;
            },
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
