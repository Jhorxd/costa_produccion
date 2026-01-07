 <template>
    <div class="card">
      <div class="card-header bg-info">
        <h3 class="my-0">Subir catálogo digemid en Xlsx</h3>
      </div>
      <div class="card-body">
        <form autocomplete="off" @submit.prevent="submit">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group" :class="{'has-danger': errors.token_false}">
                    <!--<el-checkbox v-model="form.token_false">Usar consulta interna</el-checkbox><br>-->
                    <div>
                        <el-upload
                                ref="upload"
                                :headers="headers"
                                name="file"
                                action="/configuration/items/import"
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
                    <small class="form-control-feedback" v-if="errors.token_false" v-text="errors.token_false[0]"></small>
                </div>
              </div>
              
            </div>
          </div>
          <div class="form-actions text-left pt-2">
            <a class="text-dark mr-auto" href="/formats/items_example.xlsx" target="_new">
                <span class="mr-2">Descargar formato ejemplo</span>
                <i class="fa fa-download"></i>
            </a>
          </div>
          <div class="form-actions text-right pt-2">
            <el-button type="primary" native-type="submit" :loading="loading_submit">Procesar</el-button>
          </div>
        </form>
      </div>
    </div>
</template>


<script>
export default {
  data() {
    return {
      loading_submit: false,
      headers: headers_token,
      resource: "configurations",
      errors: {},
      form: {},
    };
  },
  async created() {
    await this.initForm();

    await this.$http.get(`/${this.resource}/apiruc`).then(response => {
        this.form.url_apiruc = response.data.url_apiruc;
        this.form.token_apiruc = response.data.token_apiruc;

        if (this.form.token_apiruc == 'false') {
          this.form.token_false = true;
        }
    });
  },
  methods: {
    initForm() {
      this.errors = {};
      this.form = {
        url_apiruc: null,
        token_apiruc: null,
        token_false: false
      };
    },
    onBeforeUpload(){

    },
    successUpload(response, file, fileList) {
      console.log("response", response);
      if (response.success) {
          this.$message.success(response.message)
          this.$eventHub.$emit('reloadData')
          this.$eventHub.$emit('reloadTables')
          this.$refs.upload.clearFiles()
          this.close()
      } else {
          this.$message({message:response.message, type: 'error'})
      }
    },
    errorUpload(error) {
        console.log(error)
    },
    async submit() {
        this.loading_submit = true
        await this.$refs.upload.submit()
        this.loading_submit = false
    },
    /*submit() {
      this.loading_submit = true;
      if (this.form.token_false == true) {
        this.form.token_apiruc = 'false';
      }
      this.$http
        .post(`/${this.resource}`, this.form)
        .then(response => {
          if (response.data.success) {
            this.$message.success(response.data.message);
          } else {
            this.$message.error(response.data.message);
          }
        })
        .catch(error => {
          if (error.response.status === 422) {
            this.errors = error.response.data;
          } else {
            console.log(error);
          }
        })
        .then(() => {
          this.loading_submit = false;
        });
    },*/
  }
};
</script>

