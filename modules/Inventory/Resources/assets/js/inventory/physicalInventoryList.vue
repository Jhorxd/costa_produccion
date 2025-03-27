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
            <li class="active"><span>Lista de Inventarios Físicos</span></li>
        </ol>
        <div class="right-wrapper pull-right">
          <a class="btn btn-custom btn-sm mt-2 mr-2"
              href="#"
              @click.prevent="clickCreate()">
              <i class="fa fa-plus-circle"></i> Nuevo
          </a>
        </div>
      </div>
      <div class="container">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="control-label">Serie FO</label>
            <el-input v-model="form.number" type="number" clearable></el-input>
          </div>
          <div class="col-md-6">
            <label class="control-label">Fecha</label>
            <el-date-picker type="date" v-model="form.date" format="yyyy-MM-dd" value-format="yyyy-MM-dd" placeholder="Selecciona una fecha" clearable></el-date-picker>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-4">
            <label class="control-label">Sucursal</label>
              <div class="input-group">
                <el-select
                  v-model="form.establishment_id"
                  filterable
                  placeholder="Selecciona un establecimiento"
                  @change="handleEstablishmentChange"
                  class="form-select"
                  clearable
                >
                  <el-option
                    v-for="option in establishments"
                    :key="option.id"
                    :label="option.description"
                    :value="option.id"
                  ></el-option>
                </el-select>
          </div>
          </div>
          <div class="col-md-4">
            <label class="control-label">Almacén</label>
            <div class="input-group">
              <el-select
                v-model="form.warehouse_id"
                filterable
                placeholder="Selecciona un almacén"
                class="form-select"
                clearable
              >
                <el-option
                  v-for="option in warehouses"
                  :key="option.id"
                  :label="option.description"
                  :value="option.id"
                ></el-option>
              </el-select>
            </div>
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <a class="btn btn-custom btn-sm mr-2"
             href="#"
             @click.prevent="findPhysicalInventory()">
             <i class="fa fa-plus-circle"></i> Buscar
             </a>
          </div>
        </div>
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
          <th>Estado</th>
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
          <td>
            <span 
              :class="{
                'badge bg-warning text-dark': item.confirmed === 0 || item.confirmed === null,
                'badge bg-success text-light': item.confirmed === 1
              }"
            >
              {{ item.confirmed === 0 || item.confirmed === null ? 'Por confirmar' : 'Confirmado' }}
            </span>
          </td>      
          <td>{{ item.adjustment_type_name}}</td>
          <td>
            <button
            class="btn waves-effect waves-light btn-xs btn-info"
            type="button"
            @click.prevent="clickDownload(item.id)">
            <i class="fa fa-file-pdf"></i>
            PDF
            </button>
            <div class="dropdown">
              <button id="dropdownMenuButton"
                                        aria-expanded="false"
                                        aria-haspopup="true"
                                        class="btn btn-default btn-sm"
                                        data-toggle="dropdown"
                                        type="button">
                                    <i class="fas fa-ellipsis-v"></i>
               </button>
               <div aria-labelledby="dropdownMenuButton"   class="dropdown-menu">
                <button
                                            class="dropdown-item"
                                            @click.prevent="clickCreate(item.id)"
                                        >
                                            Confirmar
                </button>
               </div>
            </div>
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
        form:{
          establishment_id: null,
          warehouse_id: null,
          date: null,
          number:null
        },
        establishments: [],
        warehouses: [],
        loading_submit: false
      };
    },
    created(){
      this.getRecords();
      this.getEstablishments()
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
      if(recordId){
        location.href = `physical-inventory/insertInventory/${recordId}`;
      }else{
        location.href = "physical-inventory/insertInventory"
      }               
    },
    getRecords(){
      this.loading_submit = true;            
      this.$http.get(`/physical-inventory/getAllPhysicalInventories?${this.getQueryParameters()}`)
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
          ...this.search,
          ...this.form
      });
      },
      clickDownload(id) {
            window.open(`/physical-inventory/pdf/${id}`, "_blank");
      },
      getEstablishments(Establishment=null){            
            let url = '/physical-inventory/getEstablishments';
            if (Establishment !== null) {
               url += `?value=${Establishment}`;
            }
            return this.$http
            .get(url)
            .then(response => {               
              this.establishments = response.data.data;             
                // Procesar la respuesta aquí
            })
            .catch(error => {
                // Manejar el error aquí
            })
            .then(() => {
                this.loading_submit = false;
            });            
      },
      handleEstablishmentChange(value) {                        
            this.getWarehousesByEstablishment(value);          
      },
      getWarehousesByEstablishment(id=null){
            let url = `/physical-inventory/getWarehousesByEstablishment/${id}`;            
            return this.$http
            .get(url)
            .then(response => { 
              this.warehouses = response.data.data;              
                // Procesar la respuesta aquí
            })
            .catch(error => {
                // Manejar el error aquí
            })
            .then(() => {
                this.loading_submit = false;
            });  
      },
      findPhysicalInventory(){
          this.getRecords();
      }
   }
 };
  </script>
  