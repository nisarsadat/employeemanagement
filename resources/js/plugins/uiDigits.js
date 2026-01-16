// resources/js/plugins/uiDigits.js
// ✅ Converts digits ONLY in displayed TEXT nodes (not inputs / not v-model)

const FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']

function enToFa(text) {
  return String(text).replace(/\d/g, (d) => FA[Number(d)])
}

function shouldSkipNode(textNode) {
  const el = textNode?.parentElement
  if (!el) return true

  // ✅ never touch typing/inputs or code areas
  const tag = (el.tagName || '').toLowerCase()
  if (['input', 'textarea', 'code', 'pre'].includes(tag)) return true
  if (el.isContentEditable) return true

  // ✅ skip inside components that contain inputs
  if (el.closest('input, textarea, [contenteditable="true"]')) return true

  // ✅ avoid changing URLs / IDs inside links
  if (el.closest('a')) return true

  // ✅ allow opt-out anywhere: add class "no-digit-localize"
  if (el.closest('.no-digit-localize')) return true

  return false
}

function convertDigitsInDOM(root, mode) {
  const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT)
  let node

  while ((node = walker.nextNode())) {
    const t = node.nodeValue
    if (!t) continue
    if (shouldSkipNode(node)) continue

    // only convert EN -> FA for FA/PS locales
    if (mode === 'fa') {
      if (/\d/.test(t)) node.nodeValue = enToFa(t)
    }
  }
}

export function installUiDigitLocalizer(getLocale) {
  const getMode = () => {
    const loc = String(getLocale?.() || '').toLowerCase()
    return loc.startsWith('fa') || loc.startsWith('ps') ? 'fa' : 'en'
  }

  const run = () => {
    const mode = getMode()
    if (mode === 'fa') convertDigitsInDOM(document.body, 'fa')
  }

  // first run
  run()

  // keep applying when UI changes
  const obs = new MutationObserver(() => run())
  obs.observe(document.body, {
    childList: true,
    subtree: true,
    characterData: true,
  })

  return () => obs.disconnect()
}
