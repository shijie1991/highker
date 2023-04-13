// pages/my-follow/index.ts
import { IAppOption } from "typings";
import { getFollowList, setFollow, setUnFollow } from "../../api/user/index";
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
    // followList: null,
    followList: [],
    isMore: true,
    userId: '',
    pageIndex: 1
  },
  // 关注列表
  async getFollowList() {
    const res = await getFollowList(this.data.userId, { page: this.data.pageIndex });
    if (res && res.succeed) {
      let list: any = res.data.map((item: any) => {
        item.vipLevelImage = getObtainAVipLevel(item.level);
        item = setImageDomainNameSplicing(item, "avatar");
        return item;
      })
      let isMore = true
      if (res.links.next) {
        isMore = true
        this.data.pageIndex++
      } else {
        isMore = false
      }
      console.log('list的值', list);

      this.setData({
        followList: [...this.data.followList, ...list],
        isMore
      });
    }
  },
  // 关注和取消关注
  async followedClick(e: any) {
    const userId = e.currentTarget.dataset.id;
    if (userId) {
      let followList: any = this.data.followList;
      const user: any = followList.find((item: any) => item.id === userId);
      if (user) {
        user.has_followed = !user.has_followed;
        let res: any = null;
        if (user.has_followed) {
          wx.showLoading({ title: "关注中" });
          res = await setFollow(user.id);
        } else {
          wx.showLoading({ title: "取消关注" });
          res = await setUnFollow(user.id);
        }
        wx.hideLoading();

        if (res && res.succeed) {
          // wx.showToast({
          //   title: user.has_followed ? "关注成功" : "取消关注成功",
          //   icon: "none",
          // });
          followList = followList.map((item: any) => {
            if (item.id === userId) {
              item = user;
            }
            return item;
          });
          this.setData({
            followList,
          });
        }
      }
    }
  },
  // 点击用户头像和昵称
  onUserInfoClick(e: any) {
    console.log('点击了', e);
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
    this.getFollowList();
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options: any) {
    this.data.userId = options.userId
    this.getFollowList();
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
