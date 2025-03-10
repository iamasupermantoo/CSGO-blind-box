<template>
  <div id="">
    <vx-card>

      <div slot="no-body" class="tabs-container px-6 pt-6">

        <vs-tabs v-model="activeTab" class="tab-action-btn-fill-conatiner">
          <vs-tab label="奖品信息" icon-pack="feather" icon="icon-info">
            <div class="tab-text">
              <!-- <user-edit-tab-information class="mt-4" :data="user_data" :totalProbability="totalProbability" /> -->
              <prizes :sidebarData="sidebarData"/>
            </div>
          </vs-tab>
          <vs-tab label="玩家信息" icon-pack="feather" icon="icon-share-2">
            <div class="tab-text">
              <player class="mt-4" :data="playerData" />
            </div>
          </vs-tab>
        </vs-tabs>

      </div>

    </vx-card>
  </div>
</template>

<script>
import Prizes  from './Prizes.vue'
import Player  from './Player.vue'


// Store Module
import moduleUserManagement from '@/store/user-management/moduleUserManagement.js'

export default {
  components: {
    Prizes,
    Player
  },
  data () {
    return {
      roon_not_found:true,
      room_id:this.$route.query.id,
      activeTab:0,
      sidebarData:{},
      playerData:{}
    }
  },
  watch: {

  },
  methods: {
    getSkins(){
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
    }
  },
  created () {
    // this.getBoxInfo();
    console.log(this.$route);
  }
}

</script>
