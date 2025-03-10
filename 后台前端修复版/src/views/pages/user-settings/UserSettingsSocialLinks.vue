<template>
  <div>
    <vx-card no-shadow title="支付宝">
      <div class="w-full sm-base">
        <label class="text-sm">支付平台</label>
        <v-select
          v-model="zhifubao1"
          :options="zhifubaoOptions1"
          :dir="$vs.rtl ? 'rtl' : 'ltr'"
        />
      </div>
      <vs-input class="w-full sm-base" v-model="zhifubao2" label="商户号" />
      <div class="w-full sm-base">
        <label class="text-sm">支付方式</label>
        <v-select
          v-model="pay"
          :options="payList"
          :dir="$vs.rtl ? 'rtl' : 'ltr'"
        />
      </div>
    </vx-card>
    <vx-card no-shadow title="微信">
      <div class="w-full sm-base">
        <label class="text-sm">支付平台</label>
        <v-select
          v-model="zhifubao1"
          :options="zhifubaoOptions1"
          :dir="$vs.rtl ? 'rtl' : 'ltr'"
        />
      </div>
      <vs-input class="w-full sm-base" v-model="zhifubao2" label="商户号" />
      <div class="w-full sm-base">
        <label class="text-sm">支付方式</label>
        <v-select
          v-model="pay"
          :options="payList"
          :dir="$vs.rtl ? 'rtl' : 'ltr'"
        />
      </div>
      <div class="flex flex-wrap items-center justify-end">
        <vs-button class="ml-auto mt-2">保存</vs-button>
      </div>
      <!-- Save & Reset Button -->
    </vx-card>
  </div>
</template>

<script>
import vSelect from "vue-select";
export default {
  components: {
    vSelect,
  },
  data() {
    return {
      zhifubao1: "游戏聚合",
      zhifubao2: "",
      country: "11",
      pay: "扫码支付",
      // Options
      zhifubaoOptions1: [
        { label: "游戏聚合", value: "australia" },
        { label: "支付宝", value: "france" },
        { label: "易和", value: "germany" },
      ],
      payList: [
        { label: "扫码支付", value: "1" },
        { label: "网页支付", value: "2" },
      ],
    };
  },
  computed: {},
};
</script>
