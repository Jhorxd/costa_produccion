<template>
  <div>
    <div class="page-header pr-0">
        <h2><a href="/inventory/warehouses">
            <svg  xmlns="http://www.w3.org/2000/svg" style="margin-top: -5px;"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-category-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 4h6v6h-6z" /><path d="M4 14h6v6h-6z" /><path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M7 7m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /></svg>
        </a></h2>
        <ol class="breadcrumbs">
            <li class="active"><span>{{ title }}</span></li>
        </ol>
        
        
    </div>      
    <div class="container">
      <div class="row">
        <div :class="checked ? 'col-md-6' : 'col-md-4'">
          <el-checkbox v-model="checked" :disabled="isDisabled" @change="handleChangeChecked">Inventario selectivo</el-checkbox>
        </div>
        <div class="col-md-4" v-if="!checked">
          <label class="control-label">Categoria Global</label>
          <el-select @change="handleGlobalCategory"  :disabled="isDisabled"  v-model="selectedCategory" placeholder="Seleccione una categoría">
            <el-option v-for="category in categories"  :key="category.id" :label="category.name" :value="category.id"></el-option>
          </el-select>
        </div>
        <div :class="checked ? 'col-md-6' : 'col-md-4'">
          <label class="control-label">Fecha</label>
          <el-date-picker    :disabled="isDisabled" v-model="form.date" type="date" format="yyyy-MM-dd" value-format="yyyy-MM-dd" placeholder="Selecciona una fecha"></el-date-picker>
        </div>
      </div>
      <div class="row">
        <!-- Sucursal -->
        <div class="col-md-6">
          <label class="control-label">Sucursal</label>
          <div class="input-group">
            <el-select
              :disabled="isDisabled" 
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
              :disabled="isDisabled" 
              @change="handleWarehouse"
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
          <input   :disabled="isDisabled" ad type="text" v-model="form.comment" class="form-control" placeholder="N/A">
        </div>
        
        <!-- Botón Agregar Producto -->
        <div class="col-md-6 d-flex align-items-end">
          <button type="button"  :disabled="isDisabled" class="btn btn-custom w-75"  @click.prevent="clickCreate()">Agregar Producto</button>
        </div>
      </div>
    </div>
    <form-add-product  @add-item="addItem" :warehouse_id="form.warehouse_id" :establishment_id="form.establishment_id" :showDialog.sync="showDialog" :checked.sync="checked" ></form-add-product>  
    <br>
    <div class="col-md-12">
      <div class="table-responsive table-responsive-new">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Descripción</th>
              <th>Stock Sistema</th>
              <th>Stock Real</th>
              <th>Costo</th>
              <th>Importe Costo</th>
              <th>Categoria</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in form.details" :key="index">
              <td>{{ item.item_id}}</td>
              <td>{{ item.description }}</td>
              <td>{{ item.system_quantity}}</td>
              <td>{{ item.counted_quantity}}</td>
              <td>{{ item.sale_unit_price}}</td>
              <td>{{ item.counted_quantity * item.sale_unit_price }}</td>
              <td>{{ getCategoryName(item.category_id) }}</td>
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
          <input type="number" readonly  v-model="totalCantidad1" id="cantidad1" class="form-control w-75 mb-2" />
          
          <label for="cantidad2" class="form-label">Cantidad-2</label>
          <input type="number" readonly v-model="totalCantidad2" id="cantidad2" class="form-control w-75 mb-2" />
          
          <label for="importe" class="form-label">Importe</label>
          <input type="number" readonly  v-model="importeTotal" id="importe" class="form-control w-75" />
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
      props: ['inventory'],
      mixins: [],
      components: {formAddProduct},
      data() {
          return {
              isDisabled: false, 
              title: 'Nuevo Inventario Físico',
              showDialog: false, 
              resource: 'warehouses',
              recordId: null,
              location_id: null,
              locations: [],
              positions: [],
              positions_selected: [],
              form: {
                    establishment_id: null,
                    warehouse_id: null,
                    comment: null ,
                    adjustment_type_id: 1,
                    date: null ,
                    series:"Fo-",
                    number:1,
                    confirmed:null,
                    json_positions:{},
                    json_lots:{},
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
          this.getAllPhysicalInventoryCategories();
          this.getEstablishments();
          if(this.inventory){
            this.isDisabled=true;
            this.getWarehousesByEstablishment(this.inventory.establishment_id);
            this.form.date=this.inventory.date;
            this.form.establishment_id=this.inventory.establishment_id;
            this.form.warehouse_id=this.inventory.warehouse_id;
            this.form.comment=this.inventory.comment;
            this.form.details=this.inventory.details;
            this.form.details.description=this.inventory.details.item_description;
            this.form.confirmed=this.inventory.confirmed;
            if(this.inventory.adjustment_type_id==1){
              this.handleChangeChecked(true);
            }else{
              this.handleChangeChecked(false);
              this.selectedCategory=this.form.details[0].category_id;
              this.checked=false;
            }                    
            this.calculateTotals();           
          }         
      },
      methods: { 
          clickCreate() {
            if(this.form.establishment_id==null){
              this.$message.error('Debe seleccionar una sucursal');
              return;
            }  
            if(this.form.warehouse_id==null){
              this.$message.error('Debe seleccionar un almacén');
              return;
            }          
            this.showDialog = true          
          }, 
          clickDelete(id) {                
                /*this.destroy(`/warehouses/destroy/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                ) */            
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
          handleEstablishmentChange(value) {
            if(this.form.details.length>0){
              this.cleanForm(true);
            }                       
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
            const index = this.form.details.findIndex(item => item.item_id === newItem.item_id);
            if (index !== -1) {                
                this.form.details.splice(index, 1, newItem);
            } else {                
                this.form.details.push(newItem);
            }
            this.calculateTotals();
          },
          getAllPhysicalInventoryCategories(){
            let url = '/physical-inventory/getAllPhysicalInventoryCategories';           
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
            //alert(JSON.stringify(this.positions_selected));
            //this.form.json_positions.location_id=this.location_id;
            //this.form.json_positions.positions_selected=this.positions_selected;
            //alert(JSON.stringify(this.form.json_positions));
            //deareturn ;
            if(this.checked==false){
               if(this.selectedCategory==null){
                this.$message.error('Seleccione una categoria global');
                return;
               }
            }
            if (this.form.details.length <= 0) {
               this.$message.error('Debe tener productos seleccionados');
               return;
            }
            //selectedCategory
            let url = '/physical-inventory/store';           
            return this.$http
            .post(url,this.form)
            .then(response => { 
              this.$message.success(response.data.message);
              /* setTimeout(() => {
                window.location.href = "/physical-inventory";
              }, 1000);  */
            })
            .catch(error => {
                
            })
            .then(() => {
              this.cleanForm();               
            });      
          },
          cleanForm(cleanALL=false){
          if (!cleanALL) {
              this.form.establishment_id = null;
              this.form.warehouse_id = null;
              this.form.comment = null;
              this.form.adjustment_type_id = 1;
              this.form.date = null;                
            } else {
                // Solo limpiar `details` sin afectar otras propiedades de `this.form`
                this.form.details = [];
            }
            this.totalCantidad1 = 0;
            this.totalCantidad2 = 0;
            this.importeTotal = 0;
            
          },
          handleGlobalCategory(value){  
            // Actualizar category_id de todos los elementos en details
            this.form.details.forEach(item => {
                item.category_id = value;
            });            
          },
          getCategoryName(category_id) {
            const category = this.categories.find(cat => cat.id === category_id);
            return category ? category.name : " "; // Retorna el nombre o "Desconocido" si no encuentra
          },
          handleWarehouse(){
            this.cleanForm(true);
          },
          calculateTotals() {
          // Reiniciar los valores
          this.totalCantidad1 = 0;
          this.totalCantidad2 = 0;
          this.importeTotal = 0;

          // Recorrer los detalles y calcular los totales
          this.form.details.forEach((item) => {
              this.totalCantidad1 += Number(item.system_quantity);
              this.totalCantidad2 += Number(item.counted_quantity);

              const individualAmount = (Number(item.counted_quantity) - Number(item.system_quantity)) * item.sale_unit_price;
              this.importeTotal += individualAmount;r
          });
        }
      }
  }
</script>
