// pages/my-score/index.ts
import { IAppOption } from "typings";
import { getUserScoreLogList } from "../../api/user/index";
import { myTaskCenterPage, myScoreExchangePage } from "../../utils/router";
import { dingYue } from "../../utils/util";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    scoreList: [],
    score: 0,
    isMore: true,
    pageIndex: 1
  },
  async getUserScoreLogList(index?: any) {
    const res = await getUserScoreLogList({ page: this.data.pageIndex });
    if (res && res.succeed) {
      console.log(res);
      let isMore: boolean = false
      if (res.links.next) {
        isMore = true
        this.data.pageIndex++
      } else {
        isMore = false
      }
      let scoreList: any = [...this.data.scoreList, ...res.data];

      this.setData({
        // scoreList: res.data,
        scoreList: scoreList,
        score: res.score,
        isMore
      });
    }
  },
  toTaskPage() {
    wx.navigateTo({
      url: myTaskCenterPage,
    });
  },
  toMyScoreExchangePage() {
    wx.navigateTo({
      url: myScoreExchangePage,
    });
  },
  // 滚动触底
  bindscrolltolower(e) {
    console.log('咕咕咕', e);
    if (this.data.isMore === false) {
      return
    }
    this.getUserScoreLogList();
  },
  clickMyGold() {
    // dingYue()
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad() {
    this.getUserScoreLogList();
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady() { },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {
  },

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
