// pages/index/message.ts
import { IAppOption } from "../../../../typings";
import { MESSAGE_TOP_NAV_MENU_LIST } from "../../../utils/constants";
import {
  getNotificationInteractive,
  getNotificationSystem,
} from "../../../api/notification/index";
import {
  getConversationList,
  removeConversationsItem,
} from "../../../api/user/index";
import { setImageDomainNameSplicing } from "../../../utils/util";
import { currentRelativeTime, isToday } from "../../../utils/date";
import { parseEmoji } from "../../../components/emoji/utils/index";
import { chatPage, personalHomePage } from "../../../utils/router";
// import { commentListPage, } from "../../utils/router";
import dayjs from "dayjs";

// item.emojiArray = parseEmoji(item.content?.text);
function setUpdateTime(timeString: any) {
  let _isToday = isToday(timeString)
  if (_isToday) {
    return dayjs(timeString).format("HH:mm")
  }
  if (isCurYear(timeString)) {
    return dayjs(timeString).format("MM-DD")
  }
  return dayjs(timeString).format("YYYY-MM-DD")
}

function isCurYear(time) {
  const year = new Date().getFullYear();
  const lastYear = new Date(time).getFullYear();
  return (year - lastYear) === 0;

}

const app = getApp<IAppOption>();
Component({
  options: {
    addGlobalClass: true,
    multipleSlots: true,
  },
  properties: {
    redDotCount: {
      type: Object,
      value: {
        interactive_count: 0,
        private_count: 0,
        box_count: 0,
        system_count: 0,
      }
    }
  },
  pageLifetimes: {
    show: function () {
      // 页面被展示  
      this.getConversationList();
    }
  },
  lifetimes: {
    attached: function () {
      this.getConversationList();
      // let { interactive_count, box_count, private_count, system_count } = app.globalData.unreadObj
      // this.setData({
      //   interactive_count, box_count, private_count, system_count
      // })
    },
  },
  /**
   * 组件的初始数据
   */
  data: {
    emojiURL: app.globalData.emojiURL,
    customBarHeight: app.globalData.customBarHeight,
    slideButtons: [{ type: "warn", text: "删除" }],
    // 头部导航菜单
    // navMenuActivation: "mysteryBox",
    // 头部菜单列表
    navigationMenuList: MESSAGE_TOP_NAV_MENU_LIST,
    conversationList: [],
    conversationType: "box",
    // conversationType: "private",
    conversationLoading: true,
    notificationShow: false,
    notificationTab: "system",
    notificationSystemList: [],
    notificationInteractiveList: [],
    notificationSystemLoading: true,
    notificationInteractiveLoading: true,
    tabs: [
      {
        id: "system",
        text: "系统通知",
      },
      {
        id: "interactive",
        text: "互动通知",
      },
    ],
    // 下拉刷新的状态
    refreshStatus: false,
    huDong: {
      pageIndex: 1,
      isMore: false
    },
    xiTong: {
      pageIndex: 1,
      isMore: false
    }
  },
  methods: {
    userItemClick(e: any) {
      console.log(e);
      const { userid, name, cid, unread } = e.currentTarget.dataset;
      if (unread) {
        let key = this.data.conversationType == 'box' ? 'box_count' : 'private_count';
        // 设置已读消息数量
        this.triggerEvent('redDot', { name: key, value: unread, isClear: false })
      }

      // const name = e.currentTarget.dataset.name;
      wx.navigateTo({
        url: `${chatPage}?userId=${userid}&name=${name}&conversation_id=${cid}`,
      });
    },
    async slideButtonTap(e: any) {
      console.log(e);
      const index = e.detail.index;
      if (index > -1) {
        const conversationItem: any = this.data.conversationList.find(
          (_, i) => i === index
        );
        if (conversationItem) {
          this.data.conversationList.splice(index, 1);
          this.setData({
            conversationList: this.data.conversationList,
          });
          if (conversationItem) {
            const res = await removeConversationsItem(
              conversationItem.conversation_id
            );
            if (res && res.succeed) {
              wx.showToast({
                icon: "success",
                title: res.message,
              });
            }
          }
        }
      }
    },
    // 对话成员列表
    async getConversationList() {
      const res = await getConversationList(this.data.conversationType);
      if (res && res.succeed) {
        console.log(this.data.conversationType)
        if (this.data.conversationType == "private") {
          this.setData({
            conversationList: res.data.map((item: any) => {
              item.sender_user = setImageDomainNameSplicing(
                item.sender_user,
                "avatar"
              );
              item.updated_at = setUpdateTime(item.updated_at)
              return item;
            }),
            conversationLoading: false,
          });
        } else {
          this.setData({
            conversationList: res.data.map((item: any) => {
              item.sender_user = item.secret_user;
              item.sender_user.avatar = item.secret_user.fake_avatar;
              item.sender_user = setImageDomainNameSplicing(
                item.secret_user,
                "avatar"
              );
              item.sender_user.name = item.secret_user.fake_name
              item.updated_at = setUpdateTime(item.updated_at)
              return item;
            }),
            conversationLoading: false,
          });
        }

      }
      this.setData({
        refreshStatus: false
      })
    },
    // 点击导航切换类型
    handlerNavItemClick(e: any) {
      const conversationType = e.currentTarget.dataset.id;
      if (conversationType === this.data.conversationType) return;
      this.setData({
        conversationType,
        conversationLoading: true,
      });

      // shijie
      // this.triggerEvent('redDot', {name:'interactive_count',value:1,isClear:true})

      this.getConversationList();
    },

    // 互动通知
    async getNotificationInteractive() {

      // 互动通知未读数 设置为 0 
      this.triggerEvent('redDot', { name: 'interactive_count', value: 1, isClear: true })

      const res = await getNotificationInteractive({ page: this.data.huDong.pageIndex });
      if (res && res.succeed) {
        res.data = (res.data || []).map((item: any) => {
          if (item.data.target && item.data.target.body) {
            if (item.data.target.body.image) {
              item.data.target.body = setImageDomainNameSplicing(
                item.data.target.body,
                "image"
              );
            }
            if (item.data.target.body.content) {
              item.data.target.body.emojiArray = parseEmoji(
                item.data.target.body.content
              );
            }
          }
          if (
            item.data.trigger &&
            item.data.trigger.users &&
            item.data.trigger.users.length
          ) {
            item.data.trigger.users = item.data.trigger.users.map((o: any) => {
              o = setImageDomainNameSplicing(o, "avatar");
              return o;
            });
          }
          if (
            item.data.resource &&
            item.data.resource.body &&
            item.data.resource.body.content
          ) {
            item.data.resource.body.emojiArray = parseEmoji(
              item.data.resource.body.content
            );
          }
          item.createdTime = currentRelativeTime(item.created_at);
          return item;
        });
        this.setData({
          notificationInteractiveList: [...this.data.notificationInteractiveList, ...res.data],
          notificationInteractiveLoading: false,
          'huDong.isMore': !!res.links.next
        });
        console.log('notificationInteractiveList', res.data);

      }
    },
    // 系统通知
    async getNotificationSystem() {

      // 系统通知未读数 设置为 0 
      this.triggerEvent('redDot', { name: 'system_count', value: 1, isClear: true })

      const res = await getNotificationSystem({ page: this.data.xiTong.pageIndex });
      if (res && res.succeed) {
        res.data = (res.data || []).map((item: any) => {
          if (item.data.target && item.data.target.body) {
            if (item.data.target.body.image) {
              item.data.target.body = setImageDomainNameSplicing(
                item.data.target.body,
                "image"
              );
            }
            if (item.data.target.body.content) {
              item.data.target.body.emojiArray = parseEmoji(
                item.data.target.body.content
              );
            }
          }
          if (
            item.data.trigger &&
            item.data.trigger.users &&
            item.data.trigger.users.length
          ) {
            item.data.trigger.users = item.data.trigger.users.map((o: any) => {
              o = setImageDomainNameSplicing(o, "avatar");
              return o;
            });
          }
          if (
            item.data.resource &&
            item.data.resource.body &&
            item.data.resource.body.content
          ) {
            item.data.resource.body.emojiArray = parseEmoji(
              item.data.resource.body.content
            );
          }
          item.createdTime = currentRelativeTime(item.created_at);
          return item;
        });
        this.setData({
          notificationSystemList: [...this.data.notificationSystemList, ...res.data],
          'xiTong.isMore': !!res.links.next,
          notificationSystemLoading: false,
        });
      }
    },
    onPopupClose() {
      this.setData({
        notificationShow: false,
      });
    },
    // 通知切换
    onSwitchTab(e: any) {
      const notificationTab = e.detail.id;
      if (notificationTab === this.data.notificationTab) return;
      this.setData({
        notificationTab,
      });
      if (notificationTab === "system") {
        this.data.xiTong.pageIndex = 1
        this.setData({
          notificationSystemList: []
        })
        this.getNotificationSystem();
      } else {
        this.data.huDong.pageIndex = 1
        this.setData({
          notificationInteractiveList: []
        })
        this.getNotificationInteractive();
      }
    },
    onPopupShow() {
      this.setData({
        notificationShow: true,
      });
      if (this.data.notificationTab === "system") {
        this.data.xiTong.pageIndex = 1
        this.setData({
          notificationSystemList: []
        })
        this.getNotificationSystem();
      } else {
        this.data.huDong.pageIndex = 1
        this.setData({
          notificationInteractiveList: []
        })
        this.getNotificationInteractive();
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
    // 下拉刷新列表
    bindRefresh() {
      console.log('下拉刷新');

      this.setData({
        refreshStatus: true
      })
      this.getConversationList();
    },
    // 弹窗里面的上拉触底事件
    bindscrolltolower() {
      // 系统通知
      if (this.data.notificationTab === "system") {
        if (this.data.xiTong.isMore === false) {
          return
        }
        this.data.xiTong.pageIndex++
        this.getNotificationSystem();
      } else {//互动通知
        // 没有更多了
        if (this.data.huDong.isMore === false) {
          return
        }
        this.data.huDong.pageIndex++
        this.getNotificationInteractive();
      }
    }
  },

});
