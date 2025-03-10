<!-- =========================================================================================
  File Name: UserEdit.vue
  Description: User Edit Page
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->

<template>
  <div id="page-user-edit">
    
    <vs-alert color="danger" title="Box Not Found" :active.sync="user_not_found">
      <span>Box record with id: {{ $route.params.userId }} not found. </span>
      <span>
        <span>Check </span><router-link :to="{name:'page-user-list'}" class="text-inherit underline">All Box</router-link>
      </span>
    </vs-alert>

    <vx-card v-if="user_data">

      <div slot="no-body" class="tabs-container px-6 pt-6">

        <vs-tabs v-model="activeTab" class="tab-action-btn-fill-conatiner">
          <vs-tab label="箱子信息" icon-pack="feather" icon="icon-user">
            <div class="tab-text">
              <user-edit-tab-account class="mt-4" :data="user_data" @reload="getBoxInfo" />
            </div>
          </vs-tab>
          <vs-tab label="箱子饰品" icon-pack="feather" icon="icon-info">
            <div class="tab-text">
              <user-edit-tab-information class="mt-4" :data="user_data" :totalProbability="totalProbability" @reload="getBoxInfo" />
            </div>
          </vs-tab>
          <!-- <vs-tab label="价格对比" icon-pack="feather" icon="icon-share-2">
            <div class="tab-text">
              <user-edit-tab-social class="mt-4" :data="user_data" />
            </div>
          </vs-tab> -->
        </vs-tabs>

      </div>

    </vx-card>
  </div>
</template>

<script>
import UserEditTabAccount     from './UserEditTabAccount.vue'
import UserEditTabInformation from './UserEditTabInformation.vue'
import UserEditTabSocial      from './UserEditTabSocial.vue'


// Store Module
import moduleUserManagement from '@/store/user-management/moduleUserManagement.js'

export default {
  components: {
    UserEditTabAccount,
    UserEditTabInformation,
    UserEditTabSocial
  },
  data () {
    return {
      user_data: null,
      user_not_found: false,
      box_id:this.$route.params.userId,

      /*
        This property is created for fetching latest data from API when tab is changed

        Please check it's watcher
      */
      activeTab: 0,
      totalProbability:0
    }
  },
  watch: {
    activeTab () {
      // this.fetch_user_data(this.$route.params.userId)
      // this.getBoxInfo();
    }
  },
  methods: {
    // fetch_user_data (userId) {
    //   this.$store.dispatch('userManagement/fetchUser', userId)
    //     .then(res => { this.user_data = res.data })
    //     .catch(err => {
    //       if (err.response.status === 404) {
    //         this.user_not_found = true
    //         return
    //       }
    //       console.error(err) 
    //     })
    // }
    getBoxInfo(){
      let _this = this;
      _this.$axios({
          url: "/admin/Box/boxInfo",
          method: "post",
          data: {
            box_id:_this.box_id
          },
        }).then((res) => {
          this.user_data = res.data.data;
          this.user_data.mendBillie = res.data.data.mend ? res.data.data.mend.billie : '';
          this.user_data.mendVipBillie = res.data.data.mend ? res.data.data.mend.vip_billie : '';
          this.totalProbability = res.data.data.totalProbability;
          this.user_data.roleFilter = {
            label: res.data.data.rarity_name,
            value: res.data.data.rarity
          }
        });
    }
  },
  created () {
    // Register Module UserManagement Module
    if (!moduleUserManagement.isRegistered) {
      this.$store.registerModule('userManagement', moduleUserManagement)
      moduleUserManagement.isRegistered = true
    }
    this.getBoxInfo();
    console.log(this.box_id);
  }
}

</script>
