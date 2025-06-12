<template>
    <el-dialog
          :title="titleDialog"
          :visible="showDialog"
          @close="close"
          @open="create"
        >
        <div class="form-body box-padding">
            <div class="row" >
                <div class="col-lg-12 col-md-12">
                    <table width="100%" class="text-center">
                        <thead>
                            <tr>
                                <th width="15%">#</th>
                                <th width="20%">Código</th>
                                <th width="20%">Stock</th>
                                <th width="20%">Cantidad solicitada</th>
                                <th width="25%">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(lot,index) in lots_temp" :key="lot.id">
                                <td>{{ index+1 }}</td>
                                <td>{{ lot?lot.code:'---' }}</td>
                                <td>{{ lot?lot.stock:'---' }}</td>
                                <td>{{ lot?lot.stock:'---' }}</td>
                                <td>
                                    <button type="button" class="btn waves-effect waves-light btn-xs" :class="lot.selected?'btn-success':'btn-info'" @click="LotSelected(lot, index)" :disabled="!lot.enabled">
                                        <i class="fa fa-check"></i>
                                    </button>
                                    <!-- <el-button type="primary" @click="LotSelected(lot, index)">{{ lot.selected?'Seleccionado':'Seleccionar' }}</el-button> -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="form-actions text-right pt-2">
            <el-button @click.prevent="clickCancelSubmit()">Cancelar</el-button>
            <el-button type="primary" @click="submit">Guardar</el-button>
        </div>
    </el-dialog>
</template>

<script>
    export default {
        props: [
            'showDialog',
            'available_lots',
            'stock_necessary',
            'required_lots'
        ],
        data() {
            return {
                titleDialog: "Selección de lotes por posición",
                resource: 'items',
                lots_selected: [],
                lots_temp: []
            }
        },
        methods: {
            async create() {
                this.lots_temp = this.available_lots;
                
                this.lots_temp.forEach(element => {
                    const index_lot = this.required_lots.findIndex( required_lot => required_lot.id == element.id);
                    element.enabled = index_lot!=-1 ? true : false;
                    //element.compromise_quantity = 0;
                    this.$set(element, 'selected', element.selected || false);
                });
            },
            LotSelected(lot, index){
                const index_lot = this.required_lots.findIndex( required_lot => required_lot.id == lot.id);
                if(index_lot!=-1){
                    const quantity_required = parseInt(this.required_lots[index_lot].compromise_quantity);
                    if(quantity_required>parseInt(lot.stock)){
                        this.$message.warning("No hay stock suficiente en esta posición");
                    }

                    this.$set(lot, 'compromise_quantity', this.required_lots[index_lot].compromise_quantity);
                    this.$set(lot, 'selected', !lot.selected);
                }
            },
            /* getTotalQuantitySelected(){
                let stock_total = 0;
                this.lots_temp.forEach(element => {
                    if(element.selected){
                        const index_lot = this.required_lots.findIndex( required_lot => required_lot.id == element.id);
                        if(index_lot!=-1){
                            const quantity_required = this.required_lots[index_lot].compromise_quantity;
                            stock_total+=parseInt(quantity_required);
                        }
                    }
                });
                return stock_total;
            }, */
            submit(){
                /* if(this.stock_necessary>stock_total){
                    const differenceStock = parseInt(this.stock_necessary)-parseInt(stock_total);
                    this.$message.warning("Aun falta que seleccione "+differenceStock+" lote(s)");
                    return;
                } */
                
                /* if(stock_total>this.stock_necessary){
                    this.$message.warning("Lotes seleccionados de forma excesiva, la cantidad necesaria es "+this.stock_necessary+" lote(s)");
                    return;
                } */
               
                this.$emit('update-box-selected',this.lots_temp);
                this.close();
            },
            async clickCancelSubmit() {
                await this.$emit('update:showDialog', false)
            },
            close() {
                this.$emit('update:showDialog', false);
                this.resetData();
            },
            resetData() {
                this.lots_selected = [];
                this.lots_temp = [];
            }
        }
    }
</script>
<style>
.box-padding{
    padding: 0 10px;
}
</style>