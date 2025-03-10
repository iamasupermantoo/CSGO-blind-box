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
        {{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} 佣金比例
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
          label="设置汇率"
          v-model="ratio"
          class="mt-5 w-full"
          name="item-type_name"
          
        />
        <span class="text-danger text-sm" v-show="errors.has('item-type_name')">{{
          errors.first("item-type_name")
        }}</span>

      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <vs-button class="mr-6" @click="submitData">提交</vs-button>
      <vs-button
        type="border"
        color="danger"
        @click="isSidebarActiveLocal = false"
        >取消</vs-button>
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
      ratio: "",
      settings: {
        // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: 0.6,
      },
      file:''
    };
  },
  watch: {
    isSidebarActive(val) {
      if (!val) return;
      console.log(this.data);
      if(Object.entries(this.data).length === 0){
        this.initValues();
      }else{
        const { id, ratio} = JSON.parse(JSON.stringify(this.data));
        this.dataId = id;
        this.ratio  = ratio;
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
        this.dataId = '';
        this.ratio  = '';
    },
    submitData() {
      let _this = this;
      const info = {
        id:_this.dataId,
        ratio:_this.ratio
      };
        _this.$axios({
            url: "/admin/Setting/editInviteInfo",
            method: "post",
            data:{
                data:info
            },
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
    }
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
