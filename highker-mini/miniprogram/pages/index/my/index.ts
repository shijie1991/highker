import { IAppOption } from "../../../../typings";
import { MY_CELL_LIST } from "../../../utils/constants";
import { getMyBeseInfo, logout } from "../../../api/account/index";
import { clearToken } from "../../../utils/token";

import {
  myFollowPage,
  myFansPage,
  personalHomePage,
  myLevelPage,
  myScorePage,
  myScoreExchangePage,
  myVisitorPage,
  myTaskCenterPage,
  FAQPage,
  userFeedbackPage,
  aboutPage,
  subscriptionPage,
  memberCenterPage, homePage
} from "../../../utils/router";
import {
  getObtainAVipLevel,
  setImageDomainNameSplicing,
} from "../../../utils/util";
const app = getApp<IAppOption>();
Component({
  options: {
    addGlobalClass: true,
    multipleSlots: true,
  },
  lifetimes: {
    attached() {
      this.getMyBeseInfo();
    },
  },
  pageLifetimes: {
    show() {
      this.getMyBeseInfo();
    },
  },
  /**
   * 页面的初始数据
   */
  data: {
    userInfo: app.globalData.userInfo,
    // 菜单列表
    myCellList: MY_CELL_LIST,
    customBarHeight: app.globalData.customBarHeight,
    // 个人基础信息
    baseInfo: {},
  },
  methods: {
    // 获取用户基础信息
    async getMyBeseInfo() {
      const res = await getMyBeseInfo();
      if (res && res.succeed) {
        res.data.levelImage = getObtainAVipLevel(res.data.level);
        res.data = setImageDomainNameSplicing(res.data, "avatar");
        wx.setStorageSync('userInfo', res.data)
        this.setData({
          baseInfo: res.data,
        });
      }
    },
    // 我的关注
    toMyFollowPage() {
      const baseInfo: any = this.data.baseInfo;
      wx.navigateTo({
        url: `${myFollowPage}?userId=${baseInfo.id}`,
      });
    },
    // 我的粉丝
    toMyFansPage() {
      const baseInfo: any = this.data.baseInfo;
      wx.navigateTo({
        url: `${myFansPage}?userId=${baseInfo.id}`,
      });
    },
    // 我的访客
    toMyVisitorPage() {
      wx.navigateTo({
        url: myVisitorPage,
      });
    },
    // 查看头像
    getThePreviewImage() {
      const baseInfo: any = this.data.baseInfo;
      wx.previewImage({
        urls: [baseInfo.avatar],
      });
    },
    // 个人主页
    toPersonalHomepage() {
      const baseInfo: any = this.data.baseInfo;
      wx.navigateTo({
        url: `${personalHomePage}?userId=${baseInfo.id}`,
      });
    },
    menuItemClick(e: any) {
      const key = e.currentTarget.dataset.id;
      let url = "";
      console.log(key);
      switch (key) {
        // 我的等级
        case "level":
          url = myLevelPage;
          break;
        // 我的金币
        case "score":
          url = myScorePage;
          break;
        // 我的金币
        case "scoreExchange":
          url = myScoreExchangePage;
          break;
        case "theTaskCenter":
          url = myTaskCenterPage;
          break;
        case "faq":
          url = FAQPage;
          break;
        // case "feedback":
        //   url = userFeedbackPage;
        //   break;
        case "about":
          url = aboutPage;
          break;
        case "subscription":
          url = subscriptionPage;
          break;
        case "memberCenter":
          url = memberCenterPage;
          break;
        default:
          break;
      }
      if (url) {
        wx.navigateTo({
          url,
        });
      }
    },
    // 退出登陆
    async _logout() {
      wx.showModal({
        title: '提示',
        content: '是否确定要退出登录?',
        async success(res) {
          if (res.confirm) {
            console.log('用户点击确定')
            await logout()
            clearToken()
            wx.removeStorage({key:'userInfo'})
            wx.reLaunch({ url: homePage });
          } else if (res.cancel) {
            console.log('用户点击取消')
          }
        }
      })


    }
  },
});
