// components/tabs/index.ts
Component({
  options: {
    addGlobalClass: true,
  },
  /**
   * 组件的属性列表
   */
  properties: {
    data: {
      type: Object,
      value: [],
    },
    selected: {
      type: String,
      value: "",
    },
  },

  /**
   * 组件的初始数据
   */
  data: {},

  /**
   * 组件的方法列表
   */
  methods: {
    switchTab(e: any) {
      const index = e.currentTarget.dataset.index;
      const tab = this.data.data.find((_: any, i: number) => i === index);
      this.triggerEvent("click", tab);
    },
  },
});
