import { IAppOption } from "../../typings";
import { TMPLIDS } from "./constants";
import { baseConfig } from "../base.config";
const app = getApp<IAppOption>();
// 自定底部菜单点击需要跳转到对应路由
export const setCustomTabBarToIndex = (self: any, index: number): void => {
  if (typeof self.getTabBar === "function" && self.getTabBar()) {
    self.getTabBar().setData({
      selected: index,
    });
  }
};
// vip等级列表
export const vipLevelList = (): AnyArray => {
  const len: number = 25;
  const rezult: Array<string> = [];
  for (let i = 0; i < len; i++) {
    rezult.push("/images/vip-level-list/vip" + i + ".svg");
  }
  return rezult;
};
// // 获取当前vip等级
// export const getObtainAVipLevel = (index: number): string => {
//   return `/images/vip-level-list/vip${index}.svg`;
// };
// 获取当前vip等级2
export const getObtainAVipLevel = (index: number): string => {
  return `/images/vip-levell-png/vip${index}.png`;
};

// 头像域名拼接
export const setImageDomainNameSplicing = (
  rezult: any,
  avatarKey = "avatar"
): any => {
  const str = new RegExp("http");
  // const URL = (app.globalData.gCommonInfo.cdn_url + "/" || baseConfig.baseCdnURL);
  const URL = baseConfig.baseCdnURL;
  if (Array.isArray(rezult)) {
    rezult = rezult.map((item: any) => {
      if (!str.test(item[avatarKey]) && item[avatarKey]) {
        item[avatarKey] = URL + item[avatarKey];
      }
      return item;
    });
  } else {
    if (!str.test(rezult[avatarKey]) && rezult[avatarKey]) {
      rezult[avatarKey] = URL + rezult[avatarKey];
    }
  }
  return rezult;
};
//分组
export function groupBy(list: Array<any>, fn: Function): Array<any> {
  const groups: any = {};
  list.forEach(function (o) {
    const group = JSON.stringify(fn(o));
    groups[group] = groups[group] || [];
    groups[group].push(o);
  });

  return groups;
}


// 上传图片过大的提示 
export function toBigImage() {
  wx.showModal({
    title: '提示',
    content: `上传文件大小不能超过 5M`,
    showCancel: false,
  })
}

// 等级限制表情和图片回复
export function isLevelLimit(type: number) {
  // 1 表情回复拦截 2图片回复拦截
  const userInfo = wx.getStorageSync('userInfo')
  console.log('当前的会员信息', userInfo);

  if (userInfo.is_vip === true) { return false }
  if (userInfo.level < 10 && type === 1) {
    wx.showToast({
      title: '10级解锁特权',
      icon: 'error'
    })
    return true
  }
  if (userInfo.level < 15 && type === 2) {
    wx.showToast({
      title: '15级解锁特权',
      icon: 'error'
    })
    return true
  }
  return false
}

export function isVipLimit(type: number) {
  // 1 图片  2 语音
  const userInfo = wx.getStorageSync('userInfo')
  if (userInfo.is_vip === false) {
    wx.showModal({
      content: type === 1 ? 'vip才能发图片盲盒' : 'vip才能发语音盲盒',
      showCancel: false,
    })
    return true
  }
  return false
}

// export const debounce = (fn: Function, wait: number, immediate?: boolean) => {
//   let timeout: number;

//   function debounced(/* ...args */) {
//     const args = arguments;
//     const later = () => {
//       timeout = 0;
//       if (immediate !== true) {
//         fn.apply(this, args);
//       }
//     };

//     clearTimeout(timeout);
//     if (immediate === true && timeout === void 0) {
//       fn.apply(this, args);
//     }
//     timeout = setTimeout(later, wait);
//   }

//   debounced.cancel = () => {
//     clearTimeout(timeout);
//   };

//   return debounced;
// };

/**
 * 节流函数--规定执行的频率,无论点击多频繁 都是隔规定时间就执行一次
 * @param fn 传入的函数
 * @param intervalTime 间隔的时间
 * @param doItNow 是否立即执行一次
 */
export function myThrottle(fn: Function, intervalTime: Number, doItNow = true) {
  let timer: any
  let hasDoOnce = false
  return function () {
    let args = arguments
    if (doItNow && hasDoOnce === false) {
      hasDoOnce = true
      return fn.apply(this, args)
    }
    if (timer) {
      return
    }
    timer = setTimeout(() => {
      fn.apply(this, args)
      timer = null
    }, intervalTime);
  }
}


// 订阅通知
export async function dingYue() {
  try {
    let res = await wx.requestSubscribeMessage({
      tmplIds: TMPLIDS,
    })
    console.log('订阅的结果', res);
  } catch (error) {
    console.log('订阅不成功的原因', error);
  }
}

// 表情里面点删除
export function _bindemojiDelete(content: string, _cursor: any) {

  // const pos = this.data.cursor
  const pos = _cursor
  const comment = content
  let result = ''
  let cursor = 0

  let emojiLen = 6
  let startPos = pos - emojiLen
  if (startPos < 0) {
    startPos = 0
    emojiLen = pos
  }
  const str = comment.slice(startPos, pos)
  const matchs = str.match(/\[([\u4e00-\u9fa5\w]+)\]$/g)
  // 删除表情
  if (matchs) {
    const rawName = matchs[0]
    const left = emojiLen - rawName.length
    // if (this.emojiNames.indexOf(rawName) >= 0) {
    const replace = str.replace(rawName, '')
    result = comment.slice(0, startPos) + replace + comment.slice(pos)
    cursor = startPos + left
    // }
    // 删除字符
  } else {
    let endPos = pos - 1
    if (endPos < 0) endPos = 0
    const prefix = comment.slice(0, endPos)
    const suffix = comment.slice(pos)
    result = prefix + suffix
    cursor = endPos
  }
  return { content: result, cursor }

  this.setData({
    comment: result,
    cursor
  })

  return


  console.log('传入的内容', content);

  let length = content.length
  if (length === 0) {
    return false
  }
  if (length < 3) {
    console.log('情况1');

    return content.slice(0, length - 1)
  }
  let reg = /[[\u4E00-\u9FA5]{1,5}]$/
  // let reg = /[[\S]{1,6}]$/
  // let reg = /^[\[\S]*]$/
  // let reg = /[[\S]{1,8}]/
  // let reg = /[[\u4E00-\u9FA5]{1,3}]$/
  // const  reg = new RegExp('/'^\[[\S]{1,3}\]$') 
  if (reg.test(content)) {
    console.log('情况2');
    return content.replace(reg, '')
  } else {
    console.log('情况3');
    return content.slice(0, length - 1)
  }
}