<template>
    <div v-loading="loading">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <slot name="heading"></slot> <!-- Slot para los encabezados -->
                </thead>
                <tbody>
                    <slot
                        v-for="(row, index) in records"
                        :row="row"
                        :index="customIndex(index)"
                    ></slot> <!-- Slot para las filas -->
                </tbody>
            </table>
        </div>
  
        <!-- Paginación -->
        <div class="text-center mt-3">
            <el-pagination
                @current-change="getRecords"
                layout="total, prev, pager, next"
                :total="pagination.total"
                :current-page.sync="pagination.current_page"
                :page-size="pagination.per_page"
            ></el-pagination>
        </div>
    </div>
  </template>
  
  <script>
  import queryString from "query-string";
  
  export default {
    props: {
        idWarehouse: Number, // ID del almacén
        resource: String, // Recurso para la API (ej: 'locations')
    },
    data() {
        return {
            records: [], // Datos que se mostrarán en la tabla
            loading: false, // Estado de carga
            pagination: {
                total: 0, // Total de registros
                current_page: 1, // Página actual
                per_page: 10, // Registros por página
            },
        };
    },
    async mounted() {
        await this.getRecords();
    },
    methods: {
        async getRecords() {
            this.loading = true;
            
            try {
                const response = await this.$http.get(`/${this.resource}/locations/${this.idWarehouse}`
                );
                this.records = response.data.data;
                this.pagination = {
                    total: response.data.meta.total,
                    current_page: response.data.meta.current_page,
                    per_page: response.data.meta.per_page,
                };
            } catch (error) {
                console.error("Error fetching data:", error);
            } finally {
                this.loading = false;
            }
        },
  
        // Obtener los parámetros de la consulta (paginación y búsqueda)
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.pagination.per_page,
                id: this.id
            });
        },
  
        // Calcular el índice personalizado para las filas
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
    },
  };
  </script>
  
  <style scoped>
  .table-responsive {
    overflow-x: auto;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th, td {
    padding: 8px;
    border: 1px solid #ddd;
    text-align: left;
  }
  th {
    background-color: #f4f4f4;
  }
  </style>