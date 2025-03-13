<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-6">
        <div class="form-group" :class="{'has-danger': errors.nombre}">
          <label class="control-label">Nombre</label>
          <el-input v-model="form.description"></el-input>
          <small class="form-control-feedback" v-if="errors.nombre" v-text="errors.nombre[0]"></small>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group" :class="{'has-danger': errors.sucursal}">
          <label class="control-label">Sucursal</label>
          <el-select
      v-model="form.establishment_id"
      filterable
      placeholder="Selecciona un establecimiento"
      @change="handleEstablishmentChange"
    >
      <el-option
        v-for="option in establishments"
        :key="option.id"
        :label="option.description"
        :value="option.id"
      ></el-option>
    </el-select>
          <!-- <el-input v-model="form.sucursal"></el-input>-->
          <small class="form-control-feedback" v-if="errors.sucursal" v-text="errors.sucursal[0]"></small>
        </div>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-6">
        <div class="form-group" :class="{'has-danger': errors.direccion}">
          <label class="control-label">Dirección</label>
          <el-input v-model="form.address">
            <template #append>
              <el-button icon="el-icon-search"></el-button>
            </template>
          </el-input>
          <small class="form-control-feedback" v-if="errors.direccion" v-text="errors.direccion[0]"></small>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group" :class="{'has-danger': errors.responsable}">
          <label class="control-label">Responsable</label>
          <el-input v-model="form.responsible"></el-input>
          <small class="form-control-feedback" v-if="errors.responsable" v-text="errors.responsable[0]"></small>
        </div>
      </div>
    </div>
    
    <h3 class="text-center">Dimensiones</h3>
    
    <div class="row">
      <div class="col-md-4">
        <div class="form-group" :class="{'has-danger': errors.longitud}">
          <label class="control-label">Longitud (m)</label>
          <el-input v-model="form.length"></el-input>
          <small class="form-control-feedback" v-if="errors.longitud" v-text="errors.longitud[0]"></small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group" :class="{'has-danger': errors.ancho}">
          <label class="control-label">Ancho (m)</label>
          <el-input v-model="form.width"></el-input>
          <small class="form-control-feedback" v-if="errors.ancho" v-text="errors.ancho[0]"></small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group" :class="{'has-danger': errors.altura}">
          <label class="control-label">Altura (m)</label>
          <el-input v-model="form.height"></el-input>
          <small class="form-control-feedback" v-if="errors.altura" v-text="errors.altura[0]"></small>
        </div>
      </div>
                </div>
            </div>            
            <div class="form-actions text-right mt-4">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loading_submit">Aceptar</el-button>
            </div>
        </form>
    </el-dialog>

</template>

<script>

    export default {
        props: ['showDialog', 'recordId'],
        data() {
            return {
                loading_submit: false,
                titleDialog: null,
                resource: 'warehouses',
                errors: {},
                form: {
                    establishment_id: null
                },
                establishments: []
            }
        },
        created() {
            this.initForm()
        },
        methods: {
            initForm() {
                this.errors = {}
                this.form = {
                    id: null,
                    description: null
                }
            },
            create() {

                this.$http.get('/warehouses/getEstablishments')
                .then(response => {                    
                    this.establishments = response.data;
                })
                .catch(error => {
                    console.error("Error al obtener los datos:", error);
                });
                this.titleDialog = (this.recordId)? 'Editar Almacén':'Nuevo Almacén'
                if (this.recordId) {
                    this.$http.get(`/warehouses/getWarehouse/${this.recordId}`)
                        .then(response => {
                            this.form = response.data;
                            console.log(this.form);
                        })                        
                }
            },
            submit() {
                console.log(this.form);
                this.loading_submit = true
                this.$http.post(`/warehouses/storeWarehouse`, this.form)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                            this.$eventHub.$emit('reloadData')
                            this.close()
                        } else {
                            this.$message.error(response.data.message)
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data.errors
                        } else {
                            console.log(error)
                        }
                    })
                    .then(() => {
                        this.loading_submit = false
                    })
            },
            close() {
                this.$emit('update:showDialog', false)
                this.initForm()
            },
            handleEstablishmentChange(value) {
                alert("el id del establcimiento es "+ value);
                // console.log("Establecimiento seleccionado:", value);
            },
        }
    }
</script>