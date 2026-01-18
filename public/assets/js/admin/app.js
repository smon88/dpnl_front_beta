import { CFG } from "./core/config.js";
import { connectAdmin } from "../../js/dashboard.js";
/* import { mountDashboard } from "../../js/dashboard.js"; */
import { mountProfile } from "./pages/profile.page.js";
import { mountTraffic } from "./pages/traffic.page.js";
import { setConnPill } from "./ui/dashboard.ui.js";

const socket = await connectAdmin({
  onConnChange: (connected) => setConnPill(connected),
});

/* switch (CFG.page) {
  case "dashboard":
    mountDashboard({ socket });
    break;
  case "profile":
    mountProfile({ socket });
    break;
  case "traffic":
    mountTraffic({ socket });
    break;
  default:
    console.warn("Admin page no reconocida:", CFG.page);
} */