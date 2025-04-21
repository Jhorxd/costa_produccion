<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/devolutions">
                    <svg xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-building-warehouse">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 21v-13l9 -4l9 4v13" />
                        <path d="M13 13h4v8h-10v-6h6" />
                        <path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3" />
                    </svg>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Ubicaciones</span></li>
            </ol>
            <div class="right-wrapper pull-right">
                <a :href="`/${resource}/create`" class="btn btn-custom btn-sm mt-2 mr-2">
                    <i class="fa fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>
        <div class="card tab-content-default row-new mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Posiciones</th>
                        <th>Acción</th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ row.name }}</td>
                        <td>{{ row.code }}</td>
                        <td>{{ getLocationTypeName(row.type_id) }}</td>
                        <td>{{ getStatusName(row.status) }}</td>
                        <td>{{ row.rows * row.columns }}</td>
                        <td>
                            <div class="dropdown">
                                <button
                                    class="btn btn-default btn-sm"
                                    type="button"
                                    id="dropdownMenuButton"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div
                                    class="dropdown-menu"
                                    aria-labelledby="dropdownMenuButton"
                                >
                                    <a
                                        class="dropdown-item"
                                        :href="`/${resource}/edit/${row.id}`"
                                    >
                                        Editar
                                    </a>
                                    <button
                                        class="dropdown-item"
                                        @click.prevent="clickDelete(row.id)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </data-table>
            </div>
        </div>
    </div>
</template>

<script>
import DataTable from '../../components/DataTableLocation.vue';
import {deletable} from '@mixins/deletable';

export default {
    mixins: [deletable],
    components: { DataTable },
    data() {
        return {
            locationTypes: [],
            resource: 'locations',
            recordId: null,
            showDialogOptions: false,
            showDialogOptionsPdf: false,
        };
    },
    created() {
        this.getTypes();
    },
    methods: {
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit('reloadData')
            );
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
        getLocationTypeName(type_id) {
            const locationType = this.locationTypes.find(type => type.id === type_id);
            return locationType ? locationType.name : 'Desconocido';
        },
        getStatusName(status) {
            switch (status) {
                case 1:
                    return 'Venta';
                case 2:
                    return 'Picking';
                case 3:
                    return 'Mermado';
            }
        }
    },
};
</script>

<style scoped>
.anulate_color {
    color: red;
}
</style>

<style>
@media only screen and (max-width: 485px) {
    .filter-container {
        margin-top: 0px;
    }

    .btn-filter-content,
    .btn-container-mobile {
        display: flex;
        align-items: center;
        justify-content: start;
    }
}
</style>