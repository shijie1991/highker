// pages/login/index.ts
import { login } from "../../api/account/index";
import {
  homePage,
  improveUserInfoPage,
  agreementPage,
} from "../../utils/router";
import Qs from "qs";
import { IAppOption, IFormParams } from "../../../typings/index";
import { getLocalToken } from "../../utils/token";
import { getMyBeseInfo } from "../../api/account/index";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    isModalShow: false,
    //
    isAgreement: true,
    formParams: <IFormParams>{
      code: "",
      phone: "",
      name: "",
      gender: 0,
      // 微信头像地址
      avatarUrl: "",
    },
  },
  // 获取手机号
  getPhoneNumber(e: any) {
    const formParams: IFormParams = this.data.formParams;
    if (!this.data.isAgreement) {
      wx.showToast({
        title: "请同意用户协议",
        icon: "error",
      });
      return;
    }
    // 微信登录
    wx.login({
      success: (res) => {
        formParams.code = res.code;
        if (e.detail.code) {
          this.login(e.detail.code);
        }
      },
    });
  },
  // 获取用户信息  
  async getUserInfo() {
    const res = await getMyBeseInfo();
    if (res && res.succeed) {
      wx.setStorageSync("userInfo", res.data);
    }
  },
  /**
   * @method 登录注册
   * @description 如果返回手机号就是未注册，否则直接进入登录
   */
  async login(code: string) {
    wx.showLoading({ title: "加载中" });
    const res = await login(code);
    if (res && res.succeed) {
      const formParams: IFormParams = this.data.formParams;
      //   await this.getUserInfo();
      // 未注册
      if (res.data.phone) {
        // this.setData({
        // 	isModalShow: true,
        // });

        formParams.phone = res.data.phone;
        // wx.redirectTo({ url: improveUserInfoPagey });
        const query = Qs.stringify(formParams);
        wx.navigateTo({ url: improveUserInfoPage + '?' + query });

      } else {
        await this.getUserInfo();
        wx.reLaunch({ url: homePage });
      }
    }
    wx.hideLoading();
  },
  hideModal() {
    this.setData({
      isModalShow: false,
    });
  },

  getLastSpritAvatarUrl(str: string): string {
    let fileName = str.lastIndexOf("/");
    let fileFormat = str.substring(0, fileName);
    return fileFormat + "/0";
  },
  // 获取用户头像昵称
  getUserProfile() {
    wx.getUserProfile({
      desc: "完善会员资料",
      lang: "zh_CN",

      success: async (res) => {
        const userInfo = res.userInfo;
        const formParams: IFormParams = this.data.formParams;
        formParams.name = userInfo.nickName;
        formParams.gender = userInfo.gender;

        if (userInfo.avatarUrl) {
          formParams.avatarUrl = this.getLastSpritAvatarUrl(userInfo.avatarUrl);
        }
        const query = Qs.stringify(formParams);
        wx.redirectTo({ url: improveUserInfoPage + "?" + query });
      },
    });
  },
  onAgreement() {
    this.setData({
      isAgreement: true,
    });
  },
  // 查看用户协议
  checkTheAgreement() {
    wx.navigateTo({
      url: agreementPage,
    });
  },
  // 返回上一页
  backPage() {
    if (getCurrentPages().length === 1) {

      wx.redirectTo({
        url: `/pages/index/index`
      })
      return
    }
    wx.navigateBack({
      delta: 1,
    });
	},
	backHome(){},
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad() {
    if (getLocalToken()) {
      wx.reLaunch({
        url: homePage,
      });
    }
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady() { },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() { },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide() { },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload() { },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh() { },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom() { },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage() { },
});
