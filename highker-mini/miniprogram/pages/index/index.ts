// pages/index/index.ts
import { ranking } from "../../api/common/index";
import { conversationsRed } from "../../api/user/index";

const app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    selected: 0,
    redDotcount:{
      interactive_count:0,
      private_count:0,
      box_count:0,
      system_count:0,
    },
  },

  switchTab(e: any) {
    const selected = e.detail.selected;
    console.log('selected的值', selected);

    this.setData({
      selected,
    });

  },

  setRedDotCount(key :any){
    if(key.detail.isClear){
      this.setData({["redDotcount."+key.detail.name]: 0});
    }else{
      this.setData({["redDotcount."+key.detail.name]: (this.data.redDotcount as any)[key.detail.name] - key.detail.value});
    }
  },

  // 发布盲盒成功之后需要在子页面调用，弹出已经发布盲盒弹框
  setBoxDialogType() {
    const boxRef = this.selectComponent("#box");
    if (boxRef) {
      boxRef.setDialogType("ADD_MESSAGE");
    }
  },

  async getUnreadMessage() {
    let res = await conversationsRed()

    let { interactive_count, box_count, private_count, system_count } = res.data
    this.setData({
      "redDotcount.interactive_count":interactive_count,
      "redDotcount.box_count":box_count,
      "redDotcount.private_count":private_count,
      "redDotcount.system_count":system_count,
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad() {
    ranking();
    if (wx.getStorageSync('Authorization')) {
      this.getUnreadMessage()
      clearInterval(app.globalData.timer)
      app.globalData.timer = setInterval(() => {
        this.getUnreadMessage()
      }, 20000)
    };
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
    clearInterval(app.globalData.timer)
   },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  // onPullDownRefresh() { },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom() { },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage() { },
});
