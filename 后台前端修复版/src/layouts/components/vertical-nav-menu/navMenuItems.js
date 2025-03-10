/*=========================================================================================
  File Name: sidebarItems.js
  Description: Sidebar Items list. Add / Remove menu items from here.
  Strucutre:
          url     => router path
          name    => name to display in sidebar
          slug    => router path name
          icon    => Feather Icon component/icon name
          tag     => text to display on badge
          tagColor  => class to apply on badge element
          i18n    => Internationalization
          submenu   => submenu of current item (current item will become dropdown )
                NOTE: Submenu don't have any icon(you can add icon if u want to display)
          isDisabled  => disable sidebar item/group
  ----------------------------------------------------------------------------------------
  Item Name: Vuexy - Vuejs, HTML & Laravel Admin Dashboard Template
  Author: Pixinvent
  Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/



export default [
  // {
  //   url: "/apps/email",
  //   name: "Email",
  //   slug: "email",
  //   icon: "MailIcon",
  //   i18n: "Email",
  // },
  {
    url: null,
    name: '仪表盘',
    //tag: '2',
    tagColor: 'warning',
    icon: 'HomeIcon',
    i18n: '仪表盘',
    submenu: [
     /* {
        url: '/dashboard/analytics',
        name: 'Analytics',
        slug: 'dashboard-analytics',
        i18n: 'Analytics'
      },*/
      {
        url: '/dashboard/ecommerce',
        name: '统计',
        slug: 'dashboard-ecommerce',
        i18n: '统计'
      }
    ]
  },
  {
    header: 'Apps',
    icon: 'PackageIcon',
    i18n: '会员管理',
    items: [
      {
        url: '/apps/user/user-list',
        name: 'List',
        slug: 'List',
        icon: 'UserIcon',
        i18n: '会员列表'
      },

     /* {
        url: null,
        name: 'User',
        icon: 'UserIcon',
        i18n: '会员列表',
        submenu: [
          {
            url: '/apps/user/user-list',
            name: 'List',
            slug: 'app-user-list',
            i18n: 'List'
          },
          {
            url: '/apps/user/user-view/268',
            name: 'View',
            slug: 'app-user-view',
            i18n: 'View'
          },
          {
            url: '/apps/user/user-edit/268',
            name: 'Edit',
            slug: 'app-user-edit',
            i18n: 'Edit'
          }
        ]
      }*/


    ]
  },
  {
    header: '饰品/箱子',
    icon: 'LayersIcon',
    i18n: '饰品/箱子',
    items: [
      {
        url: null,
        name: 'Data List',
       // tag: 'new',
        tagColor: 'primary',
        icon: 'ListIcon',
        i18n: '饰品管理',
        submenu: [

          {
            url: '/ui-elements/data-list/thumb-view',
            name: 'Thumb View',
            slug: 'data-list-thumb-view',
            i18n: '饰品列表'
          },
          {
            url: '/ui-elements/data-list/list-view',
            name: 'List View',
            slug: 'data-list-list-view',
            i18n: '添加饰品'
          },
          {
            url: '/ui-elements/data-list/skin-type-list',
            name: 'List View',
            slug: 'data-list-list-view',
            i18n: '盲盒饰品分类'
          },
          {
            url: '/ui-elements/data-list/lucky-skin-type-list',
            name: 'List View',
            slug: 'data-list-list-view',
            i18n: '幸运饰品分类'
          },
        ]
      },
      {
        url: null,
        name: 'Grid',
        icon: 'LayoutIcon',
        i18n: '箱子管理',
        submenu: [
         /* {
            url: '/ui-elements/grid/vuesax',
            name: 'Vuesax',
            slug: 'grid-vuesax',
            i18n: '箱子列表'
          },*/
          {
            url: '/ui-elements/box/box-list',
            name: 'box-list',
            slug: 'box-list',
            i18n: '箱子列表'
          },
          {
            url: '/ui-elements/box/box_rarity',
            name: 'box-list',
            slug: 'box-list',
            i18n: '箱子分类'
          },
          /*{
            url: '/ui-elements/grid/tailwind',
            name: 'Tailwind',
            slug: 'grid-tailwind',
            i18n: 'Tailwind'
          }*/
        ]
      },
    ]
  },
  {
    header:'红包管理',
    icon: 'FileIcon',
    i18n: '红包管理',
    items: [
      {
        url: '/pages/envelope',
        slug: 'page-envelope',
        name: 'Envelope',
        icon: 'SettingsIcon',
        i18n: '红包列表'
      }
    ]
  },
  {
    header: '设置',
    icon: 'FileIcon',
    i18n: '设置',
    items: [
      {
        url: '/pages/user-settings',
        slug: 'page-user-settings',
        name: 'User Settings',
        icon: 'SettingsIcon',
        i18n: '系统设置'
      },
      // {
      //   url: '/pages/faq',
      //   slug: 'page-faq',
      //   name: 'FAQ',
      //   icon: 'HelpCircleIcon',
      //   i18n: '额外概率组'
      // },
      {
        url: '/pages/knowledge-base',
        slug: 'page-knowledge-base',
        name: 'Knowledge Base',
        icon: 'InfoIcon',
        i18n: 'Roll房间设置'
      },
      {
        url: null,
        name: 'Authentication',
        icon: 'PieChartIcon',
        i18n: '支付商号设置',
        submenu: [
          {
            url: '/extensions/select',
            name: 'Select Extension',
            slug: 'select',
            i18n: '支付宝',
          },
          {
            url: '/extensions/quill-editor',
            name: 'quill-editor',
            slug: 'quill-editor',
            i18n: '微信',
          },
          // {
          //   url: '/extensions/drag-and-drop',
          //   name: 'drag-and-drop',
          //   slug: 'drag-and-drop',
          //   i18n: '易吧',
          // },
        ]
      },
    ]
  },
  {
    header: '活动',
    icon: 'PackageIcon',
    i18n: '活动',
    items: [
      {
        url: '/pages/activity',
        slug: 'page-activity',
        name: 'Active',
        icon: 'InfoIcon',
        i18n: '活动管理'
      }
    ]
  },

  {
    header: 'Charts & Maps',
    icon: 'PieChartIcon',
    i18n: '机器人',
    items: [
      {
        icon: 'AirplayIcon',
        url: '/charts-and-maps/charts/echarts',
        name: 'echarts',
        slug: 'extra-component-charts-echarts',
        i18n: '机器人列表'
      },
      {
        url: '/charts-and-maps/maps/google-map',
        name: 'Google Map',
        icon: 'RotateCwIcon',
        slug: 'extra-component-maps-google-map',
        i18n: '机器人任务'
      }
    ]
  },
  {
    header: 'Others',
    icon: 'MoreHorizontalIcon',
    i18n: '游戏记录',
    items: [
      {
        url: '/components/upload',
        name: 'upload',
        icon: 'ArchiveIcon',
        slug: 'upload',
        i18n: '开箱记录'
      },
      // {
      //   url: "/components/tooltip",
      //   name: 'tooltip',
      //   icon: 'ArrowUpIcon',
      //   i18n: '升级记录',
      //   slug: 'tooltip',
      // },
      {
        url: "/components/tabs",
        name: 'tabs',
        icon: 'DollarSignIcon',
        i18n: '充值记录',
        slug: 'tabs',
      },
      {
        url: "/components/slider",
        name: 'slider',
        icon: 'ZapIcon',
        i18n: '竞技场记录',
        slug: 'slider',
      },
      {
        url: "/components/sidebar",
        name: 'sidebar',
        icon: 'LayoutIcon',
        i18n: '饰品发货记录',
        slug: 'sidebar',
      },
      // {
      //   url: "/components/progress",
      //   name: 'progress',
      //   icon: 'ClockIcon',
      //   i18n: '每日奖金记录',
      //   slug: 'progress',
      // },
    ]
  }
]

