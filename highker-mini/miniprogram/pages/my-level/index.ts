import { IAppOption } from "typings";
import { getLevelInfo, getUserTaskLogList } from "../../api/user/index";
import { groupBy } from "../../utils/util";
import { myTaskCenterPage } from "../../utils/router";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    tab: "levelPrivilege",
    tabs: [
      { text: "等级特权", id: "levelPrivilege" },
      { text: "经验明细", id: "experienceInDetail" },
    ],
    levelInfo: null,
    levelList: [],
    prerogative: null,
    taskLogList: [],
    isMore: false,
    pageIndex: 1
  },
  onSwitchTab(e: any) {
    const value = e.detail;
    if (value.id === this.data.tab) return;
    this.setData({
      tab: value.id,
    });
    // if (value.id === "experienceInDetail" && !this.data.taskLogList) {
    if (value.id === "experienceInDetail" && this.data.taskLogList.length === 0) {
      this.getUserTaskLogList();
    }
  },
  // 等级特权
  async getLevelInfo() {
    const res = await getLevelInfo();
    if (res && res.succeed) {
      const levelInfo = res.data.level_info;
      const prerogative = res.data.prerogative;
      const list: any = [];
      const level: any = groupBy(
        Object.keys(res.data.level_list).map((key) => res.data.level_list[key]),
        (item: any) => {
          return item.unlocked;
        }
      );
      if (level["true"]) {
        list.push({ text: "已解锁特权", nodes: level["true"] });
      }
      if (level["false"]) {
        list.push({ text: "未解锁特权", nodes: level["false"] });
      }

      this.setData({
        levelInfo,
        levelList: list,
        prerogative,
      });
    }
  },
  // 经验明细
  async getUserTaskLogList() {
    const res = await getUserTaskLogList({ page: this.data.pageIndex });
    if (res && res.succeed) {
      let isMore: boolean = false
      res.links.next && (isMore = true)
      this.setData({
        taskLogList: [...this.data.taskLogList, ...res.data],
        isMore
      });
    }
  },
  toTaskPage() {
    wx.navigateTo({
      url: myTaskCenterPage,
    });
  },
  // 上拉触底事件
  bindscrolltolower() {
    if (this.data.tab !== 'experienceInDetail') {
      return
    }
    if (this.data.isMore === false) {
      return
    }
    this.data.pageIndex++
    this.getUserTaskLogList()
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad() {
    this.getLevelInfo();
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
