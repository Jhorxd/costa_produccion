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
                                <th v-if="lots_aux.length>0">Fecha de vencimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in lots_aux" :key="index" width="100%">
                                <td>
                                    <div class="form-group mb-2 mr-2"  >
                                        <el-input v-model="row.code" readonly></el-input>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group mb-2 mr-2">
                                        <el-input v-model="row.stock"></el-input>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group mb-2 mr-2" >
                                        <el-date-picker v-model="row.date_of_due" type="date" value-format="yyyy-MM-dd" :clearable="false" readonly></el-date-picker>
                                    </div>
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
        props: ['showDialog', 'lots'],
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
            async create(){
                this.lots_aux = this.lots;
            },
            deleteMoreLots(number){

                for (let i = 0; i < number; i++) {
                    this.lots_aux.pop();
                    this.$emit('addRowLot', this.lots_aux);
                }

            },
            validateLots() {
                for (const element of this.lots_aux) {
                    if (isNaN(parseInt(element.stock)) || parseInt(element.stock) <= 0 || element.stock === '') {
                        return { success: false, message: 'El campo stock debe ser un número positivo' };
                    }
                    element.stock = parseInt(element.stock);
                }
                return { success: true };
            },
            submit(){

                let val_lots = this.validateLots()
                if(!val_lots.success)
                    return this.$message.error(val_lots.message);

                this.$emit('save-data-lots', this.lots_aux);
                this.close();
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
