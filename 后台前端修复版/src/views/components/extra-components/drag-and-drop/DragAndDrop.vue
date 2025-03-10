]<!-- =========================================================================================
    File Name: Faq.vue
    Description: FAQ Page
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
      Author: Pixinvent
    Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->


<template>
  <div>
    <vx-card code-toggler>
      <!--添加 -->
      <data-view-sidebar
        :isSidebarActive="addNewDataSidebar"
        @closeSidebar="toggleDataSidebar"
        :data="sidebarData"
      />

      <div class="flex flex-wrap-reverse items-center">
        <!-- ADD NEW -->
        <div
          class="p-3 mb-4 mr-4 rounded-lg cursor-pointer flex items-center justify-between text-lg font-medium text-base text-primary border border-solid border-primary"
          @click="addNewData"
        >
          <feather-icon icon="PlusIcon" svgClasses="h-4 w-4" />
          <span class="ml-2 text-base text-primary">添加</span>
        </div>
      </div>

      <vs-table stripe :data="users">
        <template slot="thead">
          <vs-th>备注</vs-th>
          <vs-th>商户号</vs-th>
          <vs-th>支付方式</vs-th>
          <vs-th>是否直返二维码</vs-th>
          <vs-th>私钥</vs-th>
          <vs-th>公钥</vs-th>
          <vs-th>请求根路径</vs-th>
          <vs-th>异步回调</vs-th>
          <vs-th>同步回调</vs-th>
          <vs-th>操作</vs-th>
        </template>

        <template slot-scope="{ data }">
          <vs-tr :key="indextr" v-for="(tr, indextr) in data">
            <vs-td>
              {{ data[indextr].id }}
            </vs-td>
            <vs-td>
              {{ data[indextr].name }}
            </vs-td>
            <vs-td>
              {{ data[indextr].username }}
            </vs-td>
            <vs-td>
              {{ data[indextr].email }}
            </vs-td>
            <vs-td>
              {{ data[indextr].website }}
            </vs-td>
            <vs-td>
              {{ data[indextr].website }}
            </vs-td>
            <vs-td>
              {{ data[indextr].website }}
            </vs-td>
            <vs-td>
              {{ data[indextr].website }}
            </vs-td>
            <vs-td>
              {{ data[indextr].website }}
            </vs-td>
            <vs-td>
              <vs-button>删除</vs-button>
            </vs-td>
          </vs-tr>
        </template>
      </vs-table>
    </vx-card>
  </div>
</template>

<script>
import DataViewSidebar from "./DataViewSidebar.vue";
export default {
  components: {
    DataViewSidebar,
  },
  data() {
    return {
      addNewDataSidebar: false,
      sidebarData: {},
      users: [
        {
          id: "游戏聚合账号",
          name: "123456",
          username: "http",
          email: "http",
          website: "htt",
          a: "key",
        },
      ],
    };
  },
  computed: {},
  methods: {
    addNewData() {
      this.sidebarData = {};
      this.toggleDataSidebar(true);
    },
    toggleDataSidebar(val = false) {
      this.addNewDataSidebar = val;
    },
  },
};
</script>

<style lang="scss">
</style>
