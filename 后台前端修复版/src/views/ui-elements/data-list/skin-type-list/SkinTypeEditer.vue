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
        {{ Object.entries(this.data).length === 0 ? "添加" : "编辑" }} 分类
      </h4>
      <feather-icon
        icon="XIcon"
        @click.stop="isSidebarActiveLocal = false"
        class="cursor-pointer"
      ></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>

    <component
      :is="scrollbarTag"
      class="scroll-area--data-list-add-new"
      :settings="settings"
      :key="$vs.rtl"
    >

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
        <vs-input
          label="分类名称"
          v-model="name"
          class="mt-5 w-full"
          name="item-name"
          
        />
        <span class="text-danger text-sm" v-show="errors.has('item-name')">{{
          errors.first("item-name")
        }}</span>

        <vs-input
          label="背景色"
          v-model="color"
          class="mt-5 w-full"
          name="item-name"
          
        />
        <span class="text-danger text-sm" v-show="errors.has('item-name')">{{
          errors.first("item-name")
        }}</span>


        <vs-input
          label="排序"
          v-model="order"
          class="mt-5 w-full"
          name="item-order"
          
        />
        <span class="text-danger text-sm" v-show="errors.has('item-order')">{{
          errors.first("item-order")
        }}</span>

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
      name: "",
      order: "",
      color:"",
      settings: {
        // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: 0.6,
      },
      file:'',
      dataImg:''
    };
  },
  watch: {
    isSidebarActive(val) {
      if (!val) return;
      console.log(this.data);
      if(Object.entries(this.data).length === 0){
        this.initValues();
      }else{
        const { id, name, order,color,img} = JSON.parse(JSON.stringify(this.data));
        this.dataId = id;
        this.name = name;
        this.order = order;
        this.color = color;
        this.dataImg = img;
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
        this.name
      );
    },
    scrollbarTag() {
      return this.$store.getters.scrollbarTag;
    },
  },
  methods: {
    initValues() {
      if (this.data.id) return;
      this.dataId = null;
      this.name = "";
      this.order = "";
      this.dataImg = "";
    },
    submitData() {
      let _this = this;
      const typeInfo = {
        id:_this.dataId,
        name: _this.name,
        order:_this.order,
        color:_this.color
      };
      var forms = new FormData()
      forms.append('data',JSON.stringify(typeInfo));
      forms.append('img',_this.imgFile);
      let url = "/admin/All_skin/addSkinType";
      if(this.dataId>0){
        url = "/admin/All_skin/editSkinType";
      }
      _this.$axios({
          url:url,
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
  padding-left: 20px;
}
</style>
