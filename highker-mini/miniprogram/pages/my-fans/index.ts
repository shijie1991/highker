// pages/my-fans/index.scss.ts
import { IAppOption } from "typings";
import { getFansList } from "../../api/user/index";
import { setFollow, setUnFollow } from "../../api/user/index";
import {
  setImageDomainNameSplicing,
  getObtainAVipLevel,
} from "../../utils/util";
import { personalHomePage } from "../../utils/router";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    fansList: [],
    pageIndex: 1,
    isMore: true,
  },
  // 粉丝列表
  async getFansList(userId: string) {
    const res = await getFansList(this.data.options.userId);
    wx.showLoading({ title: "加载中" });
    if (res && res.succeed) {
      let isMore = true
      if (res.links.next) {
        isMore = true
        this.data.pageIndex++
      } else {
        isMore = false
      }

      let list = res.data.map((item: any) => {
        item.vipLevel = getObtainAVipLevel(item.level);
        item = setImageDomainNameSplicing(item, "avatar");
        return item;
      })
      this.setData({
        // fansList: list
        fansList: [...this.data.fansList, ...list], isMore
      });
    }
    wx.hideLoading();
  },
  // 关注和取消关注
  async followedClick(e: any) {
    const userId = e.currentTarget.dataset.id;
    if (userId) {
      let fansList: any = this.data.fansList;
      const user: any = fansList.find((item: any) => item.id === userId);
      if (user) {
        // wx.showLoading({ title: "加载中" });
        user.has_followed = !user.has_followed;
        let res: any = null;
        if (user.has_followed) {
          wx.showLoading({ title: "关注中" });
          res = await setFollow(user.id);
        } else {
          wx.showLoading({ title: " 取消关注" });
          res = await setUnFollow(user.id);
        }
        wx.hideLoading();

        if (res && res.succeed) {
          // wx.showToast({
          //   title: user.has_followed ? "关注成功" : "取消关注成功",
          //   icon: "none",
          // });
          fansList = fansList.map((item: any) => {
            if (item.id === userId) {
              item = user;
            }
            return item;
          });
          this.setData({
            fansList,
          });
        }
      }
    }
  },
  // 点击用户头像和昵称
  onUserInfoClick(e: any) {
    const { id } = e.currentTarget.dataset;
    wx.navigateTo({
      url: `${personalHomePage}?userId=${id}`,
    });
  },
  // 滚动触底
  bindscrolltolower(e) {
    console.log('咕咕咕', e);
    if (this.data.isMore === false) {
      return
    }
    this.getFansList();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options: any) {
    this.data.options = options
    this.getFansList();
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
