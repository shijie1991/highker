import dayjs from "dayjs";
import { commentListPage } from "./router";
// import * as dayjs from 'dayjs'
// import relativeTime from "dayjs/plugin/relativeTime";
// import "dayjs/locale/zh-cn";
// dayjs.locale("zh-cn"); // use locale
// dayjs.extend(relativeTime);
// export const currentRelativeTime = (date: string): string => {
//   if (!date) return "";
//   const isToday = !dayjs(new Date()).isAfter(
//     dayjs(date).format("YYYY-MM-DD"),
//     "day"
//   );
//   return isToday ? dayjs(date).fromNow() : dayjs(date).format("MM-DD HH:mm");
// };
export const currentRelativeTime = (date: string) => {
  // console.log('传进来的值', date);
  // console.log('传进来的值2', dayjs(date).valueOf());
  if (!date) return "";
  const isToday = !dayjs(new Date()).isAfter(
    dayjs(date).format("YYYY-MM-DD"),
    "day"
  );
  // console.log('是否是今天', isToday);
  // let returnData = isToday ? dayjs(date).fromNow() : dayjs(date).format("MM-DD HH:mm");
  let returnData = isToday ? fromNow(dayjs(date).valueOf()) : dayjs(date).format("MM-DD HH:mm");
  // console.log('返回的值', returnData);

  return returnData

}


function fromNow(lastTime) {
  var msPerMinute = 60 * 1000; // 每分钟的毫秒数
  var msPerHour = msPerMinute * 60; // 每小时的毫秒数
  var msPerDay = msPerHour * 24; // 每天的毫秒数
  var msPerMonth = msPerDay * 30; // 每月的毫秒数(按每月30天)
  var msPerYear = msPerDay * 365; // 每年的毫秒数
  var elapsed = Date.now() - lastTime; // 计算上一次时间和当前时间的毫秒数
  if (elapsed < msPerMinute) { // 如果毫秒数小于一分钟的毫秒数
    // return Math.round(elapsed / 1000) + ' seconds ago';
    return Math.round(elapsed / 1000) + ' 秒前';
  }
  else if (elapsed < msPerHour) { // 如果毫秒数小于一小时的毫秒数
    // return Math.round(elapsed / msPerMinute) + ' minutes ago';
    return Math.round(elapsed / msPerMinute) + ' 分钟前';
  }
  else if (elapsed < msPerDay) { // 如果毫秒数小于一天的毫秒数
    // return Math.round(elapsed / msPerHour) + ' hours ago';
    return Math.round(elapsed / msPerHour) + ' 小时前';
  }
  else if (elapsed < msPerMonth) { // 如果毫秒数小于一个月的毫秒数
    return 'approximately ' + Math.round(elapsed / msPerDay) + ' days ago';   // 因为按每月30天计算，所以精确度不是特别高，所以是approximately
  }
  else if (elapsed < msPerYear) { // 如果毫秒数小于一分钟的毫秒数
    return 'approximately ' + Math.round(elapsed / msPerMonth) + ' months ago';
  }
  else { // 如果毫秒数小于一分钟的毫秒数
    return 'approximately ' + Math.round(elapsed / msPerYear) + ' years ago';
  }
}


export function isToday(date: string) {
  const isToday = !dayjs(new Date()).isAfter(
    dayjs(date).format("YYYY-MM-DD"),
    "day"
  );
  return isToday
}