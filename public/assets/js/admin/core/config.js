export const CFG = Object.freeze({
  nodeUrl: window.ADMIN_CFG?.nodeUrl || "http://192.168.145.120:3005/api",
  tokenEndpoint: window.ADMIN_CFG?.tokenEndpoint || "/admin/issue-token",
  page: window.ADMIN_CFG?.page,
  smallScreenMedia: "(max-width: 980px)",
});