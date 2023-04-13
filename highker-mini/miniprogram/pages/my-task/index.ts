// pages/my-task/index.ts
import { IAppOption } from "typings";

import { getTaskInfo } from "../../api/user/index";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    taskDailyMissionList: [],
    taskNewsTaskList: [],
    signInNum: 0,
    // dailyTask
  },
  async getTaskInfo() {
    const res = await getTaskInfo();
    if (res && res.succeed) {
      console.log(res);
      const signInNum = res.data.daily_login[0];
      const taskDailyMissionList = res.data.daily_task;
      const taskNewsTaskList = res.data.once_task;
      this.setData({
        signInNum,
        taskDailyMissionList,
        taskNewsTaskList,
      });
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad() {
    this.getTaskInfo();
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
