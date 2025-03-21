<template>
    <el-dialog
      :title="titleDialog"
      :visible="showDialog"
      @close="close"
      @open="create"
    >
      <div class="position-container">
        <div class="boxes-container">
          <div v-for="(row, rowIndex) in matrix" :key="rowIndex" class="row-container">
            <div v-for="(box, colIndex) in row" :key="colIndex" class="position-box">
              <div class="box-content">
                <p class="margin-bottom">{{ box.code_location }}-{{ box.row }}-{{ numberToLetter(box.column) }}</p>
                <p>Cantidad disponible: {{ box.stock_available }}</p>
                <div class="content-stock d-flex justify-content-center">
                    <el-input v-if="box.is_selected" type="number" class="form-control-feedback input-stock" v-model="box.stock" dusk="stock" placeholder="Cantidad" min="1"></el-input>
                </div>
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
        'location_id',
        'positions_selected',
        'positions'
    ],
    data() {
        return {
            titleDialog: 'Elige una posición',
            resource: 'items',
            matrix: [],
            selects_temp: []
        };
    },
    methods: {
        close() {
            this.$emit('update:showDialog', false);
        },
        async create() {
            this.buildMatrix(this.positions);
            this.selects_temp = [...this.positions_selected];
        },
        buildMatrix(positions) {
            const maxRow = Math.max(...positions.map((loc) => loc.row));
            const maxCol = Math.max(...positions.map((loc) => loc.column));

            positions.forEach(location => {
                const isSelected = this.positions_selected.some(selectedPosition => {
                    return location.row === selectedPosition.row && location.column === selectedPosition.column;
                });
                location.is_selected = isSelected;
            });
            
            const newPositions = [];
            for (let i = maxRow; i > 0; i--) {
                const row = [];
                for (let j = 1; j <= maxCol; j++) {
                const existingPosition = positions.flat().find(p => p.row == i && p.column == j);
                row.push({
                    row: i,
                    column: j,
                    status: existingPosition ? existingPosition.status : 'available',
                    quantity_used: existingPosition ? existingPosition.quantity_used : 0,
                    stock_available: existingPosition ? existingPosition.stock_available : 0,
                    code_location: existingPosition ? existingPosition.code_location: 'An',
                    is_selected: existingPosition ? Boolean(existingPosition.is_selected): false,
                    stock: existingPosition ? parseInt(existingPosition.stock): 0,
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
            if(box.stock_available>=1){
                box.is_selected = true;
                this.selects_temp.push({ row: box.row, column: box.column, stock: box.stock });
                console.log(this.selects_temp);
                
                //this.$emit('box-selected', { row: box.row, column: box.column });
                //this.close();
            }else{
                this.$message.error("No hay stock suficiente");
            }
            
        },
        saveChanges(){
            this.matrix.forEach(row => {
                row.forEach(element => {
                    const item_finded = this.selects_temp.find(item_selected => {return item_selected.row == element.row && item_selected.column == element.column});
                    if(item_finded){
                        item_finded.stock = parseInt(element.stock);
                    }
                });
            });
            this.$emit('positions-save', this.selects_temp);
            //console.log(this.selects_temp);
            this.close();
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

.input-stock{
    max-width: 100px;
    margin:0px;
}

.margin-bottom{
    margin-bottom: 0px;
}
</style>