import { CFG } from "./config.js";

const $ = (sel, root = document) => root.querySelector(sel);
const isSmallScreen = () => window.matchMedia(CFG.smallScreenMedia).matches;

export function createModal() {
  const overlay = $("#modalOverlay");
  const closeBtn = $("#closeModalBtn");

  const open = () => {
    overlay.classList.add("open");
    overlay.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  };

  const close = () => {
    overlay.classList.remove("open");
    overlay.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  };

  const isOpen = () => overlay.classList.contains("open");

  closeBtn?.addEventListener("click", close);
  overlay?.addEventListener("click", (e) => {
    if (e.target === overlay) close();
  });

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && isOpen()) close();
  });

  window.addEventListener("resize", () => {
    if (!isSmallScreen() && isOpen()) close();
  });

  return { open, close, isOpen };
}