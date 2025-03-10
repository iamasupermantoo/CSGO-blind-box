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
    <vs-popup
      class="holamundo"
      title="新建分组"
      :active.sync="popupActive"
    >
      <vs-input class="w-full mb-base" v-model="input"  label="分组名称"  />
      <vs-button @click="confirm()">确定</vs-button>
    </vs-popup>

    <vx-card code-toggler>
      <div class="vx-card p-6">
        <div class="flex flex-wrap items-center">
          <!-- <vs-button class="mb-4 md:mb-0" @click="gridApi.exportDataAsCsv()">Export as CSV</vs-button> -->
          <vs-button @click="addNewData">新建分组</vs-button>
        </div>
      </div>

      <vs-table stripe :data="users">
        <template slot="thead">
          <vs-th>id</vs-th>
          <vs-th>分组名称</vs-th>
          <vs-th>操作</vs-th>
        </template>

        <template slot-scope="{ data }">
          <vs-tr :key="indextr" v-for="(tr, indextr) in data">
            <vs-td >
              {{ tr.id }}
            </vs-td>
            <vs-td >
              {{ tr.name }}
            </vs-td>
            <vs-td>
              <vs-button @click="alter(tr.id)">修改</vs-button>
              <vs-button
                color="danger"
                style="margin-left: 10px"
                @click="rem(tr.id)"
                >删除</vs-button
              >
            </vs-td>
          </vs-tr>
        </template>
      </vs-table>
    </vx-card>
  </div>
</template>

<script>
export default {
  data() {
    return {
      popupActive:false,
      input:"",
      users: [
        {
          id: 1,
          name: "新分组",
        },
        {
          id: 2,
          name: "刀组",
        },
      ],
    };
  },
  computed: {},
  methods: {
    //添加分组
    addNewData() {
      this.popupActive = true;
    },
    //确定添加
    confirm(){
      this.popupActive =false;
    },
    //修改
    alter(id) {
      console.log(id);
    },
    //删除
    rem(id) {
      console.log(id);
    },
  },
};
</script>

<style lang="scss">
</style>
