<template>
  <el-dialog
    :title="titleDialog"
    :visible="showDialog"
    @close="close"
    @open="create"
  >
    <div class="row">
      <div class="col-md-12">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Descripción</th>
                <th>Lote</th>
                <th class="text-center">Unidad</th>
                <th class="text-center">Cantidad</th>
                <th class="text-center">Almacén</th>
              </tr>
            </thead>
            <tbody v-if="Object.keys(itemData).length > 0">
              <tr v-for="(row, index) in mappedItems" :key="index">
                <td>{{ row.item.description }}</td>
                <td>{{ row.lot_code || '-' }}</td>
                <td class="text-center">{{ row.item.unit_type_id }}</td>
                <td class="text-center">{{ row.quantity }}</td>
                <td class="text-center">
                  <span v-if="row.is_delivered" class="text-success">
                    <i class="fa fa-check-circle"></i> Recibido
                  </span>
                  <el-select
                    v-else
                    v-model="itemData.items[index].warehouse_id"
                    placeholder="Seleccionar almacén"
                    size="small"
                  >
                    <el-option
                      v-for="option in warehouses"
                      :key="option.id"
                      :label="option.description"
                      :value="option.id"
                    />
                  </el-select>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="form-actions text-right pt-2 mt-2">
          <el-button @click.prevent="close">Cancelar</el-button>
          <el-button
            @click.prevent="saveChanges"
            type="primary"
            :disabled="stateSubmitButton"
          >
            Guardar
          </el-button>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script>
export default {
  props: ['showDialog', 'purchase_id'],
  data() {
    return {
      titleDialog: 'Recepción de productos',
      resource: 'purchases',
      itemData: {},
      warehouses: [],
      stateSubmitButton: true
    };
  },
  computed: {
    mappedItems() {
      return (this.itemData.items && this.itemData.items.map(item => ({ ...item }))) || [];
    }
  },
  watch: {
    itemData: {
      deep: true,
      handler() {
        this.stateSubmitButton = !this.enableSubmitButton();
      }
    }
  },
  methods: {
    async getPurchase(purchase_id) {
      try {
        const response = await this.$http.get(`/${this.resource}/record/${purchase_id}`);
        if (response.status === 200) {
          this.itemData = response.data.data.purchase;
        }
      } catch (error) {
        console.error(error);
      }
    },

    async getWarehouses() {
      const response = await this.$http.get(`/warehouses-by-active-establishment`);
      if (response.data.success) {
        this.warehouses = response.data.data;
      }
    },

    enableSubmitButton() {
      if (!this.itemData.items || this.itemData.items.length === 0) return false;

      const pendientes = this.itemData.items.filter(item => !item.is_delivered);

      // Sin pendientes → todos entregados → bloquear
      if (pendientes.length === 0) return false;

      // Habilitar solo si todos los pendientes tienen almacén
      return pendientes.every(item => !!item.warehouse_id);
    },

    async create() {
      await Promise.all([
        this.getPurchase(this.purchase_id),
        this.getWarehouses()
      ]);
      this.stateSubmitButton = !this.enableSubmitButton();
    },

    resetData() {
      this.itemData = {};
      this.warehouses = [];
      this.stateSubmitButton = true;
    },

    close() {
      this.$emit('update:showDialog', false);
      this.resetData();
    },

    async saveChanges() {
      try {
        const itemsSinAlmacen = this.itemData.items
          .filter(item => !item.is_delivered && !item.warehouse_id);

        if (itemsSinAlmacen.length > 0) {
          this.$message.error('Todos los productos deben tener un almacén asignado');
          return;
        }

        const payload = {
          ...this.itemData,
          items: this.itemData.items.map(item => ({
            id: item.id,
            warehouse_id: item.warehouse_id,
            quantity: item.quantity
          }))
        };

        const response = await this.$http.post(`/purchases/updatePosition`, payload);
        if (response.data.success) {
          await this.getPurchase(this.purchase_id);
          this.close();
          this.$message.success('Stock registrado correctamente');
        }
      } catch (error) {
        console.error(error);
        this.$message.error('Error al guardar los cambios');
      }
    }
  }
};
</script>
