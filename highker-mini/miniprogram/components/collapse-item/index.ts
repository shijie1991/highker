// components/collapse-item/index.ts
import { setContentAnimate } from "./animate";
Component({
  options: {
    addGlobalClass: true,
  },
  // mixin: Behavior({
  //   created() {
  //     Object.defineProperty(this, "parent", {
  //       get: () => this.getRelationNodes(path)[0],
  //     });

  //     Object.defineProperty(this, "index", {
  //       // @ts-ignore
  //       get: () => this.parent?.children?.indexOf(this),
  //     });
  //   },
  // }),
  relations: {
    "/components/collapse/index": {
      type: "ancestor",
      linked(this: TrivialInstance) {},
      linkChanged(this: TrivialInstance) {},
      unlinked(this: TrivialInstance) {},
    },
  },
  /**
   * 组件的属性列表
   */
  properties: {
    size: String,
    name: null,
    title: null,
    value: null,
    icon: String,
    label: String,
    disabled: Boolean,
    clickable: Boolean,
    border: {
      type: Boolean,
      value: true,
    },
    isLink: {
      type: Boolean,
      value: true,
    },
  },
  lifetimes: {
    attached() {
      Object.defineProperty(this.data, "parent", {
        get: () => this.getRelationNodes("/components/collapse/index")[0],
      });

      Object.defineProperty(this.data, "index", {
        // @ts-ignore
        get: () => this.data.parent?.children?.indexOf(this),
      });
      setTimeout(() => {
        this.updateExpanded();
        this.data.mounted = true;
      }, 100);
    },
  },
  /**
   * 组件的初始数据
   */
  data: {
    expanded: false,
    mounted: false,
    parent: null,
  },

  /**
   * 组件的方法列表
   */
  methods: {
    updateExpanded() {
      if (!this.data.parent) {
        return;
      }

      const parentData: any = this.data.parent;
      const { value, accordion } = parentData.data;
      const { children = [] } = parentData.data;
      const { name } = this.data;

      const index = children.indexOf(this);
      const currentName = name == null ? index : name;

      const expanded = accordion
        ? value === currentName
        : (value || []).some((name: string | number) => name === currentName);

      if (expanded !== this.data.expanded) {
        setContentAnimate(this, expanded, this.data.mounted);
      }

      this.setData({ index, expanded });
    },

    onClick() {
      if (this.data.disabled) {
        return;
      }
      const parentData: any = this.data.parent;
      const { name, expanded } = this.data;
      const index = parentData.data.children.indexOf(this);
      const currentName = name == null ? index : name;
      parentData.switch(currentName, !expanded);
    },
  },
});
