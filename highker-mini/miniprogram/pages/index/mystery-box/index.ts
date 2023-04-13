// pages/index/mystery-box.ts

import { IAppOption } from "../../../../typings";
import { getBoxCount, getBox } from "../../../api/box/index";
import { boxPublishPage,chatPage } from "../../../utils/router";
const app = getApp<IAppOption>();
Component({
  options: {
    addGlobalClass: true,
  },
  /**
   * 组件的初始数据
   */
  data: {
    customBarHeight: app.globalData.customBarHeight,
    pullBoxCount: 0,
    pushBoxCount: 0,
    dialogType: "",
    conversationId:0,
    name:'',
    userId:0,
  },
  lifetimes: {
    attached() {
      this.fnGetBoxCount();
    },
  },
  methods: {
    // 获取盲盒数量
    async fnGetBoxCount() {
      const res = await getBoxCount();
      if (res && res.succeed) {
        this.setData({
          pullBoxCount: res.data.get_box_count,
          pushBoxCount: res.data.add_box_count,
        });
      }
    },
    // 拆盲盒
    async getBox() {
      if (this.data.pullBoxCount <= 0) {
        wx.showToast({
          title: "拆盲盒次数已用完",
          icon: "none",
        });
        return
      }
      const res = await getBox();

      if (res && res.succeed) {
        this.setData({
          conversationId: res.data.conversation_id,
          name: res.data.secret_user.fake_name,
          userId: res.data.sender,
        });
        this.setDialogType('OPEN_MESSAGE')
      } else {
        this.setDialogType('NOT_MESSAGE')
      }
    },

    // 私聊
    toChatPage() {
      
      this.hideModal();

      wx.navigateTo({
        url: `${chatPage}?userId=${this.data.userId}&name=${this.data.name}&conversation_id=${this.data.conversationId}`,
      });
    },

    // 发布盲盒
    toBoxPublishPage() {
      if (this.data.pushBoxCount > 0) {
        wx.navigateTo({
          url: boxPublishPage,
        });
      } else {
        wx.showToast({
          title: "放盲盒次数已用完",
          icon: "none",
        });
      }
    },
    setDialogType(val: string) {
      this.setData({
        dialogType: val,
      });
      this.fnGetBoxCount();
    },

    hideModal() {
      this.setData({
        dialogType: "",
      });
    },
  },
});
