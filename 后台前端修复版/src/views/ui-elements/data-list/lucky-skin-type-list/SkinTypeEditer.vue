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
        @click.stop="isSidebarActiveLocal = false,file=''"
        class="cursor-pointer"
      ></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>


     <!-- Product Image -->
        <template v-if="dataImg">
          <!-- Image Container -->
          <div class="img-container w-64 mx-auto flex items-center justify-center">
            <img style="width:150px;" :src="dataImg" alt="img" class="responsive">
          </div>

          <!-- Image upload Buttons -->
          <div class="modify-img flex justify-between mt-5">
            <input type="file" class="hidden" ref="updateImgInput" @change="updateCurrImg" accept="image/*">
            <vs-button class="mr-4" type="flat" @click="$refs.updateImgInput.click()">上传图片</vs-button>
            <!-- <vs-button type="flat" color="#999" @click="dataImg = null">删除图片</vs-button> -->
          </div>
        </template>
        <div class="upload-img mt-5" v-if="!dataImg">
          <input type="file" class="hidden" ref="uploadImgInput" @change="updateCurrImg" accept="image/*">
          <vs-button @click="$refs.uploadImgInput.click()">上传图片</vs-button>
        </div>

    <component
      :is="scrollbarTag"
      class="scroll-area--data-list-add-new"
      :settings="settings"
      :key="$vs.rtl"
    >
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
          label="排序"
          v-model="order"
          class="mt-5 w-full"
          name="item-name"
        />
      </div>
    


      <div v-if="status < 2" style="display:flex;position: relative;">
        <div class="p-6">
          <!-- NAME -->
          <div v-for="(item,index) in form" :key="index" style="display:flex;">
            <div>
              <vs-input
              label="子类"
              v-model="form[index].subclass_name"
              class="mt-5 w-full"
              name="item-name"
            />
            </div>
            <XIcon icon="XIcon" @click="deleteItem(item, index)" style="margin-top: 44px;"></XIcon>
          </div>
         
        </div>

        <div class="flex flex-wrap items-center add-subclass">
          <vs-button @click="addItem">添加</vs-button>
        </div>
 
      </div>
      

        <!-- <div>
          <div class="el-form-row">
              <el-form-item label="规格名称">
                  <el-input v-model="form.title" @input="onInput()"></el-input>
              </el-form-item>
          </div>
          <div style="display: flow-root;">
              <div v-for="(item,index) in form.dynamicItem" :key="index" class="title">
                  <div class="title-1">
                      <el-form-item
                              label="值标题">
                          <el-input v-model="item.title"></el-input>
                      </el-form-item>
                      <i class="el-icon-delete" @click="deleteItem(item, index)"></i>
                  </div>
              </div>
          </div>
          <el-button type="primary" icon="el-icon-plus" class="addItem" @click="addItem"></el-button>
      </div> -->

    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <!-- <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">提交</vs-button> -->
      <vs-button class="mr-6" @click="submitData">提交</vs-button>
      <vs-button
        type="border"
        color="danger"
        @click="isSidebarActiveLocal = false,file = ''"
        >取消</vs-button
      >
    </div>
  </vs-sidebar>
</template>

<script>
import VuePerfectScrollbar from "vue-perfect-scrollbar";
import { XIcon } from 'vue-feather-icons'

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
    XIcon
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
      dataImg:'',
      form:[],
      status:''
    };
  },
  watch: {
    isSidebarActive(val) {
      if (!val) return;
      console.log(this.data);
      if(Object.entries(this.data).length === 0){
        this.initValues();
      }else{
        const { id, name,img,order,status} = JSON.parse(JSON.stringify(this.data));
        console.log(status);
        this.dataId = id;
        this.name = name;
        this.dataImg = img;
        this.order = order;
        this.status = status;
        this.getSubclass();
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
    getSubclass(){
      let _this = this;
      _this.$axios({
          url: "/admin/Lucky/typeSubclass",
          method: "post",
          data: {
            skin_type_id:_this.dataId
          }
        }).then((res) => {
          console.log(res);
          if(res.data.status == 1){
            _this.form = res.data.data.list;
          }
        });
    },
    initValues() {
      if (this.data.id) return;
      this.dataId = null;
      this.name = "";
      this.order = '';
      this.status = '';
    },
    submitData() {
      let _this = this;
      var forms = new FormData()
      let data = {
        id:_this.dataId,
        name:_this.name,
        order:_this.order
      };
      let subclassInfo = _this.form ? _this.form : '';
      forms.append('data',JSON.stringify(data));
      forms.append('subclassInfo',JSON.stringify(subclassInfo));
      if(_this.file){
        forms.append('img',_this.file);
      }
      let url = "/admin/Lucky/addLucySkinType";
      if(this.dataId > 0){
        url = "/admin/Lucky/editLucySkinType";
      }
      _this.$axios({
          url: url,
          method: "post",
          data: forms
        }).then((res) => {
          if(res.data.status == 1){
            _this.file = '';
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
     //新增规格输入框
    addItem(){
        let _this = this;
        let length = _this.form.length;
        let item = _this.form[length-1];
        // if(item>0){
            _this.form.push({
                type_id: _this.data.id,
                id: '',
                subclass_name: "",
            })
        // }else {
            // _this.form.dynamicItem.push({
            //     displayorder:1,
            //     goods_id: _this.goods_id,
            //     goods_option_id: '',
            //     id: '',
            //     thumb: "",
            //     title: "",
            // })
        // }
    },
    deleteItem (item, index) {
        let _this = this;
        _this.form.splice(index, 1)
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
  }
};
</script>

<style lang="scss" scoped>
.add-new-data-sidebar /deep/ .vs-sidebar--items{
  overflow: auto!important;
}
.add-new-data-sidebar /deep/ .ps-container{
      height: auto!important;
}
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
.add-subclass{
    position: absolute;
    right: 20px;
    bottom: 22px;
}
</style>
