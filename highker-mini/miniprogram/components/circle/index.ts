// components/circle/index.
import {
  BLUE,
  WHITE,
  canIUseCanvas2d,
  getSystemInfoSync,
  isObj,
  format,
} from "./util";
import { adaptor } from "./canvas";
const PERIMETER = 2 * Math.PI;
const BEGIN_ANGLE = -Math.PI / 2;
const STEP = 1;
Component({
  /**
   * 组件的属性列表
   */
  properties: {
    text: String,
    lineCap: {
      type: String,
      value: "round",
    },
    value: {
      type: Number,
      value: 0,
      observer: "reRender",
    },
    speed: {
      type: Number,
      value: 50,
    },
    size: {
      type: Number,
      value: 100,
      observer() {
        this.drawCircle(this.data.currentValue);
      },
    },
    fill: String,
    layerColor: {
      type: String,
      value: WHITE,
    },
    color: {
      type: null,
      value: "#C183F2",
      observer() {
        this.setHoverColor().then(() => {
          this.drawCircle(this.data.currentValue);
        });
      },
    },
    type: {
      type: String,
      value: "",
    },
    strokeWidth: {
      type: Number,
      value: 4,
    },
    clockwise: {
      type: Boolean,
      value: true,
    },
    progressTotal: {
      type: Number,
      value: 100,
    },
  },
  lifetimes: {
    ready() {
      this.data.currentValue = this.data.value;

      this.setHoverColor().then(() => {
        this.drawCircle(this.data.currentValue);
      });
    },
    detached() {
      this.clearMockInterval();
    },
  },
  /**
   * 组件的初始数据
   */
  data: {
    currentValue: 0,
    hoverColor: BLUE,
    inited: false,
    interval: 0,
  },

  /**
   * 组件的方法列表
   */
  methods: {
    getContext(): Promise<WechatMiniprogram.CanvasContext> {
      const { type, size } = this.data;

      if (type === "" || !canIUseCanvas2d()) {
        const ctx = wx.createCanvasContext("hk-circle", this);
        return Promise.resolve(ctx);
      }

      const dpr = getSystemInfoSync().pixelRatio;

      return new Promise((resolve) => {
        wx.createSelectorQuery()
          .in(this)
          .select("#hk-circle")
          .node()
          .exec((res) => {
            const canvas = res[0].node;
            const ctx = canvas.getContext(type);

            if (!this.data.inited) {
              this.data.inited = true;
              canvas.width = size * dpr;
              canvas.height = size * dpr;
              ctx.scale(dpr, dpr);
            }

            resolve(adaptor(ctx));
          });
      });
    },

    setHoverColor() {
      const { color, size } = this.data;

      if (isObj(color)) {
        return this.getContext().then((context) => {
          const LinearColor: any = context.createLinearGradient(size, 0, 0, 0);
          Object.keys(color)
            .sort((a, b) => parseFloat(a) - parseFloat(b))
            .map((key) =>
              LinearColor.addColorStop(
                parseFloat(key) / 100,
                color[key] as string
              )
            );
          this.data.hoverColor = LinearColor;
        });
      }

      this.data.hoverColor = color;
      return Promise.resolve();
    },

    presetCanvas(
      context: any,
      strokeStyle: any,
      beginAngle: any,
      endAngle: any,
      fill?: string
    ) {
      const { strokeWidth, lineCap, clockwise, size } = this.data;
      const position = size / 2;
      const radius = position - strokeWidth / 2;
      context.setStrokeStyle(strokeStyle);
      context.setLineWidth(strokeWidth);
      context.setLineCap(lineCap);

      context.beginPath();
      context.arc(position, position, radius, beginAngle, endAngle, !clockwise);
      context.stroke();

      if (fill) {
        context.setFillStyle(fill);
        context.fill();
      }
    },

    renderLayerCircle(context: any) {
      const { layerColor, fill } = this.data;
      this.presetCanvas(context, layerColor, 0, PERIMETER, fill);
    },

    renderHoverCircle(context: any, formatValue: number) {
      const { clockwise } = this.data;
      // 结束角度
      const progress = PERIMETER * (formatValue / this.data.progressTotal);
      const endAngle = clockwise
        ? BEGIN_ANGLE + progress
        : 3 * Math.PI - (BEGIN_ANGLE + progress);

      this.presetCanvas(context, this.data.hoverColor, BEGIN_ANGLE, endAngle);
    },

    drawCircle(currentValue: any) {
      const { size } = this.data;

      this.getContext().then((context) => {
        context.clearRect(0, 0, size, size);
        this.renderLayerCircle(context);

        const formatValue = format(currentValue);
        if (formatValue !== 0) {
          this.renderHoverCircle(context, formatValue);
        }

        context.draw();
      });
    },

    reRender() {
      // tofector 动画暂时没有想到好的解决方案
      const { value, speed } = this.data;

      if (speed <= 0 || speed > 1000) {
        this.drawCircle(value);
        return;
      }

      this.clearMockInterval();
      this.data.currentValue = this.data.currentValue || 0;
      const run = () => {
        this.data.interval = setTimeout(() => {
          if (this.data.currentValue !== value) {
            if (Math.abs(this.data.currentValue - value) < STEP) {
              this.data.currentValue = value;
            } else if (this.data.currentValue < value) {
              this.data.currentValue += STEP;
            } else {
              this.data.currentValue -= STEP;
            }
            this.drawCircle(this.data.currentValue);
            run();
          } else {
            this.clearMockInterval();
          }
        }, 1000 / speed);
      };
      run();
    },

    clearMockInterval() {
      if (this.data.interval) {
        clearTimeout(this.data.interval);
        this.data.interval = 0;
      }
    },
  },
});
