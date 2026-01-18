export function mountProfile({ socket }) {
  // Ejemplo: en el futuro puedes pedir info por socket o fetch
  const el = document.getElementById("profileBox");
  if (el) el.textContent = "Perfil listo (aquí conectas tu API).";
}
