<template>
    <div>        
        <div class="right-wrapper pull-right">
                <a class="btn btn-custom btn-sm mt-2 mr-2"
                   href="#"
                   @click.prevent="clickCreate()">
                    <i class="fa fa-plus-circle"></i> Nuevo
                </a>
        </div>
        <br>
      
  <div v-loading="loading_submit" class="col-md-12">
  <div class="table-responsive table-responsive-new">
    <table class="table">
      <thead>
        <tr>
          <th>Clave</th>
          <th>Comentario</th>
          <th>Fecha</th>
          <th>Sucursal</th>
          <th>Almacen</th>
          <th>Tipo de ajuste</th>
          <th>Acciones</th>     
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in records" :key="index">
          <td>{{ item.id}}</td>
          <td>{{ item.comment}}</td>
          <td>{{ item.date}}</td>
          <td>{{ item.establishment_description}}</td>
          <td>{{ item.warehouse_description}}</td>
          <td>{{ item.adjustment_type_name}}</td>
          <td>
            <button
            class="btn waves-effect waves-light btn-xs btn-info"
            type="button"
            @click.prevent="clickDownload(item.id)">
            <i class="fa fa-file-pdf"></i>
            PDF
            </button>
          </td>            
        </tr>        
      </tbody>
      <el-pagination
        @current-change="getRecords"
        layout="total, prev, pager, next"
        :total="pagination.total"
        :current-page.sync="pagination.current_page"
        :page-size="pagination.per_page">
      </el-pagination>
    </table>
  </div>
  </div>

    </div>
  </template>
  
  <script>
  import queryString from "query-string";
  export default {    
    data() {
      return {
        records:[],
        pagination: {},
        search: {
                column: null,
                value: null,
                list_value: 'all',
        },
        loading_submit: false
      };
    },
    created(){
      this.getRecords();
      /*this.$http.get('/physicalInventory/getAllPhysicalInventories')
      .then(response => {
        this.records=response.data.data;                  
      })
      .catch(error => {
          console.error("Error al obtener los datos:", error);
      });*/
     },
    methods: {
    clickCreate(recordId = null) {
      location.href = "physicalInventory/insertInventory"      
    },
    getRecords(){
      this.loading_submit = true;            
      this.$http.get(`/physicalInventory/getAllPhysicalInventories?${this.getQueryParameters()}`)
      .then(response => {
        this.records = response.data.data;
        this.pagination = {
        current_page: response.data.current_page,
        from: response.data.from,
        lastPage: response.data.last_page,
        path: response.data.path,
        perPage: response.data.per_page,
        to: response.data.to,
        total: response.data.total
        };
        console.log( this.pagination);
        //this.pagination = response.data.meta;
        //console.log(JSON.stringify(response.data.meta))
        this.pagination.per_page = parseInt(
            response.data.per_page
        );
        this.loading_submit = false;               
      })
      .catch(error => {
          console.error("Error al obtener los datos:", error);
      });
     },
     getQueryParameters() {            
      return queryString.stringify({
          page: this.pagination.current_page,
          limit: this.limit,          
          ...this.search
      });
      },
      clickDownload(id) {
            window.open(`/physicalInventory/pdf/${id}`, "_blank");
      }
   }
 };
  </script>
  