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
        <h4>{{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} ROLL房3间</h4>
        <feather-icon icon="XIcon" @click.stop="isSidebarActiveLocal = false" class="cursor-pointer"></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>

    <component :is="scrollbarTag" class="scroll-area--data-list-add-new" :settings="settings" :key="$vs.rtl">

      <div class="p-6">

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

        <!-- 房间标题 -->
        <vs-input label="房间名称" v-model="roomInfo.room_name" class="mt-5 w-full"/>

        <!-- <vs-input label="房间号" v-model="roomInfo.room_num" class="mt-5 w-full"/> -->
        <vs-input label="描述" v-model="roomInfo.desc" class="mt-5 w-full"/>
        <vs-select  v-model="roomInfo.type" label="类型" class="mt-5 w-full"  v-validate="'required'" >
          <vs-select-item :key="item.value" :value="item.value" :text="item.text" v-for="item in roomInfoOption" />
        </vs-select>
        <vs-select v-model="roomInfo.condition_type" label="加入方式" class="mt-5 w-full"  v-validate="'required'">
          <vs-select-item :key="item.value" :value="item.value" :text="item.text" v-for="item in conditionOption" />
        </vs-select>

        <div v-if="roomInfo.condition_type == 1" >
          <vs-input label="房间密码" v-model="roomInfo.password" class="mt-5 w-full"/>
        </div>

        <div v-if="roomInfo.condition_type == 2">
          <vs-input label="充值金额" v-model="roomInfo.condition_charge" class="mt-5 w-full"/>
          <vs-input label="充值开始时间" v-model="roomInfo.condition_time" type="datetime-local" class="mt-5 w-full"/>
        </div>

        <div v-if="roomInfo.condition_type == 3">
          <vs-input label="房间密码" v-model="roomInfo.password" class="mt-5 w-full"/>
          <vs-input label="充值金额" v-model="roomInfo.condition_charge" class="mt-5 w-full"/>
          <vs-input label="充值开始时间" v-model="roomInfo.condition_time" type="datetime-local" class="mt-5 w-full"/>
        </div>

        <!-- 房间标题 -->
        <vs-input label="邀请码" v-model="roomInfo.invite_code" class="mt-5 w-full"/>

        <vs-input label="开奖时间" v-model="roomInfo.run_lottery_time" type="datetime-local" class="mt-5 w-full"/>




      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <!-- <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">提交</vs-button> -->
      <vs-button class="mr-6" @click="submitData">提交</vs-button>
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
      password:"",
      time:"",
      roomtitle:"",
      roomtext:"",
      roompirce:"",

      dataId: null,
      dataName: '',
      dataCategory: null,
      dataImg: null,
      dataOrder_status: 'pending',
      dataPrice: 0,

      settings: { // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: .60
      },
      roomInfo:{},
      roomInfoOption:[
        {text:'官方',value:'1'},
        {text:'主播',value:'2'}
      ],
      conditionOption:[
        {text:'口令',value:'1'},
        {text:'充值',value:'2'},
        {text:'口令 + 充值',value:'3'}
      ],
      roomFile:''
    }
  },
  watch: {
    "roomInfo.condition_type":{
      handler(val){
        if(val == 1){
          this.roomInfo.condition_charge = '';
          this.roomInfo.condition_time = '';
        }else if(val == 2){
          this.roomInfo.password = '';
        }
      },
      deep:true
    },
    isSidebarActive(val) {
      if (!val){
        this.roomInfo.condition_time = this.roomInfo.condition_time ? this.roomInfo.condition_time.replace("T"," ") : '';
        this.roomInfo.run_lottery_time = this.roomInfo.run_lottery_time ? this.roomInfo.run_lottery_time.replace("T"," ") : '';
        return
      };
      if (Object.entries(this.data).length === 0) {
        this.initValues()
        this.$validator.reset()
      } else {
        const { category, id, img, name, order_status, price,condition_time, run_lottery_time} = JSON.parse(JSON.stringify(this.data))
        this.dataId = id
        this.dataImg = img;
        this.roomInfo = this.data;
        this.roomInfo.condition_time = condition_time ? condition_time.replace(" ","T") : '';
        this.roomInfo.run_lottery_time = run_lottery_time ? run_lottery_time.replace(" ","T") : '';
        this.initValues();
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
      return !this.errors.any() && this.dataName && this.dataCategory && this.dataPrice > 0
    },
    scrollbarTag () { return this.$store.getters.scrollbarTag }
  },
  methods: {
    initValues () {
      if (this.data.id) return
      for(let key  in this.roomInfo){
        this.roomInfo[key] = ''
      }
    },
    submitData () {
      // this.$validator.validateAll().then(result => {
      //   if (result) {
          let _this = this;
          var forms = new FormData()
          forms.append('data',JSON.stringify(_this.roomInfo));
          forms.append('img_main',_this.roomFile);
          let url =  "/admin/Free/createRoom";
          if(_this.roomInfo.id>0){
            url =  "/admin/Free/editRoom";
          }
          _this.$axios({
            url: url,
            method: "post",
            data:forms,
            headers:{'Content-Type':'multipart/form-data'}
          }).then((res) => {
            console.log(res);
            if(res.data.status == 1){
              this.$emit('closeSidebar');
              this.$emit('get');
              this.initValues();
            }
          });


      //   }
      // })
    },
    updateCurrImg (input) {
      if (input.target.files && input.target.files[0]) {
        const reader = new FileReader()
        reader.onload = e => {
          this.dataImg = e.target.result
          this.roomFile = input.target.files[0];
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
