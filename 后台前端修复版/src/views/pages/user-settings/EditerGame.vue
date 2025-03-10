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
        {{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} 区间限制
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
        <vs-select label="类型" class="mt-5 w-full" v-model="type" v-validate="'required'" >
          <vs-select-item key="" value="" text="请选择" checked disabled/>
          <vs-select-item v-for="(item,index) in typeOption" :key="index" :value="item.value" :text="item.text"/>
        </vs-select>
        <span class="text-danger text-sm" v-show="errors.has('type')">{{errors.first("type")}}</span>

        <vs-input label="下限(%)" v-model="min_percent" class="mt-5 w-full" name="item-type_name"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>

        <vs-input label="上限(%，不设置即默认不限制)" v-model="max_percent" class="mt-5 w-full" name="item-type_name"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>

        <vs-input label="1阶段概率(%，不设置即默认20%)" v-model="bili1" class="mt-5 w-full" name="item-type_name"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>
        <vs-input label="1阶段饰品价格比例(盲盒价格*比例，最少100%)" v-model="percent1" class="mt-5 w-full" name="item-type_name"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>

        <vs-input label="2阶段概率(%，不设置即默认20%)" v-model="bili2" class="mt-5 w-full" name="item-type_name" v-if="max_percent>0"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>
        <vs-input label="2阶段饰品价格比例(盲盒价格*比例，最少100%)" v-model="percent2" class="mt-5 w-full" name="item-type_name"  v-if="max_percent>0"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>

        <vs-input label="3阶段概率(%，不设置即默认20%)" v-model="bili3" class="mt-5 w-full" name="item-type_name"  v-if="max_percent>0"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>
        <vs-input label="3阶段饰品价格比例(盲盒价格*比例，最少100%)" v-model="percent3" class="mt-5 w-full" name="item-type_name"  v-if="max_percent>0"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>

        <vs-input label="次数限定(不设置即默认500)" v-model="limit_times" class="mt-5 w-full" name="item-type_name"  v-if="max_percent>0"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>

        <vs-input label="3阶回升最大比(默认50%)" v-model="up_percent" class="mt-5 w-full" name="item-type_name" v-if="model == 'open' && max_percent>0" />
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>


        <vs-input label="充值回升金额比(默认30%,回升金额=上场亏损或者本场充值*比例)" v-model="recharge_up_percent" class="mt-5 w-full" name="item-type_name" v-if="model == 'open' && max_percent>0"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>
        <vs-input label="回升大件概率(默认30%,eg:30%概率随机含大件饰品)" v-model="bili4" class="mt-5 w-full" name="item-type_name" v-if="model == 'open' && max_percent>0"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>
        <vs-input label="回升饰品价格比(盲盒价格*比例，最少100%)" v-model="percent4" class="mt-5 w-full" name="item-type_name" v-if="model == 'open' && max_percent>0"/>
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{errors.first("item-type_name")}}</span>
      </div>


    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">提交</vs-button>
      <!-- <vs-button class="mr-6" @click="submitData">提交</vs-button> -->
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
      boxId: null,
      type: "",
      min_percent:'',
      max_percent:'',
      bili1:20,
      bili2:30,
      bili3:40,
      bili4:30,
      percent1:'',
      percent2:'',
      percent3:'',
      percent4:'',
      model:'',
      up_percent:50,
      recharge_up_percent:30,
      limit_times:500,
      settings: {
        // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: 0.6,
      },
      typeOption:[
        {"text":"普通","value":"ordinary"},
        {"text":"概率组","value":"group"},
        {"text":"Vip","value":"vip"},
      ]
    };
  },
  watch: {
    isSidebarActive(val) {
      if (!val){
        return;
      };
      if(Object.entries(this.data).length === 1 && this.data.model){
        this.initValues();
      }else{
        const { id, type,min_percent,max_percent,bili1,bili2,bili3,bili4,percent1,percent2,percent3,percent4,limit_times,model,up_percent,recharge_up_percent} = JSON.parse(JSON.stringify(this.data));
        this.dataId = id;
        this.type        = type,
        this.min_percent = min_percent,
        this.max_percent = max_percent,
        this.bili1        = bili1>0 ? bili1 : '20.00',
        this.bili2        = bili2>0 ? bili2 : '30.00',
        this.bili3        = bili3>0 ? bili3 : '40.00',
        this.bili4        = bili4>0 ? bili4 : '30.00',
        this.percent1     = percent1,
        this.percent2     = percent2,
        this.percent3     = percent3,
        this.percent4     = percent4,
        this.limit_times  = limit_times > 0 ? limit_times : 500,
        this.model        = model,
        this.up_percent   = up_percent,
        this.recharge_up_percent = recharge_up_percent
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
          // this.initValues()
        }
      },
    },
    isFormValid() {
      // console.log(!this.errors.any() , this.type , this.min_percent >= 1 , this.percent1 >= 100);
      // console.log( !this.errors.any() && this.type && this.min_percent >= 1 && this.percent1 >= 100);
      return ( !this.errors.any() && this.type && this.min_percent >= 1 && this.percent1 >= 100);
    },
    scrollbarTag() {
      return this.$store.getters.scrollbarTag;
    },
  },
  methods: {
    initValues() {
      if (this.data.id) return;
        this.dataId = '';
        this.type        = '',
        this.min_percent = '',
        this.max_percent = '',
        this.bili1        = 20,
        this.bili2        = 30,
        this.bili3        = 40,
        this.bili4        = 30,
        this.percent1     = '',
        this.percent2     = '',
        this.percent3     = '',
        this.percent4     = '',
        this.limit_times  = 500,
        this.model        = '',
        this.up_percent   = 50,
        this.recharge_up_percent = 30
    },
    submitData() {
      let _this = this;
      const info = {
        id:_this.dataId,
        type:_this.type,
        min_percent:_this.min_percent,
        max_percent:_this.max_percent,
        bili1:_this.bili1,
        bili2:_this.bili2,
        bili3:_this.bili3,
        percent1:_this.percent1,
        percent2:_this.percent2,
        percent3:_this.percent3,
        limit_times:_this.limit_times,
        model:_this.model
      };
      if(_this.model == 'open'){
        info.up_percent          = _this.up_percent,
        info.recharge_up_percent = _this.recharge_up_percent,
        info.bili4               = _this.bili4,
        info.percent4            = _this.percent4
      }
        _this.$axios({
            url: "/admin/Setting/editGame",
            method: "post",
            data:info,
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
