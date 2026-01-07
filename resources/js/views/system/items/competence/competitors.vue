<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create" class="dialog-import">
        <template>
            <div>
                <div class="page-header pr-0">
                    <!--<h2><a href="/brands">
                        <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-category-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 4h6v6h-6z" /><path d="M4 14h6v6h-6z" /><path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M7 7m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /></svg>
                    </a></h2>-->
                    <ol class="breadcrumbs">
                        <li class="active"><span>{{ title }}</span></li>
                    </ol>
                    <div class="right-wrapper pull-right">
                            <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i> Nuevo</button>

                    </div>
                </div>
                <div class="card tab-content-default row-new mb-0">
                    <!-- <div class="card-header bg-info">
                        <h3 class="my-0">Listado de {{ title }}</h3>
                    </div> -->
                    <div class="card-body">
                        <data-table :resource="resource">
                            <tr slot="heading">
                                <!-- <th>#</th> -->
                                <th>ID</th>
                                <th>Nombre</th>
                                <th class="text-right">Acciones</th>
                            <tr>
                            <tr slot-scope="{ index, row }">
                                <!-- <td>{{ index }}</td> -->
                                <td>{{ row.id }}</td>
                                <td>{{ row.description }}</td>
                                <td class="text-right">
                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickCreate(row.id)">Editar</button>
                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickDelete(row.id)">Eliminar</button>
                                </td>
                            </tr>
                        </data-table>
                    </div>

                    <competence-form 
                        :showDialog.sync="showDialogForm"
                        :recordId="recordId"
                            ></competence-form> 
                </div>
            </div>
        </template>
    </el-dialog>
</template>
<script>

    import CompetenceForm from './CompetenceForm.vue' 
    import DataTable from '../../../../components/DataTable.vue'
    import {deletable} from '../../../../mixins/deletable'

    export default {
        props: ['showDialog'],
        mixins: [deletable],
        components: {DataTable, CompetenceForm},
        data() {
            return {
                title: null,
                showDialog: false, 
                showDialogForm: false,
                resource: 'competitors',
                recordId: null,
                titleDialog: null,
            }
        },
        created() {
            this.title = 'Competidores'
        },
        methods: { 
            clickCreate(recordId = null) {
                this.recordId = recordId
                this.showDialogForm = true
            }, 
            clickDelete(id) {
                this.destroy(`/${this.resource}/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            },
            create() {
                this.titleDialog = 'Competidores'
            },
            close() {
                this.$emit('update:showDialog', false)
                this.initForm()
            },
        }
    }
</script>