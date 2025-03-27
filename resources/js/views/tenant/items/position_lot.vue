<template>
    <el-dialog
          :title="titleDialog"
          :visible="showDialog"
          @close="close"
          @open="create"
        >
        <h3>Lotes</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th>Stock</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(lot,index) in lots_temp" :key="lot.id">
                    <td>{{ index+1 }}</td>
                    <td>{{ lot.lots_group?lot.lots_group.code:'---' }}</td>
                    <td>{{ lot.lots_group?lot.lots_group.quantity:'---' }}</td>
                    <td>
                        <el-button type="primary" @click="LotSelected(lot)" >Seleccionar</el-button>
                    </td>
                </tr>
            </tbody>
        </table>
    </el-dialog>
</template>

<script>
    export default {
        props: [
            'showDialog',
            'box_selected',
            'lots'
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
            close() {
                this.$emit('update:showDialog', false);
            },
            async create() {
                console.log(this.box_selected);
                console.log(this.lots);
                
                this.lots_temp = Array.from(this.lots);
                console.log(this.lots_temp);
                
                if(this.box_selected.lots.length>0){
                    this.lots_selected = [...this.box_selected.lots];
                    console.log(this.lots_selected);
                    
                }
            },
            LotSelected(lot){
                console.log(lot);
                
            }
        }
    }
</script>