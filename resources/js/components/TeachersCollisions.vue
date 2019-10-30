<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <span style="margin-right: 1em;">
                            Коллизии преподавателей
                        </span>

                        <button @click="loadCollisions();"
                                style="white-space:normal !important; margin-right:0.5em; margin-bottom: 0.5em;
                                    font-size: 0.8em; justify-content: center; text-align: center;
                                    border-radius: 5px;"
                                class="button is-primary">R
                        </button>

                        <span style="margin-left: 2em; font-size: 2em;">{{totalCount}}</span>
                    </div>

                    <div class="card-body">
                        Недели: {{combineWeeksToRange(this.selectedWeeks)}}

                        <div style="margin-top: 1em; text-align: center; display: flex; flex-direction: column; justify-content: center;">
                            <div style="margin-bottom: 1em;">
                                Недели:
                                <button @click="loadAllCollisions()"
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

                            <div v-for="teacherCollisions in collisions" class="card" style="margin-bottom: 1em; width: 100%;">
                                <div class="card-title" style="text-align: center; vertical-align: middle; font-weight: bold; ">
                                    {{teacherCollisions['fio']}}
                                </div>
                                <div class="card-body">
                                    <table class="td-center" style="border-collapse:collapse; border: none; width: 100%;">
                                        <template v-for="teacherDayCollisions in teacherCollisions['collisions']">
                                        <template v-for="teacherDayCollision in teacherDayCollisions">
                                        <tr>
                                            <td>
                                                <table class="table td-center is-bordered" style="width: 100%">
                                                    <tr>
                                                        <td style="vertical-align: middle;">
                                                            {{reformatDate(teacherDayCollision[0].calendarDate)}}
                                                            ({{dow(teacherDayCollision[0].calendarDate)}})
                                                            <br />
                                                            {{teacherDayCollision[0].ringsTime.substr(0,5)}}
                                                        </td>
                                                        <template v-for="teacherDayCollisionLesson in teacherDayCollision">
                                                        <td>
                                                            {{teacherDayCollisionLesson.studentGroupName}} <br />
                                                            {{teacherDayCollisionLesson.disciplineName}} <br />
                                                            {{teacherDayCollisionLesson.auditoriumName}}
                                                        </td>
                                                        </template>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        </template>
                                        </template>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from "moment";
    export default {
        name: "TeachersCollisions",
        props: [
            'weekCount'
        ],
        data() {
            return {
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                firstLoad: true,
                loading: false,
                weeksCount: this.weekCount,
                selectedWeeks: [],
                severalWeeks: true,
                collisions: {},
                dowRu: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                totalCount: 0,
            }
        },
        methods: {
            cl(text) {
                console.log(text);
            },
            dow(date) {
                let m = moment(date, "YYYY-MM-DD");
                return this.dowRu[parseInt(m.format('E'))-1];

            },
            reformatDate(date) {
                return date.substr(8,2) + '.' + date.substr(5,2) + '.' + date.substr(0,4);
            },
            loadCollisions() {
                this.collisions = {};
                this.loading = true;

                let apiUrl = '/tcs?weeks=' + this.selectedWeeks.join('|');

                if (this.selectedWeeks.length === 1 && this.selectedWeeks[0] === -1)
                {
                    let weeksString = this.range(1, this.weekCount).join('|');

                    apiUrl = '/tcs?weeks='  + weeksString;
                }

                axios
                    .get(apiUrl + '&internal=1')
                    .then(response => {
                        let count = 0;
                        let result = [];
                        Object.keys(response.data).forEach((id) => {
                            result.push(response.data[id]);
                            count += Object.keys(response.data[id]['collisions']).length;
                        });
                        this.totalCount = count;

                        result.sort((a,b) => {
                            if (a.fio === b.fio) return 0;
                            return a.fio < b.fio ? -1 : 1;
                        });

                        this.collisions = result;
                        this.loading = false;
                    });
            },
            loadAllCollisions() {
                this.severalWeeks = true;
                this.selectedWeeks = [];
                this.selectedWeeks.push(-1);
                this.loadCollisions();
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
                    this.loadCollisions();
                }
            },
            weekToggled(week) {
                if (!this.severalWeeks) {
                    this.selectedWeeks = [];
                    this.selectedWeeks.push(week);
                    this.loadCollisions();
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
                            this.loadCollisions()
                        }

                        if (week > this.selectedWeeks[0]) {
                            for(let i = this.selectedWeeks[0]+1; i <= week; i++) {
                                this.selectedWeeks.push(i);
                            }
                            this.loadCollisions()
                        }

                        return;
                    }

                    if (event.ctrlKey)
                    {
                        this.selectedWeeks = [];
                        this.selectedWeeks.push(week);
                        this.loadCollisions();

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
                    this.loadCollisions();
                }
            },
        },
        mounted() {
            this.loadAllCollisions();
        },
        computed: {
        }
    }
</script>

<style scoped>

</style>
