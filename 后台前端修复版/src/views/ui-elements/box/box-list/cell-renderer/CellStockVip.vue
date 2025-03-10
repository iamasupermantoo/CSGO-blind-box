<template>
    <div :style="{'direction': $vs.rtl ? 'rtl' : 'ltr'}">
        <!-- <feather-icon icon="Edit3Icon" svgClasses="h-5 w-5 mr-4 hover:text-primary cursor-pointer" @click="editRecord(params)" /> -->
        <div>{{params.data.stock_vip}} / {{params.data.set_stock_vip ? params.data.set_stock_vip : 0}}</div>
    </div>
</template>

<script>
export default {
  name: 'CellStock',
  data(){
    return{
    }
  },
  methods: {
  },
}
</script>
