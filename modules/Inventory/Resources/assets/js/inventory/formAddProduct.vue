<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create" width="700px">
      <form autocomplete="off" @submit.prevent="submit">
        <div class="form-body">
          <p v-if="checked">El checkbox está activado ✅</p>
          <el-select  v-if="checked" v-model="form.category_id" placeholder="Seleccione una categoría">
            <el-option
              v-for="category in categories"
              :key="category.id"
              :label="category.name"
              :value="category.id"
            ></el-option>
          </el-select>
          <!-- Primera fila: Producto -->
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label class="control-label">Producto</label>
                <el-select v-model="form.item_id" filterable placeholder="Selecciona un producto"  @change="handleProductChange">
                  <el-option
                    v-for="product in products"
                    :key="product.item_id"
                    :label="`${product.description}${product.code ? ' | ' + product.code : ''}${product.category ? ' | ' + product.category : ''}`"
                    :value="product.item_id"
                  ></el-option>
                </el-select>
              </div>
            </div>
            <div class="col-md-4 text-center">
              <div class="image-placeholder">
                <img :src="selectedProductImage" alt="Producto" class="product-image" />
              </div>
            </div>
          </div>
  
          <!-- Segunda fila: Cantidad y Precio Unitario -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="control-label">Cantidad</label>
                <el-input v-model="form.counted_quantity" type="number" :disabled="product_selected_use_lots || product_selected_use_positions"></el-input>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="control-label">Precio Unitario</label>
                <el-input v-model="form.sale_unit_price" type="number"></el-input>
              </div>
            </div>
          </div>
  
          <!-- Tercera fila: Precio Total -->
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label class="control-label">Precio Total</label>
                <el-input v-model="form.precioTotal" type="number" disabled></el-input>
              </div>
            </div>
          </div>

          <div class="row mt-3" v-if="product_selected_use_lots">
            <div class="col-md-12">
              <el-button class="second-buton" @click.prevent="clickItemLots()">Elegir lotes</el-button>
            </div>
          </div>

          <div class="row mt-3" v-if="product_selected_use_positions && !product_selected_use_lots">
            <div class="col-md-12">
              <div class="form-group">
                <label class="control-label">Ubicación<span class="text-danger">*</span></label>
                <el-select
                    v-model="location_id"
                    filterable>
                    <el-option
                        v-for="option in locations"
                        :key="option.id"
                        :label="option.name"
                        :value="option.id"
                    ></el-option>
                </el-select>
              </div>
              <el-button class="second-buton" @click.prevent="clickItemLocation()">Elegir posición</el-button>
            </div>
          </div>
          <!-- Botón Agregar -->
          <div class="row mt-3">
             <div class="col-md-12 text-center">
                <el-button @click.prevent="sendItem()" type="primary" class="wide-button" native-type="submit" style="width: 100%; max-width: 300px;">
                 Agregar
               </el-button>
             </div>
          </div>
          
          <item-location
                  :showDialog.sync="showDialogLocation"
                  :location_id="location_id"
                  :positions_selected="positions_selected"
                  :positions="positions"
                  @positions-save="saveDataPosition">
          </item-location> 

          <physical-inventory-lots
                  :showDialog.sync="showDialogLots"
                  :lots="productSelectedLots"
                  @save-data-lots="saveDataLots">
          </physical-inventory-lots>

        </div>
      </form>
    </el-dialog>
  </template>
  
  <style scoped>
  .product-image {
    width: 100px;
    height: 100px;
    border: 1px solid #ccc;
    border-radius: 8px;
  }
  .image-placeholder {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
  }
  </style>
  
  
<script>
  import { method } from 'lodash';
  import ItemLocation from './../../../../../../resources/js/views/tenant/items/locations2.vue'
  import physicalInventoryLots from './physicalInventoryLots.vue';

  export default {
    props: ['showDialog', 'recordId','checked','establishment_id','warehouse_id'],
    components: {ItemLocation, physicalInventoryLots},
    data() {
      return {
        titleDialog: "Agregar Producto",
        showDialogLocation: false,
        location_id: null,
        locations: [],
        positions: [],
        positions_selected: [],
        temp_positions:[],
        form: {
            item_id: null,
            stock: 0,
            sale_unit_price: 0,
            precioTotal: 0,
            description: null,
            system_quantity: 0,
            counted_quantity: 0,
            difference: 0,
            stock_total: 0,
            stock_max: null,
            category_id: null,
            json_position:null,
            json_lots:null
        },
        products: [],
        categories: [],
        selectedCategory: null,
        product_selected_use_lots: false,
        product_selected_use_positions: false,
        showDialogLots: false,
        productSelectedLots: [],
        total_stock_initial: 0,
      };
    },
    created() {                                   
        this.getAllPhysicalInventoryCategories();
    },
    methods: {
      async getItemLots(){
        const response = await this.$http.get(`/physical-inventory/getItemPositionsLots/${this.form.item_id}/${this.warehouse_id}`);
        console.log(response);
        if(response.data.success){
          this.productSelectedLots = response.data.data;
        }
      },
      async clickItemLots(){
        
        this.showDialogLots = true;
        this.total_stock_initial = 0;
        this.productSelectedLots.forEach(lot =>{
          this.total_stock_initial += lot.stock;
        });
      },
      saveDataLots(data){
        let total_lot_final = 0;
        data.forEach(element => {
          total_lot_final += element.stock;
        });
        this.productSelectedLots = data;
        this.form.json_lots = data;
        const difference = total_lot_final - this.total_stock_initial;
        this.form.counted_quantity = this.form.counted_quantity + difference;
        console.log(data);
      },
      close() {
        this.cleanForm();
        this.$emit('update:showDialog', false);        
      },
      create() {             
        this.getProductsByEstablishmentAndWarehouse();        
      },
      submit() {        
      },
      getProductsByEstablishmentAndWarehouse(){
            let url = '/physical-inventory/getProductsByEstablishmentAndWarehouse';
            url+=`?establishment_id=${this.establishment_id}&warehouse_id=${this.warehouse_id}`;              
            return this.$http
            .get(url)
            .then(response => {              
              this.products = response.data.data; 
              console.log(this.products);
                // Procesar la respuesta aquí
            })
            .catch(error => {
                // Manejar el error aquí
            })
            .then(() => {
                this.loading_submit = false;
            }); 
        },
        async handleProductChange(value){
          let product = this.products.find(product => product.item_id === value);
          this.product_selected_use_lots = product.has_use_lots;
          if(this.product_selected_use_lots) {
            await this.getItemLots();
          }
          this.product_selected_use_positions = product.has_use_positions;
          this.form.sale_unit_price = product.sale_unit_price;
          //this.form.stock = product.stock;
          this.form.precioTotal = product.sale_unit_price * product.stock;
          this.form.description = product.description;
          this.form.item_id = product.item_id;
          this.form.system_quantity = product.stock;
          this.form.counted_quantity = product.stock;
          this.form.difference = this.form.system_quantity-this.form.counted_quantity;
          this.form.stock_total = product.stock_total;
          this.form.stock_max = product.stock_max;
          this.getLocations();
          if(this.locations.length>0){
            this.location_id=null;
          }
        },
        cleanForm(){
          this.form={
            item_id: null,
            stock: 0,
            sale_unit_price: 0,
            precioTotal: 0,
            description: null,
            system_quantity: 0,
            counted_quantity: 0,
            difference: 0,
            stock_total: 0,
            stock_max: null,
            category_id: null
          }
          this.locations=[];
          this.positions_selected = [];
          this.location_id=null;
       },
       sendItem() {
            if(this.checked && this.form.category_id==null){
              this.$message.error('Debe seleccionar una categoría');
              return;              
            }
            
            if(this.form.stock_max!=null && parseInt(this.form.counted_quantity)>parseInt(this.form.system_quantity)){
              const difference= parseInt(this.form.counted_quantity) - parseInt(this.form.system_quantity);
              const newStockTotal = difference + parseInt(this.form.stock_total);
              
              if( newStockTotal > parseInt(this.form.stock_max)){
                const stock_available = parseInt(this.form.stock_max) - parseInt(this.form.stock_total) + parseInt(this.form.system_quantity);
                this.$message.error('El stock actual supera el stock máximo permitido: ' + stock_available);
                return; 
              }
            }
            
            if(this.form.item_id){                            
                this.$emit('add-item', this.form);
                this.cleanForm();
                this.$emit('update:showDialog', false);
            }
            else {
                this.$message.error('Debe seleccionar un producto');
            }
        },
        getAllPhysicalInventoryCategories(){
            let url = '/physical-inventory/getAllPhysicalInventoryCategories';           
            return this.$http
            .get(url)
            .then(response => {              
              this.categories = response.data; 
              console.log(this.categories);         
                // Procesar la respuesta aquí
            })
            .catch(error => {
                // Manejar el error aquí
            })
            .then(() => {
                this.loading_submit = false;
            }); 
          }
        ,
        getLocations(){
          //url+=`?establishment_id=${this.establishment_id}&warehouse_id=${this.warehouse_id}`;              
          return this.$http
            .get(`/items/getLocations?warehouse_id=${this.warehouse_id}&item_id=${this.form.item_id}`)
            .then(response => {              
              this.locations =response.data;                     
                // Procesar la respuesta aquí
            })
            .catch(error => {
                // Manejar el error aquí
            })
            .then(() => {
                this.loading_submit = false;
            }); 
        },
        clickItemLocation() {
          if (!this.location_id) {
                this.$message.error("Seleccione una ubicación");
                return;
            }
            this.positions_selected = [];
            this.positions = [];
            this.$http.get(`/items/positions/${this.location_id}/${this.form.item_id}`)
                .then(response => {
                    if (response.data.success) {
                        const data = response.data.data;
                        this.positions_selected = data.item_positions;
                        this.temp_positions = JSON.parse(JSON.stringify(this.positions_selected));                     
                        this.positions = data.positions;
                        console.log( JSON.stringify(this.positions_selected));
                        console.log( JSON.stringify(this.positions));
                        this.positions_selected.forEach(element => {
                            const position_finded = this.positions.find(position => {return position.row == element.row && position.column == element.column});
                            if(position_finded){
                                position_finded.stock = parseInt(element.stock);
                            }else{
                                position_finded.stock = 0;
                            }
                        });
                        //if(this.form.location_id!=this.location_id){
                          ///  this.positions_selected = [];
                        //}
                        this.showDialogLocation = true;
                    }
                });
        },
        /*saveDataPosition(data) {
            if(data.length>0){
                this.position_selected = [];
                this.positions_selected = data;
            }
            console.log(this.positions_selected);
        },*/
        saveDataPosition(data) {
          
          //alert(JSON.stringify(this.positions_selected));
          let totalStockAssigned = 0;
              this.temp_positions.forEach(position => {
                  totalStockAssigned += position.stock; // Sumamos los valores de stock de cada posición seleccionada
          });
          //total de stock del data
          const totalStock = data.reduce((total, item) => total + item.stock, 0);
          this.form.json_position = {
              location_id:this.location_id,  // Aquí agregas location_id dentro del JSON
              positions: data // Aquí mantienes el arreglo
          };
          //this.form.json_position=data;
          //this.form.location_id=this.location_id;
          
          console.log(JSON.stringify(this.temp_positions));
          console.log(totalStock);
          console.log(totalStockAssigned);
          console.log(JSON.stringify(data));
          //alert(totalStock>totalStockAssigned);
          if(data.length==0){
            //alert(totalStockAssigned);
            this.form.counted_quantity -= totalStockAssigned;            
          }else if(totalStock>totalStockAssigned){
              const result=totalStock-totalStockAssigned;
              this.form.counted_quantity+=result;
          }else{
              const result2=totalStockAssigned-totalStock;
              this.form.counted_quantity-=result2;
          }
          // Si hay posiciones seleccionadas
          /*if (data.length > 0) {
              this.positions_selected = data; // Actualizas las posiciones seleccionadas
              
              // Calculamos el stock total basado en las posiciones seleccionadas
              let totalStockAssigned = 0;
              this.positions_selected.forEach(position => {
                  totalStockAssigned += position.stock; // Sumamos los valores de stock de cada posición seleccionada
              });

              // En lugar de restar, asignamos el stock basado en lo seleccionado
              this.form.counted_quantity = totalStockAssigned;

              console.log("Stock actualizado después de seleccionar posiciones: ", this.form.counted_quantity);
          } else {
              // Si no se seleccionaron posiciones, el stock sigue igual
              console.log("No positions selected. Stock remains: ", this.form.counted_quantity);
          }*/
      }              
    }
  }
</script>
  