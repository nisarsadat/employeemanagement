function applyTo(el) {
  if (!el || !(el instanceof HTMLElement)) return

  const tag = (el.tagName || '').toLowerCase()
  if (tag !== 'input' && tag !== 'textarea' && tag !== 'select') return

  const type = (el.getAttribute('type') || '').toLowerCase()
  if (type === 'password' || type === 'file') return

  // ✅ strongest values (Chrome ignores off a lot)
  el.setAttribute('autocomplete', 'new-password')
  el.setAttribute('autocorrect', 'off')
  el.setAttribute('autocapitalize', 'off')
  el.setAttribute('spellcheck', 'false')

  // ✅ this is the REAL key: prevent "history" by making name unique
  // (only if name is missing or looks auto-generated / not intentional)
  const name = el.getAttribute('name') || ''
  if (!name || name.startsWith('input-') || name.startsWith('v-') || name.includes('vuetify')) {
    const uid = (crypto?.randomUUID?.() || Math.random().toString(36).slice(2))
    el.setAttribute('name', `noaf_${uid}`)
  }
}

function scan(root) {
  if (!root?.querySelectorAll) return
  root.querySelectorAll('input, textarea, select').forEach(applyTo)
}

export default {
  install() {
    // initial scan
    requestAnimationFrame(() => scan(document))

    // ✅ re-apply on focus (this is when browser decides to show suggestions)
    document.addEventListener(
      'focusin',
      (e) => {
        const el = e.target
        applyTo(el)
      },
      true
    )

    // ✅ keep working for dialogs/menus rendered later
    const obs = new MutationObserver((mutations) => {
      for (const m of mutations) {
        m.addedNodes?.forEach((node) => {
          if (node?.querySelectorAll) scan(node)
        })
      }
    })
    obs.observe(document.documentElement, { childList: true, subtree: true })
  },
}