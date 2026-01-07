<template>
    <el-dialog :close-on-click-modal="false"
               :title="titleDialog"
               :visible="showDialog"
               append-to-body
               class="pt-0"
               top="7vh"
               width="80%"
               @close="close"
               @open="create">
        <form autocomplete="off"
              @submit.prevent="submit">


                    <div class="row">
                        
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.cod_digemid}"
                            class="form-group">
                                 <label class="control-label">
                                    Código DIGEMID
                                    <el-tooltip
                                        class="item"
                                        content="Código de observación DIGEMID"
                                        effect="dark"
                                        placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-input v-model="form.cod_digemid">
                                </el-input>
                                <small v-if="errors.cod_digemid"
                                       class="form-control-feedback"
                                       v-text="errors.cod_digemid[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.description}"
                                 class="form-group">
                                <label class="control-label">Nombre <span class="text-danger">*</span></label>
                                <el-input v-model="form.description"
                                          dusk="description"></el-input>
                                <small v-if="errors.description"
                                       class="form-control-feedback"
                                       v-text="errors.description[0]"></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.sanitary}"
                                 class="form-group">
                                <label class="control-label">
                                    Registros Sanitarios
                                    <el-tooltip
                                        class="item"
                                        content="Número de registro sanitario"
                                        effect="dark"
                                        placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-input v-model="form.sanitary">
                                </el-input>
                                <small v-if="errors.sanitary"
                                       class="form-control-feedback"
                                       v-text="errors.sanitary[0]"></small>
                            </div>
                        </div>

                        <div v-show="form.unit_type_id !='ZZ'"
                             class="col-md-12 mt-2">
                            <h5 class="separator-title mt-0">
                                Listado de precios de competencia
                                <el-tooltip class="item"
                                            content="Aplica para realizar compra/venta en presentacion de diferentes precios y/o cantidades"
                                            effect="dark"
                                            placement="top">
                                    <i class="fa fa-info-circle"></i>
                                </el-tooltip>
                                <small v-if="form.item_unit_types.length > 1 && !config.enable_list_product" class="text-warning">Sólo se toma en cuenta el primer registro para mostrar en POS</small>
                            </h5>
                        </div>
                        <div v-if="form.item_unit_types.length > 0"
                             v-show="form.unit_type_id !='ZZ'"
                             class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                    <tr>
                                        
                                        <th class="text-center">Unidad</th>
                                        <th class="text-center">Factor</th>
                                        <th class="text-center">Competidor 1</th>
                                        <th class="text-center">Precio 1</th>
                                        <th class="text-center">Competidor 2</th>
                                        <th class="text-center">Precio 2</th>
                                        <th class="text-center">Competidor 3</th>
                                        <th class="text-center">Precio 3</th>
                                        <th class="text-center">Competidor 4</th>
                                        <th class="text-center">Precio 4</th>
                                        <th v-if="config.enable_list_product"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, index) in form.item_unit_types"
                                        :key="index">
                                        <template v-if="row.id">
                                            <td class="text-center">{{ row.unit_type_id }}</td>
                                            <td class="text-center">{{ row.factor }}</td>
                                            <td class="text-center">{{ row.competence_label1 }}</td>
                                            
                                            <td class="text-center">
                                                <el-input v-model="row.competence_unit_price1"></el-input>
                                            </td>
                                            <td class="text-center">{{ row.competence_label2 }}</td>
                                            
                                            <td class="text-center">
                                                <el-input v-model="row.competence_unit_price2"></el-input>
                                            </td>
                                            <td class="text-center">{{ row.competence_label3 }}</td>
                                            
                                            <td class="text-center">
                                                <el-input v-model="row.competence_unit_price3"></el-input>
                                            </td>
                                            <td class="text-center">{{ row.competence_label4 }}</td>
                                            
                                            <td class="text-center">
                                                <el-input v-model="row.competence_unit_price4"></el-input>
                                            </td>
                                            <td class="series-table-actions text-right" v-if="config.enable_list_product">
                                                <button class="btn waves-effect waves-light btn-xs btn-danger"
                                                        type="button"
                                                        @click.prevent="clickDelete(row.id)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td>
                                                <div class="form-group">
                                                    <el-select v-model="row.unit_type_id"
                                                               dusk="item_unit_type.unit_type_id">
                                                        <el-option v-for="option in unit_types"
                                                                   :key="option.id"
                                                                   :label="option.description"
                                                                   :value="option.id"></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-input v-model="row.factor"></el-input>
                                                    <!-- <small class="form-control-feedback" v-if="errors.stock_min" v-text="errors.stock_min[0]"></small> -->
                                                </div>
                                            </td>
                                            
                                            <td>
                                                <div class="form-group">
                                                    <el-select v-model="row.competence_id1"
                                                               dusk="item_unit_type.unit_type_id">
                                                        <el-option v-for="option in competences"
                                                                   :key="option.id"
                                                                   :label="option.description"
                                                                   :value="option.id"></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-input v-model="row.competence_unit_price1"></el-input>
                                                    <!-- <small class="form-control-feedback" v-if="errors.stock_min" v-text="errors.stock_min[0]"></small> -->
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-select v-model="row.competence_id2"
                                                               dusk="item_unit_type.unit_type_id">
                                                        <el-option v-for="option in competences"
                                                                   :key="option.id"
                                                                   :label="option.description"
                                                                   :value="option.id"></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-input v-model="row.competence_unit_price2"></el-input>
                                                    <!-- <small class="form-control-feedback" v-if="errors.stock_min" v-text="errors.stock_min[0]"></small> -->
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-select v-model="row.competence_id3"
                                                               dusk="item_unit_type.unit_type_id">
                                                        <el-option v-for="option in competences"
                                                                   :key="option.id"
                                                                   :label="option.description"
                                                                   :value="option.id"></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-input v-model="row.competence_unit_price3"></el-input>
                                                    <!-- <small class="form-control-feedback" v-if="errors.stock_min" v-text="errors.stock_min[0]"></small> -->
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-select v-model="row.competence_id4"
                                                               dusk="item_unit_type.unit_type_id">
                                                        <el-option v-for="option in competences"
                                                                   :key="option.id"
                                                                   :label="option.description"
                                                                   :value="option.id"></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-input v-model="row.competence_unit_price4"></el-input>
                                                    <!-- <small class="form-control-feedback" v-if="errors.stock_min" v-text="errors.stock_min[0]"></small> -->
                                                </div>
                                            </td>
                                            <td class="series-table-actions text-right" v-if="config.enable_list_product">
                                                <button class="btn waves-effect waves-light btn-xs btn-danger"
                                                        type="button"
                                                        @click.prevent="clickCancel(index)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </template>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col" v-if="config.enable_list_product || !config.enable_list_product && form.item_unit_types.length < 1">
                            <a class="control-label font-weight-bold text-info"
                               href="#"
                               @click="clickAddRow">
                               [ + Agregar]
                            </a>
                        </div>
                        

                    </div> 

            <div class="form-actions text-right pt-2 mt-2">
                <template v-if="forOnlyShowAllDetails">
                    <el-button @click.prevent="close()">Cerrar</el-button>
                </template>
                <template v-else>
                    <el-button class="second-buton" @click.prevent="close()">Cancelar</el-button>
                    <el-button :loading="loading_submit"
                            native-type="submit"
                            type="primary">Guardar
                    </el-button>
                </template>
            </div>
        </form>

    </el-dialog>
</template>

<script>
import {mapActions, mapState} from "vuex";


export default {
    props: [
        'showDialog',
        'recordId',
        'external',
        'type',
        'pharmacy',
        'onlyShowAllDetails',
    ],
    components: {
    },
    computed: {
        forOnlyShowAllDetails()
        {
            if(this.onlyShowAllDetails != undefined && this.onlyShowAllDetails != null) return this.onlyShowAllDetails

            return false
        },
        ...mapState([
            'colors',
            'CatItemSize',
            'CatItemUnitsPerPackage',
            'CatItemMoldProperty',
            'CatItemUnitBusiness',
            'CatItemStatus',
            'CatItemPackageMeasurement',
            'CatItemMoldCavity',
            'CatItemProductFamily',
            'config',
        ]),
        isService: function () {
            // Tener en cuenta que solo oculta las pestañas para tipo servicio.
            if (this.form !== undefined) {
                // Es servicio por selección
                if (this.form.unit_type_id !== undefined && this.form.unit_type_id === 'ZZ') {
                    if (
                        this.activeName == 'second' ||
                        this.activeName == 'third' ||
                        this.activeName == 'five'
                    ) {
                        this.activeName = 'first';
                    }
                    return true;
                }
            }
            return false;
        },
        canSeeProduction:function(){
            if(this.config && this.config.production_app) return this.config.production_app
            return false;
        },
        requireSupply:function(){

            if(this.form.is_for_production) {

                if( this.form.is_for_production == true) return true
            };
            return false;
        },

        canShowExtraData: function () {
            if (this.config && this.config.show_extra_info_to_item !== undefined) {
                return this.config.show_extra_info_to_item;
            }
            return false;
        },
        showPharmaElement() {

            if (this.fromPharmacy === true) return true;
            if (this.config.is_pharmacy === true) return true;
            return false;
        },
        showPointSystem()
        {
            if(this.config) return this.config.enabled_point_system

            return false
        },
        showRestrictSaleItemsCpe()
        {
            if(this.config) return this.config.restrict_sale_items_cpe

            return false
        }

    },

    data() {
        return {
            loading_search: false,
            showDialogLots: false,
            showDialogLocation: false,
            items: [],
            loading_submit: false,
            titleDialog: null,
            resource: 'items',
            errors: {},
            form: {
                item_supplies:[],
                is_for_production:false,
            },
            competences:[],
            /*competences: [
                {
                    id: 1,
                    description: 'Inkafarma',
                },
                {
                    id: 2,
                    description: 'Mi farma',
                }
            ],*/
            unit_types: [
                {
                    id: 'ZZ',
                    description: 'Servicio',
                },
                {
                    id: 'BX',
                    description: 'Caja',
                },
                {
                    id: 'GLL',
                    description: 'Galones',
                },
                {
                    id: 'GRM',
                    description: 'Gramos',
                },
                {
                    id: 'KGM',
                    description: 'Kilos',
                },
                {
                    id: 'LTR',
                    description: 'Litros',
                },
                {
                    id: 'MTR',
                    description: 'Metros',
                },
                {
                    id: 'FOT',
                    description: 'Pies',
                },
                {
                    id: 'INH',
                    description: 'Pulgadas',
                },
                {
                    id: 'NIU',
                    description: 'Unidades',
                },
                {
                    id: 'YRD',
                    description: 'Yardas',
                },
                {
                    id: 'HUR',
                    description: 'Hora',
                },
                {
                    id: 'TM',
                    description: 'Toneladas',
                },
                {
                    id: 'TNE',
                    description: 'Toneladas',
                },
            ],
            item_unit_type: {
                id: null,
                unit_type_id: null,
                quantity_unit: 0,
                price1: 0,
                price2: 0,
                price3: 0,
                price_default: 2,

            },
            config: {
                enable_list_product: true
            }
        }
    },
    async created() {
        await this.initForm();
        this.getTables();
    },

    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),
        async getTables() {
            await this.$http.get(`/${this.resource}/tables`)
            .then(response => {
                    console.log(response);
                    console.log(response.data);
                    this.competences = response.data.competitors
                    console.log(this.competences)
                    //console.log(response)
                })
        },
        async reloadTables() {
            await this.$http.get(`/${this.resource}/tables`)
                .then(response => {
                    this.unit_types = response.data.unit_types
                    this.accounts = response.data.accounts
                    this.currency_types = response.data.currency_types
                    this.system_isc_types = response.data.system_isc_types
                    this.affectation_igv_types = response.data.affectation_igv_types
                    this.warehouses = response.data.warehouses
                    this.categories = response.data.categories
                    this.brands = response.data.brands

                    this.form.sale_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
                    this.form.purchase_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
                })
        },
        clickAddRow() {
            this.form.item_unit_types.push({
                id: null,
                unit_type_id: 'NIU',
                factor: 1,
                competence_unit_price1: 0,
                competence_label1: null,
                competence_id1: null,
                competence_unit_price2: 0,
                competence_label2: null,
                competence_id2: null,
                competence_unit_price3: 0,
                competence_label3: null,
                competence_id3: null,
                competence_unit_price4: 0,
                competence_label4: null,
                competence_id4: null,
            })
        },
        initForm() {
            this.loading_submit = false,
            this.errors = {}

            this.form = {
                id: null,
                item_type_id: '01',
                internal_id: null,
                item_code: null,
                description: null,
                name: null,
                unit_type_id: 'NIU',
                currency_type_id: 'PEN',
                sale_unit_price: 0,
                unit_price: 0,
                item_unit_types: [],
                
                
            }

            this.show_has_igv = true
            this.purchase_show_has_igv = true
            this.enabled_percentage_of_profit = false
        },
        
        setDialogTitle()
        {
            if(this.forOnlyShowAllDetails)
            {
                this.titleDialog = 'Ver Producto'
            }
            else
            {
                this.titleDialog = (this.recordId) ? 'Editar Producto' : 'Nuevo Producto'
            }
        },

        async create() {
            this.initForm();
            console.log("record", this.recordId);
            this.activeName =  'first'
            if (this.type) {
                if (this.type !== 'PRODUCTS') {
                    this.form.unit_type_id = 'ZZ';
                }
            }

            this.setDialogTitle();

            if (this.recordId) {
                await this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        console.log("res", response.data.data);
                        this.form = response.data.data;
                    })
            }else{
                //await this.getDataTables();
            }


        },
        validateItemUnitTypes() {

            let error_by_item = 0

            if (this.form.item_unit_types.length > 0) {

                this.form.item_unit_types.forEach(item => {

                    if (parseFloat(item.quantity_unit) < 0.0001) {
                        error_by_item++
                    }

                })

            }

            return error_by_item

        },
        async submit() {
            console.log("FORM",this.form);

            this.form.item_unit_types.forEach(item => {
                const competence1 = this.competences.find(c => c.id === item.competence_id1);
                if (competence1) {
                    item.competence_label1 = competence1.description;
                } else {
                    item.competence_label1 = ''; // o null, si no existe
                }

                const competence2 = this.competences.find(c => c.id === item.competence_id2);
                if (competence2) {
                    item.competence_label2 = competence2.description;
                } else {
                    item.competence_label2 = ''; // o null, si no existe
                }

                const competence3 = this.competences.find(c => c.id === item.competence_id3);
                if (competence3) {
                    item.competence_label3 = competence3.description;
                } else {
                    item.competence_label3 = ''; // o null, si no existe
                }

                const competence4 = this.competences.find(c => c.id === item.competence_id4);
                if (competence4) {
                    item.competence_label4 = competence4.description;
                } else {
                    item.competence_label4 = ''; // o null, si no existe
                }
            });

            this.loading_submit = true;
            
            await this.$http.post(`/${this.resource}`, this.form)
                .then(async response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        const item_id = response.data.id;
                        
                        this.initForm();
                        this.$eventHub.$emit('reloadData')
                        this.close();
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        console.log(error)
                        this.$message.error(error.response.data.message)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })
        },
        close() {
            this.$emit('update:showDialog', false);
        },
        async searchRemoteItems(input) {
            if (input.length > 2) {
                this.loading_search = true
                const params = {
                    'input': input,
                    'search_by_barcode': this.search_item_by_barcode ? 1 : 0,
                    'production':1
                }
                await this.$http.get(`/${this.resource}/search-items/`, {params})
                    .then(response => {
                        this.items = response.data.items
                        this.loading_search = false
                        // this.enabledSearchItemsBarcode()
                        // this.enabledSearchItemBySeries()
                        if (this.items.length == 0) {
                            // this.filterItems()
                        }
                    })
            } else {
                // await this.filterItems()
            }

        },
        getItems() {
            this.$http.get(`/${this.resource}/item/tables`).then(response => {
                this.items = response.data.items
            })
        },
        clickCancel(index) {
            this.form.item_unit_types.splice(index, 1)
            // this.initDocumentTypes()
            // this.showAddButton = true
        },
        clickDelete(id) {
            this.$http
                .delete(`/${this.resource}/item-unit-type/${id}`)
                .then((res) => {
                    if (res.data.success) {
                        this.loadRecord();
                        this.$message.success(
                            "Se eliminó correctamente el registro"
                        );
                    }
                })
                .catch((error) => {
                    if (error.response.status === 500) {
                        this.$message.error("Error al intentar eliminar");
                    } else {
                        console.log(error.response.data.message);
                    }
                });
        },
        loadRecord() {
            if (this.recordId) {
                this.$http
                    .get(`/${this.resource}/record/${this.recordId}`)
                    .then((response) => {
                        this.form = response.data.data;
                    })
                    .catch((error) => {
                        console.error("Error al cargar el registro:", error);
                    });
            }
        },
    }
}
</script>
