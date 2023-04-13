/// <reference path="./types/index.d.ts" />

interface IAppOption {
  globalData: {
    userInfo?: any;
    statusBarHeight?: number;
    playTimeInterval?: number;
    customObj?: WechatMiniprogram.IAnyObject;
    customBarHeight: number;
    gCommonInfo?: any;
    emojiURL: string;
    SocketTask: any;
    timer?: number;
    unreadObj?: any;
  };
  getCommonInfo: Function;
  setSystemInfo: Function;
  userInfoReadyCallback?: WechatMiniprogram.GetUserInfoSuccessCallback;
}
export interface IFormParams {
  code: string;
  phone: string;
  name: string;
  gender: number;
  avatar?: any;
  // 微信头像地址
  avatarUrl?: string;
}
