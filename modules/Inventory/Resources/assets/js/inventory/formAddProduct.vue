<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create" width="700px">
      <form autocomplete="off" @submit.prevent="submit">
        <div class="form-body">
          <p v-if="checked">El checkbox está activado ✅</p>
          <el-select  v-if="checked" v-model="selectedCategory" placeholder="Seleccione una categoría">
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
                    :key="product.id"
                    :label="`${product.description} | ${product.code} | ${product.category}`"
                    :value="product.id"
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
                <el-input v-model="form.system_quantity" type="number"></el-input>
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
          <!-- Botón Agregar -->
          <div class="row mt-3">
             <div class="col-md-12 text-center">
                <el-button @click.prevent="sendItem()" type="primary" class="wide-button" native-type="submit" style="width: 100%; max-width: 300px;">
                 Agregar
               </el-button>
             </div>
          </div>
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

  export default {
    props: ['showDialog', 'recordId','checked'],
    data() {
      return {
        titleDialog: "Agregar Producto",
        message: "Hola Mundo",
        form: {
            item_id: null,
            stock: 0,
            sale_unit_price: 0,
            precioTotal: 0,
            description: null,
            system_quantity: 0
        },
        products: [],
        categories: [],
        selectedCategory: null
      };
    },
    created() {               
        this.store();
        this.getAllPhysicalInventoryCategories();
        this.getProductsByEstablishmentAndWarehouse();
    },
    methods: {
      close() {
        this.cleanForm();
        this.$emit('update:showDialog', false);        
      },
      create() {
        console.log("Hola Mundo");
      },
      submit() {
        console.log("Hola Mundo");
      },
      getProductsByEstablishmentAndWarehouse(){
            let url = '/physicalInventory/getProductsByEstablishmentAndWarehouse';           
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
        handleProductChange(value){
        let product = this.products.find(product => product.id === value);
        this.form.sale_unit_price = product.sale_unit_price;
        this.form.stock = product.stock;
        this.form.precioTotal = product.sale_unit_price * product.stock;
        this.form.description = product.description;
        this.form.item_id = product.id;
        this.form.system_quantity = product.stock;
        alert("Producto seleccionado: "+ product.description);        
        },
        cleanForm(){
          this.form = {
            item_id: null,
            stock: 0,
            sale_unit_price: 0,
            precioTotal: 0,
            description: null,
            system_quantity: 0
          }
       },
       sendItem() {
            alert("emitiendo");
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
            let url = '/physicalInventory/getAllPhysicalInventoryCategories';           
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
          },
          store(){
            /*const data = {
              title: 'Ejemplo de POST',
              body: 'Este es un cuerpo de prueba',
              userId: 1
            };
            let url = '/physicalInventory/store';           
            return this.$http
            .post(url,data)
            .then(response => {                            
              console.log(response);         
                // Procesar la respuesta aquí
            })
            .catch(error => {
                // Manejar el error aquí
            })
            .then(() => {
                //this.loading_submit = false;
            }); */
          }
    }
  }
  </script>
  