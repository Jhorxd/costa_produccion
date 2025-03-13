<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/locations">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-warehouse"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-13l9 -4l9 4v13" /><path d="M13 13h4v8h-10v-6h6" /><path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Nueva ubicación </span></li>
            </ol>
        </div>
        <div class="card tab-content-default row-new mb-0 pt-md-0">
            <div class="tab-content tab-content-default">
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Nombre <span class="text-danger">*</span></label>
                                <el-input v-model="form.name"></el-input>
                                <small v-if="errors.name"
                                           class="form-control-feedback has-danger"
                                           v-text="errors.name[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Código <span class="text-danger">*</span></label>
                                <el-input v-model="form.code"></el-input>
                                <small v-if="errors.code"
                                           class="form-control-feedback has-danger"
                                           v-text="errors.code[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div
                                 class="form-group">
                                <label class="control-label">Estado</label>
                                <el-select v-model="form.status">
                                    <el-option
                                        v-for="option in status"
                                        :key="option.id"
                                        :disabled="option.id == form.status"
                                        :label="option.description"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small v-if="errors.code"
                                           class="form-control-feedback has-danger"
                                           v-text="errors.status[0]"></small>
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div 
                                 class="form-group">
                                <label class="control-label">Tipo de ubicación</label>
                                <el-select v-model="form.type_id">
                                    <el-option
                                        v-for="option in locationTypes"
                                        :key="option.id"
                                        :disabled="option.id == form.type_id"
                                        :label="option.name"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small v-if="errors.code"
                                           class="form-control-feedback has-danger"
                                           v-text="errors.type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div 
                                 class="form-group">
                                <label class="control-label">Almacén</label>
                                <el-select v-model="form.warehouse_id">
                                    <el-option
                                        v-for="option in warehouses"
                                        :key="option.id"
                                        :disabled="option.id == form.warehouse_id"
                                        :label="option.description"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small v-if="errors.code"
                                    class="form-control-feedback has-danger"
                                    v-text="errors.warehouse_id[0]"></small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="control-label">Toda Fila <span class="text-danger">*</span></label>
                            <el-input v-model="form.rows"
                                        type="number" min="0"></el-input>
                            <small v-if="errors.rows"
                                    class="form-control-feedback has-danger"
                                        v-text="errors.rows[0]"></small>
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="control-label">Columna <span class="text-danger">*</span></label>
                            <el-input v-model="form.columns"
                                        type="number" min="0"></el-input>
                            <small v-if="errors.columns"
                                    class="form-control-feedback"
                                        v-text="errors.columns[0]"></small>
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="control-label">Stock Máximo(Posición) <span class="text-danger">*</span></label>
                            <el-input v-model="form.maximum_stock"
                                        type="number" min="0"></el-input>
                            <small v-if="errors.maximum_stock"
                                    class="form-control-feedback"
                                        v-text="errors.maximum_stock[0]"></small>
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-md-6">
                            <el-button @click.prevent="openPositionEditor">Editar Posiciones</el-button>
                        </div>
                    </div>
                </div>
                <div class="form-actions text-right mt-4">
                    <el-button class="second-buton btn btn-default second-buton-default" @click.prevent="close()">Cancelar</el-button>
                    <el-button :loading="loading_submit"
                               @click.prevent="submit"
                               class="btn btn-primary btn-submit-default"
                               type="primary">Guardar
                    </el-button>
                </div>
                <el-dialog 
                    title="Editar Posiciones" 
                    :visible.sync="showPositionEditor"
                    custom-class="custom-dialog"
                >
                    <position-editor :rows="form.rows" :columns="form.columns" :code="form.code" @position-status-updated="updatePositionStatus" @save="handleSavePositions" @addRow="addRow" @addColumn="addColumn" @deleteRow="deleteRow"></position-editor>
                </el-dialog>
            </div>
        </div>
    </div>
</template>

<script>
import PositionEditor from './PositionCreate.vue';
export default {
    components: {PositionEditor },
    data() {
        return {
            showPositionEditor: false,
            warehouses: [],
            locationTypes: [],
            loading_item: false,
            loading_submit: false,
            titleDialog: null,
            showDialogLotsOutput: false,
            showDialogLotsGroup: false,
            resource: "locations",
            errors: {},
            form: {
                name: '',
                code: '',
                status: '',
                type_id: '',
                rows: 0,
                columns: 0,
                maximum_stock: 0,
                warehouse_id: '',
                positions: []
            },
            items: [],
            loading_search: false,
            search_item_by_barcode: false,
            all_items: [],
            lotsAll: [],
            lotsGroupAll: [],
            status: [],
        };
    },
    async created() {
        this.getTypes();
        this.getWarehouses();
        
        this.status = [
            {id: 1, description: 'Venta'},
            {id: 2, description: 'Picking'},
            {id: 3, description: 'Mermado'}
        ];
    },
    methods: {
        async handleSavePositions() {
            this.$message.success('Cambios guardados');
            this.showPositionEditor = false;
        },
        async submit() {
            this.loading_submit = true;
            await this.$http
                .post(`/${this.resource}`, this.form)
                .then(response => {
                    console.log(response);
                     
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.close();
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        console.log(error);
                    }
                })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        async getTypes() {
            await this.$http.get(`/${this.resource}/getTypesLocation`)
                .then(response => {
                    if (response.data.success) {
                        this.locationTypes = response.data.data;
                    } else {
                        this.$message.error('No se pudieron cargar los tipos de ubicación.');
                    }
                })
                .catch(error => {
                    console.log(error);
                    this.$message.error('Error al cargar los tipos de ubicación.');
                });
        },
        async getWarehouses() {
            await this.$http.get(`/listWarehouses`)
                .then(response => {
                    if (response.data.success) {
                        this.warehouses = response.data.data;
                    } else {
                        this.$message.error('No se pudieron cargar los alamcenes.');
                    }
                })
                .catch(error => {
                    console.log(error);
                    this.$message.error('Error al cargar los almacenes.');
                });
        },
        close() {
            location.href = '/locations'
        },
        openPositionEditor() {
            if (this.form.rows && this.form.columns) {
                this.showPositionEditor = true;
            } else {
                this.$message.error('Debe ingresar filas y columnas antes de editar las posiciones.');
            }
        },
        updatePositionStatus(position) {
            const index = this.form.positions.findIndex(p => p.row === position.row && p.column === position.column);
            if (index !== -1) {
                this.form.positions[index].status = position.status;
            } else {
                this.form.positions.push(position);
            }
        },
        addRow(){
            this.form.rows++;
        },
        deleteRow(){
            this.form.rows--;
        },
        addColumn(){
            this.form.columns++;
        }
    }
};
</script>