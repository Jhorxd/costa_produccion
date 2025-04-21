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
            :label="option.name"
            :value="option.id"
          ></el-option>
        </el-select>
      </div>
    </div>
    <div class="position-container" v-if="positions.length>0">
      <div class="boxes-container">
        <div v-for="(row, rowIndex) in matrix" :key="`row-${rowIndex}`" class="row-container">
          <div 
            v-for="(box, colIndex) in row" 
            :key="`box-${box.row}-${box.column}`" 
            class="position-box"
          >
            <div class="box-content">
              <p class="margin-bottom">{{ box.code_location }}-{{ box.row }}-{{ numberToLetter(box.column) }}</p>
              <p>{{type=="input"?'Stock en uso':'Stock disponible'}}: {{ box.stock_item }}</p>
              <div class="content-stock d-flex justify-content-center" v-if="box.is_selected && !has_lots">
                <el-input 
                  type="number" 
                  class="form-control-feedback input-stock" 
                  v-model="box.stock"
                  dusk="stock" 
                  placeholder="Cantidad" 
                  :min="1"
                  :max="box.stock_item"
                  @change="validateStock(box)"
                ></el-input>
              </div>
              <el-button
                type="primary"
                @click="selectBox(box)"
                :class="{ 'selected-button': box.is_selected }"
                :disabled="disableSelectButton(box)"
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
import { parseString } from 'xml2js';

export default {
  props: [
    'showDialog',
    'dataModal',
    'warehouse_id',
    'locations_available',
    'type'
  ],
  data() {
    return {
      titleDialog: 'Selección de posición',
      resource: 'transfers',
      positions: [],
      locations: [],
      location_id: '',
      item_id: '',
      stock_necessary: 0,
      has_lots: false,
      has_positions: false,
      boxSelected: [],
    };
  },
  computed: {
    matrix() {
      if (this.positions.length === 0) return [];
      
      const maxRow = Math.max(...this.positions.map(loc => loc.row));
      const maxCol = Math.max(...this.positions.map(loc => loc.column));
      
      return Array.from({ length: maxRow }, (_, rowIndex) => {
        const rowNum = maxRow - rowIndex; // Filas en orden descendente
        return Array.from({ length: maxCol }, (_, colIndex) => {
          const colNum = colIndex + 1;
          return this.positions.find(p => p.row === rowNum && p.column === colNum) || {
            id: null,
            row: rowNum,
            column: colNum,
            status: 'available',
            stock_available: 0,
            stock_item: 0,
            code_location: 'An',
            is_selected: false,
            stock: 0,
          };
        });
      });
    }
  },
  methods: {
    async create() {
      
      this.item_id = this.dataModal.item_id || '';
      this.locations = this.locations_available ? [...this.locations_available] : [];
      //await this.getLocations(this.warehouse_id);
      this.positions = this.dataModal.positions ? [...this.dataModal.positions] : [];

      this.stock_necessary = this.dataModal.stock_necessary || 0;
      this.has_lots = this.dataModal.has_lots;
      this.has_position = this.dataModal.has_position;
      
      if (this.dataModal.location_id!=null){
        
        await this.getPositions(parseInt(this.dataModal.location_id), parseInt(this.item_id), this.warehouse_id); 
      }
      
    },
    disableSelectButton(box){
      if(this.type=="input"){
        if(parseInt(box.stock_available)<=0 && parseInt(box.stock_item)<=0){
          return true;
        }else{
          return false;
        }
      }else{
        if(this.has_lots){
          if(parseInt(box.stock_available)<=0){
            return true;
          }else{
            return false;
          }
        }else{
          if(parseInt(box.stock_item)<=0){
            return true;
          }else{
            return false;
          }
        }
      }
    },
    async getLocations(warehouse_id) {
      await this.$http
        .get(`/${this.resource}/locations/${warehouse_id}`)
        .then(response => {
          const response_data = response.data;
          if(response_data.success) {
            this.locations = response_data.data;
            if (this.dataModal.location_id){
              const locationExists = this.locations.some(loc => loc.id == this.dataModal.location_id);
              if(locationExists){
                this.location_id = this.dataModal.location_id;
              }
            }
          }
        });
    },
    async getPositions(location_id, item_id, warehouse_id) {
      await this.$http
        .get(`/${this.resource}/positions/${location_id}/${item_id}/${warehouse_id}`)
        .then(response => {
          const response_data = response.data;
          if(response_data.success) {
            this.positions = response_data.data.map(pos => ({
              ...pos,
              is_selected: Boolean(pos.is_selected),
              stock: pos.stock !== undefined ? parseInt(pos.stock) : 0
            }));
          }
        });
    },
    async changeLocation() {
      await this.getPositions(this.location_id, this.item_id, this.warehouse_id);
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
      if(!this.has_lots){
        if (box.is_selected) {
          box.is_selected = false;
          box.stock = 0;
        } else {        
          // Seleccionar la nueva posición
          box.is_selected = true;
          box.stock = Math.min(1, box.stock_item);
        }
      }else{
        this.matrix.forEach(row => {
          row.forEach(element =>{
            element.is_selected = false;
          });
        });
        box.is_selected = true;
      }
    },
    validateStock(box) {
      if (box.stock < 1) {
        box.stock = 1;
        this.$message.warning("La cantidad mínima es 1");
      } else if (box.stock_item<=0 && this.type!="input") {
        box.stock = 0;
        box.is_selected = false;
        this.$message.warning(`No hay stock disponible`);
      }
    },
    saveChanges() {
      
      if (!this.location_id) {
        this.$message.error("Debes seleccionar una ubicación");
        return;
      }

      if(!this.has_lots){
        let stock_total = 0;
        this.positions.forEach(element => {
          if(element.is_selected){
            stock_total+=parseInt(element.stock);
          }
        });
        
        if(this.stock_necessary != 0 && this.stock_necessary!=stock_total){
          this.$message.error("El stock total necesario es: "+this.stock_necessary);
          return;
        }
      }
      
      this.$emit('positions-save', {
        item_id: this.item_id,
        location_id: this.location_id,
        positions: [...this.positions] || [],
        stock_necessary: this.stock_necessary,
        has_lots: this.has_lots,
        has_position: this.has_position,
      });
      
      this.close();
      this.$message.success("Posición guardada correctamente");
    },
    resetData() {
      this.positions = [];
      this.locations = [];
      this.location_id = null;
    },
    close() {
      this.$emit('update:showDialog', false);
      this.resetData();
    }
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

.input-stock {
  max-width: 100px;
  margin: 0px;
}

.margin-bottom {
  margin-bottom: 0px;
}

.el-button[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>