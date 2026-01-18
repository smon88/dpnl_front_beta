export function createStore(initial) {
  let state = structuredClone(initial);
  const listeners = new Set();

  return {
    get: () => state,
    set: (partial) => {
      state = { ...state, ...partial };
      listeners.forEach((fn) => fn(state));
    },
    sub: (fn) => (listeners.add(fn), () => listeners.delete(fn)),
  };
}