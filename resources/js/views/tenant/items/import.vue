<template>
    <el-dialog :title="titleDialog" :visible="showDialog" @close="close" @open="create" class="dialog-import">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-12 form-group" :class="{'has-danger': errors.warehouse_id}">
                        <label for="warehouse">Almacén</label>
                        <el-select v-model="form.warehouse_id">
                            <el-option v-for="w in warehouses" :key="w.id" :label="w.description" :value="w.id"></el-option>
                        </el-select>
                        <small class="form-control-feedback" v-if="errors.warehouse_id" v-text="errors.warehouse_id[0]"></small>
                    </div>
                    <div class="col-12 form-group" :class="{'has-danger': errors.file}">
                        <el-upload
                                ref="upload"
                                :headers="headers"
                                action="/items/import"
                                :show-file-list="true"
                                :auto-upload="false"
                                :multiple="false"
                                :on-error="errorUpload"
                                :before-upload="onBeforeUpload"
                                :limit="1"
                                :data="form"
                                :on-success="successUpload">
                            <el-button slot="trigger" type="primary">Seleccione un archivo (xlsx)</el-button>
                        </el-upload>
                        <small class="form-control-feedback" v-if="errors.file" v-text="errors.file[0]"></small>
                    </div>
                    <div class="col-12 mt-4 mb-2">
                        <a class="text-dark mr-auto" href="/formats/items.xlsx" target="_new">
                            <span class="mr-2">Descargar formato de ejemplo para importar</span>
                            <i class="fa fa-download"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right mt-5">
                <el-button class="second-buton" @click.prevent="close()">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loading_submit">Procesar</el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>

    export default {
        props: ['showDialog'],
        data() {
            return {
                loading_submit: false,
                headers: headers_token,
                titleDialog: null,
                resource: 'items',
                errors: {},
                form: {},
                warehouses: []
            }
        },
        async created() {
            this.initForm();
            await this.onFetchTables();
        },
        methods: {
            onBeforeUpload(file) {},
            async onFetchTables() {
                this.loading_submit = true;
                await this.$http.get('/items/import/tables').then(response => {
                    this.warehouses = response.data.warehouses;
                }).finally(() => this.loading_submit = false);
            },
            initForm() {
                this.errors = {}
                this.form = {
                    warehouse_id: null
                }
            },
            create() {
                this.titleDialog = 'Importar Productos'
            },
            async submit() {
                // 1. Evitar múltiples clics si ya está cargando
                if (this.loading_submit) return;

                // 2. Validaciones previas
                if (!this.form.warehouse_id) {
                    this.$message.warning('Seleccione un almacén para poder continuar');
                    return;
                }

                if (this.$refs.upload.uploadFiles.length === 0) {
                    this.$message.warning('Seleccione un archivo xlsx');
                    return;
                }

                // 3. Bloquear el botón
                this.loading_submit = true;

                // 4. Disparar la subida (esto es asíncrono pero no devuelve promesa)
                this.$refs.upload.submit();

                // OJO: No pongas "this.loading_submit = false" aquí.
                // El botón se desbloqueará en successUpload() o errorUpload().
            },
            close() {
                this.$emit('update:showDialog', false)
                this.initForm()
            },
successUpload(response, file, fileList) {
    this.loading_submit = false;

    if (response.success) {
        // Si hay omitidos, usamos un formato de notificación más persistente
        if (response.data && response.data.skipped > 0) {
            this.$notify({
                title: 'Importación Completada',
                message: response.message,
                type: 'warning',
                duration: 0 // No se cierra solo para que el usuario lo lea bien
            });
        } else {
            this.$message.success(response.message);
        }

        this.$eventHub.$emit('reloadData')
        this.$eventHub.$emit('reloadTables')
        this.$refs.upload.clearFiles()
        this.close()
    } else {
        this.$message({
            message: response.message, 
            type: 'error',
            duration: 8000 // Más tiempo para errores de validación
        })
    }
},

errorUpload(error) {
    this.loading_submit = false;
    try {
        const res = JSON.parse(error.message);
        
        // Si el error viene de una validación de Laravel ($request->validate)
        if (res.errors) {
            const firstError = Object.values(res.errors)[0][0];
            this.$message.error(firstError);
        } else if (res.message) {
            this.$message.error(res.message);
        } else {
            this.$message.error('Error desconocido en el servidor.');
        }
    } catch (e) {
        this.$message.error('Error fatal: El archivo es muy pesado o el formato es incorrecto.');
    }
}
        }
    }
</script>
