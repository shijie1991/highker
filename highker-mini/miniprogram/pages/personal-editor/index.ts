// pages/personalProfile/index.ts
import { getObtainingUserInfo } from "../../api/user/index";
import { setImageDomainNameSplicing ,toBigImage} from "../../utils/util";
import { baseConfig } from "../../base.config";
import { tokenKey, getLocalToken } from "../../utils/token";
import { PURPOSE_LIST, EMOTION_LIST } from "../../utils/constants";
import { setUserInfo } from "../../api/user/index";

import { IAppOption } from "typings";
const app = getApp<IAppOption>();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    purposeList: PURPOSE_LIST,
    emotionList: EMOTION_LIST,
    form: {
      // 昵称
      name: "",
      // 性别
      gender: 1,
      // 目的
      purpose: 0,
      // 情感
      emotion: 0,
      // 生日
      birthday: "",
      // 描述
      description: "",
      // 所在地
      region: "",
    },
    // 头像，头像单独上传调用接口
    avatar: "",
    regionArray: [],
  },
  // 获取用户信息
  async getObtainingUserInfo(userId: string) {
    const form = this.data.form;
    const res = await getObtainingUserInfo(userId);

    if (res && res.succeed) {
      res.data = setImageDomainNameSplicing(res.data, "avatar");
      const data = res.data;
      form.name = data.name;
      form.gender = data.gender || 1;
      form.description = data.info.description || "";
      form.emotion = data.info.emotion || 0;
      form.purpose = data.info.purpose || 0;
      form.birthday = data.info.birthday || "";
      this.setData({
        form,
        avatar: data.avatar,
      });
    }
  },
  // 修改头像
  uploadAvatar() {
    wx.chooseImage({
      count: 1,
      success: (res) => {
        const tempFiles = res.tempFiles;
        this.data.avatar = tempFiles[0].path;
        this.setData({
          avatar: this.data.avatar,
        });
        wx.uploadFile({
          url: baseConfig.baseURL + "users/setting",
          filePath: this.data.avatar,
          header: {
            [tokenKey]: getLocalToken(),
          },
          name: "avatar",
          success: (res) => {
            if (res.statusCode === 413) toBigImage()

            const data = JSON.parse(res.data);
            if (data.status !== "success") {
              wx.showToast({
                title: data.message,
              });
            }
          },
          fail: () => {
            wx.showToast({
              title: "上传失败",
            });
          },
        });
      },
    });
  },
  // 文本输入
  textareaAInput(e: any) {
    const form: any = this.data.form;
    const key = e.currentTarget.dataset.name;
    if (key) {
      form[key] = e.detail.value;
    }
    this.setData({
      form,
    });
  },
  //
  selectChange(e: any) {
    const form: any = this.data.form;
    const key = e.currentTarget.dataset.name;
    const value = e.detail.value;
    if (key) {
      if (key === "birthday") {
        form.birthday = value;
      } else if (key === "emotion") {
        const emotionNode = this.data.emotionList.find(
          (_, index) => index === parseInt(value, 10)
        );
        if (emotionNode) {
          form.emotion = emotionNode.id;
        }
      } else if (key === "region") {
        this.data.regionArray = value;
      } else if (key === "purpose") {
        const purposeNode = this.data.purposeList.find(
          (_, index) => index === parseInt(value, 10)
        );
        if (purposeNode) {
          form.purpose = purposeNode.id;
        }
      }

      this.setData({
        form,
        regionArray: this.data.regionArray,
      });
    }
  },
  async submit() {
    const params: any = {};
    const form: any = this.data.form;
    if (this.data.regionArray.length) {
      this.data.form.region = this.data.regionArray.join("-");
    }
    if (this.data.form.name.trim() === "") {
      wx.showToast({
        icon: "none",
        title: "昵称不能为空！",
      });
      return;
    }
    wx.showLoading({ title: "加载中" });
    for (let key in this.data.form) {
      const value = form[key];
      if (value || key === 'description') {
        params[key] = value;
      }
    }
    const res = await setUserInfo(params);
    wx.hideLoading();
    if (res && res.succeed) {
      wx.navigateBack();
    }
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options: any) {
    this.getObtainingUserInfo(options.userId);
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
