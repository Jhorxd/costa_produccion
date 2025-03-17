<template>
  <div>
      <div class="page-header pr-0">
          <h2><a href="/inventory/warehouses">
              <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-category-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 4h6v6h-6z" /><path d="M4 14h6v6h-6z" /><path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M7 7m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /></svg>
          </a></h2>
          <ol class="breadcrumbs">
              <li class="active"><span>{{ title }}</span></li>
          </ol>
          <div class="right-wrapper pull-right">
                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i>Nuevo</button>
                <!--  <a href="/dashboard" class="btn btn-custom btn-sm mt-2 mr-2">
                    <i class="fa fa-plus-circle"></i>Agregar nuevo almacen</a>-->
          </div>
      </div>
      <label class="control-label">Sucursal</label>
          <el-select
      v-model="form.establishment_id"
      filterable
      placeholder="Selecciona un establecimiento"
      @change="handleEstablishmentChange"
      :filter-method="handleFilter"
    >
      <el-option
        v-for="option in establishments"
        :key="option.id"
        :label="option.description"
        :value="option.id"
      ></el-option>
    </el-select>
    <label class="control-label">Almacen</label>
    <el-select
      v-model="form.warehouse_id"
      filterable
      placeholder="Selecciona un almacen">
      <el-option
        v-for="option in warehouses"
        :key="option.id"
        :label="option.description"
        :value="option.id"></el-option>
    </el-select>
    
  </div>
</template>
<style>
@media only screen and (max-width: 485px){
  .filter-container{
    margin-top: 0px;
    & .btn-filter-content, .btn-container-mobile{
      display: flex;
      align-items: center;
      justify-content: start;
    }
  }
}
</style>
<script>
  
  //import DataTable from './DataTable.vue'
  //import FormWarehouse from './form2.vue'
  //import DataTable from '../../../../../../../resources/js/components/DataTable.vue'
  //import {deletable} from '../../../../../../resources/js/mixins/deletable'
  //import {deletable} from '../../../../../../../resources/js/mixins/deletable'


  export default {
      mixins: [],
      components: {},
      data() {
          return {
              title: null,
              showDialog: false, 
              resource: 'warehouses',
              recordId: null,
              form: {
                    establishment_id: null,
                    warehouse_id: null,
              },
              establishments: [],
              warehouses: []
          }
      },
      created() {                    
          this.title = 'Inventario Fisicos'
          this.getEstablishments()
      },
      methods: { 
          clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true          
          }, 
          clickDelete(id) {                
                /*this.destroy(`/warehouses/destroy/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                ) */            
          },         
          getEstablishments(Establishment=null){
            console.log("Establecimiento seleccionado:", Establishment);
            let url = '/physicalInventory/getEstablishments';
            if (Establishment !== null) {
               url += `?value=${Establishment}`;
            }
            return this.$http
            .get(url)
            .then(response => { 
              console.log(response.data.data);
              this.establishments = response.data.data;
              console.log(this.establishments);
              console.log(this.form.establishment_id);
                // Procesar la respuesta aquí
            })
            .catch(error => {
                // Manejar el error aquí
            })
            .then(() => {
                this.loading_submit = false;
            });            
          },
          getWarehousesByEstablishment(id=null){
            let url = `/physicalInventory/getWarehousesByEstablishment/${id}`;            
            return this.$http
            .get(url)
            .then(response => { 
              this.warehouses = response.data.data;
              console.log(this.warehouses);
              //console.log(this.form.establishment_id);
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
            //const selectedEstablishment = this.establishments.find(est => est.id === value);
            //console.log("Establecimiento seleccionado:", selectedEstablishment);
            //this.getEstablishments(selectedEstablishment.description);
            alert("el valor es"+ value);
            console.log("el valor es"+ value);
            this.getWarehousesByEstablishment(value);          
          },
          handleFilter(value) {

            //const selectedEstablishment = this.establishments.find(est => est.id === value);
            this.getEstablishments(value);
             console.log("Hola mundo, escribiste:", value);
             console.log("Hola mundo, escribiste2 :"+ this.form.establishment_id);
             console.log(this.establishments.length);
             if(this.establishments.length == 0){
                 this.form.establishment_id = null;
                 this.form.warehouse_id = null;
                 this.establishments = [];
                 this.warehouses = [];
                 this.getEstablishments();
                 console.log("estara vacio mirando"+ JSON.stringify(this.establishments));
             }
          }
      }
  }
</script>
