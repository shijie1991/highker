import { rankingDetail } from "../../api/common/index";
import {
  setImageDomainNameSplicing,
  getObtainAVipLevel,
} from "../../utils/util";
import { setFollow, setUnFollow } from "../../api/user/index";
import { personalHomePage } from "../../utils/router";

const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    slug: '',
    list: null,
    ranking: null,
    name: null
  },
  async getRankDetail() {
    let res = await rankingDetail(this.data.slug)
    // res.data.forEach((element: any) => {
    //   element = setImageDomainNameSplicing(element, 'avatar')
    //   if (element.level > 0) {
    //     element.vipLevel = getObtainAVipLevel(element.level);
    //   }
    // });

    for (const key in res.data) {
      let element = res.data[key];
      element = setImageDomainNameSplicing(element, 'avatar')
      element.index = Number(key)
      if (element.level > 0) {
        element.vipLevel = getObtainAVipLevel(element.level);
      }
    }
    console.log('res.data的值', res.data);

    this.setData({
      list: res.data,
      ranking: res.ranking
    })
    // this.setData({
    // 	list: [...res.data, ...res.data, ...res.data, ...res.data]
    // })
  },
  // 关注和取消关注
  async followedClick(e: any) {
    const { id, item, index } = e.currentTarget.dataset;
    // let list: any = this.data.list;
    // wx.showLoading({ title: "加载中" });
    let res: any = null;
    if (item.has_followed) {
      wx.showLoading({ title: " 取消关注" });
      res = await setUnFollow(id);
    } else {
      wx.showLoading({ title: "关注中" });
      res = await setFollow(id);
    }

    if (res && res.succeed) {
      // this.setData({
      //   [`list[${index}].has_followed`]: !item.has_followed,
      // });
      this.getRankDetail()
    }
    wx.hideLoading();
  },
  // 点击用户头像和昵称
  onUserInfoClick(e: any) {
    console.log('点击了', e);
    const { id } = e.currentTarget.dataset;
    wx.navigateTo({
      url: `${personalHomePage}?userId=${id}&others=${true}`,
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(query: any) {
    console.log('进入的参数', query);
    this.data.slug = query.slug
    this.setData({
      name: query.name
    })
    this.getRankDetail()
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  // onPullDownRefresh() {

  // },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage() {

  }
})