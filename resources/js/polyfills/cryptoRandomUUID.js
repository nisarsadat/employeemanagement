// Polyfill for browsers that don't support crypto.randomUUID()
export function ensureRandomUUID() {
  if (typeof crypto === "undefined") return;

  if (typeof crypto.randomUUID === "function") return;

  crypto.randomUUID = function () {
    // fallback using getRandomValues
    const bytes = new Uint8Array(16);
    crypto.getRandomValues(bytes);

    // RFC 4122 version 4
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;

    const hex = [...bytes].map((b) => b.toString(16).padStart(2, "0")).join("");
    return (
      hex.slice(0, 8) + "-" +
      hex.slice(8, 12) + "-" +
      hex.slice(12, 16) + "-" +
      hex.slice(16, 20) + "-" +
      hex.slice(20)
    );
  };
}