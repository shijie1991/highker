// pages/my-score-exchange/index.ts
import { IAppOption } from "typings";
import { getUserScoreList, setExchangeScore } from "../../api/user/index";
import { myTaskCenterPage } from "../../utils/router";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    score: 0,
    list: null,
  },
  // 获取兑换列表
  async getUserScoreList() {
    const res = await getUserScoreList();
    if (res && res.succeed) {
      this.setData({
        score: res.data.score,
        list: res.data.list,
      });
    }
  },
  // 兑换
  async bindExchangeScore(e: any) {
    const slug = e.currentTarget.dataset.id;
    const list: any = this.data.list;
    const scoreItem = list[slug];
    if (scoreItem && this.data.score >= scoreItem.score) {
      wx.showLoading({ title: "加载中" });
      const res = await setExchangeScore(slug);
      if (res && res.succeed) {
        this.setData({
          score: this.data.score - scoreItem.score,
        });
        wx.showToast({
          icon: "success",
          title: "兑换成功",
          duration: 1000
        });
      }
      wx.hideLoading();
    }
  },
  toTaskPage() {
    wx.navigateTo({
      url: myTaskCenterPage,
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad() {
    this.getUserScoreList();
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady() {},

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {},

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide() {},

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload() {},

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh() {},

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom() {},

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage() {},
});
