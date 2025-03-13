<template>
  <div class="position-container">
    <div class="add-column-button-container">
      <el-button type="success" @click="addColumn"> Agregar Columna </el-button>
    </div>

    <div v-if="positions.length > 0" class="boxes-container">
      <div v-for="(row, rowIndex) in positions" :key="rowIndex" class="row-container">
        <div v-for="(position, colIndex) in row" :key="colIndex" class="position-box">
          <div style="padding: 20px; border-radius: 5px; border: solid gray 1px;">
            <div class="text-center">
              {{ code!=''?code:'N' }}-{{ position.row }}-{{ numberToLetter(position.column) }}
            </div>
            <el-select v-model="position.status" @change="updatePositionStatus(position)">
              <el-option label="Disponible" value="available"></el-option>
              <el-option label="Ocupado" value="occupied"></el-option>
              <el-option label="Mermado" value="damaged"></el-option>
            </el-select>
          </div>
        </div>
        <div class="delete-row-button">
          <el-button type="danger" @click="deleteRow(rowIndex)" icon="el-icon-delete" circle></el-button>
        </div>
      </div>
    </div>
    <div v-else>
      <p>No se han definido filas y columnas.</p>
    </div>

    <div class="add-row-button-container">
      <el-button type="success" @click="addRow" icon="el-icon-plus">Agregar Fila</el-button>
    </div>

    <div class="text-right mt-5">
      <el-button type="primary" @click="savePositions">Guardar Cambios</el-button>
    </div>
  </div>
</template>

<script>
export default {
  props: ['rows', 'columns', 'code'],
  data() {
    return {
      positions: []
    };
  },
  watch: {
    rows(newRows) {
      this.initializePositions(newRows, this.columns);
    },
    columns(newColumns) {
      this.initializePositions(this.rows, newColumns);
    },
  },
  created() {
    this.initializePositions(this.rows, this.columns);
  },
  methods: {
    initializePositions(rows, columns) {
      this.positions = [];
      if (rows && columns) {
        for (let i = rows; i > 0; i--) {
          let row = [];
          for (let j = 1; j <= columns; j++) {
            row.push({ row: i, column: j, status: 'available' });
          }
          this.positions.push(row);
        }
      }
    },
    numberToLetter(number) {
      let letter = '';
      while (number > 0) {
        let remainder = (number - 1) % 26;
        letter = String.fromCharCode(65 + remainder) + letter;
        number = Math.floor((number - 1) / 26);
      }
      return letter;
    },
    updatePositionStatus(position) {
      this.$emit('position-status-updated', position);
    },
    savePositions() {
      this.$emit('save');
    },
    addColumn() {
      const newColumnIndex = this.positions[0].length + 1;
      this.positions.forEach(row => {
        row.push({ row: row[0].row, column: newColumnIndex, status: 'available' });
      });
      this.$emit('addColumn');
    },
    addRow() {
      const newRowIndex = this.positions.length + 1;
      const newRow = [];
      for (let j = 1; j <= this.positions[0].length; j++) {
        newRow.push({ row: newRowIndex, column: j, status: 'available' });
      }
      this.positions.unshift(newRow);
      this.$emit('addRow');
    },
    deleteRow(rowIndex) {
      this.positions.splice(rowIndex, 1);
      this.$emit('deleteRow');
    },
  },
};
</script>

<style>
.position-container {
  max-width: 100%;
  max-height: 70vh;
  padding: 10px;
  position: relative;
}

.boxes-container {
  max-height: calc(70vh - 150px);
  overflow: auto;
  margin-top: 50px;
  margin-bottom: 50px;
}

.row-container {
  display: flex;
  flex-wrap: nowrap;
  align-items: center;
}

.position-box {
  min-width: 120px;
  margin: 5px;
}

.add-column-button-container {
  position: absolute;
  right: 20px;
  top: 20px;
  z-index: 1;
}

.add-row-button-container {
  position: absolute;
  left: 20px;
  bottom: 20px;
  z-index: 1;
}

.text-right {
  text-align: right;
  margin-top: 20px;
}

.delete-row-button {
  margin-left: 10px;
}
</style>