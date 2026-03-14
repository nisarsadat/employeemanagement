import { onBeforeUnmount } from 'vue';

const refreshHandlers = new Map();

function normalizeKey(key) {
  return String(key || '').trim();
}

export function registerPageRefresh(key, handler) {
  const k = normalizeKey(key);
  if (!k || typeof handler !== 'function') return () => {};

  refreshHandlers.set(k, handler);

  return () => {
    if (refreshHandlers.get(k) === handler) {
      refreshHandlers.delete(k);
    }
  };
}

export async function runPageRefresh(key) {
  const k = normalizeKey(key);
  const handler = refreshHandlers.get(k);

  if (typeof handler !== 'function') return false;

  await handler();
  return true;
}

export function usePageRefresh(key, handler) {
  const unregister = registerPageRefresh(key, handler);

  onBeforeUnmount(() => {
    unregister();
  });

  return unregister;
}