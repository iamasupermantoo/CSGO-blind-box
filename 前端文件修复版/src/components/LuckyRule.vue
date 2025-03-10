<template>
  <div class="rule">
    <div class="rule-warp">
      <div class="roomleft-num">
        <h5 @click="goLucky">盲盒对战</h5>
        >
        <h6>活动规则</h6>
      </div>

      <div class="rule-text">
        <div class="text-title">关于活动</div>
        <p>
          1
          创建活动时，可以选择活动人数（1V1，3人对战和4人对战），一场乱斗最多可以选择
          6 个盲盒。房间一但创建，不可取消。
        </p>
        <p>
          2
          活动参与费用即本场活动开启的盲盒价格总和，支付费用后，即视为加入房间，中途退出房间，并不会退还盲盒或参与费。
        </p>
        <p>
          3
          当一场活动有多名赢家均分奖励时，非本人所得饰品将被兑换为硬币发放到赢家的账户余额。
        </p>
        <p>
          4
          每场活动的结果受到（创建时间，用户ID，用户位置和开始时间等）多方面因素影响，为完全随机产生的结果。任意盲盒奖励均可以验证公平性。
        </p>
      </div>

      <div class="rule-input">
        <div class="input-title">请输入活动场次编号</div>
        <div class="input">
          <input type="text" />
          <span>验证活动</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    goLucky() {
      this.$router.push({
        path: `/Lucky`,
      });
    },
  },
};
</script>

<style lang="less" scoped>
.rule {
  overflow: hidden;
  overflow-y: scroll;
  width: 100%;
  height: 100%;
  background-color: #1a1c24;

  .rule-warp {
    padding: 16px;
  }
  .rule-text {
    margin-top: 20px;
    .text-title {
      padding: 10px 0;
      color: #c3c3e2;
      font-size: 16px;
    }
    p {
      font-size: 14px;
      color: #848492;
      padding: 8px 0;
    }
  }
  .rule-input {
    display: table;
    margin-top: 30px;
    padding: 16px;
    background-color: #2b2c37;
    border-radius: 5px;
    .input-title {
      font-size: 16px;
      color: #c3c3e2;
    }
    .input {
      padding: 16px 0;
      input {
        height: 40px;
        line-height: 40px;
        padding: 0 10px;
        border-radius: 5px;
        width: 400px;
        color: #c3c3e2;
        font-size: 12px;
        background-color: #24252f;
        outline: none;
        box-sizing: border-box;
        border: none;
      }
      span {
        display: inline-table;
        height: 40px;
        margin-left: 20px;
        line-height: 40px;
        padding: 0 20px;
        background-color: #e9b10e;
        border-radius: 5px;
        font-weight: 600;
        color: #1a1c24;
        font-size: 15px;
      }
      span:hover {
        cursor: pointer;
        background-color: #f5c432;
      }
    }
  }

  .roomleft-num {
    display: flex;
    align-items: center;
    color: #848492;
    font-size: 16px;

    h5 {
      font-size: 16px;
      font-weight: 200;
    }
    h5:hover {
      cursor: pointer;
      color: #e9b10e;
      text-decoration: underline;
    }
    h6 {
      font-weight: 200;
      color: #c3c3e2;
      font-size: 16px;
    }
  }
}
</style>