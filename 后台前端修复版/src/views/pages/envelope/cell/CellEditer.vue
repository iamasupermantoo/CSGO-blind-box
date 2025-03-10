<template>
  <vs-sidebar
    click-not-close
    position-right
    parent="body"
    default-index="1"
    color="primary"
    class="add-new-data-sidebar items-no-padding"
    spacer
    v-model="isSidebarActiveLocal"
  >
    <div class="mt-6 flex items-center justify-between px-6">
      <h4>
        {{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} 红包
      </h4>
      <feather-icon
        icon="XIcon"
        @click.stop="isSidebarActiveLocal = false"
        class="cursor-pointer"
      ></feather-icon>
    </div>
    <!-- <vs-divider class="mb-0"></vs-divider> -->

    <component
      :is="scrollbarTag"
      class="scroll-area--data-list-add-new"
      :settings="settings"
      :key="$vs.rtl"
    >

      <div class="p-6">
        <!-- NAME -->
        <vs-input
          label="红包名称"
          v-model="edtiterData.name"
          class="mt-5 w-full"
          name="item-name"

        />

        <vs-input
          label="金额"
          v-model="edtiterData.money"
          class="mt-5 w-full"
          name="item-money"

        />
        <span class="text-danger text-sm" v-show="errors.has('item-money')">{{
          errors.first("item-money")
        }}</span>

        <vs-input
          label="红包个数"
          v-model="edtiterData.total_num"
          class="mt-5 w-full"
          name="item-total_num"

        />

        <vs-input
          label="口令"
          v-model="edtiterData.pass"
          class="mt-5 w-full"
          name="item-pass"

        />
        <vs-input
          label="单日总消费"
          v-model="edtiterData.zong"
          class="mt-5 w-full"
          name="item-pass"

        />
        <vs-input
          label="时间限制"
          v-model="edtiterData.end"
          class="mt-5 w-full"
          name="item-pass"

        />
        <vs-input
          label="默认邀请码"
          v-model="edtiterData.default_invite_code"
          class="mt-5 w-full"
          name="item-pass"

        />
        <vs-input
          label="邀请码"
          v-model="edtiterData.invite_code"
          class="mt-5 w-full"
          name="item-pass"

        />
        <vs-select v-model="edtiterData.type" label="类型" class="mt-4 w-full">
            <vs-select-item :key="index" :value="item.value" :text="item.label" v-for="(item,index) in typeOptions" />
        </vs-select>


      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <!-- <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">提交</vs-button> -->
      <vs-button class="mr-6" @click="submitData">提交</vs-button>
      <vs-button
        type="border"
        color="danger"
        @click="isSidebarActiveLocal = false"
        >取消</vs-button
      >
    </div>
  </vs-sidebar>
</template>

<script>
import VuePerfectScrollbar from "vue-perfect-scrollbar";
// import { delete } from 'vue/types/umd';

export default {
  props: {
    isSidebarActive: {
      type: Boolean,
      required: true,
    },
    data: {
      type: Object,
      default: () => {},
    },
  },
  components: {
    VuePerfectScrollbar,
  },
  data() {
    return {
    edtiterData:{
        dataId: null,
        type:'',
        money: "",
        name:'',
        total_num:'',
        pass:'',
        zong:'',
        end:'',
        invite_code:'',
        default_invite_code:'',
    },
      settings: {
        // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: 0.6,
      },
      typeOptions:[
        {label:'随机',value:'1'},
        {label:'平分',value:'2'},
      ]
    };
  },
  watch: {
    isSidebarActive(val) {
      if (!val) return;
      console.log(this.data);
      if(Object.entries(this.data).length === 0){
        this.initValues();
      }else{
        const { id, money, name,total_num,pass,type,zong,end,invite_code,default_invite_code} = JSON.parse(JSON.stringify(this.data));
        this.edtiterData.dataId    = id;
        this.edtiterData.money     = money;
        this.edtiterData.name      = name;
        this.edtiterData.total_num = total_num;
        this.edtiterData.pass      = pass;
        this.edtiterData.type      = type;
        this.edtiterData.zong      = zong;
        this.edtiterData.end      = end;
        this.edtiterData.invite_code = invite_code;
        this.edtiterData.default_invite_code = default_invite_code;
      }
    },
  },
  computed: {
    isSidebarActiveLocal: {
      get() {
        return this.isSidebarActive;
      },
      set(val) {
        if (!val) {
          this.$emit("closeSidebar");
          // this.$validator.reset()
          this.initValues()
        }
      },
    },
    isFormValid() {
      return (
        !this.errors.any() &&
        this.type_name
      );
    },
    scrollbarTag() {
      return this.$store.getters.scrollbarTag;
    },
  },
  methods: {
    initValues() {
      if (this.data.id) return;
        this.edtiterData.dataId = '';
        this.edtiterData.money = '';
        this.edtiterData.name = '';
        this.edtiterData.total_num = '';
        this.edtiterData.pass  = '';
        this.edtiterData.type      = '';
        this.edtiterData.invite_code  = '';
        this.edtiterData.default_invite_code  = '';
    },
    submitData() {
      let _this = this;
        let url = "/admin/Activity/addEnvelope";
        if(this.edtiterData.dataId>0){
            url = "/admin/Activity/editEnvelope";
        }else{
            delete this.edtiterData.dataId
        }
        _this.$axios({
            url: url,
            method: "post",
            data:_this.edtiterData,
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
    },
    updateCurrImg(input) {
      if (input.target.files && input.target.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.dataImg = e.target.result;
        };
        reader.readAsDataURL(input.target.files[0]);
        this.file = input.target.files[0];
      }
    },
  },
};
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
    margin-left: 20px;
}
.img-container{
    width: 100px!important;
}
</style>
