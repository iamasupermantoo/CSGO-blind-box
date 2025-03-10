<!-- =========================================================================================
  File Name: AddNewDataSidebar.vue
  Description: Add New Data - Sidebar component
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->


<template>
  <vs-sidebar click-not-close position-right parent="body" default-index="1" color="primary" class="add-new-data-sidebar items-no-padding" spacer v-model="isSidebarActiveLocal">
    <div class="mt-6 flex items-center justify-between px-6">
        <h4>{{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} 支付配置</h4>
        <feather-icon icon="XIcon" @click.stop="isSidebarActiveLocal = false" class="cursor-pointer"></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>

    <component :is="scrollbarTag" class="scroll-area--data-list-add-new" :settings="settings" :key="$vs.rtl">

      <div class="p-6">

    
        <!-- 备注 -->
        <vs-input label="备注" v-model="remarks" class="mt-5 w-full" name="item-name"/>

        <!-- appid -->
        <vs-input label="appid" v-model="app_id" class="mt-5 w-full" name="item-name" v-validate="'required'" />
      
        <!-- 私钥 -->
        <vs-input label="私钥" v-model="private_key" class="mt-5 w-full" name="item-name" v-validate="'required'" />

         <!-- 公钥 -->
        <vs-input label="公钥" v-model="public_key" class="mt-5 w-full" name="item-name" v-validate="'required'" />

        <!-- <vs-select label="状态" class="mt-5 w-full" v-model="status">
          <vs-select-item key="0" value="0" text="关闭"/>
          <vs-select-item key="1" value="1" text="启用"/>
        </vs-select> -->
      
      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">保存</vs-button>
      <vs-button type="border" color="danger" @click="isSidebarActiveLocal = false">关闭</vs-button>
    </div>
  </vs-sidebar>
</template>

<script>
import VuePerfectScrollbar from 'vue-perfect-scrollbar'

export default {
  props: {
    isSidebarActive: {
      type: Boolean,
      required: true
    },
    data: {
      type: Object,
      default: () => {}
    }
  },
  components: {
    VuePerfectScrollbar
  },
  data () {
    return {
      dataId:null,
      remarks:'',
      app_id:'',
      private_key:'',
      public_key:'',
      status:'',
      type:'alipay',
      settings: { // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: .60
      }
    }
  },
  watch: {
    isSidebarActive (val) {
      if (!val) return
      if (Object.entries(this.data).length === 0) {
        this.initValues()
        this.$validator.reset()
      } else {
        const { id, remarks, app_id, private_key, public_key,status } = JSON.parse(JSON.stringify(this.data))
        this.dataId = id
        this.remarks = remarks
        this.app_id = app_id
        this.private_key = private_key
        this.public_key = public_key
        this.status = status
        this.initValues()
      }
      // Object.entries(this.data).length === 0 ? this.initValues() : { this.dataId, this.dataName, this.dataCategory, this.dataOrder_status, this.dataPrice } = JSON.parse(JSON.stringify(this.data))
    }
  },
  computed: {
    isSidebarActiveLocal: {
      get () {
        return this.isSidebarActive
      },
      set (val) {
        if (!val) {
          this.$emit('closeSidebar')
          // this.$validator.reset()
          // this.initValues()
        }
      }
    },
    isFormValid () {
      // return !this.errors.any() && this.app_id && this.private_key && this.public_key > 0
      return this.app_id && this.private_key && this.public_key
    },
    scrollbarTag () { return this.$store.getters.scrollbarTag }
  },
  methods: {
    initValues () {
      if (this.data.id) return
      this.dataId = null
      this.remarks = ''
      this.app_id = ''
      this.private_key = ''
      this.public_key = ''
      this.status = ''
    },
    submitData () {
      this.$validator.validateAll().then(result => {
        if (result) {
          const obj = {
            id: this.dataId,
            remarks: this.remarks,
            app_id: this.app_id,
            private_key: this.private_key,
            public_key: this.public_key,
            status: this.status,
            type:this.type
          }

          this.$axios({
            url:'/admin/Recharge/savePayInfo',
            method: "post",
            data:obj
          }).then(res=>{
            if(res.data.status == 1){
               this.$vs.notify({
                title: '提示',
                text: res.data.msg,
                iconPack: 'feather',
                icon: 'icon-alert-circle',
                color: 'success'
              })
            }else{
              this.$vs.notify({
                title: '提示',
                text: res.data.msg,
                iconPack: 'feather',
                icon: 'icon-alert-circle',
                color: 'danger'
              })
            }
          })
          this.$emit('closeSidebar')
          this.initValues()
        }
      })
    },
    updateCurrImg (input) {
      if (input.target.files && input.target.files[0]) {
        const reader = new FileReader()
        reader.onload = e => {
          this.dataImg = e.target.result
        }
        reader.readAsDataURL(input.target.files[0])
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.add-new-data-sidebar {
  ::v-deep .vs-sidebar--background {
    z-index: 52010;
  }

  ::v-deep .vs-sidebar {
    z-index: 52010;
    width: 400px;
    max-width: 90vw;

    .img-upload {
      margin-top: 2rem;

      .con-img-upload {
        padding: 0;
      }

      .con-input-upload {
        width: 100%;
        margin: 0;
      }
    }
  }
}

.scroll-area--data-list-add-new {
    // height: calc(var(--vh, 1vh) * 100 - 4.3rem);
    height: calc(var(--vh, 1vh) * 100 - 16px - 45px - 82px);

    &:not(.ps) {
      overflow-y: auto;
    }
}
</style>
