import { createStore } from "../core/store.js";
import { createModal } from "../core/modal.js";
import { renderList, renderDetail, setConnPill, isSmallScreen } from "../ui/dashboard.ui.js";

const $ = (sel, root = document) => root.querySelector(sel);

export function mountDashboard({ socket }) {
  const store = createStore({
    sessionsById: {},
    selectedId: null,
    connected: socket?.connected ?? false,
  });

  const modal = createModal();

  // Render reactivo
  store.sub((state) => {
    setConnPill(state.connected);
    renderList(state);
    renderDetail(state);
  });

  // Conexión pill según socket real
  socket.on("connect", () => store.setConnPill({ connected: true }));
  socket.on("connect_error", () => store.setConnPill({ connected: false }));

  // Socket events (igual que antes)
  socket.on("admin:sessions:bootstrap", (sessions) => {
    const map = {};
    (sessions || []).forEach((s) => (map[s.id] = s));

    const { selectedId } = store.get();
    store.set({ sessionsById: map });

    if (selectedId && !map[selectedId]) store.set({ selectedId: null });
  });

  socket.on("admin:sessions:upsert", (s) => {
    if (!s?.id) return;
    const { sessionsById } = store.get();
    store.set({ sessionsById: { ...sessionsById, [s.id]: s } });
  });

  // Search
  $("#q")?.addEventListener("input", (e) => {
    store.set({ query: (e.target.value || "").trim().toLowerCase() });
  });

  // Selección de sesión (delegación)
  $("#sessionsList")?.addEventListener("click", (e) => {
    const row = e.target.closest(".row[data-session-id]");
    if (!row) return;

    const id = row.dataset.sessionId;
    const { sessionsById } = store.get();
    const s = sessionsById[id];

    store.set({ selectedId: id });

    if (isSmallScreen() && s) modal.open();
  });

  // Acciones (delegación desktop + modal)
  const onActionClick = (e) => {
    const btn = e.target.closest("button[data-action][data-session-id]");
    if (!btn) return;

    const action = btn.dataset.action;
    const sessionId = btn.dataset.sessionId;
    const needsMessage = btn.dataset.needsMessage === "1";

    let message = null;
    if (needsMessage) {
      message = prompt("Mensaje personalizado para el usuario:");
      if (message === null) return;
    }

    const eventName = `admin:${action}`;
    socket.emit(eventName, message ? { sessionId, message } : { sessionId });
  };

  $("#actions")?.addEventListener("click", onActionClick);
  $("#modalActions")?.addEventListener("click", onActionClick);

  // Render inicial
  store.set({ connected: socket?.connected ?? false });
}