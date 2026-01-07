<template>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="my-0">Productos</h3>

            <button
                class="btn btn-custom btn-sm mt-2 mr-2"
                type="button"
                @click.prevent="clickCompetitors()"
            >
                <i class="fa fa-plus-circle"></i>Competidores
            </button>
            <button
                class="btn btn-custom btn-sm mt-2 mr-2"
                type="button"
                @click.prevent="clickImport()"
            >
                <i class="fa fa-plus-circle"></i> Importar
            </button>
            <button
                class="btn btn-custom btn-sm mt-2 mr-2"
                type="button"
                @click.prevent="clickCreate()"
            >
                <i class="fa fa-plus-circle"></i> Nuevo
            </button>
        </div>
        <div class="card-body">  
            <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Digemid</th>
                        <th>Registro Sanitario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in records" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ row.description }}</td>
                        <td>{{ row.cod_digemid }}</td>
                        <td>{{ row.sanitary }}</td>
                        <td>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-primary"  @click.prevent="clickCreate(row.id)"> Editar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <accounting-form :showDialog.sync="showDialog"
                           :recordId="recordId"></accounting-form>

        <import :showDialog.sync="showDialogImport"
                           ></import>

        <competitors :showDialog.sync="showDialogCompetitors"
                           ></competitors>

    </div>
</template>

<script>

    import AccountingForm from './form.vue'
    import Import from './import.vue'
    import Competitors from './competence/competitors.vue'

    export default {
        components: {AccountingForm, Import, Competitors},
        data() {
            return {
                showDialog: false,
                showDialogImport: false,
                showDialogCompetitors: false,
                resource: 'items',
                recordId: null,
                record: {},
                records: [],
                loading_submit: false,
                headers: headers_token,

            }
        },
        created() {
            this.$eventHub.$on('reloadData', () => {
                this.getData()
            })
            this.getData()
        },
        methods: {
            getData() {
                this.$http.get(`/${this.resource}/records`)
                    .then(response => {
                        this.records = response.data.data
                    })
            },
            clickCreate(recordId) {
                this.recordId = recordId
                this.showDialog = true
            },
            clickImport() {
                this.showDialogImport = true
            },
            clickCompetitors() {
                this.showDialogCompetitors = true
            },
            async submit() {
                console.log("submit")
                this.loading_submit = true
                await this.$refs.upload.submit()
                this.loading_submit = false
            },
        }
    }
</script>
