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
                <!--  <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i> Nuevo</button>-->
                  <a href="/dashboard" class="btn btn-custom btn-sm mt-2 mr-2">
                    <i class="fa fa-plus-circle"></i>Agregar nuevo almacen</a>
          </div>
      </div>
      <div class="card tab-content-default row-new mb-0">
          <!-- <div class="card-header bg-info">
              <h3 class="my-0">Listado de {{ title }}</h3>
          </div> -->
          <div class="card-body">
            <data-table :resource="resource">
                <p slot="jacksito">.</p>
                <tr slot="heading">
                        <!-- <th>#</th> -->
                        <th>Nombre</th>
                        <th>Sucursal</th>
                        <th>Ubicación </th>
                        <th>Dimensiones</th>                                               
                        <th>Reponsable</th>
                        <th>Acciones</th>                                               
                </tr>
                <tr slot-scope="{ index, row }">
                        <td>{{ row.warehouse_description }}</td>
                        <td>{{ row.establishment_description }}</td>
                        <td>{{ row.address }}</td>
                        <td>
                            <p>Longitud {{ row.dimensions.length}}</p>
                            <p>Ancho {{ row.dimensions.width}}</p>
                            <p>Altura {{ row.dimensions.height}}</p>                        
                        </td>
                        <td>{{ row.responsible}}</td>
                        <td class="text-right">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" >Editar</button>
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-danger">Eliminar da</button>
                        </td>
                        <!-- <td class="text-right">
                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickCreate(row.id)">Editar</button>
                        </td>-->
                </tr>         
            </data-table>
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
  
  import DataTable from './DataTable.vue'
  //import CategoryForm from './form.vue' 
  //import DataTable from '../../../../../../../resources/js/components/DataTable.vue'
  //import {deletable} from '../../../../../../../resources/js/mixins/deletable'

  export default {
      //mixins: [deletable],
      components: {DataTable},
      data() {
          return {
              title: null,
              showDialog: false, 
              resource: 'categories',
              recordId: null,
          }
      },
      created() {
          this.$message.error("como vamos");          
          this.title = 'Listado Almacen'

          this.$http.get('/warehouses/recordsCustom')
    .then(response => {
        console.log(response.data); // Muestra los datos en la consola

        console.log(response.data.data);
    })
    .catch(error => {
        console.error("Error al obtener los datos:", error);
    });

          
      },
      methods: { 
          clickCreate(recordId = null) {
              this.recordId = recordId
              this.showDialog = true
          }, 
          clickDelete(id) {
              this.destroy(`/${this.resource}/${id}`).then(() =>
                  this.$eventHub.$emit('reloadData')
              )
          }
      }
  }
</script>
