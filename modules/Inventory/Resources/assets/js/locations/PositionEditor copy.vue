<template>
  <div class="position-container">
    <div v-if="positions.length > 0" class="scroll-container">
      <div class="columns-container box-left" style="background-color: red;">
        <div class="button-left-container">
          <el-button type="primary"> PRUEBA </el-button>
        </div>
      </div>

      <div class="columns-container box-rigth" style="background-color: blue;">
        <div class="button-top-container">
          <el-button type="primary"> PRUEBA </el-button>
        </div>
        <div v-for="(row, rowIndex) in positions" :key="rowIndex" class="row-container">
          <div v-for="(position, colIndex) in row" :key="colIndex" class="position-box">
            <div style="padding: 20px; border-radius: 5px; border: solid gray 1px;">
              <div class="text-center">
                {{ code || 'An' }}-{{ position.row }}-{{ numberToLetter(position.column) }}
              </div>
              <el-select v-model="position.status" @change="updatePositionStatus(position)">
                <el-option label="Disponible" value="available"></el-option>
                <el-option label="Ocupado" value="occupied"></el-option>
                <el-option label="Mermado" value="damaged"></el-option>
              </el-select>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else>
      <p>No se han definido filas y columnas.</p>
    </div>
    <div class="text-right mt-4">
      <el-button type="primary" @click="savePositions">Guardar Cambios</el-button>
    </div>
  </div>
</template>

<script>
export default {
  props: ['rows', 'columns', 'code', 'initialPositions'],
  data() {
    return {
      positions: [],
    };
  },
  watch: {
    rows(newRows) {
      this.initializePositions(newRows, this.columns);
    },
    columns(newColumns) {
      this.initializePositions(this.rows, newColumns);
    },
    initialPositions(newPositions) {
      if (newPositions && newPositions.length > 0) {
        this.positions = this.mapPositionsToGrid(newPositions, this.rows, this.columns);
      } else {
        this.initializePositions(this.rows, this.columns);
      }
    },
  },
  created() {
    if (this.initialPositions && this.initialPositions.length > 0) {
      this.positions = this.mapPositionsToGrid(this.initialPositions, this.rows, this.columns);
    } else {
      this.initializePositions(this.rows, this.columns);
    }
  },
  methods: {
    initializePositions(rows, columns) {
      if (!rows || !columns) {
        this.positions = [];
        return;
      }

      this.positions = [];
      for (let i = 1; i <= rows; i++) {
        const row = [];
        for (let j = 1; j <= columns; j++) {
          row.push({
            row: i,
            column: j,
            status: 'available',
          });
        }
        this.positions.push(row);
      }
    },
    mapPositionsToGrid(positions, rows, columns) {
      const grid = [];
      for (let i = 1; i <= rows; i++) {
        const row = [];
        for (let j = 1; j <= columns; j++) {
          const position = positions.find(p => p.row === i && p.column === j);
          row.push({
            row: i,
            column: j,
            status: position ? position.status : 'available',
          });
        }
        grid.push(row);
      }
      return grid;
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
    updatePositionStatus(position) {
      this.$emit('position-status-updated', position);
    },
    savePositions() {
      this.$emit('save', this.positions.flat());
    },
  },
};
</script>

<style>
  .position-container {
    max-width: 100%;
    max-height: 70vh;
    overflow: auto;
    padding: 10px;
  }

  .scroll-container {
    display: flex;
    flex-direction: row;
    overflow: auto;
  }

  .row-container {
    display: flex;
    flex-wrap: nowrap;
  }

  .position-box {
    min-width: 120px;
    margin: 5px;
  }

  .button-top-container{
    display: flex;
    justify-content: flex-end;
  }
  .columns-container{
    display: inline-block;
  }

  .button-left-container{
    display: flex;
    align-items: flex-end;
  }
</style>