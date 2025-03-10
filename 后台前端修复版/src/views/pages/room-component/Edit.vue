<template>
  <vs-sidebar click-not-close position-right parent="body" default-index="1" color="primary" class="add-new-data-sidebar items-no-padding" spacer v-model="isEditActive">
    <div class="mt-6 flex items-center justify-between px-6">
        <h4>设置</h4>
        <feather-icon icon="XIcon" @click.stop="isEditActive = false" class="cursor-pointer"></feather-icon>
    </div>
    <vs-divider class="mb-0"></vs-divider>

    <component :is="scrollbarTag" class="scroll-area--data-list-add-new" :settings="settings" :key="$vs.rtl">

      <div class="p-6">

        <!-- Product Image -->
        <template v-if="skinInfo.imageUrl">

          <!-- Image Container -->
          <div class="img-container w-64 mx-auto flex items-center justify-center">
            <img :src="skinInfo.imageUrl" alt="img" class="responsive">
          </div>

          <!-- Image upload Buttons -->
          <div class="modify-img flex justify-between mt-5">
            <!-- <input type="file" class="hidden" ref="updateImgInput" @change="updateCurrImg" accept="image/*"> -->
          </div>
        </template>

        <!-- 房间标题 -->
        <vs-input label="饰品名称" v-model="skinInfo.itemName" class="mt-5 w-full" disabled/>
        <vs-input label="价格" v-model="skinInfo.price" class="mt-5 w-full" disabled/>
        <vs-input label="指定账号" v-model="skinInfo.appoint" class="mt-5 w-full"/>
        
        
      </div>
    </component>

    <div class="flex flex-wrap items-center p-6" slot="footer">
      <!-- <vs-button class="mr-6" @click="submitData" :disabled="!isFormValid">提交</vs-button> -->
      <vs-button class="mr-6" @click="submitData">提交</vs-button>
      <vs-button type="border" color="danger" @click="isEditActive = false">关闭</vs-button>
    </div>
  </vs-sidebar>
</template>

<script>
import VuePerfectScrollbar from 'vue-perfect-scrollbar'

export default {
  props: {
    editActive: {
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
        dataImg:'',
        settings: {
            maxScrollbarLength: 60,
            wheelSpeed: 0.6,
        },
        skinInfo:{},
        skinInfoOption:[],
        conditionOption:[]
    }
  },
  watch: {
    editActive (val) {
      if (!val) return
      console.log(this.data);
      if (Object.entries(this.data).length === 0) {
        
      } else {
        // const { id, imageUrl, itemName, price } = JSON.parse(JSON.stringify(this.data))
        this.skinInfo = this.data;
        this.initValues()
      }
      // Object.entries(this.data).length === 0 ? this.initValues() : { this.dataId, this.dataName, this.dataCategory, this.dataOrder_status, this.dataPrice } = JSON.parse(JSON.stringify(this.data))
    }
  },
  computed: {
        isEditActive:{
            get() {
                return this.editActive;
            },
            set(val) {
                if (!val) {
                    this.$emit("closeEditbar");
                }
            },
        },
        scrollbarTag () { return this.$store.getters.scrollbarTag }
  },
  methods: {
    initValues () {
      if (this.data.id) return
    //   for(let key  in this.skinInfo){
    //     this.skinInfo[key] = ''
    //   }
    },
    submitData () {
        let _this = this;
        console.log(_this.skinInfo);
        _this.$axios({
            url: "/admin/Free/appoint",
            method: "post",
            data:{
                free_skins_id:_this.skinInfo.free_skins_id,
                skin_id:_this.skinInfo.id,
                free_room_id:_this.skinInfo.free_room_id,
                account:_this.skinInfo.appoint
            },
            }).then((res) => {
                console.log(res);
                if(res.data.status == 1){
                    this.$emit('closeEditbar')
                    this.initValues()
                    this.$emit('get')
                    this.$vs.notify({
                        color: 'success',
                        title: '提示',
                        text: res.data.msg
                    })
                }else{
                    this.$vs.notify({
                        color: 'danger',
                        title: '提示',
                        text: res.data.msg
                    })
                }
        });
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
