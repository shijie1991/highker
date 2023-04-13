import { IAppOption } from "typings";
import { getVisitsList, setFollow, setUnFollow } from "../../api/user/index";

import {
  setImageDomainNameSplicing,
  getObtainAVipLevel,
} from "../../utils/util";
import { personalHomePage } from "../../utils/router";

// import { THEMEMBER_CENTER_PRIVILEGE_LIST } from "../../utils/constants";

let vips = [
  { "slug": 1, "moon": 1, "price": 1, "discount": "", "name": "1\u4e2a\u6708", "day": "\u6bcf\u5929\u4ec5 0.9 \u5143" },
  { "slug": 2, "moon": 3, "price": 68, "discount": "8.1\u6298", "name": "3\u4e2a\u6708", "day": "\u6bcf\u5929\u4ec5 0.8 \u5143" },
  { "slug": 3, "moon": 12, "price": 198, "discount": "5.9\u6298", "name": "12\u4e2a\u6708", "day": "\u6bcf\u5929\u4ec5 0.6 \u5143" }
]

const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    visitorList: null,
    is_vip: false,
    payList: vips,
    slug: 1,
    isMore: false,
    pageIndex: 1

  },
  async getVisitsList() {
    const res = await getVisitsList({ page: this.data.pageIndex });
    res.data.forEach((element: any) => {
      element.visitor = setImageDomainNameSplicing(element.visitor, 'avatar')
      if (element.visitor.level > 0) {
        element.visitor.vipLevel = getObtainAVipLevel(element.visitor.level);
      }
    });

    if (res && res.succeed) {
      let list = this.data.visitorList ? [...this.data.visitorList, ...res.data] : res.data
      this.setData({
        // isVip: res.is_vip,
        is_vip: res.is_vip,
        day_visit_count: res.day_visit_count,
        visit_count: res.visit_count,
        // visitorList: res.data,
        visitorList: list,
        isMore: !!res?.links?.next
      });
    }
  },
  // 关注 取消关注
  async followedClick(e: any) {
    let { visitor, index } = e.currentTarget.dataset;
    console.log('数据', visitor, index);

    let res: any = null;
    if (visitor.has_followed) {
      wx.showLoading({ title: "取消关注" });
      res = await setUnFollow(visitor.id);
    } else {
      wx.showLoading({ title: "关注中" });
      res = await setFollow(visitor.id);
    }
    wx.hideLoading();
    console.log('打印', `visitorList[${index}].visitor.has_followed`);

    if (res && res.succeed) {
      this.setData({
        // [`visitorList[${index}].visitor.has_followed]`]: 
        [`visitorList[${index}].visitor.has_followed`]: !visitor.has_followed
      })
    }
  },
  payItemClick(e: any) {
    // console.log(e);
    let { id } = e.currentTarget.dataset
    this.setData({
      slug: id
    })
  },
  // 点击用户头像和昵称
  onUserInfoClick(e: any) {
    // console.log('点击了', e);
    const { id } = e.currentTarget.dataset;
    wx.navigateTo({
      url: `${personalHomePage}?userId=${id}`,
    });
  },
  // 上拉触底事件
  bindscrolltolower() {
    this.data.pageIndex++
    this.getVisitsList()
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad() {
    this.getVisitsList();
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
