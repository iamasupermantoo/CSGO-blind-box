<!-- =========================================================================================
    File Name: DashboardEcommerce.vue
    Description: Dashboard - Ecommerce
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
      Author: Pixinvent
    Author URL: http://www.themeforest.net/user/pixinvent
========================================================================================== -->

<template>
    <div>
        <div class="vx-row">
            <div class="vx-col w-full sm:w-1/2 md:w-1/2 lg:w-1/4 xl:w-1/4 mb-base">
                <statistics-card-line
                  v-if="subscribersGained.analyticsData"
                  icon="UsersIcon"
                  :statistic="subscribersGained.analyticsData.subscribers | k_formatter"
                  statisticTitle="会员数量"
                  :chartData="subscribersGained.series"
                  type="area" />
            </div>

            <div class="vx-col w-full sm:w-1/2 md:w-1/2 lg:w-1/4 xl:w-1/4 mb-base">
                <statistics-card-line
                  v-if="revenueGenerated.analyticsData"
                  icon="DatabaseIcon"
                  :statistic="revenueGenerated.analyticsData.revenue | k_formatter"
                  statisticTitle="会员总充值"
                  :chartData="revenueGenerated.series"
                  color="success"
                  type="area" />
            </div>

            <div class="vx-col w-full sm:w-1/2 md:w-1/2 lg:w-1/4 xl:w-1/4 mb-base">
                <statistics-card-line
                  v-if="quarterlySales.analyticsData"
                  icon="DollarSignIcon"
                  :statistic="quarterlySales.analyticsData.sales"
                  statisticTitle="平台总支出"
                  :chartData="quarterlySales.series"
                  color="danger"
                  type="area" />
                  <!-- icon="ShoppingCartIcon" -->
            </div>
            <div class="vx-col w-full sm:w-1/2 md:w-1/2 lg:w-1/4 xl:w-1/4 mb-base">
                <statistics-card-line
                  v-if="ordersRecevied.analyticsData"
                  icon="ShoppingBagIcon"
                  :statistic="ordersRecevied.analyticsData.orders | k_formatter"
                  statisticTitle="平台盈亏"

                  color="warning"
                  type="area" />
            </div>
        </div>

        <div class="vx-row">

            <!-- LINE CHART -->
            <div class="vx-col w-full md:w-3/3 mb-base">
                <vx-card title="月支出与消费">
                    <template slot="actions">
                        <feather-icon icon="SettingsIcon" svgClasses="w-6 h-6 text-grey"></feather-icon>
                    </template>
                    <div slot="no-body" class="p-6 pb-0">
                        <div class="flex" v-if="revenueComparisonLine.analyticsData">
                            <div class="mr-6">
                                <p class="mb-1 font-semibold">当月支出</p>
                                <p class="text-3xl text-success"><sup class="text-base mr-1">$</sup>{{ revenueComparisonLine.analyticsData.thisMonth.toLocaleString() }}</p>
                            </div>
                            <div>
                                <p class="mb-1 font-semibold">当月收入</p>
                                <p class="text-3xl"><sup class="text-base mr-1">￥</sup>{{ revenueComparisonLine.analyticsData.lastMonth.toLocaleString() }}</p>
                            </div>
                        </div>
                        <vue-apex-charts
                          type="line"
                          height="266"
                          :options="analyticsData.revenueComparisonLine.chartOptions"
                          :series="revenueComparisonLine.series" />
                    </div>
                </vx-card>
            </div>

        </div>

        <div class="vx-row">

          <!-- LINE CHART -->
          <div class="vx-col w-full md:w-3/3 mb-base">
            <vx-card title="最近30天日充值金额">
              <template slot="actions">
                <feather-icon icon="SettingsIcon" svgClasses="w-6 h-6 text-grey"></feather-icon>
              </template>
              <div slot="no-body" class="p-6 pb-0">
                <vue-apex-charts
                  type="bar"
                  height="266"
                  :options="chartOptions"
                  :series="series" />
              </div>
            </vx-card>
          </div>

        </div>
    </div>
</template>

<script>
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import VueApexCharts from 'vue-apexcharts'
import StatisticsCardLine from '@/components/statistics-cards/StatisticsCardLine.vue'
import analyticsData from './ui-elements/card/analyticsData.js'
import ChangeTimeDurationDropdown from '@/components/ChangeTimeDurationDropdown.vue'

export default{
  components: {
    VueApexCharts,
    StatisticsCardLine,
    VuePerfectScrollbar,
    ChangeTimeDurationDropdown
  },
  data () {
    return {
      subscribersGained: {},
      revenueGenerated: {},
      quarterlySales: {},
      ordersRecevied: {},

      revenueComparisonLine: {},
      goalOverview: {},

      browserStatistics: [],
      clientRetentionBar: {},

      sessionsData: {},
      chatLog: [],
      chatMsgInput: '',
      customersData: {},

      analyticsData,
      settings: { // perfectscrollbar settings
        maxScrollbarLength: 60,
        wheelSpeed: .60
      },
      chartOptions: {
        chart: {
          id: 'vuechart-example'
        },
        xaxis: {
          // categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998]
          //categories: []
        }
      },
      series: [{
        name: '当日充值金额',
        data: [30, 40, 35, 50, 49, 60, 70, 91]
        // data: []
      }]
    }
  },
  computed: {
    scrollbarTag () { return this.$store.getters.scrollbarTag }
  },
  mounted () {
    // const scroll_el = this.$refs.chatLogPS.$el || this.$refs.chatLogPS
    // scroll_el.scrollTop = this.$refs.chatLog.scrollHeight
  },
  created () {
    // Subscribers gained - Statistics
    // this.$axios.get('/api/card/card-statistics/subscribers')
    //   .then((response) => {
    //        this.subscribersGained = response.data
    //       console.log(this.subscribersGained)
    //     })
    //   .catch((error) => { console.log(error) })

    this.$axios({
      url: '/admin/Statistics/member',
      method: "post",
      data:{},
    }).then((res)=>{
      this.subscribersGained = res.data.data
    })

      //充值
    this.$axios({
      url: '/admin/Statistics/recharge',
      method: "post",
      data:{},
    }).then((res)=>{
      this.revenueGenerated = res.data.data;

      this.chartOptions.xaxis.categories = this.revenueGenerated.xaxis;
      this.series = this.revenueGenerated.series;
    })

      //支出
    this.$axios({
      url: '/admin/Statistics/expenditure',
      method: "post",
      data:{},
    }).then((res)=>{
      this.quarterlySales = res.data.data
    })

    //用于盈亏，仅计算玩家背包正常的饰品总价值
    // this.$axios({
    //   url: '/admin/Statistics/totalValue',
    //   method: "post",
    //   data:{},
    // }).then((res)=>{
    //   console.log(this.revenueGenerated.analyticsData.revenue);
    //   console.log(this.quarterlySales.analyticsData.sales);
    //   console.log(res.data.data.analyticsData.orders);
    //   res.data.data.analyticsData.orders = (this.revenueGenerated.analyticsData.revenue - this.quarterlySales.analyticsData.sales - res.data.data.analyticsData.orders).toFixed(2)
    //   this.ordersRecevied = res.data.data
    // })

    // Orders - Statistics盈亏
    // this.$axios.get('/api/card/card-statistics/orders')
    //   .then((response) => { this.ordersRecevied = response.data
    //   console.log(this.ordersRecevied);
    //   })
    //   .catch((error) => { console.log(error) })

    //Revenue Comparison//本月收入/支出
    // this.$axios.get('/api/card/card-analytics/revenue-comparison')
    //   .then((response) => {
    //        this.revenueComparisonLine = response.data
    //        console.log(this.revenueComparisonLine)
    //        })
    //   .catch((error) => { console.log(error) })

    //本月收入/支出
    this.$axios({
      url: '/admin/Statistics/contrast',
      method: "post",
      data:{},
    }).then((res)=>{
      console.log(res.data.data);
      this.revenueComparisonLine = res.data.data
    })

      // Goal Overview
    this.$axios.get('/api/card/card-analytics/goal-overview')
      .then((response) => { this.goalOverview = response.data })
      .catch((error) => { console.log(error) })

      // Browser Analytics
    this.$axios.get('/api/card/card-analytics/browser-analytics')
      .then((response) => { this.browserStatistics = response.data })
      .catch((error) => { console.log(error) })

      // Client Retention
    this.$axios.get('/api/card/card-analytics/client-retention')
      .then((response) => { this.clientRetentionBar = response.data })
      .catch((error) => { console.log(error) })

      // Sessions By Device
    this.$axios.get('/api/card/card-analytics/session-by-device')
      .then((response) => { this.sessionsData = response.data })
      .catch((error) => { console.log(error) })

      // Chat Log
    this.$axios.get('/api/chat/demo-1/log')
      .then((response) => { this.chatLog = response.data })
      .catch((error) => { console.log(error) })

      // Customers
    this.$axios.get('/api/card/card-analytics/customers')
      .then((response) => { this.customersData = response.data })
      .catch((error) => { console.log(error) })
  }
}
</script>

<style lang="scss">
.chat-card-log {
    height: 400px;

    .chat-sent-msg {
        background-color: #f2f4f7 !important;
    }
}
</style>
