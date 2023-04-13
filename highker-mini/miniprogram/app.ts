import { IAppOption } from "../typings";
import { getCommonInfo } from "./api/common/index";

// app.ts
App<IAppOption>({
  globalData: {
    customBarHeight: 60,
    gCommonInfo: {},
    emojiURL:
      "https://hk-resources.oss-cn-beijing.aliyuncs.com/emoji/eROMsLpnNC10dC40vzF8qviz63ic7ATlbGg20lr5pYykOwHRbLZFUhgg23RtVorX.png",
    playTimeInterval: 0,
    SocketTask: null,
    timer: 0,
  },
  // 获取公共参数  登陆初始化
  async getCommonInfo() {
    const res = await getCommonInfo();
    if (res && res.succeed) {
      this.globalData.gCommonInfo = res.data;
    }
  },
 
  async setSystemInfo() {
    wx.getSystemInfo({
      success: (e) => {
        this.globalData.statusBarHeight = e.statusBarHeight;
        let capsule = wx.getMenuButtonBoundingClientRect();
        if (capsule) {
          this.globalData.customObj = capsule;
          this.globalData.customBarHeight =
            capsule.bottom + capsule.top - e.statusBarHeight;
        } else {
          this.globalData.customBarHeight = e.statusBarHeight + 50;
        }
        wx.setStorageSync("systemInfo", e);
      },
    });
  },

  // 获取用户信息
  async onLaunch() {
    this.setSystemInfo();
    await this.getCommonInfo();
  },
  onShow() {},
});
