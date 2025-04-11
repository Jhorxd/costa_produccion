<template>
    <el-dialog :title="titleDialog"
               :visible="showDialog"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               append-to-body
               @close="close"
               @open="create">

        <div class="row" v-if="search_item_by_barcode">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="control-label">Código de barras</label>
                    <el-input
                        placeholder="Buscar"
                        v-model="input_search_barcode"
                        @change="searchRemoteItems(input_search_barcode)"
                        ref="input_search_barcode"
                    >
                    </el-input>
                </div>
            </div>
        </div>

        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group" :class="{'has-danger': errors.item_id}">
                            <label class="control-label">Producto</label>
                            <el-select v-model="form.item_id"
                                       filterable
                                       remote
                                       :remote-method="searchRemoteItems"
                                       :loading="loading_search"
                                       @change="changeItem"
                                       :disabled="search_item_by_barcode"
                                       >
                                <el-option v-for="option in items"
                                           :key="option.id"
                                           :value="option.id"
                                           :label="option.description"></el-option>
                            </el-select>
                            <small class="form-control-feedback" v-if="errors.item_id"
                                   v-text="errors.item_id[0]"></small>
                            <el-checkbox v-model="search_item_by_barcode">Buscar por código de barras</el-checkbox>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" :class="{'has-danger': errors.quantity}">
                            <label class="control-label">Cantidad</label>
                            <el-input v-model="form.quantity"></el-input>
                            <small class="form-control-feedback" v-if="errors.quantity"
                                   v-text="errors.quantity[0]"></small>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group" :class="{'has-danger': errors.warehouse_id}">
                            <label class="control-label">Almacén</label>
                            <el-select v-model="form.warehouse_id" filterable @change="changeItem">
                                <el-option v-for="option in warehouses" :key="option.id" :value="option.id"
                                           :label="option.description"></el-option>
                            </el-select>
                            <small class="form-control-feedback" v-if="errors.warehouse_id"
                                   v-text="errors.warehouse_id[0]"></small>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end justify-content-center" v-if="form.positions_enabled && !form.lots_enabled">
                        <el-button type="primary" @click="selectPosition">
                            Seleccionar Posición
                        </el-button>
                    </div>
                    <div style="padding-top: 3%;" class="col-md-4" v-if="form.item_id && form.lots_enabled && form.warehouse_id">
                        <a href="#" class="text-center font-weight-bold text-info" @click.prevent="clickLotGroup">[&#10004;
                            Seleccionar lote]</a>
                    </div>
                    <!-- <div style="padding-top: 3%;" class="col-md-3 col-sm-3" v-if="form.item_id && form.series_enabled && form.warehouse_id">
                        <a href="#" class="text-center font-weight-bold text-info" @click.prevent="clickSelectLots">[&#10004;
                            Seleccionar series]</a>
                    </div> -->

                    <div class="col-md-8">
                        <div class="form-group" :class="{'has-danger': errors.inventory_transaction_id}">
                            <label class="control-label">Motivo traslado</label>
                            <el-select v-model="form.inventory_transaction_id" filterable>
                                <el-option v-for="option in inventory_transactions" :key="option.id" :value="option.id"
                                           :label="option.name"></el-option>
                            </el-select>
                            <small class="form-control-feedback" v-if="errors.inventory_transaction_id"
                                   v-text="errors.inventory_transaction_id[0]"></small>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label class="control-label">Fecha registro</label>
                        <el-date-picker v-model="form.created_at" type="datetime"
                                        value-format="yyyy-MM-dd HH:mm:ss" format="dd/MM/yyyy HH:mm:ss"
                                        :clearable="true"></el-date-picker>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group" :class="{'has-danger': errors.comments}">
                            <label class="control-label">Comentarios
                            </label>
                            <el-input type="textarea" :rows="3" :maxlength="250" v-model="form.comments"></el-input>
                            <small class="form-control-feedback" v-if="errors.comments"
                                   v-text="errors.comments[0]"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button class="second-buton" @click.prevent="close()">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loading_submit">Aceptar</el-button>
            </div>
        </form>

        <lots-group
            :quantity="form.quantity"
            :showDialog.sync="showDialogLots"
            :lots-group-all="lotsGroupAll"
            :lots_group="form.lots_group"
            @addRowLotGroup="addRowLotGroup">
        </lots-group>

        <select-lots-form
            :showDialog.sync="showDialogSelectLots"
            :lots-all="lotsAll"
            :itemId="form.item_id"
            :lots="form.lots"
            :quantity="form.quantity"
            :warehouseId="form.warehouse_id"
            @addRowSelectLot="addRowSelectLot">
        </select-lots-form>

        <approve-position
            :showDialog.sync="showDialogPositions"
            :warehouse_id="form.warehouse_id"
            :dataModal="dataModal"
            :locations_available="locations"
            :type="'output'"
            @positions-save="savePositions">
        </approve-position>

    </el-dialog>
</template>

<script>
import LotsGroup from '../../../../../../resources/js/views/tenant/documents/partials/lots_group.vue'
import SelectLotsForm from '../../../../../../resources/js/views/tenant/documents/partials/lots.vue'
import approvePosition from './approvePosition.vue';
import {filterWords} from "../../../../../../resources/js/helpers/functions";
import { inventory_search_item_barcode } from '../mixins/functions'

export default {
    components: {LotsGroup, SelectLotsForm, approvePosition},
    props: ['showDialog', 'recordId'],
    mixins: [
        inventory_search_item_barcode,
    ],
    data() {
        return {
            type: 'output',
            loading: false,
            loading_search: false,
            loading_submit: false,
            showDialogLots: false,
            showDialogSelectLots: false,
            showDialogPositions: false,
            titleDialog: null,
            resource: 'inventory',
            errors: {},
            form: {},
            items: [],
            warehouses: [],
            inventory_transactions: [],
            lotsAll: [],
            lotsGroupAll: [],
            locations: [],
            dataModal: {}
        }
    },
    created() {
        this.initForm()
    },
    methods: {
        async changeItem() {
            this.form.lots = []
            let item = await _.find(this.items, {'id': this.form.item_id})
            this.form.lots_enabled = item.lots_enabled
            this.lotsAll = await _.filter(item.lots, {'warehouse_id': this.form.warehouse_id})
            this.form.lots_enabled = item.lots_enabled
            this.form.series_enabled = item.series_enabled
            this.form.lots_group = [];
            this.lotsGroupAll = item.lots_group;
            
            this.checkPosition();
        },
        
        async checkPosition(){
            this.dataModal={};
            this.form.data_item = { location_id: '', positions: [] };
            if(!this.form.item_id || !this.form.warehouse_id) return;

            const response = await this.$http.get(`/${this.resource}/checkPositions/${this.form.warehouse_id}/${this.form.item_id}`);
            if(response.data.success){
                const data = response.data.data;
                this.form.positions_enabled = data.positions_enabled;
                if(this.form.positions_enabled && data.locations.length>0){
                    this.locations = data.locations;
                }
            }            
        },
        
        async savePositions(data){
            this.dataModal = data;
        },
        
        selectPosition(){
            if(this.form.quantity==0 || this.form.quantity==''){
                this.$message.warning("Ingrese la cantidad");
                return;
            }
            
            this.dataModal = {
                item_id: this.form.item_id,
                location_id: this.form.data_item.location_id || '',
                positions: this.form.data_item.positions || [],
                stock_necessary: this.form.quantity,
                has_lots: this.form.lots_enabled,
                has_positions: this.form.positions_enabled
            }
            this.showDialogPositions=true;
        },

        clickLotGroup() {
            this.showDialogLots = true
        },
        
        addRowLotGroup(id) {
            this.form.IdLoteSelected = id
        },
        
        async clickSelectLots() {
            this.showDialogSelectLots = true
        },
        
        addRowSelectLot(lots) {
            this.form.lots = lots
        },
        
        initForm() {
            this.errors = {}
            this.form = {
                id: null,
                item_id: null,
                warehouse_id: null,
                inventory_transaction_id: null,
                quantity: 0,
                type: this.type,
                lot_code: null,
                lots_enabled: false,
                positions_enabled: false,
                series_enabled: false,
                lots: [],
                date_of_due: null,
                IdLoteSelected: null,
                lots_group: [],
                created_at: null,
                comments: null,
                data_item: {
                    location_id: '',
                    positions: []
                }
            }
        },
        
        async initTables() {
            await this.$http.get(`/${this.resource}/tables/transaction/${this.type}`)
                .then(response => {
                    this.warehouses = response.data.warehouses
                    this.inventory_transactions = response.data.inventory_transactions
                })
            await this.searchRemoteItems('')
        },
        
        async create() {
            this.loading = true;
            this.titleDialog = 'Salida de producto del almacén'
            await this.initTables();
            this.initForm();
            this.loading = false;
        },
        
        async searchRemoteItems(search) {
            this.loading_search = true;
            this.items = [];

            const params = {
                search: search,
                search_item_by_barcode: this.search_item_by_barcode ? 1 : 0
            }

            await this.$http.post(`/${this.resource}/search_items`, params)
                .then(response => {
                    let items = response.data.items;
                    if (items.length > 0) {
                        this.items = items;
                    }

                    this.enabledSearchItemsBarcode()
                })
            this.loading_search = false;
        },
        
        async submit() {
            if (this.form.lots.length > 0 && this.form.series_enabled) {
                if (this.form.lots.length !== parseInt(this.form.quantity)) {
                    return this.$message.error('La cantidad ingresada es diferente a las series seleccionadas');
                }
            }
            if (this.form.lots_enabled) {
                if (!this.form.IdLoteSelected)
                    return this.$message.error('Debe seleccionar un lote.');
            }
            
            if(this.dataModal && this.dataModal.positions.length>0){
                this.form.data_item.location_id = this.dataModal.location_id;
                this.form.data_item.positions = [...this.dataModal.positions.filter(element => element.is_selected)];
            }

            if(this.form.positions_enabled && !this.form.lots_enabled){
                if(this.form.data_item.positions.length==0)
                    return this.$message.error('Selecciona las posiciones');
            }

            this.loading_submit = true
            this.form.type = this.type
            
            await this.$http.post(`/${this.resource}/transaction`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.close()
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        console.log(error)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })
        },
        
        close() {
            this.$emit('update:showDialog', false)
            this.initForm()
        }
    }
}
</script>