import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import Roll from '../components/Roll.vue'
import Index from '../components/Index.vue'
import Lucky from '../components/Lucky.vue'
import Arena from '../components/Arena.vue'
import Dota from '../components/Dota.vue'
import dome from '../components/dome.vue'
import Openbox from '../components/Openbox.vue'
import LuckyRoom from '../components/LuckyRoom.vue'
import Doubt from '../components/Doubt.vue'
import LuckyRule from '../components/LuckyRule.vue'
import LuckyHistory from '../components/LuckyHistory.vue'
import Ornament from '../components/Ornament.vue'
import OrnamentOpen from '../components/OrnamentOpen.vue'
import Bill from '../components/Bill.vue'
import Spread from '../components/Spread.vue'
import Me from '../components/Me.vue'
import Inform from '../components/Inform.vue'
import Payment from '../components/Payment.vue'
import ArenaRoom from '../components/ArenaRoom.vue'
import Abouts from '../components/Abouts.vue'
import Agreement from '../components/Agreement.vue'
import Privacy from '../components/Privacy.vue'
import Activity from '../components/Activity.vue'
import Clause from '../components/Clause.vue'
import OrnamentHistory from '../components/OrnamentHistory.vue'
import SpreadLonger from '../components/SpreadLonger.vue'
import PackageBill from '../components/PackageBill.vue'

const originalPush = VueRouter.prototype.push
VueRouter.prototype.push = function push(location) {
  return originalPush.call(this, location).catch(err => err)
}

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    redirect:"Index",
    children:[{
      path: 'Index',
      name: 'Index',
      component: Index,
      children:[]
    },
    {
      path: 'Lucky',
      name: 'Lucky',
      component: Lucky
    },{
      path: 'Arena',
      name: 'Arena',
      component: Arena
    },
    {
      path: '/Roll',
      name: 'Roll',
      component: Roll
    },
    {
      path: '/Dota',
      name: 'Dota',
      component: Dota
    },{
      path: '/dome',
      name: 'dome',
      component: dome
    },{
      path: 'Openbox',
      name: 'Openbox',
      component: Openbox,
    },{
      path: '/LuckyRoom',
      name: 'LuckyRoom',
      component: LuckyRoom
    },{
      path: '/Doubt',
      name: 'Doubt',
      component: Doubt
    },{
      path: '/LuckyRule',
      name: 'LuckyRule',
      component: LuckyRule
    },{
      path: '/LuckyHistory',
      name: 'LuckyHistory',
      component: LuckyHistory
    },{
      path: '/Ornament',
      name: 'Ornament',
      component: Ornament
    },{
      path: '/OrnamentOpen',
      name: 'OrnamentOpen',
      component: OrnamentOpen
    },{
      path: '/Bill',
      name: 'Bill',
      component: Bill
    },{
      path: '/Spread',
      name: 'Spread',
      component: Spread
    },{
      path: '/Inform',
      name: 'Inform',
      component: Inform
    },{
      path: '/Me',
      name: 'Me',
      component: Me
    },{
      path: '/Payment',
      name: 'Payment',
      component: Payment
    },{
      path: '/ArenaRoom',
      name: 'ArenaRoom',
      component: ArenaRoom
    },{
      path: '/Abouts',
      name: 'Abouts',
      component: Abouts
    },{
      path: '/Agreement',
      name: 'Agreement',
      component: Agreement
    },{
	  path: '/Activity',
	  name: 'Activity',
	  component: Activity
	},{
      path: '/Privacy',
      name: 'Privacy',
      component: Privacy
    },{
      path: '/Clause',
      name: 'Clause',
      component: Clause
    },{
      path: '/OrnamentHistory',
      name: 'OrnamentHistory',
      component: OrnamentHistory
	},{
	  path: '/PackageBill',
	  name: 'PackageBill',
	  component: PackageBill
	},{
      path: '/SpreadLonger',
      name: 'SpreadLonger',
      component: SpreadLonger
    }]
  },
  //SpreadLonger
  /*{
    path: '/about',
    name: 'About',
    component: function () {
      return import('../views/About.vue')
    }
  }  */
]

const router = new VueRouter({
  routes
})


export default router
