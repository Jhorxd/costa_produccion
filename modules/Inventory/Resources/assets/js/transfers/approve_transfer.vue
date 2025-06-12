<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/transfers">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-warehouse"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-13l9 -4l9 4v13" /><path d="M13 13h4v8h-10v-6h6" /><path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Aprobar Traslado </span></li>
            </ol>
        </div>
        <div class="card tab-content-default row-new mb-0 pt-md-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Nuevo Traslado</h3>
            </div> -->
            <div class="tab-content tab-content-default">
                <!-- <form autocomplete="off"
                      @submit.prevent="submit"> -->
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div :class="{ 'has-danger': errors.date_of_transfer }" class="form-group">
                                <label class="control-label">Fecha de traslado<span class="text-danger"> *</span></label>
                                <el-date-picker v-model="form.date_of_transfer" :clearable="false" type="date"
                                    value-format="yyyy-MM-dd"></el-date-picker>
                                <small v-if="errors.date_of_transfer" class="form-control-feedback"
                                    v-text="errors.date_of_transfer[0]"></small>
                            </div>
                        </div>
                        <div :class="form.location_destination_id!=null?'col-md-3':'col-md-4'">
                            <div class="form-group">
                                <label class="control-label">Almacén Inicial</label>
                                <el-select v-model="form.warehouse_id"
                                           @change="changeWarehouseInit"
                                           disabled>
                                    <el-option
                                        v-for="option in warehouses"
                                        :key="option.id"
                                        :label="option.description"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small
                                    v-if="errors.warehouse_id"
                                    class="form-control-feedback"
                                    v-text="errors.warehouse_id[0]"
                                ></small>
                            </div>
                        </div>
                        <div :class="form.location_destination_id!=null?'col-md-4':'col-md-5'">
                            <div :class="{'has-danger': errors.warehouse_destination_id}"
                                 class="form-group">
                                <label class="control-label">Almacén Final</label>
                                <el-select v-model="form.warehouse_destination_id" disabled>
                                    <el-option
                                        v-for="option in warehouses"
                                        :key="option.id"
                                        :disabled="option.id == form.warehouse_id"
                                        :label="option.description"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small
                                    v-if="errors.warehouse_destination_id"
                                    class="form-control-feedback"
                                    v-text="errors.warehouse_destination_id[0]"
                                ></small>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end" v-if="form.location_destination_id!=null">
                            <div class="form-group">
                                <el-button
                                    type="primary"
                                    @click.prevent="clickSelectPosition"
                                >Mostrar ubicaciones
                                </el-button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.description}"
                                 class="form-group">
                                <label class="control-label">Motivo de Traslado</label>
                                <el-input v-model="form.description"
                                          :rows="3"
                                          type="textarea"
                                          disabled></el-input>
                                <small
                                    v-if="errors.description"
                                    class="form-control-feedback"
                                    v-text="errors.description[0]"
                                ></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.transfer_reason_description}"
                                 class="form-group">
                                <label class="control-label">Comentario</label>
                                <el-input v-model="form.transfer_reason_description"
                                          :rows="3"
                                          type="textarea"
                                          disabled></el-input>
                                <small
                                    v-if="errors.transfer_reason_description"
                                    class="form-control-feedback"
                                    v-text="errors.transfer_reason_description[0]"
                                ></small>
                            </div>
                        </div>
                    </div>
                    <br/>
                    <!-- <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="control-label">
                                    Producto
                                    <template v-if="!search_item_by_barcode">
                                        <el-tooltip class="item"
                                                    effect="dark"
                                                    content="Puede escribir para buscar un producto en especifico"
                                                    placement="top-start">
                                            <i class="fa fa-info-circle"></i>
                                        </el-tooltip>
                                    </template>
                                </label>
                                <el-input v-model="form.item_description" :readonly="true"></el-input>
                                <template v-if="search_item_by_barcode">
                                    <el-input
                                        ref="inputSearchByBarcode"
                                        placeholder="Buscar producto por código de barras..."
                                        v-model="form_add.input_search"
                                        :disabled="!form.warehouse_id"
                                        @change="changeInputSearch">
                                    </el-input>
                                </template>
                                <template v-else>
                                    <el-select
                                        v-model="form_add.item_id"
                                        class="border-left rounded-left border-info"
                                        filterable
                                        popper-class="el-select-document_type"
                                        @change="changeItem"
                                        :disabled="!form.warehouse_id"
                                        id="select-width"
                                        ref="selectSearchNormal"
                                        slot="prepend"
                                        placeholder="Escribe para buscar ..."
                                        remote
                                        :loading="loading_search"
                                        :remote-method="searchRemoteItems"
                                        @focus="focusSelectItem"
    
                                    >
                                        <el-tooltip
                                            v-for="option in items"
                                            :key="option.id"
                                            placement="left">
                                            <div
                                                slot="content"
                                                v-html="ItemSlotTooltipView(option)"
                                            ></div>
                                            <el-option
                                                :label="ItemOptionDescriptionView(option)"
                                                :value="option.id"
                                            ></el-option>
                                        </el-tooltip>
                                        
                                            <el-option
                                                v-for="option in items"
                                                :key="option.id"
                                                :label="ItemOptionDescriptionView(option)"
                                            :value="option.id"
                                        ></el-option>
                                       
    
                                        
                                        <el-option
                                            v-for="option in items"
                                            :key="option.id"
                                            :label="option.description"
                                            :value="option.id"
                                        ></el-option>
                                       
                                    </el-select>
                                    <a
                                        v-if="form_add.item_id  && form_add.series_enabled"
                                        class="text-center font-weight-bold text-info"
                                        href="#"
                                        @click.prevent="clickLotcodeOutput"
                                    >[&#10004; Seleccionar series]</a>
    
                                    <div class="col-md-4 mt-4" v-if="form_add.item_id && form_add.lots_enabled">
                                        <a href="#" class="text-center font-weight-bold text-info"
                                           @click.prevent="clickSelectLotsGroup">[&#10004; Seleccionar lotes]</a>
                                    </div>
                                </template>
    
                                <el-checkbox class="mt-2" v-model="search_item_by_barcode"
                                             @change="changeSearchItemByBarcode">Buscar por código de barras
                                </el-checkbox>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Cantidad Actual</label>
                                <el-input v-model="form_add.stock"
                                          @change="clearStockNumber"
                                          :readonly="true"></el-input>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">Cantidad a Trasladar</label>
                                <el-input v-model="form_add.quantity"
                                          @change="clearQuantyNumber"
                                          type="number"></el-input>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <el-button
                                    :disabled="form_add.item_id == null"
                                    :loading="loading_item"
                                    style="margin-top:10%;"
                                    type="primary"
                                    @click.prevent="clickAddItem"
                                >Agregar Producto
                                </el-button>
                            </div>
                        </div>
                    </div> -->
                    <br/>
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <table class="table"
                                   width="100%">
                                <thead>
                                <tr width="100%">
                                    <th width="10%" class="text-center">#</th>
                                    <th width="20%" class="text-center">Cód. Barras</th>
                                    <th width="20%" class="text-center">Producto</th>
                                    <th width="15%" class="text-center">Cantidad</th>
                                    <!-- <th width="15%">Costo</th>
                                    <th width="10%">Importe Costo</th> -->
                                    <th width="20%" class="text-center">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(row, index) in form.items"
                                    :key="index"
                                    width="100%">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td class="text-center">{{ row.barcode }}</td>
                                    <td class="text-center">{{ row.description }}</td>
                                    <td class="text-center" v-if="!row.has_lots">
                                        <!-- {{ row.quantity }} -->
    
                                        <el-input-number v-model="row.quantity"
                                                         :min="0.01"
                                                         :step="1"
                                                         @change="changeQuantity(row, index)"></el-input-number>
                                    </td>
                                    <td v-else class="text-center">{{ row.quantity }}</td>
                                    <td class="series-table-actions text-center">
                                        <el-button
                                            @click.prevent="clickPosition(row)"
                                            class="btn btn-primary btn-submit-default btn-sm"
                                            type="primary"
                                            v-if="row.has_position">Posición
                                        </el-button>
                                        <el-button
                                            class="btn waves-effect waves-light btn-sm btn-danger"
                                            type="danger"
                                            @click.prevent="clickCancel(index)"
                                            icon="el-icon-delete"
                                            size="mini">
                                        </el-button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="form-actions text-right mt-4">
                    <el-button class="second-buton btn btn-default second-buton-default" @click.prevent="close()">Cancelar</el-button>
                    <el-button :loading="loading_submit"
                               @click.prevent="submit"
                               class="btn btn-primary btn-submit-default"
                               type="primary">Confirmar Traslado
                    </el-button>
                </div>
                <!-- </form> -->
            </div>
    
            <output-lots-form
                :itemId="form_add.item_id"
                :lots="form_add.lots"
                :quantity="form_add.quantity"
                :showDialog.sync="showDialogLotsOutput"
                @addRowOutputLot="addRowOutputLot"></output-lots-form>
    
            <output-lots-group-form
                :showDialog.sync="showDialogLotsGroup"
                :itemId="form_add.item_id"
                :lots-group-all="lotsGroupAll"
                :lots_group="form_add.lots_group"
                :quantity="form_add.quantity"
                @addRowLotGroup="addRowLotGroup"
                :compromise-all-quantity="true">
            </output-lots-group-form>

            <approve-position
                :showDialog.sync="showDialogPositionOrigin"
                :dataModal.sync="selectedItem"
                :warehouse_id="form.warehouse_id"
                @positions-save="savedDataModal">
            </approve-position>

            <positions 
                :showDialog.sync="showDialogPositions" 
                :dataModal="dataModalPosition"
                :warehouse_id="form.warehouse_destination_id">
            </positions>
        </div>
    </div>
</template>

<script>
//import OutputLotsForm from "./partials/lots.vue";
import OutputLotsForm from '../../../../../../resources/js/views/tenant/documents/partials/lots.vue'
import OutputLotsGroupForm from '../../../../../../resources/js/views/tenant/documents/partials/lots_group'
import {ItemOptionDescription, ItemSlotTooltip} from "../../../../../../resources/js/helpers/modal_item";
import {filterWords} from "../../../../../../resources/js/helpers/functions";
import approvePosition from './partials/approvePosition.vue';
import positions from './partials/positions.vue';

export default {
    props: ['resourceId'],
    components: {OutputLotsForm, OutputLotsGroupForm, approvePosition, positions},
    data() {
        return {
            loading_item: false,
            loading_submit: false,
            titleDialog: null,
            showDialogLotsOutput: false,
            showDialogLotsGroup: false,
            showDialogPositionOrigin: false,
            resource: "transfers",
            errors: {},
            form: {},
            warehouses: [],
            items: [],
            form_add: {},
            loading_search: false,
            search_item_by_barcode: false,
            all_items: [],
            lotsAll: [],
            lotsGroupAll: [],
            selectedItem:{},
            showDialogPositions: false,
            dataModalPosition: {location_id: null, positions: []}
        };
    },
    async created() {
        await this.initForm();
        try {
            const response = await this.$http.get(`/${this.resource}/tables`);
            
            this.warehouses = response.data.warehouses;
            this.items = response.data.items;
            this.all_items = this.items;
            await this.initRecord();

            if (this.form.location_destination_id && this.form.position_destination_id) {
                await this.getPositions(this.form.location_destination_id);
            }
            
        } catch (error) {
            console.error("Error en created:", error);
        }
        
        this.initFormAdd();
    },
    methods: {
        clickSelectPosition(){
            this.showDialogPositions = true;
        },
        async getPositions(location_id) {
            try {
                const response = await this.$http.get(`/${this.resource}/positions/${location_id}`);
                const response_data = response.data;

                if (response_data.success) {
                    const positions = response_data.data.map(element => ({
                        ...element,
                        is_selected: element.id === this.form.position_destination_id
                    }));

                    this.dataModalPosition = {
                        location_id,
                        positions
                    };
                }
            } catch (error) {
                console.error("Error fetching positions:", error);
            }
        },
        savedDataModal(dataModal){
            const item = this.form.items.find(element => element.id == this.selectedItem.item_id);
            
            if(item){
                item.dataModal = dataModal;
            }
        },
        addRowLotGroup(id) {
            this.form.selected_lots_group = id
        },
        changeWarehouseInit() {
            this.form.warehouse_destination_id = null;
            this.form_add.item_id = null
            this.form.items = [];

            this.$http
                .get(`/${this.resource}/items/${this.form.warehouse_id}`)
                .then(response => {
                    this.items = response.data.items;
                    this.all_items = this.items
                });
        },
        addRowOutputLot(lots) {
            let row = this.items.find(x => x.id == this.form_add.item_id);
            row.lots = lots;
        },
        clickCancel(index) {
            this.form.items.splice(index, 1);
        },
        async changeItem() {
            this.loading_item = true;
            await this.$http
                .get(
                    `/${this.resource}/stock/${this.form_add.item_id}/${this.form.warehouse_id}`
                )
                .then(response => {
                    this.form_add.stock = response.data.stock;
                    this.loading_item = false;
                });

            let row = this.items.find(x => x.id == this.form_add.item_id);

            // this.form = _.clone(data);
            // this.form.lots = []; //Object.values(response.data.data.lots)
            this.lotsAll = row.lots;
            this.lotsGroupAll = row.lots_group;//Object.values(response.data.data.lots);
            // this.form = Object.assign({}, this.form, {'quantity_remove': 0});

            // this.form_add.lots = row.lots;
            this.form_add.lots_enabled = row.lots_enabled;
            this.form_add.series_enabled = row.series_enabled;

        },
        initFormAdd() {
            this.form_add = {
                item_id: null,
                stock: 0,
                quantity: 0,
                barcode: null,
                lots: [],
                lots_enabled: false,
                series_enabled: false,
                input_search: null,
            };
        },
        clearStockNumber() {

            delete (this.errors.stock)
        },
        clearQuantyNumber() {

            delete (this.errors.quantity)
        },
        changeQuantity(row, index) {
            row.dataModal.stock_necessary = row.quantity;
            
            /* if (parseFloat(row.current_stock) < row.quantity) {
                row.quantity = 1
                return this.$message.error('El stock es menor a la cantidad de traslado.')
            } */
        },
        changeSearchItemByBarcode() {
            if (this.search_item_by_barcode) {
                this.selectInputSearchByBarcode()
            }
        },
        async enabledSearchItemByBarcode() {
            if (this.search_item_by_barcode) {
                if (this.items.length === 1) {
                    const item = this.items[0]
                    this.form_add.item_id = item.id
                    this.form_add.quantity = 1
                    await this.changeItem()
                    await this.clickAddItemByBarcode()
                } else {
                    this.itemBarcodeNotFound()
                }
            }
        },
        itemBarcodeNotFound() {
            this.form_add.input_search = null
            this.$message.error('No se encontró el producto.')
        },
        selectedItemSearch() {
            this.$refs.selectSearchNormal.$data.selectedLabel = ''
            this.$refs.selectSearchNormal.blur()
        },
        validateAddItem() {
            if (parseFloat(this.form_add.stock) < 1) {
                return {
                    success: false,
                    message: 'El stock debe ser mayor o igual a 1.'
                }
            }

            if (this.form_add.quantity < 1) {
                return {
                    success: false,
                    message: 'La cantidad debe ser mayor o igual a 1.'
                }
            }

            if (parseFloat(this.form_add.stock) < this.form_add.quantity) {
                return {
                    success: false,
                    message: 'El stock es menor a la cantidad de traslado.'
                }
            }

            return {
                success: true
            }
        },
        clickAddItemByBarcode() {
            const validate_add_item = this.validateAddItem()
            if (!validate_add_item.success) return this.$message.error(validate_add_item.message)

            let exist_item = this.form.items.find(row => row.id == this.form_add.item_id)

            if (exist_item) {
                exist_item.quantity++

                if (parseFloat(this.form_add.stock) < exist_item.quantity) {
                    exist_item.quantity--
                    this.form_add.input_search = null
                    return this.$message.error('El stock es menor a la cantidad de traslado.')
                }
            } else {
                const row = this.items.find(x => x.id == this.form_add.item_id)

                this.form.items.push({
                    id: row.id,
                    description: row.description,
                    barcode: row.barcode,
                    current_stock: parseFloat(this.form_add.stock),
                    quantity: this.form_add.quantity,
                    lots: []
                })
            }

            this.initFormAdd()

            this.selectInputSearchByBarcode()

            this.$notify({
                title: '',
                message: 'Producto añadido!',
                type: 'success',
                duration: 700
            })
        },
        selectInputSearchByBarcode() {
            this.$nextTick(() => {
                this.$refs.inputSearchByBarcode.$el.getElementsByTagName('input')[0].focus()
            })
        },
        clickAddItem() {
            /* if (!this.form_add.item_id) {
              return;
            }*/

            if (parseFloat(this.form_add.stock) < 1) {
                return this.$message.error("El stock debe ser mayor o igual a 1");
            }

            if (this.form_add.quantity < 1) {
                return this.$message.error("La cantidad debe ser mayor o igual a 1");
            }

            if (parseFloat(this.form_add.stock) < this.form_add.quantity) {
                return this.$message.error("El stock es menor a la cantidad de traslado.");
            }

            if (this.form_add.series_enabled) {
                //let selected_lots = this.form_add.lots.filter(x => x.has_sale == true).length;
                if (parseInt(this.form_add.quantity) !== this.form_add.lots.length) {
                    return this.$message.error("La cantidad de series seleccionadas es diferente a la cantidad de traslado");
                }
            }

            let dup = this.form.items.find(x => x.id == this.form_add.item_id);
            if (dup) {
                return this.$message.error("Este producto ya esta agregado.");
            }

            let row = this.items.find(x => x.id == this.form_add.item_id);
            this.form.items.push({
                id: row.id,
                description: row.description,
                barcode: row.barcode,
                current_stock: parseFloat(this.form_add.stock),
                quantity: this.form_add.quantity,
                lots: this.form_add.lots,
                purchase_unit_price: row.purchase_unit_price,
            });

            // cargamos lotes seleccionados previamentes
            if(this.form.selected_lots_group.length > 0){
                this.form.selected_lots_group.forEach(element => {
                    this.form.lot_group_total.push(element)
                });
            }

            this.initFormAdd();
        },
        async initRecord() {
            try {
                const response = await this.$http.get(`/${this.resource}/record2/${this.resourceId}`);
                const data = response.data.data.inventory_transfer;
                const items = response.data.data.inventory_items;
                
                this.form = {
                ...this.form,
                id: data.id,
                description: data.description,
                transfer_reason_description: data.transfer_reason_description,
                warehouse_id: data.warehouse_id,
                warehouse_destination_id: data.warehouse_destination_id,
                location_destination_id: data.location_destination_id,
                position_destination_id: data.position_destination_id,
                items: items.map(item => ({
                    ...item,
                    dataModal: {
                        item_id: item.id,
                        location_id: item.location_id || null,
                        positions: [],
                        stock_necessary: item.quantity,
                        has_lots: item.has_lots,
                        has_position: item.has_position,
                        lots: item.lots
                    }
                }))
                };

            } catch (error) {
                console.error("Error en initRecord:", error);
                throw error;
            }
        },
        clickLotcodeOutput() {
            this.showDialogLotsOutput = true;
        },

        clickSelectLotsGroup() {
            this.showDialogLotsGroup = true
        },
        initForm() {
            this.errors = {};
            this.form = {
                warehouse_id: null,
                warehouse_destination_id: null,
                location_destination_id: null,
                position_destination_id: null,
                description: null,
                transfer_reason_description: null,
                state: false,
                date_of_transfer: moment().format('YYYY-MM-DD'),
                items: [],
                selected_lots_group: [],
                lot_groups_total: [],
            };
        },
        formatForm(form){
            const items = form.items.map(element => {
                const selectedPositions = element.dataModal.positions
                    .filter(position => position.is_selected)
                    .map(position => ({
                        id: position.id,
                        column: position.column,
                        row: position.row,
                        stock: position.stock,
                        ...(position.uses_lots && { 
                            lots_group_list: position.lots_group_list 
                        })
                    }));
                return {
                    id: element.id,
                    has_lots: element.dataModal.has_lots,
                    has_positions: element.dataModal.has_position,
                    location_id: element.dataModal.location_id,
                    stock_necessary: element.quantity,
                    positions: selectedPositions.length > 0 ? selectedPositions : undefined
                }
            });
            return {
                id: form.id,
                items: items,
                warehouse_init_id: form.warehouse_id,
                warehouse_destination_id: form.warehouse_destination_id,
                location_destination_id: form.location_destination_id,
                position_destination_id: form.position_destination_id,
                date_of_transfer: form.date_of_transfer
            };
        },
        validatePositionsSelected() {
            for (const item of this.form.items) {
                // Si el producto requiere posición
                if (item.has_position || (item.dataModal && item.dataModal.has_position)) {
                    // Verifica si hay posiciones seleccionadas
                    const positions = item.dataModal && item.dataModal.positions
                        ? item.dataModal.positions
                        : [];
                    const selected = positions.some(pos => pos.is_selected);
                    if (!selected) {
                        return {
                            success: false,
                            message: `Debe seleccionar al menos una posición para el producto "${item.description}".`
                        };
                    }
                }
            }
            return { success: true };
        },
        async submit() {
            if (this.form.items.length == 0) {
                return this.$message.error("Debe agregar productos.");
            }
            
            const posValidation = this.validatePositionsSelected();
            if (!posValidation.success) {
                return this.$message.error(posValidation.message);
            }

            this.loading_submit = true;

            const data = this.formatForm(this.form);
            //return;
            await this.$http
                .post(`/${this.resource}/approve_transfer`, data)
                .then(response => {
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
        close() {
            location.href = '/transfers'
        },
        ItemSlotTooltipView(item) {
            return ItemSlotTooltip(item);
        },
        ItemOptionDescriptionView(item) {
            return ItemOptionDescription(item)
        },
        async changeInputSearch() {
            await this.searchRemoteItems(this.form_add.input_search)
        },
        async searchRemoteItems(input) {
            // console.error(input.length)
            if (this.form.warehouse_id && this.form.warehouse_id > 0 && input.length > 1) {
                this.loading_search = true
                const params = {
                    'input': input,
                    'search_by_barcode': this.search_item_by_barcode ? 1 : 0,
                    'warehouse_id': this.form.warehouse_id,
                }
                await this.$http
                    .post(`/${this.resource}/search-items`, {params})
                    .then(response => {
                        let items = response.data.items;
                        if (items.length > 0) {
                            this.items = items; //filterWords(input, items);
                            this.enabledSearchItemByBarcode()
                        } else {
                            this.filterItems()
                            if (this.search_item_by_barcode) this.itemBarcodeNotFound()
                        }
                    })
                    .finally(() => {
                        this.loading_search = false

                    })
            } else {
                await this.filterItems()
            }

        },
        filterItems() {
            this.items = this.all_items
        },

        focusSelectItem() {
            this.$refs.selectSearchNormal.$el.getElementsByTagName('input')[0].focus()
        },

        clickPosition(item){
            this.selectedItem = item.dataModal;
            
            this.showDialogPositionOrigin = true;
        }
    }
};
</script>
