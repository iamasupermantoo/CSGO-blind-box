<!-- =========================================================================================
  File Name: UserEditTabInformation.vue
  Description: User Edit Information Tab content
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->

<template>
  <div id="user-edit-tab-info">
    <!-- Avatar Row -->
    <p class="text-lg font-medium mb-2 mt-4 sm:mt-0">
              {{ data.name }}
            </p>
    <div class="vx-row imgs-1">
      
      <div class="vx-col w-full">
        <div class="flex items-start flex-col sm:flex-row">
          <img v-if="img_main" :src="img_main" class="mr-8 rounded h-24 w-24 img_main" />
          <!-- <vs-avatar :src="data.avatar" size="80px" class="mr-4" /> -->
          <div>
            
            <input
              type="file"
              class="hidden"
              ref="update_main_img_input"
              @change="update_main_img"
              accept="image/*"
            />
            <vs-button class="mr-4 mb-4 change" type="border" @click="$refs.update_main_img_input.click()" >更换主图</vs-button>
          </div>
        </div>
      </div>
      

       <div class="vx-col w-full">
        <div class="flex items-start flex-col sm:flex-row">
          <img v-if="img_active" :src="img_active" class="mr-8 rounded h-24 w-24 img_active"/>

          <div>
            <input
              type="file"
              class="hidden"
              ref="update_active_img_input"
              @change="update_active_img"
              accept="image/*"
            />
            <vs-button class="mr-4 mb-4 change" type="border" @click="$refs.update_active_img_input.click()" >更换动图</vs-button>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Row -->
    <div class="vx-row box_input">
      <!-- <div class="vx-col md:w-1/2 w-full"> -->
        <!-- <vs-input
          class="w-full mt-4"
          label="名称"
          v-model="data_local.name"
          v-validate="'required|name'"
          name="name"
        /> -->

        <div class="w-full mt-4 edit">
          <vs-input
            class="w-full mt-4"
            label="名称"
            v-model="data_local.name"
            name="name"
          />
          <span class="text-danger text-sm" v-show="errors.has('name')">{{ errors.first("name") }}</span>
        </div>
        

        
        <div class="w-full mt-4 edit">
          <vs-input
            class="w-full mt-4"
            label="价格"
            v-model="data_local.price"
            name="price"
          />
        </div>
        <span class="text-danger text-sm" v-show="errors.has('price')">{{ errors.first("price") }}</span>
        

        <div class="w-full mt-4 edit">
          <label class="text-sm opacity-75">盲盒类型</label>
          <v-select
            :options="boxTypeOptions"
            :clearable="false"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            v-model="boxTypeFilter"
            class="mb-4 md:mb-0"
          />
        </div>

        <div class="w-full mt-4 edit">
          <label class="text-sm opacity-75">是否为对战</label>
          <v-select
            :options="battleOptions"
            :clearable="false"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            v-model="battleFilter"
            class="mb-4 md:mb-0"
          />
          <span class="text-danger text-sm" v-show="errors.has('email')">{{
            errors.first("email")
          }}</span>
        </div>

        <div class="w-full mt-4 edit">
          <label class="text-sm opacity-75">稀有度</label>
          <v-select
            :options="roleOptions"
            :clearable="false"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            v-model="roleFilter"
            class="mb-4 md:mb-0"
          />
        </div>

        <div class="w-full mt-4 edit">
          <label class="text-sm opacity-75">状态</label>
          <v-select
            :options="roleOptions1"
            :clearable="false"
            :dir="$vs.rtl ? 'rtl' : 'ltr'"
            v-model="roleFilter1"
            class="mb-4 md:mb-0"
          />
        </div>

        <div class="w-full mt-4 edit">
          <vs-input
            class="w-full mt-4"
            label="普通自动补充库存比(%)"
            v-model="data_local.mendBillie"
            name="mendBillie"
          />
        </div>
        

        <div class="w-full mt-4 edit">
          <vs-input
            class="w-full mt-4"
            label="Vip自动补充库存比(%)"
            v-model="data_local.mendVipBillie"
            name="mendVipBillie"
          />
        </div>
      <!-- </div> -->
    </div>

    <div class="vx-row box_input">

      <div class="w-full mt-4 edit" v-for="(item,index) in skins_type" :key="index">
        <vs-input
          class="w-full mt-4"
          :label="item.name+'概率(%)'"
          v-model="item.probability"
          name="mendVipBillie"
        />
      </div>

      <div class="w-full mt-4 edit">
        <vs-input
          class="w-full mt-4"
          label="创建时间"
          v-model="data_local.create_time"
          name="create_time"
        />
        <span class="text-danger text-sm" v-show="errors.has('create_time')">{{
          errors.first("create_time")
        }}</span>
      </div>

    </div>
    <!-- Save & Reset Button -->
    <div class="vx-row">
      <div class="vx-col w-full">
        <div class="mt-8 flex flex-wrap items-center justify-end">
          <vs-button
            class="ml-auto mt-2"
            @click="save_changes"
            :disabled="!validateForm"
            >保存</vs-button
          >
          <!-- <vs-button
            class="ml-4 mt-2"
            type="border"
            color="warning"
            @click="reset_data"
            >重置</vs-button
          > -->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { AgGridVue } from "ag-grid-vue";
import vSelect from "vue-select";

export default {
  components: {
    vSelect,
    AgGridVue,
  },
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      date: "2020-12-28",
      // Filter Options
      roleFilter:{},
      roleOptions: [
        // { label: "消费", value: "1" },
        // { label: "工业", value: "2" },
        // { label: "军规", value: "3" },
        // { label: "受限", value: "4" },
        // { label: "保密", value: "5" },
        // { label: "隐秘", value: "6" },
        // { label: "特殊", value: "7" },
      ],
      boxTypeOptions:[],
      boxTypeFilter:{},
      roleFilter1: { label: "正常", value: "1" },
      roleOptions1: [
        { label: "正常", value: "1" },
        { label: "禁用", value: "2" }
      ],
      battleOptions:[
        { label: "是", value: "1" },
        { label: "否", value: "0" }
      ],
      battleFilter:{},
      data_local: JSON.parse(JSON.stringify(this.data)),
      img_main:'',
      img_active:'',
      img_main_file:'',
      img_active_file:'',
      boxType:'',
      skins_type:this.data.skins_type,//
    };
  },
  computed: {
    status_local: {
      get() {
        return {
          label: this.capitalize(this.data_local.status),
          value: this.data_local.status,
        };
      },
      set(obj) {
        this.data_local.status = obj.value;
      },
    },
    role_local: {
      get() {
        return {
          label: this.capitalize(this.data_local.role),
          value: this.data_local.role,
        };
      },
      set(obj) {
        this.data_local.role = obj.value;
      },
    },
    validateForm() {
      return !this.errors.any();
    },
  },
  methods: {
    getBoxRarity() {
      let _this = this;
      _this.$axios({
          url: "/admin/Box/boxRarity",
          method: "post",
          params: {},
        }).then((res) => {
          res.data.data.forEach((element) => {
            element["value"] = element["id"];
            element["label"] = element["rarity_name"];
          });
          _this.roleOptions = res.data.data;
        });
    },

    getBoxType(){
      let _this = this;
      _this.$axios({
          url: "/admin/Box/getBoxType",
          method: "post",
          params: {},
        }).then((res) => {
          res.data.data.forEach((element) => {
            element["value"] = element["id"];
            element["label"] = element["type_name"];
            // console.log(element['id']);
            if(_this.boxType == element["id"]){
                _this.boxTypeFilter = element;
            }
          });
          _this.boxTypeOptions = res.data.data;
        });
    },

    capitalize(str) {
      return str.slice(0, 1).toUpperCase() + str.slice(1, str.length);
    },
    save_changes() {
       // if (!this.validateForm) return;
      let _this = this;
      console.log(_this.skins_type);
      let pramas = {
        'box_id':_this.data_local.id,
        'name':_this.data_local.name,
        'img_main':_this.data_local.img_main,
        'img_active':_this.data_local.img_active,
        'price':_this.data_local.price,
        'rarity':_this.roleFilter.value,
        'flag':_this.roleFilter1.value,
        'type':_this.boxTypeFilter.value,
        'battle':_this.battleFilter.value,
        'billie':_this.data_local.mendBillie,
        'vip_billie':_this.data_local.mendVipBillie,
        'skins_type':_this.skins_type
      };
      var forms = new FormData()
      forms.append('data',JSON.stringify(pramas));
      forms.append('img_main',_this.img_main_file);
      forms.append('img_active',_this.img_active_file);
      _this.$axios({
          url: "/admin/Box/editBox",
          method: "post",
          data:forms,
          headers:{'Content-Type':'multipart/form-data'}
        }).then((res) => {
          if(res.data.status == 1){
            _this.$vs.notify({
              title: '提示',
              text: res.data.msg,
              iconPack: 'feather',
              icon: 'icon-alert-circle',
              color: 'success'
            })
            _this.$emit('reload');
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
    },
    reset_data() {
      this.data_local = JSON.parse(JSON.stringify(this.data));
    },
    update_main_img($e) {
      console.log($e);
      let file = $e.target.files[0];
      console.log(file);
      this.img_main_file = file;
      var dataURL;
      var windowURL = window.URL || window.webkitURL;
      dataURL = windowURL.createObjectURL(file);
      this.img_main = dataURL;
      // You can update avatar Here
      // For reference you can check dataList example
      // We haven't integrated it here, because data isn't saved in DB
    },
    update_active_img($e) {
      console.log($e);
      let file = $e.target.files[0];
      this.img_active_file = file;
      var dataURL;
      var windowURL = window.URL || window.webkitURL;
      dataURL = windowURL.createObjectURL(file);
      this.img_active = dataURL;
    },
  },
  mounted(){
    let _this = this;
    _this.getBoxRarity();
    _this.getBoxType();
    _this.roleFilter = _this.data.roleFilter;
    _this.boxType = _this.data.type;
    _this.roleFilter1  = (_this.data.flag == 1) ? { label: "正常", value: "1" } : { label: "禁用", value: "0" };
    _this.battleFilter = (_this.data.battle == 1) ? { label: "是", value: "1" } : { label: "否", value: "0" };
    _this.img_main = _this.data.img_main;
    _this.img_active = _this.data.img_active
    // console.log(_this.data);
  }
};
</script>

<style>
.img_active, .img_main{
  object-fit: cover;
}
.imgs-1{
  display: flex;
}
.imgs-1 .w-full{
  width:max-content!important;
}
.change{
  margin-top: 20px;
}
.box_input{
  justify-content: flex-start;
}
.box_input .edit{
  width:calc(50% - 20px)!important;
  margin: 0 10px 0 10px;
}
.edit .w-full {
  margin-top: 0!important;
}

.box_input_1{
  width:100%;
  justify-content: flex-start;
  /* margin-left: 20px; */
}
.box_input_1 .edit-1{
    width: calc(50% - 20px)!important;
    float: left;
    margin-top: 0!important;
}
</style>
