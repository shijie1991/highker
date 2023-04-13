let systemInfo: WechatMiniprogram.SystemInfo;
export function getSystemInfoSync() {
  if (systemInfo == null) {
    systemInfo = wx.getSystemInfoSync();
  }

  return systemInfo;
}
export function compareVersion(v1: any, v2: any) {
  v1 = v1.split(".");
  v2 = v2.split(".");
  const len = Math.max(v1.length, v2.length);

  while (v1.length < len) {
    v1.push("0");
  }
  while (v2.length < len) {
    v2.push("0");
  }

  for (let i = 0; i < len; i++) {
    const num1 = parseInt(v1[i], 10);
    const num2 = parseInt(v2[i], 10);

    if (num1 > num2) {
      return 1;
    }
    if (num1 < num2) {
      return -1;
    }
  }

  return 0;
}
export function gte(version: string) {
  const system = getSystemInfoSync();

  return compareVersion(system.SDKVersion, version) >= 0;
}
export function canIUseCanvas2d() {
  return gte("2.9.0");
}
export function format(rate: number) {
  return Math.min(Math.max(rate, 0), 100);
}
export function isObj(x: unknown): x is Record<string, unknown> {
  const type = typeof x;
  return x !== null && (type === "object" || type === "function");
}
export const BLUE: string = "#1989fa";
export const WHITE: string = "#fff";
