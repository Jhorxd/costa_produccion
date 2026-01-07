<template>
    <el-dialog :close-on-click-modal="false"
               :title="titleDialog"
               :visible="showDialog"
               append-to-body
               class="pt-0"
               top="7vh"
               width="65%"
               @close="close"
               @open="create">
        <form autocomplete="off"
              @submit.prevent="submit">


            <el-tabs v-model="activeName">
                <el-tab-pane class
                             name="first">
                    <span slot="label">General</span>
                    <div class="row">
                        <!-- <div class="col-md-3">
                            <div v-show="show_has_igv"
                                 class="">
                                <div :class="{'has-danger': errors.has_igv}"
                                     class="form-group">
                                    <el-checkbox v-model="form.has_igv">Incluye Igv
                                    </el-checkbox>
                                    <br>
                                    <small v-if="errors.has_igv"
                                           class="form-control-feedback"
                                           v-text="errors.has_igv[0]"></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div v-show="form.unit_type_id !='ZZ'"
                                 class="">
                                <div :class="{'has-danger': errors.calculate_quantity}"
                                     class="form-group">
                                    <el-checkbox v-model="form.calculate_quantity">Calcular cantidad por precio
                                    </el-checkbox>
                                    <br>
                                    <small v-if="errors.calculate_quantity"
                                           class="form-control-feedback"
                                           v-text="errors.calculate_quantity[0]"></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="">
                                <div :class="{'has-danger': errors.has_plastic_bag_taxes}"
                                     class="form-group">
                                    <el-checkbox v-model="form.has_plastic_bag_taxes">Impuesto a la Bolsa Plástica
                                    </el-checkbox>
                                    <br>
                                    <small v-if="errors.has_plastic_bag_taxes"
                                           class="form-control-feedback"
                                           v-text="errors.has_plastic_bag_taxes[0]"></small>
                                </div>
                            </div>
                        </div> -->
                        <div class="col-12" v-if="recordId!=null">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 table-borderless">
                                    <thead>
                                    <tr>
                                        <th width="25%">
                                            <div v-show="form.unit_type_id !='ZZ'">
                                                <el-checkbox v-model="form.lots_enabled"
                                                             @change="changeLotsEnabled">¿Maneja lotes?
                                                </el-checkbox>
                                            </div>
                                        </th>
                                    </tr>
                                                                        </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div v-show="form.unit_type_id !='ZZ' && form.lots_enabled">
                                                <div :class="{'has-danger': errors.lot_code}"
                                                     class="form-group">
                                                    <el-button icon="el-icon-edit-outline"
                                                               size="small"
                                                               type="primary"
                                                               @click.prevent="clickLotcode">Ingrese lotes
                                                    </el-button>
                                                    <small v-if="errors.lot_code"
                                                           class="form-control-feedback"
                                                           v-text="errors.lot_code[0]"></small>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.internal_id}"
                            class="form-group">
                                 <label class="control-label">Código Único
                                        <el-tooltip class="item"
                                                    content="Código interno de la empresa para el control de sus productos"
                                                    effect="dark"
                                                    placement="top-start">
                                            <i class="fa fa-info-circle"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input v-model="form.internal_id"
                                            dusk="internal_id"></el-input>
                                    <small v-if="errors.internal_id"
                                        class="form-control-feedback"
                                        v-text="errors.internal_id[0]"></small>
                            </div>
                        </div>
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
                            <div :class="{'has-danger': errors.active_principle}"
                                 class="form-group">
                                <label class="control-label">Principio activo</label>
                                <el-input v-model="form.active_principle"
                                          dusk="active_principle"></el-input>
                                <small v-if="errors.active_principle"
                                       class="form-control-feedback"
                                       v-text="errors.active_principle[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.concentration}"
                                 class="form-group">
                                <label class="control-label">Concentración</label>
                                <el-input v-model="form.concentration"
                                          dusk="name"></el-input>
                                <small v-if="errors.concentration"
                                       class="form-control-feedback"
                                       v-text="errors.concentration[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.pharmaceutical_unit_type_id}"
                                 class="form-group">
                                <label class="control-label">Presentación / Forma Farmaceútica <span class="text-danger">*</span></label>
                                <el-select v-model="form.pharmaceutical_unit_type_id"
                                           dusk="unit_type_id">
                                    <el-option v-for="option in pharmaceutical_unit_types"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.pharmaceutical_unit_type_id"
                                       class="form-control-feedback"
                                       v-text="errors.pharmaceutical_unit_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.sale_unit_price}"
                                 class="form-group">
                                <label class="control-label">Precio de Venta <span class="text-danger">*</span></label>
                                <el-input v-model="form.sale_unit_price"
                                          dusk="sale_unit_price"
                                          @input="calculatePercentageOfProfitBySale"></el-input>
                                <small v-if="errors.sale_unit_price"
                                       class="form-control-feedback"
                                       v-text="errors.sale_unit_price[0]"></small>
                            </div>
                        </div>
                        <div v-show="form.unit_type_id !='ZZ'"
                             class="col-md-3">
                            <div :class="{'has-danger': errors.stock}"
                                 class="form-group">
                                <label class="control-label">{{recordId?'Stock':'Stock Inicial'}}</label>
                                <el-input v-model="form.stock" :disabled="form.series_enabled || recordId!=null"></el-input>
                                <small v-if="errors.stock"
                                       class="form-control-feedback"
                                       v-text="errors.stock[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.stock_min}"
                                 class="form-group">
                                <label class="control-label">
                                    Stock mínimo
                                </label>
                                <el-input v-model="form.stock_min">
                                </el-input>
                                <small v-if="errors.stock_min"
                                       class="form-control-feedback"
                                       v-text="errors.stock_min[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.stock_max}"
                                 class="form-group">
                                <label class="control-label">
                                    Stock máximo
                                </label>
                                <el-input v-model="form.stock_max">
                                </el-input>
                                <small v-if="errors.stock_max"
                                       class="form-control-feedback"
                                       v-text="errors.stock_max[0]"></small>
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
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.average_usage}"
                                 class="form-group">
                                <label class="control-label">
                                    Consumo promedio
                                </label>
                                <el-input v-model="form.average_usage">
                                </el-input>
                                <small v-if="errors.average_usage"
                                       class="form-control-feedback"
                                       v-text="errors.average_usage[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.days_to_alert}"
                                 class="form-group">
                                <label class="control-label">
                                    Días para alertar
                                    <el-tooltip
                                        class="item"
                                        content="Días restantes para alertar sobre el stock"
                                        effect="dark"
                                        placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-input v-model="form.days_to_alert">
                                </el-input>
                                <small v-if="errors.days_to_alert"
                                       class="form-control-feedback"
                                       v-text="errors.days_to_alert[0]"></small>
                            </div>
                        </div>
                        
                        <!-- <div :class="recordId?'col-md-6':'col-md-3'">
                            <div :class="{'has-danger': errors.lot}"
                                 class="form-group">
                                <label class="control-label">
                                    Lote
                                    <el-tooltip
                                        class="item"
                                        content="Lote"
                                        effect="dark"
                                        placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-input v-model="form.lot">
                                </el-input>
                                <small v-if="errors.lot"
                                       class="form-control-feedback"
                                       v-text="errors.lot[0]"></small>
                            </div>
                        </div> -->
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.sale_affectation_igv_type_id}"
                                 class="form-group">
                                <label class="control-label">Tipo de afectación</label>
                                <el-select
                                    v-model="form.sale_affectation_igv_type_id"
                                    filterable
                                    @change="changeAffectationIgvType">
                                    <el-option
                                        v-for="option in affectation_igv_types"
                                        :key="option.id"
                                        :label="option.description"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small
                                    v-if="errors.sale_affectation_igv_type_id"
                                    class="form-control-feedback"
                                    v-text="errors.sale_affectation_igv_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.unit_type_id}"
                                 class="form-group">
                                <label class="control-label">Unidad</label>
                                <el-select v-model="form.unit_type_id"
                                           dusk="unit_type_id">
                                    <el-option v-for="option in unit_types"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.unit_type_id"
                                       class="form-control-feedback"
                                       v-text="errors.unit_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.supplier_id}"
                                 class="form-group">
                                <label class="control-label">Laboratorio</label>
                                <el-input v-model="form.laboratory"
                                          dusk="laboratory"></el-input>
                                <small
                                    v-if="errors.laboratory"
                                    class="form-control-feedback"
                                    v-text="errors.laboratory[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.supplier_id}"
                                 class="form-group">
                                <label class="control-label">Proveedor</label>
                                <el-select
                                    v-model="form.supplier_id"
                                    filterable
                                    @change="changeAffectationIgvType">
                                    <el-option
                                        v-for="option in suppliers"
                                        :key="option.id"
                                        :label="option.name"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small
                                    v-if="errors.supplier_id"
                                    class="form-control-feedback"
                                    v-text="errors.supplier_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.sales_condition_id}"
                                 class="form-group">
                                <label class="control-label">Condición de venta</label>
                                <el-select
                                    v-model="form.sales_condition_id"
                                    filterable
                                    @change="changeAffectationIgvType">
                                    <el-option
                                        v-for="option in sales_conditions"
                                        :key="option.id"
                                        :label="option.description"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                                <small
                                    v-if="errors.sales_condition_id"
                                    class="form-control-feedback"
                                    v-text="errors.sales_condition_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.purchase_unit_price}"
                                 class="form-group">
                                <label class="control-label">Precio unitario de costo<span class="text-danger">*</span></label>
                                <el-input v-model="form.purchase_unit_price"
                                          dusk="purchase_unit_price"
                                          @input="calculatePercentageOfProfitBySale"></el-input>
                                <small v-if="errors.purchase_unit_price"
                                       class="form-control-feedback"
                                       v-text="errors.purchase_unit_price[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Estado<span class="text-danger">*</span></label>
                                <el-select
                                    v-model="form.inventory_state_id"
                                    filterable>
                                    <el-option
                                        v-for="option in states"
                                        :key="option.id"
                                        :label="option.description"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div v-if="form.unit_type_id !='ZZ'"
                             v-show="recordId==null"
                             class="col-md-6">
                            <div :class="{'has-danger': errors.warehouse_id}"
                                 class="form-group">
                                <label class="control-label">
                                    Almacén
                                    <el-tooltip class="item"
                                                content="Si no selecciona almacén, se asignará por defecto el relacionado al sucursal"
                                                effect="dark"
                                                placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-select v-model="form.warehouse_id"
                                           filterable>
                                    <el-option v-for="option in warehouses"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.warehouse_id"
                                       class="form-control-feedback"
                                       v-text="errors.warehouse_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3" v-if="recordId">
                            <div class="form-group">
                                <label class="control-label">Ubicación<span class="text-danger">*</span></label>
                                <el-select
                                    v-model="location_id"
                                    filterable
                                    @change="changeLocation">
                                    <el-option
                                        v-for="option in locations"
                                        :key="option.id"
                                        :label="option.name"
                                        :value="option.id"
                                    ></el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end" v-if="recordId">
                            <el-button class="second-buton" @click.prevent="clickItemLocation()">{{form.has_sales?'Mostrar posiciones':'Elegir posición'}}</el-button>
                        </div>

                        <!-- <div class="col-md-3">
                            <div :class="{'has-danger': errors.currency_type_id}"
                                 class="form-group">
                                <label class="control-label">Moneda</label>
                                <el-select v-model="form.currency_type_id"
                                           dusk="currency_type_id">
                                    <el-option v-for="option in currency_types"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.currency_type_id"
                                       class="form-control-feedback"
                                       v-text="errors.currency_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.second_name}"
                                 class="form-group">
                                <label class="control-label">Nombre secundario </label>
                                <el-input v-model="form.second_name"
                                          dusk="second_name"></el-input>
                                <small v-if="errors.second_name"
                                       class="form-control-feedback"
                                       v-text="errors.second_name[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.name}"
                                 class="form-group">
                                <label class="control-label">Descripción</label>
                                <el-input v-model="form.name"
                                          dusk="name"></el-input>
                                <small v-if="errors.name"
                                       class="form-control-feedback"
                                       v-text="errors.name[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.model}"
                                 class="form-group">
                                <label class="control-label">Modelo</label>
                                <el-input v-model="form.model"
                                          dusk="model"></el-input>
                                <small v-if="errors.model"
                                       class="form-control-feedback"
                                       v-text="errors.model[0]"></small>
                            </div>
                        </div>
                        <div class="col-12"></div>
                        <div v-if="form.unit_type_id !='ZZ'"
                             v-show="recordId==null"
                             class="col-md-3">
                            <div :class="{'has-danger': errors.warehouse_id}"
                                 class="form-group">
                                <label class="control-label">
                                    Almacén
                                    <el-tooltip class="item"
                                                content="Si no selecciona almacén, se asignará por defecto el relacionado al sucursal"
                                                effect="dark"
                                                placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-select v-model="form.warehouse_id"
                                           filterable>
                                    <el-option v-for="option in warehouses"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.warehouse_id"
                                       class="form-control-feedback"
                                       v-text="errors.warehouse_id[0]"></small>
                            </div>
                        </div>
                        
                        <div v-show="form.unit_type_id !='ZZ'"
                             class="col-md-3">
                            <div :class="{'has-danger': errors.stock_min}"
                                 class="form-group">
                                <label class="control-label">Stock Mínimo</label>
                                <el-input v-model="form.stock_min"></el-input>
                                <small v-if="errors.stock_min"
                                       class="form-control-feedback"
                                       v-text="errors.stock_min[0]"></small>
                            </div>
                        </div>
                        <div v-show="form.unit_type_id !='ZZ'"
                             class="col-md-3">
                            <div :class="{'has-danger': errors.date_of_due}"
                                 class="form-group">
                                <label class="control-label">Fec. Vencimiento</label>
                                <el-date-picker v-model="form.date_of_due"
                                                :clearable="true"
                                                type="date"
                                                value-format="yyyy-MM-dd"></el-date-picker>
                                <small v-if="errors.date_of_due"
                                       class="form-control-feedback"
                                       v-text="errors.date_of_due[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.barcode}"
                                 class="form-group">
                                <label class="control-label">Código de barra</label>
                                <el-input v-model="form.barcode"></el-input>
                                <small v-if="errors.barcode"
                                       class="form-control-feedback"
                                       v-text="errors.barcode[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.internal_id}"
                                 class="form-group">
                                    <label class="control-label">Código Interno
                                        <el-tooltip class="item"
                                                    content="Código interno de la empresa para el control de sus productos"
                                                    effect="dark"
                                                    placement="top-start">
                                            <i class="fa fa-info-circle"></i>
                                        </el-tooltip>
                                    </label>
                                    <el-input v-model="form.internal_id"
                                            dusk="internal_id"></el-input>
                                    <small v-if="errors.internal_id"
                                        class="form-control-feedback"
                                        v-text="errors.internal_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.item_code}"
                                 class="form-group">
                                <label class="control-label">Código Sunat
                                    <el-tooltip class="item"
                                                content="Código proporcionado por SUNAT, campo obligatorio para exportaciones"
                                                effect="dark"
                                                placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-input v-model="form.item_code"
                                          dusk="item_code"></el-input>
                                <small v-if="errors.item_code"
                                       class="form-control-feedback"
                                       v-text="errors.item_code[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.line}"
                                 class="form-group">
                                <label class="control-label">
                                    Línea de producto
                                    <el-tooltip class="item"
                                                content="Grupo de productos que tienen una relación directa entre sí"
                                                effect="dark"
                                                placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-input v-model="form.line">
                                </el-input>
                                <small v-if="errors.line"
                                       class="form-control-feedback"
                                       v-text="errors.line[0]"></small>
                            </div>
                        </div>
                        
                        <div
                             class="col-md-3">
                            <div :class="{'has-danger': errors.sanitary}"
                                 class="form-group">
                                <label class="control-label">
                                    Registro Sanitario
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
                        
                        <div
                             class="col-md-3">
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

                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.factory_code}"
                                 class="form-group">
                                <label class="control-label">
                                    Código de fábrica
                                    <el-tooltip
                                        class="item"
                                        content="Para habilitar la búsqueda debe realizarlo en configuración/avanzado"
                                        effect="dark"
                                        placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <el-input v-model="form.factory_code">
                                </el-input>
                                <small v-if="errors.factory_code"
                                       class="form-control-feedback"
                                       v-text="errors.factory_code[0]"></small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 table-borderless">
                                    <thead>
                                    <tr>
                                        <th width="25%">
                                            <el-checkbox v-model="form.has_perception"
                                                         @change="changeHasPerception">Incluye percepción
                                            </el-checkbox>
                                        </th>
                                        <th width="25%">
                                            <div v-show="form.unit_type_id !='ZZ'">
                                                <el-checkbox v-model="form.lots_enabled"
                                                             @change="changeLotsEnabled">¿Maneja lotes?
                                                </el-checkbox>
                                            </div>
                                        </th>
                                        <th width="25%">
                                            <div v-show="form.unit_type_id !='ZZ'">
                                                <el-checkbox v-model="form.series_enabled"
                                                             @change="changeLotsEnabled">¿Maneja series?
                                                </el-checkbox>
                                            </div>
                                        </th>
                                        <th width="25%">
                                            <div v-show="form.unit_type_id !='ZZ' && canSeeProduction">
                                                <el-checkbox v-model="form.is_for_production"
                                                             @change="changeProductioTab">Este producto, ¿requiere insumos?
                                                </el-checkbox>
                                            </div>
                                        </th>
                                    </tr>
                                                                        </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div v-show="form.has_perception">
                                                <div class="form-group">
                                                    <el-input v-model="form.percentage_perception"
                                                              placeholder="% de percepción"></el-input>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div v-show="form.unit_type_id !='ZZ' && form.lots_enabled">
                                                <div :class="{'has-danger': errors.lot_code}"
                                                     class="form-group">

                                                     <el-tooltip class="item"
                                                    content="Si va a usar el mismo LOTE en otros almacenes coloque un prefijo para diferenciarlos."
                                                    effect="dark"
                                                    placement="top">
                                                    <el-input v-model="form.lot_code"
                                                              placeholder="Código de lote"></el-input>
                                                    </el-tooltip>
                                                    <small v-if="errors.lot_code"
                                                           class="form-control-feedback"
                                                           v-text="errors.lot_code[0]"></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div v-show="form.unit_type_id !='ZZ' && form.series_enabled && !recordId">
                                                <div :class="{'has-danger': errors.lot_code}"
                                                     class="form-group">
                                                    <el-button icon="el-icon-edit-outline"
                                                               size="small"
                                                               type="primary"
                                                               @click.prevent="clickLotcode">Ingrese series
                                                    </el-button>
                                                    <small v-if="errors.lot_code"
                                                           class="form-control-feedback"
                                                           v-text="errors.lot_code[0]"></small>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.has_isc}"
                                 class="form-group ml-2">
                                <el-checkbox v-model="form.has_isc"
                                             @change="changeIsc">Incluye ISC
                                </el-checkbox>
                                <br>
                                <small v-if="errors.has_isc"
                                       class="form-control-feedback"
                                       v-text="errors.has_isc[0]"></small>
                            </div>
                        </div>

                        <template v-if="form.has_isc">
                            <div class="col-md-3">
                                <div :class="{'has-danger': errors.system_isc_type_id}"
                                     class="form-group">
                                    <label class="control-label">Tipo de sistema ISC</label>
                                    <el-select
                                        v-model="form.system_isc_type_id"
                                        filterable>
                                        <el-option
                                            v-for="option in system_isc_types"
                                            :key="option.id"
                                            :label="option.description"
                                            :value="option.id"
                                        ></el-option>
                                    </el-select>
                                    <small
                                        v-if="errors.system_isc_type_id"
                                        class="form-control-feedback"
                                        v-text="errors.system_isc_type_id[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div :class="{'has-danger': errors.percentage_isc}"
                                     class="form-group">
                                    <label class="control-label">Porcentaje ISC</label>
                                    <el-input v-model="form.percentage_isc"></el-input>
                                    <small
                                        v-if="errors.percentage_isc"
                                        class="form-control-feedback"
                                        v-text="errors.percentage_isc[0]"></small>
                                </div>
                            </div>
                        </template>


                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.subject_to_detraction}"
                                 class="form-group ml-1">
                                <el-checkbox v-model="form.subject_to_detraction">Sujeto a detracción</el-checkbox>
                                <br>
                                <small v-if="errors.subject_to_detraction"
                                       class="form-control-feedback"
                                       v-text="errors.subject_to_detraction[0]"></small>
                            </div>
                        </div>


                        <div class="col-md-3" v-if="showRestrictSaleItemsCpe">
                            <div :class="{'has-danger': errors.restrict_sale_cpe}"
                                 class="form-group">
                                <el-checkbox v-model="form.restrict_sale_cpe">Restringir venta en CPE</el-checkbox>
                                <br>
                                <small v-if="errors.restrict_sale_cpe"
                                       class="form-control-feedback"
                                       v-text="errors.restrict_sale_cpe[0]"></small>
                            </div>
                        </div>

                        <template v-if="showPointSystem">
                            <div class="col-md-3">
                                <div :class="{'has-danger': errors.exchange_points}"
                                    class="form-group">
                                    <el-checkbox v-model="form.exchange_points">¿Se puede canjear por puntos?</el-checkbox>
                                    <br>
                                    <small v-if="errors.exchange_points"
                                        class="form-control-feedback"
                                        v-text="errors.exchange_points[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-3 mb-2" v-if="form.exchange_points">
                                <label class="control-label">
                                    N° de puntos
                                    <el-tooltip
                                        class="item"
                                        content="Total de puntos que necesitará el cliente para canjear el producto."
                                        effect="dark"
                                        placement="top-start">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </label>
                                <div :class="{'has-danger': errors.quantity_of_points}" class="form-group">
                                    <el-input-number v-model="form.quantity_of_points" :min="0.01" :precision="2" :step="1" controls-position="right"></el-input-number>
                                    <small v-if="errors.quantity_of_points" class="form-control-feedback" v-text="errors.quantity_of_points[0]"></small>
                                </div>
                            </div>
                        </template> -->

                    </div> 
                </el-tab-pane>

                <!-- <el-tab-pane class
                             v-if="!isService"
                             name="second">
                    <span slot="label">Almacenes</span>
                    <div class="row">
                        <div v-show="form.unit_type_id !='ZZ'"
                             class="col-12">
                            <h5 class="separator-title mt-0">Precios por almacén</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                    <tr v-for="item in form.item_warehouse_prices"
                                        :key="item.id">
                                        <td>{{ item.description }}</td>
                                        <td width="150">
                                            <el-input v-model="item.price"
                                                      min="0"
                                                      placeholder="Precio"
                                                      step="0.01"
                                                      type="number"></el-input>
                                        </td>
                                    </tr>
                                    <tr v-for="w in warehouses" :key="w.id">
                                        <td>{{ w.description }}</td>
                                        <td width="150">
                                            <el-input placeholder="Precio" v-model="w.price" type="number" min="0" step="0.01"></el-input>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </el-tab-pane> -->
                <el-tab-pane class  v-if="!isService" name="third">
                    <span slot="label">Presentaciones</span>
                    <div class="row">
                        <div v-show="form.unit_type_id !='ZZ'"
                             class="col-md-12">
                            <h5 class="separator-title mt-0">
                                Listado de precios
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
                                        <th class="text-center">Código de barra</th>
                                        <th class="text-center">Unidad</th>
                                        <th class="text-center">Descripción</th>
                                        <th class="text-center">
                                            Factor
                                            <el-tooltip class="item"
                                                        content="Cantidad de unidades"
                                                        effect="dark"
                                                        placement="top">
                                                <i class="fa fa-info-circle"></i>
                                            </el-tooltip>
                                        </th>
                                        <th class="text-center">Precio 1</th>
                                        <th class="text-center">Precio 2</th>
                                        <th class="text-center">Precio 3</th>
                                        <th class="text-center">P. Defecto</th>
                                        <th v-if="config.enable_list_product"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, index) in form.item_unit_types"
                                        :key="index">
                                        <template v-if="row.id">
                                            <td class="text-center"> {{row.barcode}} </td>
                                            <td class="text-center">{{ row.unit_type_id }}</td>
                                            <td class="text-center">{{ row.description }}</td>
                                            <td class="text-center">{{ row.quantity_unit }}</td>
                                            <td class="text-center">
                                                <el-input v-model="row.price1"></el-input>
                                            </td>
                                            <td class="text-center">
                                                <el-input v-model="row.price2"></el-input>
                                            </td>
                                            <td class="text-center">
                                                <el-input v-model="row.price3"></el-input>
                                            </td>
                                            <td class="text-center">Precio {{ row.price_default }}</td>
                                            <td class="series-table-actions text-right" v-if="config.enable_list_product">
                                                <button class="btn waves-effect waves-light btn-xs btn-danger"
                                                        type="button"
                                                        @click.prevent="clickDelete(row.id)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </template>
                                        <template v-else>
                                            <td class="text-center">
                                                <el-input v-model="row.barcode"></el-input>
                                            </td>
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
                                                    <el-input v-model="row.description"></el-input>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-input v-model="row.quantity_unit"></el-input>
                                                    <!-- <small class="form-control-feedback" v-if="errors.quantity_unit" v-text="errors.quantity_unit[0]"></small> -->
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-input v-model="row.price1"></el-input>
                                                    <!-- <small class="form-control-feedback" v-if="errors.stock_min" v-text="errors.stock_min[0]"></small> -->
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-input v-model="row.price2"></el-input>
                                                    <!-- <small class="form-control-feedback" v-if="errors.stock_min" v-text="errors.stock_min[0]"></small> -->
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <el-input v-model="row.price3"></el-input>
                                                    <!-- <small class="form-control-feedback" v-if="errors.stock_min" v-text="errors.stock_min[0]"></small> -->
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <el-select v-model="row.price_default">
                                                        <el-option :key="1"
                                                                   :value="1"
                                                                   label="Precio 1"></el-option>
                                                        <el-option :key="2"
                                                                   :value="2"
                                                                   label="Precio 2"></el-option>
                                                        <el-option :key="3"
                                                                   :value="3"
                                                                   label="Precio 3"></el-option>
                                                    </el-select>
                                                </div>
                                            </td>
                                            <td class="series-table-actions text-right" v-if="config.enable_list_product">
                                                <!-- <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickSubmit(index)">
                                                    <i class="fa fa-check"></i>
                                                </button> -->
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
                </el-tab-pane>
                <el-tab-pane class
                             name="fourth">
                    <span slot="label">Atributos</span>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Imágen <span class="text-danger"></span></label>
                                <el-upload :action="`/${resource}/upload`"
                                           :data="{'type': 'items'}"
                                           :headers="headers"
                                           :on-success="onSuccess"
                                           :show-file-list="false"
                                           class="avatar-uploader">
                                    <img v-if="form.image_url"
                                         :src="form.image_url"
                                         class="avatar">
                                    <i v-else
                                       class="el-icon-plus avatar-uploader-icon"></i>
                                </el-upload>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <div :class="{'has-danger': errors.category_id}"
                                         class="form-group">
                                        <label class="control-label">
                                            Categoría
                                            <a v-if="form_category.add == false"
                                                class="control-label font-weight-bold text-info"
                                                href="#"
                                                @click="form_category.add = true"> [ + Nuevo]</a>
                                            <a v-if="form_category.add == true"
                                                class="control-label font-weight-bold text-info"
                                                href="#"
                                                @click="saveCategory()"> [ + Guardar]</a>
                                            <a v-if="form_category.add == true"
                                                class="control-label font-weight-bold text-danger"
                                                href="#"
                                                @click="form_category.add = false"> [ Cancelar]</a>
                                        </label>
                                        <el-input v-if="form_category.add == true"
                                                  v-model="form_category.name"
                                                  dusk="item_code"
                                                  style="margin-bottom:1.5%;"></el-input>

                                        <el-select v-if="form_category.add == false"
                                                   v-model="form.category_id"
                                                   clearable
                                                   filterable>
                                            <el-option v-for="option in categories"
                                                       :key="option.id"
                                                       :label="option.name"
                                                       :value="option.id"></el-option>
                                        </el-select>
                                        <small v-if="errors.category_id"
                                               class="form-control-feedback"
                                               v-text="errors.category_id[0]"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div :class="{'has-danger': errors.brand_id}"
                                         class="form-group">
                                        <label class="control-label">
                                            Marca
                                            <a v-if="form_brand.add == false"
                                                class="control-label font-weight-bold text-info"
                                                href="#"
                                                @click="form_brand.add = true"> [ + Nuevo]</a>
                                            <a v-if="form_brand.add == true"
                                                class="control-label font-weight-bold text-info"
                                                href="#"
                                                @click="saveBrand()"> [ + Guardar]</a>
                                            <a v-if="form_brand.add == true"
                                                class="control-label font-weight-bold text-danger"
                                                href="#"
                                                @click="form_brand.add = false"> [ Cancelar]</a>
                                        </label>
                                        <el-input v-if="form_brand.add == true"
                                                  v-model="form_brand.name"
                                                  dusk="item_code"
                                                  style="margin-bottom:1.5%;"></el-input>

                                        <el-select v-if="form_brand.add == false"
                                                   v-model="form.brand_id"
                                                   clearable
                                                   filterable>
                                            <el-option v-for="option in brands"
                                                       :key="option.id"
                                                       :label="option.name"
                                                       :value="option.id"></el-option>
                                        </el-select>
                                        <small v-if="errors.brand_id"
                                               class="form-control-feedback"
                                               v-text="errors.brand_id[0]"></small>
                                    </div>
                                </div>
                            </div>
                            <div v-if="attribute_types.length > 0">
                                <h5 class="separator-title mb-0">
                                    Listado
                                    <el-tooltip class="item"
                                                content="Diferentes presentaciones para la venta del producto"
                                                effect="dark"
                                                placement="top">
                                        <i class="fa fa-info-circle"></i>
                                    </el-tooltip>
                                </h5>
                            </div>
                            <div v-if="form.attributes.length > 0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0 table-borderless">
                                        <thead>
                                        <tr>
                                            <th class="pb-0">Tipo</th>
                                            <th class="pb-0">Descripción</th>
                                            <th class="pb-0"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(row, index) in form.attributes"
                                            :key="index">
                                            <td>
                                                <el-select v-model="row.attribute_type_id"
                                                           filterable
                                                           @change="changeAttributeType(index)">
                                                    <el-option v-for="option in attribute_types"
                                                               :key="option.id"
                                                               :label="option.description"
                                                               :value="option.id"></el-option>
                                                </el-select>
                                            </td>
                                            <td>
                                                <el-input v-model="row.value"></el-input>
                                            </td>
                                            <td>
                                                <button class="btn btn-danger btn-sm"
                                                        type="button"
                                                        @click.prevent="clickRemoveAttribute(index)">x
                                                </button>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <a class="control-label font-weight-bold text-info"
                               href="#"
                               @click.prevent="clickAddAttribute">[+ Agregar]</a>
                        </div>
                    </div>
                </el-tab-pane>
                <!-- <el-tab-pane class
                             v-if="!isService"
                             name="five">
                    <span slot="label">Compra</span>
                    <div class="row">
                        <div class="col-md-8">
                            <div :class="{'has-danger': errors.purchase_affectation_igv_type_id}"
                                 class="form-group">
                                <label class="control-label">Tipo de afectación</label>
                                <el-select v-model="form.purchase_affectation_igv_type_id"
                                           @change="changePurchaseAffectationIgvType">
                                    <el-option v-for="option in affectation_igv_types"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.purchase_affectation_igv_type_id"
                                       class="form-control-feedback"
                                       v-text="errors.purchase_affectation_igv_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div :class="{'has-danger': errors.purchase_unit_price}"
                                 class="form-group">
                                <label class="control-label">Precio Unitario</label>
                                <el-input v-model="form.purchase_unit_price"
                                          dusk="purchase_unit_price"
                                          @input="calculatePercentageOfProfitByPurchase"></el-input>
                                <small v-if="errors.purchase_unit_price"
                                       class="form-control-feedback"
                                       v-text="errors.purchase_unit_price[0]"></small>
                            </div>
                        </div>
                        <div v-show="purchase_show_has_igv"
                             class="col-md-4 center-el-checkbox pt-2">
                            <div :class="{'has-danger': errors.purchase_has_igv}"
                                 class="form-group">
                                <el-checkbox v-model="form.purchase_has_igv">Incluye Igv</el-checkbox>
                                <br>
                                <small v-if="errors.purchase_has_igv"
                                       class="form-control-feedback"
                                       v-text="errors.purchase_has_igv[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-4 center-el-checkbox pt-2">
                            <div class="form-group">
                                <el-checkbox v-model="enabled_percentage_of_profit"
                                             @change="changeEnabledPercentageOfProfit">Aplica ganancia
                                </el-checkbox>
                                <br>
                            </div>
                        </div>
                        <div class="col-md-4 pt-2">
                            <div :class="{'has-danger': errors.percentage_of_profit}"
                                 class="form-group">
                                <label class="control-label">Porcentaje de ganancia (%)</label>
                                <el-input v-model="form.percentage_of_profit"
                                          :disabled="!enabled_percentage_of_profit"
                                          @input="calculatePercentageOfProfitByPercentage"></el-input>
                                <small v-if="errors.percentage_of_profit"
                                       class="form-control-feedback"
                                       v-text="errors.percentage_of_profit[0]"></small>
                            </div>
                        </div>

                        isc compras
                        <div class="col-md-4">
                            <div :class="{'has-danger': errors.purchase_has_isc}"
                                 class="form-group">
                                <el-checkbox v-model="form.purchase_has_isc"
                                             @change="purchaseChangeIsc">Incluye ISC
                                </el-checkbox>
                                <br>
                                <small v-if="errors.purchase_has_isc"
                                       class="form-control-feedback"
                                       v-text="errors.purchase_has_isc[0]"></small>
                            </div>
                        </div>

                        <template v-if="form.purchase_has_isc">
                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.purchase_system_isc_type_id}"
                                     class="form-group">
                                    <label class="control-label">Tipo de sistema ISC</label>
                                    <el-select
                                        v-model="form.purchase_system_isc_type_id"
                                        filterable>
                                        <el-option
                                            v-for="option in system_isc_types"
                                            :key="option.id"
                                            :label="option.description"
                                            :value="option.id"
                                        ></el-option>
                                    </el-select>
                                    <small
                                        v-if="errors.purchase_system_isc_type_id"
                                        class="form-control-feedback"
                                        v-text="errors.purchase_system_isc_type_id[0]"></small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div :class="{'has-danger': errors.purchase_percentage_isc}"
                                     class="form-group">
                                    <label class="control-label">Porcentaje ISC</label>
                                    <el-input v-model="form.purchase_percentage_isc"></el-input>
                                    <small
                                        v-if="errors.purchase_percentage_isc"
                                        class="form-control-feedback"
                                        v-text="errors.purchase_percentage_isc[0]"></small>
                                </div>
                            </div>
                        </template>
                        isc compras

                    </div>
                </el-tab-pane> -->

                <el-tab-pane v-if="canShowExtraData"
                             class
                             name="last">
                    <span slot="label">Informacion Adicional</span>
                    <extra-info
                        :form.sync="form"
                    ></extra-info>
                </el-tab-pane>

                <el-tab-pane class
                             v-if="form.is_for_production && canSeeProduction"
                             name="six">
                    <span slot="label">Producción</span>
                    <div class="row">

                        <div class="col-md-7 col-lg-7 col-xl-7 col-sm-7">
                            <div id="custom-select"
                                 :class="{'has-danger': errors.item_id}"
                                 class="form-group">
                                <label class="control-label">
                                    Insumo
                                </label>

                                <template id="select-append">
                                    <el-input id="custom-input">
                                        <el-select
                                            id="select-width"
                                            ref="selectSearchNormal"
                                            slot="prepend"
                                            v-model="item_suplly"
                                            :loading="loading_search"
                                            :remote-method="searchRemoteItems"
                                            filterable
                                            placeholder="Buscar"
                                            popper-class="el-select-items"
                                            remote
                                            @change="changeItem"
                                            @focus="focusSelectItem">


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
                                        </el-select>
                                    </el-input>
                                </template>
                                <small v-if="errors.item_id"
                                       class="form-control-feedback"
                                       v-text="errors.item_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-7 col-lg-7 col-xl-7 col-sm-7 " style="    margin-top: 1rem !important;">
                            <div class="form-group ">
                                <button class="btn waves-effect waves-light btn-primary"
                                        type="button"
                                        @click.prevent="clickAddSupply" >
                                    + Agregar Producto
                                </button>
                            </div>
                        </div>
                        <div class="col-12 table-responsive" v-if="form.supplies && form.supplies.length > 0">

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
<!--                                        <th>item_id</th>-->
                                        <th>Insumo</th>
                                        <th>Cantidad</th>
<!--                                        <th class="text-right">Acciones</th>-->
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, index) in form.supplies" :key="index">
                                        <td>{{ index + 1 }}</td>
<!--                                        <td>{{ row.item_id }}</td>-->
                                        <td>{{ (row.individual_item)?row.individual_item.description:row.individual_item }}</td>
                                        <td>
                                            <el-input-number v-model="row.quantity"
                                                      ></el-input-number>
                                            </td>

                                        <!--
                                        <td class="text-right">
                                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickCreate(row.id)">Editar</button>

                                            <template v-if="typeUser === 'admin'">
                                                <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"  @click.prevent="clickDelete(row.id)">Eliminar</button>
                                            </template>
                                        </td>
                                        -->
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!--
                        <div class="col-md-4">
                            <div :class="{'has-danger': errors.purchase_unit_price}"
                                 class="form-group">
                                <label class="control-label">Precio Unitario</label>
                                <el-input v-model="form.purchase_unit_price"
                                          dusk="purchase_unit_price"
                                          @input="calculatePercentageOfProfitByPurchase"></el-input>
                                <small v-if="errors.purchase_unit_price"
                                       class="form-control-feedback"
                                       v-text="errors.purchase_unit_price[0]"></small>
                            </div>
                        </div>
                        <div v-show="purchase_show_has_igv"
                             class="col-md-4 center-el-checkbox pt-2">
                            <div :class="{'has-danger': errors.purchase_has_igv}"
                                 class="form-group">
                                <el-checkbox v-model="form.purchase_has_igv">Incluye Igv</el-checkbox>
                                <br>
                                <small v-if="errors.purchase_has_igv"
                                       class="form-control-feedback"
                                       v-text="errors.purchase_has_igv[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-4 center-el-checkbox pt-2">
                            <div class="form-group">
                                <el-checkbox v-model="enabled_percentage_of_profit"
                                             @change="changeEnabledPercentageOfProfit">Aplica ganancia
                                </el-checkbox>
                                <br>
                            </div>
                        </div>
                        <div class="col-md-4 pt-2">
                            <div :class="{'has-danger': errors.percentage_of_profit}"
                                 class="form-group">
                                <label class="control-label">Porcentaje de ganancia (%)</label>
                                <el-input v-model="form.percentage_of_profit"
                                          :disabled="!enabled_percentage_of_profit"
                                          @input="calculatePercentageOfProfitByPercentage"></el-input>
                                <small v-if="errors.percentage_of_profit"
                                       class="form-control-feedback"
                                       v-text="errors.percentage_of_profit[0]"></small>
                            </div>
                        </div>
                        -->
                    </div>
                </el-tab-pane>
                <el-tab-pane class name="seven">
                    <span slot="label">Documentos</span>
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-center mb-4 mt-4">
                            <el-upload
                                :action="''"
                                ref="upload"
                                :headers="headers"
                                :show-file-list="false"
                                :auto-upload="false"
                                :on-change="handleFileChange"
                                :on-error="errorUpload"
                                :multiple="true"
                                :data="form">
                                <el-button slot="trigger" type="primary">Añadir archivo</el-button>
                            </el-upload>
                        </div>
                        <div v-if="form.item_files && form.item_files.length > 0"
                             class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="bg-light">
                                    <tr>
                                        <th class="text-center">Nombre</th>
                                        <th class="text-center">Fecha</th>
                                        <th class="text-center">Usuario</th>
                                        <th class="text-center">Opciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, index) in form.item_files"
                                        :key="index">
                                        <template v-if="true">
                                            <td class="text-center"> {{row.filename}} </td>
                                            <td class="text-center">{{ row.created_at }}</td>
                                            <td class="text-center">{{ row.user_created_at }}</td>
                                            <td class="series-table-actions text-right" v-if="config.enable_list_product">
                                                <button class="btn waves-effect waves-light btn-xs btn-danger"
                                                        type="button"
                                                        @click.prevent="deleteFile(index)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                                <button class="btn waves-effect waves-light btn-xs btn-primary"
                                                        type="button"
                                                        v-if="recordId"
                                                        @click.prevent="downloadFile(row.id)">
                                                    <i class="fa fa-download"></i>
                                                </button>
                                            </td>
                                        </template>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>
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

        <lots-form
            :lots="form.lots"
            :recordId="recordId"
            :showDialog.sync="showDialogLots"
            :stock="form.stock"
            @addRowLot="saveLotsModal">
        </lots-form>

        <item-location
            :showDialog.sync="showDialogLocation"
            :location_id="location_id"
            :positions_selected="positions_selected"
            :positions="positions"
            :stock="form.stock"
            :lots_enabled="form.lots_enabled"
            :lots="form.lots"
            :has_sales="form.has_sales"
            @positions-save="saveDataPosition">
        </item-location>

    </el-dialog>
</template>

<script>
import LotsForm from './partials/lots.vue'
import ExtraInfo from './partials/extra_info'
import {mapActions, mapState} from "vuex";
import {ItemOptionDescription, ItemSlotTooltip} from "../../../helpers/modal_item";
import ItemLocation from './locations.vue';
import { Alert } from 'bootstrap';

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
        LotsForm,
        ExtraInfo,
        ItemLocation
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
            form_category: {add: false, name: null, id: null},
            form_brand: {add: false, name: null, id: null},
            warehouses: [],
            items: [],
            suppliers: [],
            sales_conditions: [],
            positions: [],
            states: [],
            positions_selected: [],
            lots_enabled_init_aux: null,
            location_id: null,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            pharmaceutical_unit_types: [],
            username: '---',
            item_files_deleted: [],
            locations: [],
            loading_submit: false,
            showPercentagePerception: false,
            has_percentage_perception: false,
            percentage_perception: null,
            enabled_percentage_of_profit: false,
            titleDialog: null,
            resource: 'items',
            errors: {},
            item_suplly: {},
            headers: headers_token,
            form: {
                item_supplies:[],
                is_for_production:false,
            },
            // configuration: {},
            unit_types: [],
            currency_types: [],
            system_isc_types: [],
            affectation_igv_types: [],
            categories: [],
            brands: [],
            accounts: [],
            show_has_igv: true,
            purchase_show_has_igv: true,
            have_account: false,
            item_unit_type: {
                id: null,
                unit_type_id: null,
                quantity_unit: 0,
                price1: 0,
                price2: 0,
                price3: 0,
                price_default: 2,

            },
            attribute_types: [],
            activeName: 'first',
            fromPharmacy: false,
            inventory_configuration: null
        }
    },
    async created() {
        this.loadConfiguration()
        if (this.pharmacy !== undefined && this.pharmacy == true) {
            this.fromPharmacy = true;
        }
        await this.initForm();
        await this.getDataTables();

        this.$eventHub.$on('submitPercentagePerception', (data) => {
            this.form.percentage_perception = data
            if (!this.form.percentage_perception) this.has_percentage_perception = false
        })

        this.$eventHub.$on('reloadTables', () => {
            this.reloadTables()
        })

        await this.setDefaultConfiguration()
        
    },

    methods: {
        changeLocation(){
            this.positions_selected = [];
            this.form.positions_selected = [];
        },
        ...mapActions([
            'loadConfiguration',
        ]),
        handleFileChange(file) {            
            if(this.username='---'){
                this.username = JSON.parse(localStorage.getItem('establishment')).email;
            }
            const filename = file.name;
            const now = new Date();
            const created_at = now.toISOString().slice(0, 19).replace("T", " ");
            let data = {
                filename: filename,
                created_at: created_at,
                user_created_at: this.username,
                raw: file.raw
            };
            this.form.item_files.push(data);
        },
        errorUpload(error) {
            console.log(error)
        },
        saveDataPosition(data) {
            if(data.length>0){
                this.positions_selected = [];
                this.positions_selected = data;
                console.log(this.positions_selected);
                
            }
        },
        setDefaultConfiguration() {
            this.form.sale_affectation_igv_type_id = (this.config) ? this.config.affectation_igv_type_id : '10'

            this.$http.get(`/configurations/record`).then(response => {
                this.form.has_igv = response.data.data.include_igv
                this.form.purchase_has_igv = response.data.data.include_igv
                // this.$setStorage('configuration',response.data.data)
                this.$store.commit('setConfiguration', response.data.data);
                this.loadConfiguration()
            })
        },
        purchaseChangeIsc() {

            if (!this.form.purchase_has_isc) {
                this.form.purchase_system_isc_type_id = null
                this.form.purchase_percentage_isc = 0
            }

        },
        changeIsc() {

            if (!this.form.has_isc) {
                this.form.system_isc_type_id = null
                this.form.percentage_isc = 0
            }

        },
        clickAddAttribute() {
            this.form.attributes.push({
                attribute_type_id: null,
                description: null,
                value: null,
                start_date: null,
                end_date: null,
                duration: null,
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
        changeLotsEnabled() {
            this.positions_selected = [];
            this.positions = [];
            if(!this.form.lots_enabled){
                this.form.lot_code = null;
                this.form.lots = [];
            }
        },
        changeProductioTab(){

        },
        saveLotsModal(lots) {
            if(lots.length>0){
                this.form.lots = lots
                let stock_total = 0;
                lots.forEach(element => {
                    element.quantity = parseInt(element.quantity); 
                    stock_total += parseInt(element.quantity);
                });
                this.form.stock = stock_total;
            }
        },
        async clickItemLocation() {
            if (!this.location_id) {
                this.$message.error("Seleccione una ubicación");
                return;
            }
            
            await this.$http.get(`/${this.resource}/positions/${this.location_id}/${this.recordId}`)
                .then(response => {
                    if (response.data.success) {
                        const data = response.data.data;
                        this.positions = data.positions;
                        this.mapLots(this.positions);
                        
                        if(this.positions_selected.length==0){
                            this.positions_selected = data.item_positions;
                        }
                        this.positions_selected.forEach(element => {
                            const position_finded = this.positions.find(position => {return position.row == element.row && position.column == element.column});
                            if(position_finded){
                                position_finded.stock = parseInt(element.stock);
                            }else{
                                position_finded.stock = 0;
                            }
                            console.log(element);
                            
                            if (this.form.lots_enabled && element.lots!=undefined) {
                                if (element.lots.length>0){
                                    position_finded.lots = element.lots;
                                }
                            }
                        });
                        
                        /* if(this.form.location_id!=this.location_id || this.lots_enabled_init_aux != this.form.lots_enabled){
                            this.positions_selected = [];
                        } */
                        
                        
                    }
                });
            this.showDialogLocation = true;
        },
        mapLots(positions){
            positions.forEach(element => {
                if(element.lots.length>0){
                    element.lots.forEach(element_lot => {
                        const lot_finded = this.form.lots.find(form_lot => form_lot.id == element_lot.lots_group_id);
                        if(lot_finded){
                            lot_finded.selected_global = true;
                            element_lot.code = lot_finded.code;
                        }
                    });
                }
            });
        },
        clickLotcode() {
            this.showDialogLots = true
        },
        changeHaveAccount() {
            if (!this.have_account) this.form.account_id = null
        },
        changeEnabledPercentageOfProfit() {
            // if(!this.enabled_percentage_of_profit) this.form.percentage_of_profit = 0
        },
        deleteFile(index) {
            this.item_files_deleted.push(this.form.item_files[index]);
            this.form.item_files.splice(index, 1);
        },
        changeHasPerception() {
            if (!this.form.has_perception) {
                this.form.percentage_perception = null
            }
        },
        clickAddRow() {
            this.form.item_unit_types.push({
                id: null,
                description: null,
                unit_type_id: 'NIU',
                quantity_unit: 0,
                price1: 0,
                price2: 0,
                price3: 0,
                price_default: 2,
                barcode: null
            })
        },
        clickCancel(index) {
            this.form.item_unit_types.splice(index, 1)
        },
        initForm() {
            this.loading_submit = false,
            this.errors = {}

            this.form = {
                id: null,
                colors: [],
                item_type_id: '01',
                internal_id: null,
                item_code: null,
                item_code_gs1: null,
                description: null,
                name: null,
                second_name: null,
                unit_type_id: 'NIU',
                pharmaceutical_unit_type_id: null,
                item_files: [],
                currency_type_id: 'PEN',
                sale_unit_price: 0,
                unit_price: 0,
                purchase_unit_price: 0,
                has_isc: false,
                system_isc_type_id: null,
                percentage_isc: 0,
                suggested_price: 0,
                sale_affectation_igv_type_id: null,
                purchase_affectation_igv_type_id: null,
                calculate_quantity: false,
                stock: 0,
                stock_max: null,
                stock_old: 1,
                stock_min: 1,
                stock_total: 0,
                has_igv: true,
                has_perception: false,
                has_sales: false,
                item_unit_types: [],
                percentage_of_profit: 0,
                percentage_perception: null,
                image: null,
                image_url: null,
                temp_path: null,
                is_set: false,
                account_id: null,
                category_id: null,
                brand_id: null,
                date_of_due: null,
                lot_code: null,
                location_id: null,
                line: null,
                lots_enabled: false,
                lots: [],
                //lot:0,
                attributes: [],
                series_enabled: false,
                purchase_has_igv: true,
                web_platform_id: null,
                has_plastic_bag_taxes: false,
                item_warehouse_prices: [],
                item_supplies:[],

                purchase_has_isc: false,
                purchase_system_isc_type_id: null,
                purchase_percentage_isc: 0,
                subject_to_detraction: false,

                exchange_points: false,
                quantity_of_points: 0,
                factory_code: null,
                restrict_sale_cpe: false,
                sales_condition_id: null,
                supplier_id: null,
                inventory_state_id: '1',
                average_usage: null,
                days_to_alert: null
            }

            this.show_has_igv = true
            this.purchase_show_has_igv = true
            this.enabled_percentage_of_profit = false
        },
        onSuccess(response, file, fileList) {
            if (response.success) {
                this.form.image = response.data.filename
                this.form.image_url = response.data.temp_image
                this.form.temp_path = response.data.temp_path
            } else {
                this.$message.error(response.message)
            }
        },
        changeAffectationIgvType() {

            let affectation_igv_type_exonerated = [20, 21, 30, 31, 32, 33, 34, 35, 36, 37]
            let is_exonerated = affectation_igv_type_exonerated.includes((parseInt(this.form.sale_affectation_igv_type_id)));

            if (is_exonerated) {
                this.show_has_igv = false
                this.form.has_igv = true
            } else {
                this.show_has_igv = true
            }

        },
        changePurchaseAffectationIgvType() {

            let affectation_igv_type_exonerated = [20, 21, 30, 31, 32, 33, 34, 35, 36, 37]
            let is_exonerated = affectation_igv_type_exonerated.includes((parseInt(this.form.purchase_affectation_igv_type_id)));

            if (is_exonerated) {
                this.purchase_show_has_igv = false
                this.form.purchase_has_igv = true
            } else {
                this.purchase_show_has_igv = true
            }

        },
        resetForm() {
            this.positions_selected = [];
            this.positions = [];
            this.item_files_deleted = [];
            this.form.item_files = [];
            this.location_id = null;
            this.lots_enabled_init_aux = null;
            this.initForm()
            this.form.sale_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
            this.form.purchase_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
            this.setDefaultConfiguration()
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
                        this.form = response.data.data;
                        this.form.stock_old = this.form.stock;
                        if(this.form.lots.length>0){
                            this.form.lots.forEach(lot => {
                                lot.selected_global = false;
                            });
                        }
                        this.lots_enabled_init_aux = this.form.lots_enabled;
                        if(response.data.data.positions_selected.length>0){
                            response.data.data.positions_selected.forEach(position_selected => {
                                const lots = position_selected.lots.map(lot_element => {
                                    return {
                                        ...lot_element,
                                        code: lot_element.get_code_lots_group.code
                                    };
                                });  
                                this.positions_selected.push({
                                    row: position_selected.row,
                                    column: position_selected.column,
                                    stock: position_selected.stock,
                                    id: position_selected.id,
                                    lots: lots
                                });
                            });
                        }
                        this.location_id = response.data.data.location_id;
                        this.has_percentage_perception = (this.form.percentage_perception) ? true : false;
                        this.changeAffectationIgvType();
                        this.changePurchaseAffectationIgvType();
                        // let warehousePrices = response.data.data.warehouse_prices;
                        // console.error(warehousePrices);
                        // if (warehousePrices.length > 0) {
                        //     this.warehouses = this.warehouses.map(w => {
                        //         let price = warehousePrices.find(wp => wp.warehouse_id === w.id);
                        //         if (price) {
                        //             var priceToJson = {...price};
                        //             w.price = priceToJson.price;
                        //         }
                        //         return w;
                        //     });
                        // } else {
                        //     this.warehouses = this.warehouses.map(w => {
                        //         delete w.price;
                        //         return w;
                        //     });
                        // }
                    })
                    await this.getLocationsByWarehouse(this.form.warehouse_id);
            }else{
                await this.getDataTables();
            }

            this.setDataToItemWarehousePrices()

        },
        async getLocationsByWarehouse(warehouse_id){
            try {
                const response = await this.$http.get(`/${this.resource}/getLocations/${warehouse_id}`);
                if(response.data.success){
                    this.locations = response.data.data;
                }
            } catch (error) {
                console.log(error);
            }
        },
        async getDataTables(){
            const response = await this.$http.get(`/${this.resource}/tables`);
            let data = response.data;
            this.unit_types = data.unit_types;
            this.pharmaceutical_unit_types = data.pharmaceutical_item_unit_types;
            this.sales_conditions = data.sales_conditions
            this.suppliers = data.suppliers
            this.accounts = data.accounts
            this.currency_types = data.currency_types
            this.system_isc_types = data.system_isc_types
            this.affectation_igv_types = data.affectation_igv_types
            this.warehouses = data.warehouses
            this.categories = data.categories
            this.brands = data.brands
            this.attribute_types = data.attribute_types
            
            this.states = data.states;
            if(this.states.length>0)
                this.form.inventory_state_id = this.states[0].id;
            if(this.pharmaceutical_unit_types.length>0)
                this.form.pharmaceutical_unit_type_id = this.pharmaceutical_unit_types[0].id;
            
            // this.config = data.configuration
            if (this.canShowExtraData) {
                this.$store.commit('setColors', data.colors);
                this.$store.commit('setCatItemSize', data.CatItemSize);
                this.$store.commit('setCatItemUnitsPerPackage', data.CatItemUnitsPerPackage);
                this.$store.commit('setCatItemStatus', data.CatItemStatus);
                this.$store.commit('setCatItemMoldCavity', data.CatItemMoldCavity);
                this.$store.commit('setCatItemMoldProperty', data.CatItemMoldProperty);
                this.$store.commit('setCatItemUnitBusiness', data.CatItemUnitBusiness);
                this.$store.commit('setCatItemPackageMeasurement', data.CatItemPackageMeasurement);
                this.$store.commit('setCatItemProductFamily', data.CatItemPackageMeasurement);
            }
            this.$store.commit('setConfiguration', data.configuration);


            this.loadConfiguration()
            this.form.sale_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
            this.form.purchase_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
            this.inventory_configuration = data.inventory_configuration;
        },
        setDataToItemWarehousePrices() {

            this.warehouses.forEach(warehouse => {

                let item_warehouse_price = _.find(this.form.item_warehouse_prices, {warehouse_id: warehouse.id})

                if (!item_warehouse_price) {

                    this.form.item_warehouse_prices.push({
                        id: null,
                        item_id: null,
                        warehouse_id: warehouse.id,
                        price: null,
                        description: warehouse.description,
                    })
                }

            });

            this.form.item_warehouse_prices = _.orderBy(this.form.item_warehouse_prices, ['warehouse_id'])

        },
        loadRecord() {
            if (this.recordId) {
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data.data
                        console.error(this.form.is_for_production)
                        this.changeAffectationIgvType()
                        this.changePurchaseAffectationIgvType()
                    })
            }
        },
        calculatePercentageOfProfitBySale() {
            let difference = parseFloat(this.form.sale_unit_price) - parseFloat(this.form.purchase_unit_price);

            if (parseFloat(this.form.purchase_unit_price) === 0) {
                this.form.percentage_of_profit = 0;
            } else {
                if (this.enabled_percentage_of_profit) this.form.percentage_of_profit = difference / parseFloat(this.form.purchase_unit_price) * 100;
            }
        },
        calculatePercentageOfProfitByPurchase() {
            if (this.form.percentage_of_profit === '') {
                this.form.percentage_of_profit = 0;
            }

            if (this.enabled_percentage_of_profit) this.form.sale_unit_price = (this.form.purchase_unit_price * (100 + parseFloat(this.form.percentage_of_profit))) / 100
        },
        calculatePercentageOfProfitByPercentage() {
            if (this.form.percentage_of_profit === '') {
                this.form.percentage_of_profit = 0;
            }

            if (this.enabled_percentage_of_profit) this.form.sale_unit_price = (this.form.purchase_unit_price * (100 + parseFloat(this.form.percentage_of_profit))) / 100
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

            const stock = parseInt(this.form.stock);
            if(isNaN(stock)){
                 return this.$message.error('Stock Inicial debe ser un número entero.');
            }

            if(this.stock_max!=null && parseInt(this.form.stock)>parseInt(this.form.stock_old) && this.recordId){
                const difference = parseInt(this.form.stock)-parseInt(this.form.stock_old);
                const new_stock_total = parseInt(this.form.stock_total)+difference;
                if(new_stock_total>parseInt(this.form.stock_max)){
                    const stock_available = parseInt(this.form.stock_max) - parseInt(this.form.stock_total) + parseInt(this.form.stock_old); 
                    return this.$message.error('El stock maximo disponible es: '+ stock_available);
                }
            }

            if (this.validateItemUnitTypes() > 0) return this.$message.error('El campo factor no puede ser menor a 0.0001');

            if (this.fromPharmacy === true) {
                if (this.form.cod_digemid === null) {
                    return this.$message.error('Debe haber un codigo DIGEMID');
                }
                if (this.form.sanitary === null) {
                    return this.$message.error('Debe haber un Registro Sanitario');
                }
            }
            if (this.form.has_perception && !this.form.percentage_perception) return this.$message.error('Ingrese un porcentaje');

            if (this.form.lots_enabled) {
                
                if(this.form.lots.length==0){
                    return this.$message.error('Ingrese los lotes correctamente');
                }
            }

            /* if (!this.recordId && this.form.series_enabled) {

                if (this.form.lots.length > this.form.stock)
                    return this.$message.error('La cantidad de series registradas es superior al stock');

            } */

            if (this.form.has_isc) {
                if (this.form.percentage_isc <= 0)
                    return this.$message.error('El porcentaje isc debe ser mayor a 0');
            }

            if (this.form.purchase_has_isc) {
                if (this.form.purchase_percentage_isc <= 0)
                    return this.$message.error('El porcentaje isc debe ser mayor a 0 (Compras)');
            }
            console.log(this.form.lots);
            
            const someUnselected = this.form.lots.some(lot => lot.selected_global == false);
            console.log(someUnselected);
            if (this.recordId && this.form.lots_enabled && someUnselected) {
                return this.$message.error('Debe elegir posiciones para los lotes'); 
            }
            
            this.form.positions_selected = this.positions_selected;
            
            this.form.location_id = this.location_id;

            this.loading_submit = true;
            
            await this.$http.post(`/${this.resource}`, this.form)
                .then(async response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        const item_id = response.data.id;
                        this.positions_selected=[];
                        
                        if (this.form.item_files.length > 0 || this.recordId)  {
                            let formData = new FormData();
                            
                            await this.form.item_files.forEach((file, index) => {
                                if (file.raw){
                                    formData.append(`documents[${index}]`, file.raw, file.filename);
                                }
                            });

                            if (this.item_files_deleted && this.item_files_deleted.length > 0) {
                                formData.append('files_deleted', JSON.stringify(this.item_files_deleted));
                            }
                            this.positions_selected=[];
                            this.$http.post(`/${this.resource}/saveDocuments/${item_id}`, formData, {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                }
                            })
                            .then(response => {
                                if (response.data.success) {
                                    this.item_files_deleted=[];
                                    this.close();
                                } else {
                                    console.error("Error al subir documentos:", response.data.message);
                                }
                            })
                            .catch(error => {
                                console.error("Error en la petición:", error);
                            });
                        }
                        if (this.external) {
                            this.$eventHub.$emit('reloadDataItems', response.data.id)
                        } else {
                            this.$eventHub.$emit('reloadData')
                        }
                        this.initForm();
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
        async downloadFile(file_id) {
            try {
                const response = await this.$http.get(`/${this.resource}/fileDownload/${file_id}`, {
                    responseType: 'blob', // <-- Este es el punto clave
                });

                const contentDisposition = response.headers['content-disposition'];
                const filename = contentDisposition
                    ? contentDisposition.split('filename=')[1].replace(/['"]/g, '')
                    : `archivo_${file_id}.ext`;

                const blob = new Blob([response.data]);
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link); // <-- Importante para Firefox
                link.click();
                document.body.removeChild(link);

                this.$message.success("Archivo descargado correctamente");
            } catch (error) {
                console.error("Error al descargar el archivo:", error);
                this.$message.error("Error al descargar el archivo");
            }
        },
        close() {
            this.resetForm();
            this.positions_selected = [];
            this.positions = [];
            c
            this.$emit('update:showDialog', false);
        },
        changeHasIsc() {
            this.form.system_isc_type_id = null
            this.form.percentage_isc = 0
            this.form.suggested_price = 0
        },
        changeSystemIscType() {
            if (this.form.system_isc_type_id !== '03') {
                this.form.suggested_price = 0
            }
        },
        saveCategory() {
            this.form_category.add = false

            this.$http.post(`/categories`, this.form_category)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.categories.push(response.data.data)
                        this.form_category.name = null
                    } else {
                        this.$message.error('No se guardaron los cambios')
                    }
                })
                .catch(error => {

                })
        },
        saveBrand() {
            this.form_brand.add = false

            this.$http.post(`/brands`, this.form_brand)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.brands.push(response.data.data)
                        this.form_brand.name = null

                    } else {
                        this.$message.error('No se guardaron los cambios')
                    }
                })
                .catch(error => {

                })


        },
        changeAttributeType(index) {
            let attribute_type_id = this.form.attributes[index].attribute_type_id
            let attribute_type = _.find(this.attribute_types, {id: attribute_type_id})
            this.form.attributes[index].description = attribute_type.description
        },
        clickRemoveAttribute(index) {
            this.form.attributes.splice(index, 1)
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
        changeItem() {
            this.getItems();
            this.item_suplly = _.find(this.items, {'id': this.item_suplly});
            /*
            this.form.unit_price = this.item_suplly.sale_unit_price;

            this.lots = this.item_suplly.lots

            this.form.has_igv = this.item_suplly.has_igv;

            this.form.affectation_igv_type_id = this.item_suplly.sale_affectation_igv_type_id;
            this.form.quantity = 1;
            this.item_unit_types = this.item_suplly.item_unit_types;

            (this.item_unit_types.length > 0) ? this.has_list_prices = true : this.has_list_prices = false;
            */

        },
        focusSelectItem() {
            this.$refs.selectSearchNormal.$el.getElementsByTagName('input')[0].focus()
        },

        ItemSlotTooltipView(item) {
            return ItemSlotTooltip(item);
        },
        ItemOptionDescriptionView(item) {
            return ItemOptionDescription(item)
        },
        clickAddSupply(){
            // item_supplies
            if(this.form.supplies === undefined) this.form.supplies = [];
            let item = this.item_suplly;
            if(item === null) return false;
            if(item === undefined) return false;
            if(item.id=== undefined) return false;
            this.items = [];
            this.item_suplly = {}

            item.item_id = this.form.id
            //item.individual_item_id = item.id
            item.individual_item_id = item.id
            item.individual_item = {
                'description':item.description
            }
            //item.individual_item = item
            // item.quantity = 0
            //if(isNaN(item.quantity)) item.quantity = 0 ;
            this.form.supplies.push(item)
            this.changeItem()


        },
    }
}
</script>
