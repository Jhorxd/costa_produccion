<template>
    <el-dialog :title="titleDialog"
               width="50%"
               :visible="showDialog"
               @open="create"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               append-to-body
               :show-close="false">

        <div class="form-body" v-if="lots_aux">
            <div class="row" >
                <div class="col-lg-12 col-md-12">
                    <table width="100%">
                        <thead>
                            <tr width="100%">
                                <th v-if="lots_aux.length>0">Código</th>
                                <th v-if="lots_aux.length>0">Stock</th>
                                <th v-if="lots_aux.length>0">Estado</th>
                                <th v-if="lots_aux.length>0">Fecha de vencimiento</th>
                                <th width="15%"><a href="#" @click.prevent="clickAddLot" class="text-center font-weight-bold text-info">[+ Agregar]</a></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in lots_aux" :key="index" width="100%">
                                <td>
                                    <div class="form-group mb-2 mr-2"  >
                                        <el-input @blur="duplicateSerie(row.code, index)" v-model="row.code"></el-input>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group mb-2 mr-2">
                                        <el-input v-model="row.quantity"></el-input>
                                    </div>
                                </td>
                                 <td>
                                    <div class="form-group mb-2 mr-2"  >
                                        <el-select v-model="row.status">
                                            <el-option
                                                v-for="(option, index) in states"
                                                :key="index"
                                                :value="option.id"
                                                :label="option.description"
                                            ></el-option>
                                        </el-select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group mb-2 mr-2" >
                                        <el-date-picker v-model="row.date_of_due" type="date" value-format="yyyy-MM-dd" :clearable="false"></el-date-picker>
                                    </div>
                                </td>
                                <td class="series-table-actions text-center">
                                    <button  type="button" class="btn waves-effect waves-light btn-xs btn-danger" @click.prevent="clickCancel(index)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                <br>
                            </tr>
                        </tbody>
                    </table>


                </div>

            </div>
        </div>

        <div class="form-actions text-right pt-2">
            <el-button @click.prevent="close()">Cancelar</el-button>
            <el-button type="primary" @click="submit" >Guardar</el-button>
        </div>
    </el-dialog>
</template>

<script>
    export default {
        props: ['showDialog', 'lots', 'stock','recordId'],
        data() {
            return {
                titleDialog: 'Lotes',
                loading: false,
                errors: {},
                form: {},
                states: [],
                lots_aux: [],
            }
        },
        async created() {
        },
        methods: {
            async duplicateSerie(data, index)
            {

                let duplicates = await _.filter(this.lots_aux, {'series':data})
                if(duplicates.length > 1)
                {
                    this.lots_aux[index].series = ''
                }
            },
            async create(){
                if(this.recordId){
                    this.lots_aux = JSON.parse(JSON.stringify(this.lots));
                }
                if(this.lots_aux.length==0){
                    this.lots_aux.push({
                        item_id: null,
                        code: null,
                        quantity: this.stock,
                        date_of_due:  moment().format('YYYY-MM-DD'),
                        status: 1
                    });
                }
                if (this.states.length === 0) { // Carga solo si states está vacío
                    await this.$http.get(`/inventoryStates`).then((response) => {
                        const response_data = response.data;
                        if (response_data.success) {
                            this.states = response_data.data;
                        }
                    });
                }
            },
            clickAddLot() {
                this.lots_aux.push({
                    item_id: null,
                    code: null,
                    quantity: 0,
                    date_of_due:  moment().format('YYYY-MM-DD'),
                    status: 1
                });
            },
            deleteMoreLots(number){

                for (let i = 0; i < number; i++) {
                    this.lots_aux.pop();
                    this.$emit('addRowLot', this.lots_aux);
                }

            },
            validateLots() {
                for (const element of this.lots_aux) {
                    if (element.code == null || element.code.trim() === '') {
                    return { success: false, message: 'El campo Código es obligatorio' };
                    } else if (isNaN(parseInt(element.quantity)) || parseInt(element.quantity) <= 0 || element.quantity === '') {
                    return { success: false, message: 'El campo stock debe ser un número positivo' };
                    }
                }
                return { success: true };
            },
            submit(){

                let val_lots = this.validateLots()
                if(!val_lots.success)
                    return this.$message.error(val_lots.message);

                this.$emit('addRowLot', this.lots_aux);
                this.close();
            },
            
            clickCancel(index) {
                /* if(this.recordId){
                    if(this.lots_aux[index].id!=undefined){
                        this.lots_aux[index].deleted = true;
                    }else{
                        
                    }
                }else{
                    this.lots_aux.splice(index, 1);
                } */
                this.lots_aux.splice(index, 1);
            },
            close() {
                this.$emit('update:showDialog', false);
                this.resetData();
            },
            resetData() {
                this.lots_aux = [];
                this.states = [];
            },
        }
    }
</script>
