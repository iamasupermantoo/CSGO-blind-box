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
        <h4>{{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} 机器人</h4>
        <feather-icon icon="XIcon" @click.stop="isSidebarActiveLocal = false,robot={},imgFile=null" class="cursor-pointer"></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>

    <component :is="scrollbarTag" class="scroll-area--data-list-add-new" :settings="settings" :key="$vs.rtl">

       <!-- Product Image -->
        <template v-if="dataImg">
          <!-- Image Container -->
          <div class="img-container w-64 mx-auto flex items-center justify-center">
            <img :src="dataImg" alt="img" class="responsive">
          </div>

          <!-- Image upload Buttons -->
          <div class="modify-img flex justify-between mt-5">
            <input type="file" class="hidden" ref="updateImgInput" @change="updateCurrImg" accept="image/*">
            <vs-button class="mr-4" type="flat" @click="$refs.updateImgInput.click()">上传图片</vs-button>
            <vs-button type="flat" color="#999" @click="dataImg = null">删除图片</vs-button>
          </div>
        </template>
        <div class="upload-img mt-5" v-if="!dataImg">
          <input type="file" class="hidden" ref="uploadImgInput" @change="updateCurrImg" accept="image/*">
          <vs-button @click="$refs.uploadImgInput.click()">上传图片</vs-button>
        </div>

      <div class="p-6">
        <!-- NAME -->
        <vs-input label="姓名" v-model="robot.name" class="mt-5 w-full" name="item-name" v-validate="'required'" />

        <!-- NAME -->
        <vs-input label="手机号" v-model="robot.mobile" class="mt-5 w-full" name="item-name" v-validate="'required'" />

        <vs-input label="邮箱" v-model="robot.email" class="mt-5 w-full" name="item-name" v-validate="'required'" />

        <!-- NAME -->
        <vs-input label="余额" v-model="robot.total_amount" class="mt-5 w-full" name="item-name" v-validate="'required'" />

      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">提交</vs-button>
      <vs-button type="border" color="danger" @click="isSidebarActiveLocal = false,robot={},imgFile=null">取消</vs-button>
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

      dataId: null,
      dataName: '',
      dataCategory: null,
      dataImg: null,
      dataOrder_status: 'pending',
      dataPrice: 0,
      dataTime:"",

      category_choices: [
        {text:'消费', value:'消费'},
        {text:'工业', value:'工业'},
        {text:'军规', value:'军规'},
        {text:'受限', value:'受限'},
         {text:'保密', value:'保密'},
        {text:'隐秘', value:'隐秘'},
        {text:'特殊', value:'特殊'},
      ],

      order_status_choices: [
        {text:'Pending', value:'pending'},
        {text:'Canceled', value:'canceled'},
        {text:'Delivered', value:'delivered'},
        {text:'On Hold', value:'on_hold'}
      ],
      settings: { // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: .60
      },
      robot:{},
      imgFile:''
    }
  },
  watch: {
    isSidebarActive (val) {
      if (!val) return
      console.log(this.data);
      if (Object.entries(this.data).length === 0) {
        this.initValues()
        this.$validator.reset()
      } else {
        const { id, img, name, mobile, email,total_amount } = JSON.parse(JSON.stringify(this.data))
        this.robot.id = id;
        this.robot.name = name;
        this.dataImg = img;
        this.robot.mobile = mobile;
        this.robot.email = email;
        this.robot.total_amount = total_amount;
        // this.initValues()
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
      // return !this.errors.any() && this.dataName && this.dataCategory && this.dataPrice > 0
      return true
    },
    scrollbarTag () { return this.$store.getters.scrollbarTag }
  },
  methods: {
    initValues () {
      if (this.data.id) return
      this.robot = {};
    },
    submitData () {
      // this.$validator.validateAll().then(result => {
      //   if (result) {
        let _this = this;
        var forms = new FormData()
        forms.append('data',JSON.stringify(_this.robot));
        forms.append('img',_this.imgFile);
        let url =  "/admin/Robot/addRobot";
        if(_this.robot.id>0){
          url =  "/admin/Robot/edit";
        }
        _this.$axios({
          url: url,
          method: "post",
          data:forms,
          headers:{'Content-Type':'multipart/form-data'}
        }).then((res) => {
          console.log(res);
          if(res.data.status == 1){
            _this.$vs.notify({
              title: '提示',
              text: res.data.msg,
              iconPack: 'feather',
              icon: 'icon-alert-circle',
              color: 'success'
            })
            this.$emit('closeSidebar');
            this.$emit('get');
            this.initValues();
          }else{
            _this.$vs.notify({
              title: '提示',
              text: res.data.msg,
              iconPack: 'feather',
              icon: 'icon-alert-circle',
              color: 'danger'
            })
          }
        });
          return

          this.$emit('closeSidebar')
          this.initValues()
      //   }
      // })
    },
    updateCurrImg (input) {
      if (input.target.files && input.target.files[0]) {
        const reader = new FileReader()
        reader.onload = e => {
          this.dataImg = e.target.result;
          // this.robot.img = e.target.result;
          this.imgFile = input.target.files[0];
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
.upload-img{
  padding-left: 20px;
}
</style>
