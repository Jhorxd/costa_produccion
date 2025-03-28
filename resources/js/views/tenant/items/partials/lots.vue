<template>
    <el-dialog :title="titleDialog"
               width="30%"
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
                            <tr v-for="(row, index) in lots_aux.filter(row => row.deleted == false)" :key="index" width="100%">
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
            <el-button @click.prevent="clickCancelSubmit()">Cancelar</el-button>
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
                states: [{id:1, description:'Activo'}, {id:2, description:'Inactivo'}],
                lots_aux: [],

            }
        },
        async created() {
            
            // await this.$http.get(`/pos/payment_tables`)
            //     .then(response => {
            //         this.payment_method_types = response.data.payment_method_types
            //         this.cards_brand = response.data.cards_brand
            //         this.clickAddLot()
            //     })
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
            create(){

                if(this.recordId){
                    this.lots_aux = JSON.parse(JSON.stringify(this.lots));
                    this.lots_aux.forEach(element => {
                        element.deleted=false;
                    });                    
                    console.log(this.lots_aux);
                    
                }else{
                    if(this.lots_aux.length==0){
                        this.lots_aux.push({
                            item_id: null,
                            code: null,
                            quantity: this.stock,
                            date_of_due:  moment().format('YYYY-MM-DD'),
                            status: 1,
                            deleted: false
                        });
                    }
                }
            },
            /* addMoreLots(number){

                for (let i = 0; i < number; i++) {
                    this.clickAddLot()
                }

            }, */
            clickAddLot() {
                /* if(!this.recordId){
                    if(this.lots_aux.length >= this.stock)
                        return this.$message.error('La cantidad de registros es superior al stock o cantidad');
                } */


                this.lots_aux.push({
                    item_id: null,
                    code: null,
                    quantity: 0,
                    date_of_due:  moment().format('YYYY-MM-DD'),
                    status: 1,
                    deleted: false
                });

                //this.$emit('addRowLot', this.lots_aux);
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
                if(this.recordId){
                    if(this.lots_aux[index].id!=undefined){
                        this.lots_aux[index].deleted = true;
                    }else{
                        this.lots_aux.splice(index, 1);
                    }
                }else{
                    this.lots_aux.splice(index, 1);
                }
               // item.deleted = true
                //this.$emit('addRowLot', this.lots_aux);
            },

            async clickCancelSubmit() {
                await this.$emit('update:showDialog', false)
            },
            close() {
                this.$emit('update:showDialog', false)
            },
        }
    }
</script>
