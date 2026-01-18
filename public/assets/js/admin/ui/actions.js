export const ACTIONS_BY_STEP = Object.freeze({
  AUTH_WAIT_ACTION: [
    { label: "Error Login", kind: "danger", action: "reject_auth" },
    { label: "Pedir dinámica", kind: "primary", action: "request_dinamic" },
    { label: "Pedir OTP", kind: "primary", action: "request_otp" },
  ],
  AUTH_ERROR: [{ info: true, label: "Esperando nuevos datos" }],

  DINAMIC_WAIT_ACTION: [
    { label: "Error dinámica", kind: "danger", action: "reject_dinamic" },
    { label: "Pedir OTP", kind: "primary", action: "request_otp" },
    { label: "Finalizar", kind: "", action: "finish" },
  ],
  DINAMIC_ERROR: [{ info: true, label: "Esperando nueva dinámica" }],

  OTP_WAIT_ACTION: [
    { label: "Error OTP", kind: "danger", action: "reject_otp" },
    { label: "Alerta personalizada", kind: "", action: "custom_alert", needsMessage: true },
    { label: "Pedir dinámica", kind: "primary", action: "request_dinamic" },
    { label: "Finalizar", kind: "", action: "finish" },
  ],
  OTP_ERROR: [{ info: true, label: "Esperando nuevo OTP" }],
});
