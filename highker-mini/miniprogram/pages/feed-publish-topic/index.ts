import { IAppOption } from "typings";

// pages/feed-publish-topic/index.ts
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    selectedNodes: [],
  },
  onChangeTopicSelected(e: any) {
    console.log(
      '选择的话题', e
    );

    this.setData({
      selectedNodes: e.detail,
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(query: any) {
    console.log('进入话题列表页面的参数', query);
    let topicList = JSON.parse(query.topicList)
    this.setData({
      selectedNodes: topicList,
    });
    // let topicList= query
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
  onUnload() {
    const pages = getCurrentPages();
    const prevPage = pages[pages.length - 2];
    if (prevPage) {
      prevPage.setData({
        topicList: this.data.selectedNodes,
      });
    }
  },

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
