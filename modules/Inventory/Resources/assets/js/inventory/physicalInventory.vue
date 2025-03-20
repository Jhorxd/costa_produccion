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
                <el-select v-model="seleccionado" placeholder="Seleccione una opción">
                  <el-option label="Captura de inventario físico" value="inventario"></el-option>
                  <el-option label="Reportes" value="reportes"></el-option>
                </el-select>
                <!--  <a href="/dashboard" class="btn btn-custom btn-sm mt-2 mr-2">
                    <i class="fa fa-plus-circle"></i>Agregar nuevo almacen</a>-->
          </div> 
          <el-checkbox v-model="checked"  @change="handleChangeChecked">Inventario selectivo</el-checkbox>        
          <el-select v-if="!checked" v-model="selectedCategory" placeholder="Seleccione una categoría">
            <el-option
              v-for="category in categories"
              :key="category.id"
              :label="category.name"
              :value="category.id"
            ></el-option>
          </el-select>
          <el-date-picker
            v-model="form.date"
            type="date"
            format="yyyy-MM-dd"
            value-format="yyyy-MM-dd"
            placeholder="Selecciona una fecha">            
          </el-date-picker>
      </div>      
      <div class="container">
  <div class="row">
    <!-- Sucursal -->
    <div class="col-md-6">
      <label class="control-label">Sucursal</label>
      <div class="input-group">
        <el-select
          v-model="form.establishment_id"
          filterable
          placeholder="Selecciona un establecimiento"
          @change="handleEstablishmentChange"
          :filter-method="handleFilter"
          class="form-select"
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

    <!-- Almacén -->
    <div class="col-md-6">
      <label class="control-label">Almacén</label>
      <div class="input-group">
        <el-select
          v-model="form.warehouse_id"
          filterable
          placeholder="Selecciona un almacén"
          class="form-select"
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
  </div>
  <div class="row mt-2">
    <!-- Comentario -->
    <div class="col-md-6">
      <label class="control-label">Comentario</label>
      <input type="text" v-model="form.comment" class="form-control" placeholder="N/A">
    </div>
    
    <!-- Botón Agregar Producto -->
    <div class="col-md-6 d-flex align-items-end">
      <button type="button" class="btn btn-custom w-75" @click.prevent="clickCreate()">Agregar Producto</button>
    </div>
  </div>
</div>
<form-add-product  @add-item="addItem" :showDialog.sync="showDialog" :checked.sync="checked" ></form-add-product>
<br>
<div class="col-md-12">
  <div class="table-responsive table-responsive-new">
    <table class="table">
      <thead>
        <tr>
          <th>Clave</th>
          <th>Descripción</th>
          <th>Cantidad-1</th>
          <th>Cantidad-2</th>
          <th>Costo</th>
          <th>Importe Costo</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in form.details" :key="index">
          <td>{{ item.item_id}}</td>
          <td>{{ item.description }}</td>
          <td>{{ item.system_quantity}}</td>
          <td>{{ item.counted_quantity}}</td>
          <td>{{ item.sale_unit_price}}</td>
          <td>{{ item.system_quantity * item.sale_unit_price }}</td>
          <!-- <td>
            <button type="button" class="btn btn-danger btn-sm" @click.prevent="removeItem(index)">
              <i class="fa fa-trash"></i>
            </button>
          </td>-->
        </tr>        
      </tbody>
    </table>
    <div>
      <slot name="jacksito"></slot>
      <p>..</p>
    </div>
  </div>
</div> 
<div class="container">
  <div class="row align-items-center">
    <div class="col-auto">
      <label for="cantidad1" class="form-label">Cantidad-1</label>
      <input type="number" v-model="totalCantidad1" id="cantidad1" class="form-control w-75 mb-2" />
      
      <label for="cantidad2" class="form-label">Cantidad-2</label>
      <input type="number" v-model="totalCantidad2" id="cantidad2" class="form-control w-75 mb-2" />
      
      <label for="importe" class="form-label">Importe</label>
      <input type="number" v-model="importeTotal" id="importe" class="form-control w-75" />
    </div>

    <!-- Cambiar col-auto por col -->
    <div class="col d-flex align-items-center justify-content-center ">
      <button type="button" @click.prevent="sendForm()" class="btn btn-primary w-75">Confirmar</button>
    </div>
  </div>
</div>

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
   import formAddProduct from './formAddProduct.vue'

  export default {
      mixins: [],
      components: {formAddProduct},
      data() {
          return {
              title: null,
              showDialog: false, 
              resource: 'warehouses',
              recordId: null,
              form: {
                    establishment_id: null,
                    warehouse_id: null,
                    comment: null ,
                    adjustment_type_id: 1,
                    date: null ,
                    details: []                               
              },
              establishments: [],
              warehouses: [],
              items: [] ,
              totalCantidad1: 0,
              totalCantidad2: 0,
              importeTotal: 0,
              checked: true,
              seleccionado: "inventario",
              categories: [],
              selectedCategory: null    
          }
      },
      created() {                    
          this.title = 'Inventario Fisicos'
          this.getAllPhysicalInventoryCategories()
          this.getEstablishments()
      },
      methods: { 
          clickCreate() {            
            this.showDialog = true          
          }, 
          clickDelete(id) {                
                /*this.destroy(`/warehouses/destroy/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                ) */            
          },         
          getEstablishments(Establishment=null){            
            let url = '/physicalInventory/getEstablishments';
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
          getWarehousesByEstablishment(id=null){
            let url = `/physicalInventory/getWarehousesByEstablishment/${id}`;            
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
          handleEstablishmentChange(value) {                        
            this.getWarehousesByEstablishment(value);          
          },
          handleFilter(value) {

            //const selectedEstablishment = this.establishments.find(est => est.id === value);
            this.getEstablishments(value);             
             if(this.establishments.length == 0){
                 this.form.establishment_id = null;
                 this.form.warehouse_id = null;
                 this.establishments = [];
                 this.warehouses = [];
                 this.getEstablishments();                 
             }
          },
          addItem(newItem) {
            this.totalCantidad1+= Number(newItem.system_quantity);
            this.totalCantidad2 += Number(newItem.counted_quantity);
            this.importeTotal += (this.totalCantidad2 - this.totalCantidad1) * newItem.sale_unit_price;
            this.form.details.push(newItem);          
            //this.items.push(newItem);
          },
          getAllPhysicalInventoryCategories(){
            let url = '/physicalInventory/getAllPhysicalInventoryCategories';           
            return this.$http
            .get(url)
            .then(response => {              
              this.categories = response.data;                     
                // Procesar la respuesta aquí
            })
            .catch(error => {
                // Manejar el error aquí
            })
            .then(() => {
                this.loading_submit = false;
            }); 
          },
          handleChangeChecked(value){
            if(value==true){
              this.form.adjustment_type_id = 1;
            }else{
              this.form.adjustment_type_id = 2;
            }                               
          },
          sendForm(){
            let url = '/physicalInventory/store';           
            return this.$http
            .post(url,this.form)
            .then(response => { 
              this.$message.success(response.data.message);
              setTimeout(() => {
                window.location.href = "/physicalInventory";
              }, 1000); // 1000 milisegundos = 1 segundo     
            })
            .catch(error => {
                // Manejar el error aquí
            })
            .then(() => {
              this.cleanForm();
                //this.loading_submit = false;
            });
                      
          },
          cleanForm(){
            this.form = {
                    establishment_id: null,
                    warehouse_id: null,
                    comment: null ,
                    adjustment_type_id: 1,
                    date: null ,
                    details: []                            
              }
            this.totalCantidad1 = 0;
            this.totalCantidad2 = 0;
            this.importeTotal = 0;
          }
      }
  }
</script>
