<template>
    <el-dialog :visible="showDialog" @open="create" width="30%" :title="titleDialog"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false">
    
        <div class="text-center padding_text">
            <p>¿Estas seguro que deseas cambiar a un precio menor al configurado?</p>
        </div>
        <span slot="footer" class="dialog-footer">
            <template>
                <el-button @click="clickCancel">Cancelar</el-button>
                <el-button type="primary" @click="clickConfirm">Confirmar</el-button>
            </template>
        </span>
    </el-dialog>
</template>

<script>

    export default {
        props: ['showDialog'],
        data() {
            return {
                titleDialog: 'Confirmar cambio de precio menor',
            }
        },
        methods: {
            create() {
                //
            },
          
            clickCancel() {
                this.$emit('responseModal',false);
                this.clickClose();
            },
            clickConfirm() {
                this.$emit('responseModal',true);
                this.clickClose()
            },
            clickClose() {
                this.$emit('update:showDialog', false);
            },
        }
    }
</script>
<style>
    .padding_text {
        padding: 0px 10px;
        word-wrap: break-word;
    }
</style>