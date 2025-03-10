
<template>
  <div id="user-edit-tab-info">
    <!-- Avatar Row -->
    <div class="vx-row">
      <div class="vx-col w-full">
        <div class="flex items-start flex-col sm:flex-row">
          <img :src="data.img" class="mr-8 rounded h-24 w-24" />
          <!-- <vs-avatar :src="data.avatar" size="80px" class="mr-4" /> -->
          <div style="margin-top: 25px;">
            <input
              type="file"
              class="hidden"
              ref="update_avatar_input"
              @change="update_avatar"
              accept="image/*"
            />

            <!-- Toggle comment of below buttons as one for actual flow & currently shown is only for demo -->
            <!-- <vs-button class="mr-4 mb-4">更换头像</vs-button> -->
            <vs-button class="mr-4 mb-4" @click="$refs.update_avatar_input.click()">更换头像</vs-button>
            <!-- <vs-button type="border" color="danger">Remove Avatar</vs-button> -->
          </div>
        </div>
      </div>
    </div>

    <!-- Content Row -->
    <div class="vx-row">
      <div class="vx-col md:w-1/2 w-full">
        <vs-input
          class="w-full mt-4"
          label="昵称"
          v-model="data_local.name"
          v-validate="'required|alpha_num'"
          name="data_local.name"
          disabled
        />
        <span class="text-danger text-sm" v-show="errors.has('username')">{{
          errors.first("username")
        }}</span>

        <vs-input
          class="w-full mt-4"
          label="邀请码"
          v-model="data_local.invite_code"
          type="email"
          name="data_local.invite_code"
        />

        <vs-select
          v-model="data_local.group"
          label="用户身份"
          class="mt-4 w-full"
          name="item-category"
        >
          <vs-select-item
            :key="item.value"
            :value="item.value"
            :text="item.label"
            v-for="item in groupOptions"
          />
        </vs-select>

        <vs-select
          v-model="data_local.allow"
          label="取回状态"
          class="mt-4 w-full"
          name="item-category"
        >
          <vs-select-item
            :key="item.value"
            :value="item.value"
            :text="item.label"
            v-for="item in allowOptions"
          />
        </vs-select>

        <vs-select
          v-model="data_local.union"
          label="工会名称"
          class="mt-4 w-full"
          name="item-category"
        >
        <vs-select-item key="0" value="0" text="请选择" checked disabled/>
          <vs-select-item
            :key="item.id"
            :value="item.id"
            :text="item.name"
            v-for="item in unionOptions"
          />
        </vs-select>

         <vs-select
          v-model="data_local.anchor"
          label="是否为主播"
          class="mt-4 w-full"
          name="item-category"
        >
          <vs-select-item
            :key="item.value"
            :value="item.value"
            :text="item.label"
            v-for="item in anchorOptions"
          />
        </vs-select>

        <!-- <span class="text-danger text-sm" v-show="errors.has('invite_code')">{{
          errors.first("invite_code")
        }}</span> -->
      </div>

      <div class="vx-col md:w-1/2 w-full">
        <vs-input
          class="w-full mt-4"
          label="邮箱"
          v-model="data_local.email"
          type="email"
          v-validate="'required'"
          name="email"
          disabled
        />
        <span class="text-danger text-sm" v-show="errors.has('email')">{{
          errors.first("email")
        }}</span>

        <vs-input
          class="w-full mt-4"
          label="余额"
          v-model="data_local.total_amount"
          v-validate="'required'"
          name="total_amount"
        />
        <!-- <span class="text-danger text-sm" v-show="errors.has('total_amount')">{{
          errors.first("total_amount")
        }}</span> -->
		
		<vs-input
		  class="w-full mt-4"
		  label="收货人"
		  v-model="data_local.lxpeople"
		  name="lxpeople"
		/>
		<span class="text-danger text-sm" v-show="errors.has('lxpeople')">{{
		  errors.first("lxpeople")
		}}</span>
		
		<vs-input
		  class="w-full mt-4"
		  label="联系电话"
		  v-model="data_local.lxdh"
		  name="lxdh"
		/>
		<span class="text-danger text-sm" v-show="errors.has('lxdh')">{{
		  errors.first("lxdh")
		}}</span>
		
        <vs-input
          class="w-full mt-4"
          label="收货地址"
          v-model="data_local.tradeUrl"
          name="tradeUrl"
        />
        <span class="text-danger text-sm" v-show="errors.has('tradeUrl')">{{
          errors.first("tradeUrl")
        }}</span>
		


        <!-- <vs-select
          v-model="data_local.group_vip"
          label="是否Vip用户"
          class="mt-5 w-full"
          name="item-category"
        >
          <vs-select-item
            :key="item.value"
            :value="item.value"
            :text="item.label"
            v-for="item in vipOptions"
          />
        </vs-select> -->

        <vs-input
          class="w-full mt-4"
          label="下线充值额外加赠(%)"
          v-model="data_local.extra_gift_recharge"
          name="extra_gift_recharge"
          v-if="data_local.group == 1"
        />
        <span class="text-danger text-sm" v-show="errors.has('extra_gift_recharge')">{{
          errors.first("extra_gift_recharge")
        }}</span>


         <vs-input
          class="w-full mt-4"
          label="创建时间"
          draggable="true"
          v-model="data_local.create_time"
          v-validate="'alpha_spaces'"
          name="create_time"
          disabled
        />
        <span class="text-danger text-sm" v-show="errors.has('create_time')">{{
          errors.first("create_time")
        }}</span>

        <vs-input
          class="w-full mt-4"
          label="上级用户昵称"
          v-model="data_local.inviter_name"
          name="inviter_name"
          disabled
        />
      </div>
    </div>



    <!-- Permissions -->
    <!-- <vx-card class="mt-base" no-shadow card-border>
      <div class="vx-row">
        <div class="vx-col w-full">
          <div class="flex items-end px-3">
            <feather-icon svgClasses="w-6 h-6" icon="LockIcon" class="mr-2" />
            <span class="font-medium text-lg leading-none">已额外设置概率</span>
          </div>
          <vs-divider />
        </div>
      </div>

      <div class="block overflow-x-auto">
        <ag-grid-vue


          class="ag-theme-material w-100 my-4 ag-grid-table "
          :columnDefs="columnDefs"
          :defaultColDef="defaultColDef"
          :rowData="usersData"
          rowSelection="multiple"
          colResizeDefault="shift"
          :animateRows="false"
          :floatingFilter="false"
          :pagination="false"
          :suppressPaginationPanel="false"
        >
        </ag-grid-vue>
      </div>
    </vx-card> -->

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
      file:'',
      player_id:'',
      data_local: JSON.parse(JSON.stringify(this.data)),

      statusOptions: [
        { label: "Active", value: "active" },
        { label: "Blocked", value: "blocked" },
        { label: "Deactivated", value: "deactivated" },
      ],
      roleOptions: [
        { label: "Admin", value: "admin" },
        { label: "User", value: "user" },
        { label: "Staff", value: "staff" },
      ],
      groupOptions:[
        { label: "普通", value: "0" },
        { label: "概率组", value: "1" },
        { label: "VIP", value: "2" },
      ],
      vipOptions:[
        { label: "是", value: "1" },
        { label: "否", value: "0" },
      ],
      allowOptions:[
        { label: "允许", value: "1" },
        { label: "禁止", value: "2" },
      ],
      anchorOptions:[
        { label: "是", value: "1" },
        { label: "否", value: "0" },
      ],
      unionOptions:[],

      //表格
      defaultColDef: {
        sortable: false,
        resizable: false,
        suppressMenu: false,
      },
      usersData:[{id:1,role:"20%",status:"删除",username:"111"}],

      columnDefs: [
        {
          headerName: "箱子名称",
          field: "id",
          width: 125,
          filter: true,
        },
        {
          headerName: "箱子图片",
          field: "username",
          filter: true,
          width: 300,
        },
        {
          headerName: "饰品名称",
          field: "email",
          filter: true,
          width: 225,
        },
        {
          headerName: "饰品图片",
          field: "name",
          filter: true,
          width: 300,
        },
        {
          headerName: "饰品等级",
          field: "country",
          filter: true,
          width: 150,
        },
        {
          headerName: "额外概率",
          field: "role",
          filter: true,
          width: 150,
        },
        {
          headerName: "操作",
          field: "status",
          filter: true,
          width: 150,
         // cellRendererFramework: "CellRendererStatus",
        },
      ],
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
  watch:{
    'file':{
      handler(val){
        if(val){
          this.edit()
        }
      }
    }
  },
  methods: {
    getUnion(){
      let _this = this;
      _this.$axios({
        url: '/admin/User/getUnion',
        method: "post"
      }).then(res=>{
        console.log(res);
        if(res.data.status == 1){
          _this.unionOptions = res.data.data.list;
          console.log(_this.unionOptions);
        }else{
          _this.unionOptions = [];
        }
      })
    },
    edit(){
      let _this = this;
      var form = new FormData();
      form.append('id',_this.player_id);
      form.append('file',_this.file);
      _this.$axios({
            url: '/admin/User/editHeadImage',
            method: "post",
            headers:{'Content-Type':'multipart/form-data'},
            data:form,
        }).then((res) => {
            if(res.data.status == 1){
                _this.$vs.notify({
                title: '提示',
                text: res.data.msg,
                iconPack: 'feather',
                icon: 'icon-alert-circle',
                color: 'success'
                })
                this.$emit("closeSidebar");
                this.$emit("reload");
                // this.initValues();
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
    capitalize(str) {
      return str.slice(0, 1).toUpperCase() + str.slice(1, str.length);
    },
    save_changes() {
      //只修改用户是否为概率组和VIP
      // console.log(this.data_local.group);
      let _this = this;
      _this.data.allow = _this.data_local.allow;
      _this.data.group = _this.data_local.group;
      _this.data.group_vip = _this.data_local.group_vip;
      _this.data.invite_code = _this.data_local.invite_code;
      _this.data.tradeUrl = _this.data_local.tradeUrl;
	  _this.data.lxdh = _this.data_local.lxdh;
	  _this.data.lxpeople = _this.data_local.lxpeople;
      _this.data.extra_gift_recharge = _this.data_local.extra_gift_recharge;
      _this.data.union = _this.data_local.union;
      _this.data.anchor = _this.data_local.anchor;
      _this.$axios({
            url: '/admin/User/editGroup',
            method: "post",
            data:{
              player_id:_this.player_id,
              group:_this.data_local.group,
              group_vip:_this.data_local.group_vip,
              invite_code:_this.data.invite_code,
              allow:_this.data_local.allow,
              tradeUrl:_this.data_local.tradeUrl,
			  lxdh:_this.data_local.lxdh,
			  lxpeople:_this.data_local.lxpeople,
              extra_gift_recharge:_this.data_local.extra_gift_recharge,
              union:_this.data_local.union,
              anchor:_this.data_local.anchor,
              total_amount: _this.data_local.total_amount
            },
        }).then((res) => {
            if(res.data.status == 1){
              _this.data.group = _this.data_local.group;
                _this.$vs.notify({
                title: '提示',
                text: res.data.msg,
                iconPack: 'feather',
                icon: 'icon-alert-circle',
                color: 'success'
                })
                this.$emit("closeSidebar");
                this.$emit("reload");
                // this.initValues();
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
    update_avatar(input) {
      if (input.target.files && input.target.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.data.img = e.target.result;
        };
        reader.readAsDataURL(input.target.files[0]);
        this.file = input.target.files[0];
      }
    },
  },
  mounted(){
    this.player_id = this.data.id;
    this.getUnion();
  }
};
</script>
<style lang="scss">
.rounded{
  object-fit: cover;
}
</style>
