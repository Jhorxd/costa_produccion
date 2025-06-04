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
                                <th width="30%">Código</th>
                                <th width="30%">Stock</th>
                                <th width="25%">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(lot,index) in lots_temp" :key="lot.id">
                                <td>{{ index+1 }}</td>
                                <td>{{ lot?lot.code:'---' }}</td>
                                <td>{{ lot?lot.quantity:'---' }}</td>
                                <td>
                                    <button type="button" class="btn waves-effect waves-light btn-xs" :class="lot.selected?'btn-success':'btn-info'" @click="LotSelected(lot, index)" :disabled="calculaBotonDesabilitar(lot, lots_selected)">
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
            'box_selected',
            'lots',
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
            calculaBotonDesabilitar(lot, lots_selected){                
                if(lot.selected_global){
                    const finded = lots_selected.find(element => {
                        if (element.lots_group_id) {
                            return element.lots_group_id == lot.id;
                        } else {
                            return element.code == lot.code;
                        }
                    });
                    if(finded){
                        return false;
                    }else{
                        return true;
                    }
                }else{
                    return false;
                }
            },
            async create() {
                this.lots_temp = JSON.parse(JSON.stringify(this.lots));
                
                this.lots_temp.forEach(element => {
                    element.selected = false;
                });
                
                if(this.box_selected.lots.length>0){
                    this.lots_selected = [...this.box_selected.lots];
                    
                    this.lots_selected.forEach(element => {
                        if(element.lots_group && element.lots_group.code) {
                            element.code = element.lots_group.code;
                        }
                        //const lots_finded = this.lots_temp.find(lot => lot.id == element.lots_group_id);
                        const lots_finded = this.lots_temp.find(lot => {
                            if (element.lots_group_id) {
                                return element.lots_group_id == lot.id;
                            } else {
                                return element.code == lot.code;
                            }
                        });
                        if(lots_finded){
                            lots_finded.selected = true;
                        }
                    });
                }
            },
            LotSelected(lot, index) {              
                let updatedLot = { ...lot, selected: !lot.selected };
                
                if(lot.selected) {
                    const lotIndex = this.lots_selected.findIndex(existingLot => existingLot.lots_group_id === updatedLot.id);
                    if (lotIndex !== -1) {
                        this.lots_selected.splice(lotIndex, 1);
                    }
                } else {
                    let lotIndex = null;
                    if(updatedLot.id!=undefined){
                        lotIndex = this.lots_selected.findIndex(existingLot => existingLot.lots_group_id === updatedLot.id);
                    }else{
                        lotIndex = this.lots_selected.findIndex(existingLot => existingLot.code === updatedLot.code);
                    }
                    
                    if (lotIndex == -1) {
                        updatedLot.lots_group_id = updatedLot.id;
                        updatedLot.stock = updatedLot.quantity;
                        updatedLot.code = updatedLot.code;
                        
                        this.lots_selected.push(updatedLot);
                    }
                }
                
                this.lots_temp.splice(index, 1, updatedLot);
            },
            submit() {
                const newlySelectedCode = this.lots_selected.map(lot => lot.code);
                
                this.lots.forEach(lot => {
                    if (newlySelectedCode.includes(lot.code)) {
                        lot.selected_global = true;
                    }else if (!this.calculaBotonDesabilitar(lot, this.box_selected.lots)){
                        lot.selected_global = false;
                    }
                });
                
                this.$emit('update-box-selected', this.lots_selected);
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