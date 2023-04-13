// custom-tab-bar/index.ts

import { getLocalToken } from "../../utils/token";
import { loginPage } from "../../utils/router";
Component({
  options: {
    addGlobalClass: true,
    multipleSlots: true,
  },
  /**
   * 组件的属性列表
   */
  properties: {
    selected: {
      type: Number,
      value: 0,
    },
    redDotCount: {
      type: Object,
      value:{
        interactive_count:0,
        private_count:0,
        box_count:0,
        system_count:0,
      }
    }
  },

  /**
   * 组件的初始数据
   */
  data: {
    list: [
      {
        selected: 0,
        iconPath: "/images/tab-bar/icon-community.svg",
        selectedIconPath: "/images/tab-bar/icon-community-hl.svg",
        text: "社区",
      },
      {
        selected: 1,
        iconPath: "/images/tab-bar/icon-mystery-box.svg",
        selectedIconPath: "/images/tab-bar/icon-mystery-box-hl.svg",
        text: "盲盒",
      },
      {
        selected: 2,
        iconPath: "/images/tab-bar/icon-message.svg",
        selectedIconPath: "/images/tab-bar/icon-message-hl.svg",
        text: "消息",
      },
      {
        selected: 3,
        iconPath: "/images/tab-bar/icon-my.svg",
        selectedIconPath: "/images/tab-bar/icon-my-hl.svg",
        text: "我的",
      },
    ],
  },
  lifetimes: {
    attached() {

    },
    detached: function () {
      console.log('页面离开吗 ');

    },
  },
  /**
   * 组件的方法列表
   */
  methods: {
    switchTab(e: any) {
      const data = e.currentTarget.dataset;
      const selected = data.selected;
      if(selected !== 0){
        const token = getLocalToken();
        if (!token) {
          wx.navigateTo({
            url: loginPage,
          });
          return;
        }
      }
      if (selected !== this.data.selected ) {
        this.triggerEvent("click", { selected });
      }
    },
  },
});
