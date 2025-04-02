<template>
    <el-dialog
      :title="titleDialog"
      :visible="showDialog"
      @close="close"
      @open="create"
    >
      <div class="col-md-3">
        <div class="form-group">
            <label class="control-label">Ubicación</label>
            <el-select v-model="location_id" @change="changeLocation">
                <el-option
                    v-for="option in locations"
                    :key="option.id"
                    :disabled="option.id == location_id"
                    :label="option.name"
                    :value="option.id"
                ></el-option>
            </el-select>
        </div>
      </div>
      <div class="position-container" v-if="positions.length>0">
        <div class="boxes-container">
          <div v-for="(row, rowIndex) in matrix" :key="rowIndex" class="row-container">
            <div v-for="(box, colIndex) in row" :key="colIndex" class="position-box">
              <div class="box-content">
                <p class="margin-bottom">{{ box.code_location }}-{{ box.row }}-{{ numberToLetter(box.column) }}</p>
                <p>Cantidad disponible: {{ box.stock_available }}</p>
                <el-button
                  type="primary"
                  @click="selectBox(box)"
                  :class="{ 'selected-button': box.is_selected }"
                >
                  {{ box.is_selected ? 'Seleccionado' : 'Seleccionar' }}
                </el-button>
              </div>
            </div>
          </div>
        </div>
        <div class="form-actions text-right pt-2 mt-2">
          <el-button class="second-buton" @click.prevent="close">Cancelar</el-button>
          <el-button @click.prevent="saveChanges" type="primary">Guardar</el-button>
        </div>
      </div>
    </el-dialog>
  </template>

<script>
import { forEach } from 'lodash';


export default {
    props: [
        'showDialog',
        'dataModal',
        'warehouse_id'
    ],
    data() {
        return {
            titleDialog: 'Selección de posicion',
            resource: 'transfers',
            matrix: [],
            positions: [],
            locations: [],
            location_id: ''//necesario para traer locations
        };
    },
    methods: {
        async create() {
          this.positions = [...this.dataModal.positions];
          if(this.dataModal.positions!=null){
            this.location_id = {...this.dataModal.location_id};
          }else{
            this.location_id = '';
          }
          await this.getLocations(this.warehouse_id); //llenamos locations
          if (this.positions.length>0){
            this.buildMatrix(this.positions);
          }
        },
        async getLocations(warehouse_id){
          await this.$http
            .get(`/${this.resource}/locations/${warehouse_id}`)
            .then(response => {
                const response_data = response.data;
                if(response_data.success){
                  this.locations = response_data.data;
                }
            })
        },
        async getPositions(location_id){
          await this.$http
            .get(`/${this.resource}/positions/${location_id}`)
            .then(response => {
                const response_data = response.data;
                if(response_data.success){
                  this.positions = response_data.data;
                }
            })
        },
        async changeLocation(){
          await this.getPositions(this.location_id);
          if (this.positions.length>0){
            this.buildMatrix(this.positions);
          }
        },
        
        buildMatrix(positions) {
            const maxRow = Math.max(...positions.map((loc) => loc.row));
            const maxCol = Math.max(...positions.map((loc) => loc.column));
            
            const newPositions = [];
            for (let i = maxRow; i > 0; i--) {
                const row = [];
                for (let j = 1; j <= maxCol; j++) {
                    const existingPosition = positions.flat().find(p => p.row == i && p.column == j);
                    row.push({
                        id: existingPosition ? existingPosition.id : null,
                        row: i,
                        column: j,
                        status: existingPosition ? existingPosition.status : 'available',
                        stock_available: existingPosition ? existingPosition.stock_available : 0,
                        code_location: existingPosition ? existingPosition.code_location: 'An',
                        is_selected: existingPosition ? Boolean(existingPosition.is_selected): false
                    });
                }
                newPositions.push(row);
            }
            this.matrix = newPositions;
        },
        numberToLetter(number) {
            let letter = '';
            while (number > 0) {
                const remainder = (number - 1) % 26;
                letter = String.fromCharCode(65 + remainder) + letter;
                number = Math.floor((number - 1) / 26);
            }
            return letter;
        },
        selectBox(box) {
            if(box.is_selected){
              box.is_selected = false;
              const position_finded = this.positions.find(position => position.row == box.row && position.column == box.column);
              if(position_finded){
                position_finded.is_selected = false
              }
            }else{
              if(box.stock_available>=1){
                this.matrix.forEach(row => {
                  row.forEach(element => {
                    if(element.is_selected){
                      element.is_selected = false;
                      const position_finded = this.positions.find(position => position.row == element.row && position.column == element.column);
                      if(position_finded){
                        position_finded.is_selected = false
                      }
                    }
                  });
                });
                const position_finded = this.positions.find(position => position.row == box.row && position.column == box.column);
                if(position_finded){
                  position_finded.is_selected = true;
                }
                box.is_selected = true;
              }else{
                this.$message.error("No hay stock suficiente");
              }
            }
        },
        isOneSelected() {
          if (this.positions.length > 0) {
            return this.positions.some(element => element.is_selected);
          }
          return false;
        },
        saveChanges(){
          
          const data = {
            location_id: null,
            positions: []
          };
          let is_one_selected = this.isOneSelected(); // verificar si hay almenos un seleccionado;
          console.log(is_one_selected);
          
          if(this.location_id!=null && this.positions.length>0 && is_one_selected){
            data.location_id = this.location_id;
            data.positions = this.positions;
            this.$emit('positions-save', data);
            this.close();
            this.$message.success("Posición guardada correctamente");
          }else{
            this.$message.error("Elige una ubicación");
          }
        },
        resetData() {
            this.matrix = [];
            this.positions = [];
            this.locations =[];
            this.location_id = null;
        },
        close() {
            this.$emit('update:showDialog', false);
            this.resetData();
        },
    }
};
</script>

<style scoped>
.position-container {
  width: 100%;
  height: 100%;
  padding: 10px;
  display: flex;
  flex-direction: column;
}

.boxes-container {
  flex: 1;
  overflow: auto;
}

.row-container {
  display: flex;
  justify-content: center;
  width: 100%;
  gap: 10px;
  margin-bottom: 10px;
}

.position-box {
  flex: 1;
  min-width: 150px;
  max-width: 200px;
  border: 1px solid #ccc;
  padding: 10px;
  border-radius: 5px;
  text-align: center;
  box-sizing: border-box;
}

.box-content {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.text-right {
  text-align: right;
  margin-top: 20px;
}

.selected-button {
  background-color: #67c23a !important;
  border-color: #67c23a !important;
  color: white !important;
}

.selected-button:hover {
  background-color: #5daf34 !important;
  border-color: #5daf34 !important;
}

.input-stock{
    max-width: 100px;
    margin:0px;
}

.margin-bottom{
    margin-bottom: 0px;
}
</style>