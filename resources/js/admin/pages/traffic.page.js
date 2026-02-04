export function mountTraffic({ socket }) {
  const el = document.getElementById("trafficBox");
  if (el) el.textContent = "Tráfico listo (aquí pintas logs, paginación, filtros, etc.).";
}