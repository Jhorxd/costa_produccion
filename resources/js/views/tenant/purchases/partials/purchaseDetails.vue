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
                                <!-- <th>#</th> -->
                                <th>Descripción</th>
                                <th>Almacén</th>
                                <th>Ubicación</th>
                                <th>Posición</th>
                                <th>Lote</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-center">Acción</th>
                                <!-- <th class="text-right">Cantidad</th>
                                <th class="text-right">Valor Unitario</th>
                                <th class="text-right">Precio Unitario</th>
                                <th class="text-right">Descuento</th>
                                <th class="text-right">Cargo</th>
                                <th class="text-right">Total</th> -->
                                <th></th>
                            </tr>
                        </thead>
                        <tbody v-if="Object.keys(itemData).length>0">
                            <tr v-for="(row, index) in itemData.items" :key="index">
                                <td class="text-center">{{ row.item.description }}</td>
                                <td class="text-left">{{ row.data_position.warehouse_name || '-' }}</td>
                                <td class="text-left">{{ row.data_position.location_name || '-' }}</td>
                                <td class="text-left">{{row.data_position.position!=null?getNamePosition(row.data_position.position):'-'}}</td>
                                <td class="text-left">{{ row.lot_code || '-'}}</td>
                                <td class="text-center">{{ row.item.unit_type_id }}</td>
                                <td class="text-center" v-if="enabledSelectPosition(row)">
                                    <button class="btn waves-effect waves-light btn-xs btn-success"
                                            type="button"
                                            @click.prevent="clickSelectPosition(row)">Posición
                                    </button>
                                    <button class="btn waves-effect waves-light btn-xs btn-danger"
                                            type="button"
                                            @click.prevent="clickRemoveItem(index)">x
                                    </button>
                                </td>
                                <td class="text-center" v-else> - </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-actions text-right pt-2 mt-2">
                    <el-button class="second-buton" @click.prevent="close">Cancelar</el-button>
                    <el-button @click.prevent="saveChanges" type="primary">Guardar</el-button>
                </div>
                <positions
                    :key="purchase_id||0"
                    :dataModal="positionData"
                    :showDialog.sync="showDialogSelectPosition"
                    @positions-save="savePositionsData"
                    ref="positions">
                </positions>
            </div>
        </div>
    </el-dialog>
</template>

<script>
import { create } from 'lodash';
import positions from './positions.vue';

export default {
    props: [
        'showDialog',
        'purchase_id'
    ],
    components:{
        positions
    },
    data() {
        return {
            titleDialog: 'Recepción de productos',
            resource: 'purchases',
            itemData: {},
            positionData: {},
            showDialogSelectPosition: false,
            stock_positions: [],
            item_selected: ''
        };
    },
    async created() {
        await this.getPurchase(this.purchase_id);
    },
    async mounted(){
        //await this.getPurchase(this.purchase_id);
    },
    methods:{
        async getPurchase(purchase_id){
            try {
                const response = await this.$http.get(`/${this.resource}/record/${purchase_id}`);
                
                if(response.status==200){
                    this.itemData = response.data.data.purchase;
                }
            } catch (error) {
                console.log(error);
            }
        },
        create(){
            //console.log(this.itemData);
            
        },
        close() {
            this.$emit('update:showDialog', false);
            //this.resetData();
        },
        initModalDataPosition(){
            return {
                item_data:{
                    item_id:'',
                    item_name:'',
                    quantity:0,
                    has_lot:'',
                },
                position_data:{
                    expiration_date:'',
                    lot_name:'',
                    warehouse_id:'',
                    location_id:'',
                    position_id:''
                },
                stock_positions:[]
            }
        },
        getNamePosition(position){
            const name = position.code + '-' + position.row + '-' + this.numberToLetter(position.column);
            return name;
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
        clickRemoveItem(index){

        },
        enabledSelectPosition(item){
            if(item.data_position.position!=null){
                return false;
            }else{
                return true;
            }
        },
        clickSelectPosition(item){
            this.item_selected = item;
            this.positionData = this.initModalDataPosition();
            if(item.item.position_data!=undefined){
                this.positionData.position_data = item.item.position_data;
            }
            
            this.positionData.item_data.item_id = item.item_id;
            this.positionData.item_data.item_name = item.item.description;
            this.positionData.item_data.quantity = parseInt(item.quantity);
            this.positionData.item_data.has_lot = item.item.lots_enabled;

            this.positionData.stock_positions = this.stock_positions;

            this.showDialogSelectPosition = true;
        },
        savePositionsData(data){
            this.stock_positions=data.stock_positions;
            this.item_selected.item.position_data = data.position_data;
            this.positionData = this.initModalDataPosition();
        },
        async saveChanges(){
            const response = await this.$http.post(`/purchases/updatePosition`, this.itemData);
            if(response.data.success){
                this.close();
                this.$message.success("Cambios registrados correctamente");
            }
        }
    }
}
</script>