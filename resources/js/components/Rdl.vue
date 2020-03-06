<template>
    <div style="text-align: center;">
        <button @click="removeDuplicates();" style="font-size: 2em;" class="button is-primary">Удалить дублирующиеся уроки</button>

        <div v-if="loading === true" style="font-size: 2em; text-align: center; margin-left: 2em;">
            Загрузка <img :src="'./assets/img/loading.gif'" style="height:50px;" />
        </div>

        <modal v-if="showOKWindow">
            <template v-slot:body>
                <div style="width: 100%; text-align: center;">
                    <button style="width: 800px; font-size: 2em;" @click="showOKWindow = false;" class="button is-primary">
                        Операция завершена. Удалено {{lessons.length}} уроков.
                    </button>
                </div>
            </template>
        </modal>
    </div>
</template>

<script>
    import modal from './Modal';

    export default {
        name: "Rdl",
        components: {
            'modal' : modal
        },
        data() {
            return {
                lessons: [],
                loading: false,
                showOKWindow: false,
            }
        },
        methods: {
            removeDuplicates() {
                this.loading = true;
                axios.get('/rdl')
                    .then(response => {
                        this.loading = false;
                        this.showOKWindow = true;

                        this.lessons = response.data;


                    });
            },
        },
    }
</script>

<style scoped>

</style>
