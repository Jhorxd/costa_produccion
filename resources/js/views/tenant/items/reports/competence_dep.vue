<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/reports/kardex">
                <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-building-warehouse"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-13l9 -4l9 4v13" /><path d="M13 13h4v8h-10v-6h6" /><path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3" /></svg>
            </a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span> Reporte de precios de competencia por producto </span></li>
            </ol>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <!-- <div class="card-header bg-info">
                <h3 class="my-0">Consulta kardex</h3>
            </div> -->
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12  ">
                       
                                    <data-table :resource="resource">
                                        <tr slot="heading">
                                            <!-- <th>#</th> -->
                                            <th v-if="!item_id">Producto</th>
                                            <th>Fecha y hora transacción</th>
                                            <th>Tipo transacción</th>
                                            <th>Número</th>
                                            <th>NV. Asociada</th>
                                            <th>Pedido</th>
                                            <th>Doc. Asociado</th>
                                            <th>Lotes</th>
                                            <th>Fecha emisión</th>
                                            <th>Fecha registro</th>
                                            <th>Entrada</th>
                                            <th>Salida</th>
                                            <th v-if="item_id">Saldo</th>
                                            <th></th>
                                            <!--
                                            <th >Almacen </th>
                                            <th >Precio de almacen</th>
                                        -->
                                        </tr>
                                        <tr slot-scope="{ index, row }">
                                            <!-- <td>{{ index }}</td> -->
                                            <td v-if="!item_id">{{ row.item_name }}</td>
                                            <td>{{ row.date_time }}</td>
                                            <td>{{ row.type_transaction }}</td>
                                            <td>{{ row.number }}</td>
                                            <td>{{ row.sale_note_asoc }}</td>
                                            <td>{{ row.order_note_asoc }}</td>
                                            <td>{{ row.doc_asoc }}</td>
                                            <td>{{ row.lots ? row.lots : '-' }}</td>
                                            <td>{{ row.date_of_issue }}</td>
                                            <td>{{ row.date_of_register }}</td>
                                            <!-- <td>{{ row.inventory }}</td> -->
                                            <td>{{ row.input }}</td>
                                            <td>{{ row.output }}</td>
                                            <td v-if="item_id">{{ row.balance }}</td>
                                            <td class="text-right">
                                                <button class="btn waves-effect waves-light btn-xs btn-info"
                                                        type="button"
                                                        @click.prevent="downloadPdfGuide(row.guide_id)"
                                                        v-if="row.guide_id">
                                                    <i class="fa fa-file-pdf"></i>
                                                </button>
                                            </td>
                                            <!--
                                                <td v-if="row.warehouse">{{row.warehouse}}</td>
                                                <td v-if="row.item_warehouse_price">{{row.item_warehouse_price}}</td>
                                                -->
                                        </tr>
                                    </data-table>
                                
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {mapActions, mapState} from "vuex/dist/vuex.mjs";
import DataTable from '../../../../components/DataTableCompetence.vue'

export default {
    props:['configuration'],
    components: {DataTable},
    data() {
        return {
            activeName: "first",
            resource: 'reports/kardex',
            form: {},
            item_id: null
        };
    },
    created() {
        this.loadConfiguration()
        /*this.$store.commit('setConfiguration', this.configuration)
        this.$eventHub.$on('emitItemID', (item_id) => {
            this.item_id = item_id
        })*/
    },
    methods:{
        ...mapActions([
            'loadConfiguration',
        ]),
        downloadPdfGuide(guide_id) {
            if (guide_id) {
                window.open(`/${this.resource}/get_pdf_guide/${guide_id}`, "_blank");
            }
        }
    },
};
</script>
