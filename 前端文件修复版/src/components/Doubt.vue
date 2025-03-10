<template>
  <div class="doubt">
    <div class="doubt-warp">
      <ul>
        <li>
          <div class="name">联系我们</div>
          <p class="p">联系我们，我们会第一时间处理您的反馈</p>
          <p class="p">客服邮箱：{{email}}</p>
          <p class="p">：{{qq}}</p>
        </li>
        <li>
            <div class="name">常规问题</div>

            <div class="name-title">1.无法通过 Steam 注册/登录</div>
            <p class="title-p">由于国内限制，用户在使用 Steam 注册/登录时可能出现无法连接、拒绝访问等问题，用户可通过安装网游加速器加速 Steam 社区来解决此问题。如仍存在问题，可尝试通过更换浏览器解决。</p>

            <div class="name-title">2.Steam 交易链接获取</div>
            <div class="title1">2.1 PC端用户</div>
            <div class="title2">(1).<span @click="goUrl(url1)">获取 Steam 交易地址</span></div>
            <div class="title2">(2).找到第三方网站获取 Steam 交易链接</div>
            <div class="img"><img src="../assets/img/faq/faq1.png"> </div>
            <div class="title1">2.2 手机端用户</div>
            <div class="title2">(1).下载 Steam App，登录后，通过流程“您与好友—库存—交易报价—右侧菜单按钮—谁能向我发送交易报价”，在最下面即可找到交易链接。</div>
            <div class="img"><img src="../assets/img/faq/faq2.png"> </div>

            <div class="name-title">3.充值相关问题</div>
            <div class="title3">Q：充值手续费是什么？？</div>
            <div class="title4">A：平台会收取饰品充值总额15%的手续费（仍高于市场价）。手续费用于平衡由饰品 7 天交易限制带来的饰品价格波动、账号锁定风险以及饰品售卖时向第三方平台支付手续费等成本支出。</div>
            <div class="title3">Q：饰品充值为什么会显示非可用饰品？</div>
            <div class="title4">A：存在交易冷却或交易限制的饰品是不可以进行充值的。</div>
            <div class="title3">Q：为什么我的饰品可以在 Steam 市场交易但在充值时仍显示非可用饰品？</div>
            <div class="title4">A：网站出于对运营成本的控制，对市场上流通性较差的饰品是不予支持的，请您谅解。</div>
            <div class="title3">Q：充值没有到账？</div>
            <div class="title4">A：支付宝/微信支付系统可能会有延迟，超3分钟后仍未到账，请您及时联系网站客服进行处理，客服QQ ：1073161290。</div>


            <div class="name-title">4.每日盲盒活动相关问题</div>
            <div class="title3">Q：为什么提示我的 Steam 游戏时间小于10小时</div>
            <div class="title4">A：打开 Steam 个人资料，编辑个人资料，找到隐私设置，保证各项设置均保持公开，并且确保下面总游戏时间私密没有打✓,如设置完仍有问题，请联系客服刷新。</div>
            <div class="img"><img src="../assets/img/faq/faq3.png"></div>
            <div class="title3">Q：我邀请了人为什么没有获得盲盒?</div>
            <div class="title4">A：1）您邀请的玩家同样需要满足获得每日盲盒的条件（需要绑定 Steam 账号，账号设置为公开且游戏时长需要大于10小时）才会被系统认定为有效邀请。</div>
            <div class="title4">2）每个用户每天能获得盲盒数量上限为5个，如果你今天已经获得了5个盲盒，你也不会再获得更多盲盒。</div>

            <div class="name-title">5.饰品取回相关问题</div>
            <div class="title3">Q：为什么提示我饰品无法取回？</div>
            <div class="title4">A： 多次尝试取回饰品无法取回可能是账号交易权限被限制，请打开链接查找不可交易的原因后再尝试取回，<span @click="goUrl(url2)">前往 Steam 客服页面查询原因</span></div>
            <div class="img"> <img src="../assets/img/faq/faq4.png"></div>
            <div class="title4">PS：如问题仍然存在，可更换一个可用的 Steam 帐号绑定交易URL取回或联系客服解决。</div>
            <div class="title3">Q：Steam 未公开</div>
            <div class="title4">A：打开 Steam 个人信息，点击编辑个人资料，找到隐私设置，将库存选项调整为公开，并将下面选项框的 ✓ 取消。</div>
            <div class="img"><img src="../assets/img/faq/faq5.png"></div>
            <div class="title3">Q：取回了好久一直没有发送报价</div>
            <div class="title4">A：部分饰品采取人工发货途径，所以发货时间相比自动发货可能会有所延迟，我们会在全网为您搜索匹配的饰品，一般在24小时内即可发货，请您耐心等待，如遇发货失败可多尝试几次取回。</div>
            <div class="title4">PS：注意：报价发出后一小时未接受，系统将会自动取消交易。</div>

            <div class="name-title">6.其他问题</div>
            <div class="title3">Q：我如何使用邀请码？</div>
            <div class="title4">A：注册之后，你可以在个人中心--个人资料页面（第三选项）找到填写邀请码，邀请码会在你首次充值的时候给你提供 5% 的额外收益。 你也可以在分销页面找到自己的分销链接和分销码，分享给你的好友。他们在游戏中的充值和押注行为也会为你带来收益。</div>
             <div class="title3">Q：饰品的价格依据是什么？</div>
            <div class="title4">A：我们根据 Steam 市场的价格给每个饰品估价，饰品估价在全平台都是一致的。盲盒奖励的饰品，您也可以选择直接兑换为余额。</div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  data(){
    return{
      qq:"852179466",
      email:"www.gosking.net@163.com",
      url1:"https://steamcommunity.com/id/me/tradeoffers/privacy#trade_offer_access_url",
      url2:"https://help.steampowered.com/zh-cn/wizard/Login?redir=%2Fzh-cn%2Fwizard%2FHelpWhyCantITrade&title=%E7%99%BB%E5%BD%95"
    }
  },
  methods:{
    //获得steam交易链接
    goUrl(url) {
      window.open(url, "_blank");
    },
  }

};
</script>

<style lang="less" scoped>
.doubt{
    overflow: hidden;
    overflow-y: scroll;
    width: 100%;
    height: 100%;
    background-color: #1a1c24;

    .doubt-warp{
        width: 1200px;
        margin: 0 auto;
        padding: 20px 0;
        margin-bottom: 50px;

        ul li{
            .name{
                color: #c3c3e2;
                font-size: 24px;
                font-weight: 600;
            }
            .name-title{
              font-size: 20px;
              margin:20px 0 0 20px;
              color: #c3c3e2;
            }
            .p{
                font-weight: 400;
               font-size: 16px;
               color: #848492;
               padding: 8px 0;
            }
            .title-p{
              color: #848492;
              margin:20px 0 0 20px;
            }
        }

        .title1{
          padding: 20px 0 0 40px;
          font-size: 16px;
          color: #c3c3e2;
          font-weight: 600;
        }
        .title2{
          padding: 20px 0 0 60px;
          font-size: 16px;
          color: #848492;
          span{
            text-decoration: underline;
            color: #17b4ed;
          }
          span:hover{
            cursor: pointer;
          }
        }
        .title3{
          padding: 20px 0 0 60px;
          font-size: 14px;
          color: #c3c3e2;
          font-weight: 600;
        }
        .title4{
          padding: 20px 0 0 60px;
          font-size: 14px;
          color: #848492;
          font-weight: 600;
          span{
            text-decoration: underline;
            color: #17b4ed;
          }
          span:hover{
            cursor: pointer;
          }
        }
        .img{
         padding: 20px 0 0 60px;
        }
    }
}
</style>
