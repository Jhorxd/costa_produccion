<template>
    <el-dialog
      :title="titleDialog"
      :visible="showDialog"
      @close="close"
      @open="create"
    >
      <div class="row">
        <div :class="dataModalLocal.item_data.has_lot?'col-md-2':'col-md-4'">
          <label class="control-label">Producto</label>
          <el-input 
            type="text" 
            class="form-control-feedback" 
            v-model="dataModalLocal.item_data.item_name"
            readonly
          ></el-input>
        </div>
        <div class="col-md-3" v-if="dataModalLocal.item_data.has_lot">
          <label class="control-label">Nombre de Lote</label>
          <el-input 
            type="text" 
            class="form-control-feedback" 
            v-model="dataModalLocal.position_data.lot_name"
          ></el-input>
        </div>
        <div class="col-md-3" v-if="dataModalLocal.item_data.has_lot">
          <label class="control-label">Fecha</label>
          <el-date-picker 
            type="date"
            class="form-control-feedback" 
            v-model="dataModalLocal.position_data.expiration_date"
          ></el-date-picker>
        </div>
        <div :class="dataModalLocal.item_data.has_lot?'col-md-2':'col-md-4'">
          <div class="form-group">
            <label class="control-label">Almacenes</label>
            <el-select v-model="dataModalLocal.position_data.warehouse_id" @change="changeWarehouse">
                <el-option
                    v-for="option in warehouses"
                    :key="option.id"
                    :disabled="option.id == dataModalLocal.position_data.warehouse_id"
                    :label="option.description"
                    :value="option.id"
                ></el-option>
            </el-select>
          </div>
        </div>
        <div :class="dataModalLocal.item_data.has_lot?'col-md-2':'col-md-4'">
          <div class="form-group">
            <label class="control-label">Ubicaciones</label>
            <el-select v-model="dataModalLocal.position_data.location_id" @change="changeLocation">
                <el-option
                    v-for="option in locations"
                    :key="option.id"
                    :disabled="option.id == dataModalLocal.position_data.location_id"
                    :label="option.name"
                    :value="option.id"
                ></el-option>
            </el-select>
          </div>
        </div>
      </div>
      <div class="position-container" v-if="positions.length>0">
        <div class="boxes-container">
          <div v-for="(row, rowIndex) in matrix" :key="rowIndex" class="row-container">
            <div v-for="(box, colIndex) in row" :key="colIndex" class="position-box">
              <div class="box-content">
                <p class="margin-bottom">{{ box.code_location }}-{{ box.row }}-{{ numberToLetter(box.column) }}</p>
                <p>Stock disponible: {{ box.stock_available }}</p>
                <el-input v-if="box.is_selected" placeholder="Cantidad"
                  v-model="box.quantity"
                  style="width: 100%;">
                </el-input>
                <el-button
                  type="primary"
                  @click="selectBox(box)"
                  :class="{ 'selected-button': box.is_selected }"
                  :disabled="disabledPosition(box)"
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
        'dataModal'
    ],
    data() {
        return {
            titleDialog: 'Selección de posicion',
            resource: 'transfers',
            matrix: [],
            warehouses: [],
            positions: [],
            locations: [],
            dataModalLocal:{
              item_data:{
                item_id:'',
                item_name:'',
                quantity:0,
                has_lot:'',
                current_stock: 0,
                stock_max: 0,
                quantity_delivered: 0
              },
              position_data:{
                expiration_date:'',
                lot_name:'',
                warehouse_id:'',
                location_id:'',
                position_id:'',
                quantity:0,
              },
              stock_positions:[]
            },
        };
    },
    async created() {
        await this.getWarehouses();
    },
    methods: {
        disabledPosition(position){
          let stock_available = parseInt(position.stock_available);
          this.dataModalLocal.stock_positions.forEach(element => {
            if(position.id == element.position_id){
              stock_available++;
            }
          });

          if(position.exist_item){
            return false;
          }else{
            if(stock_available>0){
              return false;
            }else{
              return true;
            }
          }
        },
        async create() {          
          this.dataModalLocal = this.dataModal;
          
          if(this.dataModalLocal.position_data.position_id!=''){
            await this.getPositions(this.dataModalLocal.position_data.location_id, this.dataModalLocal.item_data.item_id);
            this.stock_positions=this.dataModalLocal.stock_positions;
            const stock_position_finded = this.stock_positions.findIndex(element => element.item_id==this.dataModalLocal.item_data.item_id);
            
            if(stock_position_finded!=-1){
              /* this.matrix.forEach(row => {
                const finded = row.findIndex(element => element.id==this.stock_positions[stock_position_finded].position_id);
                if(finded != -1){
                  row[finded].is_selected = true;
                }
              }); */

              /* const positionFinded = this.positions.findIndex(element => element.id==this.stock_positions[stock_position_finded].position_id)
              if(positionFinded!=-1){
                this.positions[positionFinded].is_selected = true;
              } */
            }
            
            
            if (this.positions.length>0){
              this.buildMatrix(this.positions);
            }
          }
          /* this.positions = [...this.dataModal.positions];
          if(this.dataModal.positions!=null){
            this.location_id = {...this.dataModal.location_id};
          }else{
            this.location_id = '';
          }
          //await this.getLocations(this.warehouse_id); //llenamos locations
          if (this.positions.length>0){
            this.buildMatrix(this.positions);
          } */
        },
        changeWarehouse(){
          this.dataModalLocal.position_data.location_id='';
          this.dataModalLocal.position_data.position_id='';
          this.locations=[];
          this.positions=[];
          this.getLocations(this.dataModalLocal.position_data.warehouse_id);
        },
        async getWarehouses(){
          const response = await this.$http.get(`/warehouses-by-active-establishment`);
          if(response.data.success){
            this.warehouses = response.data.data;
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
        async getPositions(location_id, item_id){
          await this.$http
            .get(`/get-position-add-item/${location_id}/${item_id}`)
            .then(response => {
                const response_data = response.data;
                if(response_data.success){
                  this.positions = response_data.data;
                }
            })
        },
        async changeLocation(){
          this.dataModalLocal.position_data.position_id='';
          this.positions=[];
          await this.getPositions(this.dataModalLocal.position_data.location_id, this.dataModalLocal.item_data.item_id);
          this.positions.map(position_element => {
            if(position_element.is_selected!=undefined){
              return position_element;
            }else{
              return {
                ...position_element,
                is_selected: false
              }
            }
          });
          
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
                        is_selected: existingPosition ? Boolean(existingPosition.is_selected): false,
                        exist_item: existingPosition ? Boolean(existingPosition.exist_item): false,
                        quantity: existingPosition ? existingPosition.quantity: 0,
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
              this.matrix.forEach(row => {
                  row.forEach(element => {
                    if(element.is_selected){
                      element.is_selected = false;
                      const position_finded = this.positions.find(position => position.row == element.row && position.column == element.column);
                      if(position_finded){
                        position_finded.is_selected = false
                      }
                      const stock_position_finded = this.dataModalLocal.stock_positions.findIndex(stock_position_element => stock_position_element.position_id==element.id && stock_position_element.item_id == this.dataModalLocal.item_data.item_id);
                      if(stock_position_finded!=-1){
                        this.dataModalLocal.stock_positions.splice(stock_position_finded, 1);
                      }
                    }
                  });
              });
              const position_finded = this.positions.find(position => position.row == box.row && position.column == box.column);
              if(position_finded){
                position_finded.is_selected = true;
              }
              this.dataModalLocal.stock_positions.push({
                position_id: box.id,
                item_id: this.dataModalLocal.item_data.item_id
              });
              box.is_selected = true;
            }
        },
        isOneSelected() {
          if (this.positions.length > 0) {
            return this.positions.some(element => element.is_selected);
          }
          return false;
        },
        formatDate(date) {
          if (!date) return null; // Manejar el caso de fecha nula o indefinida
          const year = date.getFullYear();
          const month = String(date.getMonth() + 1).padStart(2, '0'); // Meses van de 0 a 11
          const day = String(date.getDate()).padStart(2, '0');
          return `${year}-${month}-${day}`;
        },
        getQuantityOfPositionSelected() {
          for (const row of this.matrix) {
            const findedElementIndex = row.findIndex(element => element.is_selected === true);
            if (findedElementIndex !== -1) {
              return parseInt(row[findedElementIndex].quantity);
            }
          }
          return 0;
        },
        saveChanges(){
          let is_one_selected = this.isOneSelected(); // verificar si hay almenos un seleccionado;
          if(!is_one_selected){
            return this.$message.error("Elige una posición");
          }

          const current_stock = parseInt(this.dataModalLocal.item_data.current_stock);
          const stock_max = parseInt(this.dataModalLocal.item_data.stock_max);
          const quantity_selected = this.getQuantityOfPositionSelected();
          const quantity_required = parseInt(this.dataModalLocal.item_data.quantity);
          const quantity_delivered = parseInt(this.dataModalLocal.item_data.quantity_delivered);

          console.log(quantity_required);
          console.log(quantity_delivered);
          
          

          if(quantity_selected>this.dataModalLocal.item_data.quantity){
            return this.$message.error("La cantidad ingresada supera la cantidad registrada en la compra ("+(this.dataModalLocal.item_data.quantity)+")");
          }

          const quantity_available = quantity_required-quantity_delivered;
          if(quantity_selected>quantity_available){
            return this.$message.error("La cantidad ingresada supera la cantidad disponible de recepción ("+(quantity_available)+")");
          }

          if((current_stock+quantity_selected)>stock_max){
            return this.$message.error("Se supera el stock máximo para el item, disponible: "+(stock_max-current_stock));
          }

          const index_stock_position = this.dataModalLocal.stock_positions.findIndex(element => element.item_id == this.dataModalLocal.item_data.item_id);
          if(index_stock_position!=-1){
            this.dataModalLocal.position_data.position_id = this.dataModalLocal.stock_positions[index_stock_position].position_id;
          }
          
          this.dataModalLocal.position_data.expiration_date = this.formatDate(this.dataModalLocal.position_data.expiration_date);
          this.dataModalLocal.position_data.quantity = quantity_selected;
          this.$emit('positions-save', this.dataModalLocal);
          this.resetData();
          this.close();
          this.$message.success("Posición guardada correctamente");
        },
        resetData() {
            this.matrix = [];
            this.locations = [];
            this.positions = [];
            this.dataModalLocal={
              item_data:{
                item_id:'',
                item_name:'',
                quantity:0,
                has_lot:'',
                current_stock: 0,
                stock_max: 0,
                quantity_delivered: 0
              },
              position_data:{
                expiration_date:'',
                lot_name:'',
                warehouse_id:'',
                location_id:'',
                position_id:''
              },
              stock_positions:[]
            };
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