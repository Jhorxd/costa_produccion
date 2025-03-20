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
      
  <div class="col-md-12">
  <div class="table-responsive table-responsive-new">
    <table class="table">
      <thead>
        <tr>
          <th>Clave</th>
          <th>Fecha</th>
          <th>Sucursal</th>
          <th>Almacen</th>
          <th>Tipo de ajuste</th>        
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in records" :key="index">
          <td>{{ item.id}}</td>
          <td>{{ item.date}}</td>
          <td>{{ item.establishment_description}}</td>
          <td>{{ item.warehouse_description}}</td>
          <td>{{ item.adjustment_type_name}}</td>            
        </tr>        
      </tbody>
    </table>
  </div>
  </div>

    </div>
  </template>
  
  <script>
  export default {    
    data() {
      return {
        records:[]
      };
    },
    created(){
      this.$http.get('/physicalInventory/getAllPhysicalInventories')
      .then(response => {
        this.records=response.data;                  
      })
      .catch(error => {
          console.error("Error al obtener los datos:", error);
      });
     },
    methods: {
    clickCreate(recordId = null) {
      location.href = "physicalInventory/insertInventory"      
    },
    }
  };
  </script>
  