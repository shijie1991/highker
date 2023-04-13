// components/collapse/index.ts

type TrivialInstance = WechatMiniprogram.Component.TrivialInstance;
type RelationOption = WechatMiniprogram.Component.RelationOption;
Component({
  relations: {
    "/components/collapse-item/index": {
      type: "descendant",
      // linked(this: TrivialInstance, target: any) {
      //   return target;
      // },
      // linkChanged(this: TrivialInstance, target: any) {
      //   return target;
      // },
      // unlinked(this: TrivialInstance, target: any) {
      //   return target;
      // },
    },
  },
  lifetimes: {
    created() {
      Object.defineProperty(this.data, "children", {
        get: () =>
          this.getRelationNodes("/components/collapse-item/index") || [],
      });
    },
  },
  /**
   * 组件的属性列表
   */
  properties: {
    value: {
      type: null,
      observer: "updateExpanded",
    },
    accordion: {
      type: Boolean,
      observer: "updateExpanded",
    },
    border: {
      type: Boolean,
      value: true,
    },
  },

  /**
   * 组件的初始数据
   */
  data: {
    children: [],
  },

  /**
   * 组件的方法列表
   */
  methods: {
    updateExpanded() {
      const children = this.data.children;
      children.forEach((child: any) => {
        child.updateExpanded();
      });
    },

    switch(name: string | number, expanded: boolean) {
      const { accordion, value } = this.data;
      // console.log(accordion);
      const changeItem = name;
      if (!accordion) {
        name = expanded
          ? (value || []).concat(name)
          : (value || []).filter(
              (activeName: string | number) => activeName !== name
            );
      } else {
        name = expanded ? name : "";
      }

      if (expanded) {
        this.triggerEvent("open", changeItem);
      } else {
        this.triggerEvent("close", changeItem);
      }

      this.triggerEvent("change", name);
      this.triggerEvent("input", name);
    },
  },
});
