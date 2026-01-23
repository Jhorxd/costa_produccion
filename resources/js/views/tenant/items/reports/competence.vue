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
                       
                        <div class="table-responsive">
                            <data-table :resource="resource">
                                <tr slot="heading">
                                    <th width="5%">#</th>
                                    <th>Nombre Producto</th>
                                    <th>Descripción</th>
                                    <th>Precio Local</th>
                                    <th>P. Competencia 1</th>
                                    <th>P. Competencia 2</th>
                                    <th>P. Competencia 3</th>
                                    <th>P. Competencia 4</th>
                                </tr>
                                <tr slot-scope="{ index, row }">
                                <!--<tr v-for="(row, index) in records">-->
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ row.item_description }}</td>
                                    <td>{{ row.description }}</td>
                                    <td>{{ row.label_1 }}</td>
                                    <td>{{ row.label_2 }}</td>
                                    <td>{{ row.label_3 }}</td>
                                    <td>{{ row.label_4 }}</td>
                                    <td>{{ row.label_5 }}</td>
                                </tr>
                                
                            </data-table>
                        </div>
                                
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
            //resource: 'items/report/records',
            resource: 'items/report',
            form: {},
            item_id: null,
            records: []
        };
    },
    created() {
        this.loadConfiguration()
        this.getData();
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
        },
        getData() {
            this.$http.get(`/items/report/records`).then(response => {
                console.log("==>", response.data)
                this.records = response.data;
            });
        }
    },
};
</script>
