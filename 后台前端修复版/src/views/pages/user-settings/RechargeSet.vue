<template>
  <div id="page-user-edit">
    
    <!-- <vs-alert color="danger" title="Box Not Found" :active.sync="user_not_found">
      <span>Box record with id: {{ $route.params.userId }} not found. </span>
      <span>
        <span>Check </span><router-link :to="{name:'page-user-list'}" class="text-inherit underline">All Box</router-link>
      </span>
    </vs-alert> -->

    <vx-card>

      <div slot="no-body" class="tabs-container px-6 pt-6">

        <vs-tabs v-model="activeTab" class="tab-action-btn-fill-conatiner"  :class="'tab-'+tab">
          <vs-tab label="额度管理" icon-pack="feather" @click="clickTab(1)">
            <div class="tab-text">
              <quota-list class="mt-4"  />
            </div>
          </vs-tab>
          <vs-tab label="充值比例" icon-pack="feather" @click="clickTab(2)">
            <div class="tab-text">
              <charge-billie class="mt-4" />
            </div>
          </vs-tab>
          <vs-tab label="充值佣金" icon-pack="feather" @click="clickTab(3)">
            <div class="tab-text">
              <charge-commission class="mt-4" />
            </div>
          </vs-tab>

          <!-- <vs-tab label="充值赠送" icon-pack="feather">
            <div class="tab-text">
              <charge-give class="mt-4" />
            </div>
          </vs-tab> -->

        </vs-tabs>

      </div>

    </vx-card>
  </div>
</template>

<script>
import QuotaList  from './QuotaList.vue'
import ChargeBillie from './ChargeBillie.vue'
import ChargeGive from './ChargeGive.vue'
import ChargeCommission from './ChargeCommission.vue'
// import UserEditTabSocial      from './UserEditTabSocial.vue'

import moduleUserManagement from '@/store/user-management/moduleUserManagement.js'

export default {
  components: {
    QuotaList,
    ChargeBillie,
    ChargeGive,
    ChargeCommission
  },
  data () {
    return {
      user_data: null,
      user_not_found: false,
      box_id:this.$route.params.userId,
      activeTab: 0,
      totalProbability:0,
      tab:1
    }
  },
  watch: {
    activeTab () {
     
    }
  },
  methods: {
  
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
          this.totalProbability = res.data.data.totalProbability;
          this.user_data.roleFilter = {
            label: res.data.data.rarity_name,
            value: res.data.data.rarity
          }
        });
    },
    clickTab(tab){
      this.tab = tab;
    }
  },
  created () {
    // Register Module UserManagement Module
    if (!moduleUserManagement.isRegistered) {
      this.$store.registerModule('userManagement', moduleUserManagement)
      moduleUserManagement.isRegistered = true
    }
    // this.getBoxInfo();
  }
}

</script>
<style lang="scss">
// .tab-action-btn-fill-conatiner /deep/ .vs-tabs--ul{
//   display: flex;
// }
// .tab-action-btn-fill-conatiner /deep/ .line-vs-tabs{
//   // left: 0!important;
//   display: none;
// }
</style>

<style>
.tab-1 .vs-tabs--ul{
  display: flex!important;
  padding-bottom: 2px;
}
.tab-1 .line-vs-tabs{
  left: 1px!important;
}

.tab-2  .vs-tabs--ul{
  display: flex!important;
  padding-bottom: 2px;
}
.tab-2 .line-vs-tabs{
  left: 84px!important;
}

.tab-3 .vs-tabs--ul{
  display: flex!important;
  padding-bottom: 2px;
}
.tab-3 .line-vs-tabs{
  left: 170px!important;
}
</style>
