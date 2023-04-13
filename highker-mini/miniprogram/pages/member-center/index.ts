// pages/member-center/index.ts
import { IAppOption } from "typings";
import { getVip, setVipPay } from "../../api/vip/index";
import { setImageDomainNameSplicing } from "../../utils/util";
import { THEMEMBER_CENTER_PRIVILEGE_LIST } from "../../utils/constants";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    //
    payList: [],
    userInfo: null,
    slug: 1,
    isIos: false,

    // 会员特权列表 
    thememberCenterPrivilegeList: THEMEMBER_CENTER_PRIVILEGE_LIST,
  },

  // vip信息
  async getVip() {
    const res = await getVip();
    if (res && res.succeed) {
      if (res && res.data.user) {
        res.data.user = setImageDomainNameSplicing(res.data.user, "avatar");
      }
      this.setData({
        userInfo: res.data.user,
        payList: res.data.vip,
      });
    }
  },
  async requestPayment() {
    wx.showLoading({
      title: "正在加载...",
    });
    const res = await setVipPay(this.data.slug);
    if (res && res.succeed) {
      wx.requestPayment({
        ...res.data,
        success: () => {
          wx.showToast({
            icon: "success",
            title: "支付成功",
          });
          this.getVip();
        },
        fail(res: any) {
          wx.showToast({
            icon: "none",
            title: res,
          });
        },
      });
    }
    wx.hideLoading();
  },
  payItemClick(e: any) {
    // console.log(e);
    let { id } = e.currentTarget.dataset
    this.setData({
      slug: id
    })
  },
  // 设置是否为ios手机的标识
  setIos() {
    let systemInfo = wx.getStorageSync('systemInfo')
    console.log('是否为ios,systemInfo', systemInfo);
    if (systemInfo.system.includes('iOS')) {
      console.log('是否为ios2');

      this.setData({
        isIos: true
      })
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad() {
    this.getVip();
    this.setIos()
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
