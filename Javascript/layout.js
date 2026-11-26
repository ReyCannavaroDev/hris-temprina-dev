import { useRouter, useRoute } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount, watch } from 'vue'

const store = inject('store')
const route = useRoute()
const RESPO_ID = 'hris-respo-label'
const LABEL_SELECTOR = 'label.flex.gap-1.items-start'
const NOTIF_BADGE_CLASS = 'hris-notif-badge'
let respo = null
let observer = null
let fetching = false
let notifCount = 0
let isFetchingNotif = false
let notifTimer = null

function readJson(key) {
  try {
    return JSON.parse(localStorage.getItem(key) || '{}') || {}
  } catch (e) {
    return {}
  }
}

async function fetchRespo(dataLogin) {
  const user = store?.user?.token ? store.user : dataLogin
  if (!user?.token) return null
  try {
    const res = await fetch(`${store.server.url_backend}/operation/m_respo/main_respo`, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${user.token_type} ${user.token}`
      }
    })
    if (!res.ok) return null
    const text = await res.text()
    if (!text) return null
    const json = JSON.parse(text)
    const data = json?.data || json
    if (data && data.name) {
      localStorage.setItem('respo', JSON.stringify(data))
      return data
    }
  } catch (e) { }
  return null
}

// Pasang nama dan respo di header. Aman dipanggil berkali-kali: hanya mengubah yang belum sesuai.
function apply() {
  const dataLogin = readJson('user')
  const name = dataLogin?.data?.name || dataLogin?.username
  const label = document.querySelector(LABEL_SELECTOR)
  if (!name || !label) return

  const textNode = [...label.childNodes].find(n => n.nodeType === Node.TEXT_NODE && n.textContent.trim() !== '')
  if (textNode && textNode.textContent !== ` ${name}`) {
    textNode.textContent = ` ${name}`
  }

  let el = document.getElementById(RESPO_ID)
  if (!respo || !respo.name) {
    if (el) el.remove()
    return
  }
  const branchName = respo['m_branch.name']
  const text = branchName ? `${respo.name} - ${branchName}` : respo.name
  if (!el || el.previousElementSibling !== label) {
    if (el) el.remove()
    el = document.createElement('div')
    el.id = RESPO_ID
    el.classList.add('ml-6', 'text-sm', 'text-blue-600', 'font-medium', 'opacity-90')
    label.insertAdjacentElement('afterend', el)
  }
  if (el.textContent !== text) el.textContent = text
}

// CSS global untuk shell. Di HP, top bar (sticky, z-50) menutupi bagian atas sidebar (fixed, z-40)
// sehingga judul aplikasi tidak terlihat. Sidebar HP dinaikkan ke atas top bar; ia punya tombol tutup sendiri.
const STYLE_ID = 'hris-layout-style'
if (!document.getElementById(STYLE_ID)) {
  const style = document.createElement('style')
  style.id = STYLE_ID
  style.textContent = `
    @media (max-width: 767px) { #scroll-nav-bar { z-index: 60 !important; } }
    .hris-notif-badge {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      min-width: 20px !important;
      height: 20px !important;
      padding: 0 6px !important;
      margin-left: auto !important;
      margin-right: 6px !important;
      font-size: 11px !important;
      font-weight: 700 !important;
      font-family: inherit !important;
      line-height: 1 !important;
      color: #ffffff !important;
      background-color: #ef4444 !important;
      border-radius: 9999px !important;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
      pointer-events: none !important;
      z-index: 10 !important;
    }
  `
  document.head.appendChild(style)
}

function findNotifMenuElements() {
  const elements = []
  const sidebar = document.getElementById('scroll-nav-bar') || document.body

  // 1. Selector by href/to
  const links = sidebar.querySelectorAll('a[href*="/notifikasi"], a[href$="notifikasi"], [to*="/notifikasi"]')
  links.forEach(el => elements.push(el))

  // 2. Fallback pencarian teks di sidebar
  if (elements.length === 0) {
    const candidates = sidebar.querySelectorAll('a, button, .child-menu, li')
    for (const el of candidates) {
      const text = el.textContent?.trim()?.toLowerCase() || ''
      if (text === 'notification' || text === 'notifikasi' || text.startsWith('notification') || text.startsWith('notifikasi')) {
        elements.push(el)
      }
    }
  }

  // 3. Tombol notifikasi di dashboard/header jika ada
  const dashboardNotif = document.querySelectorAll('button.hd-action, a.hd-action, [title*="Notifikasi" i], [title*="Notification" i]')
  dashboardNotif.forEach(el => {
    const txt = el.textContent?.trim()?.toLowerCase() || ''
    if (txt.includes('notification') || txt.includes('notifikasi')) {
      elements.push(el)
    }
  })

  return elements
}

function applyNotifBadge() {
  const elements = findNotifMenuElements()
  if (!elements || elements.length === 0) return

  elements.forEach((menuEl) => {
    let badge = menuEl.querySelector('.' + NOTIF_BADGE_CLASS)

    if (!notifCount || notifCount <= 0) {
      if (badge) badge.remove()
      return
    }

    const displayCount = notifCount > 99 ? '99+' : String(notifCount)

    if (!badge) {
      badge = document.createElement('span')
      badge.className = NOTIF_BADGE_CLASS
      badge.title = `${notifCount} Notifikasi Belum Dibaca`

      if (getComputedStyle(menuEl).display !== 'flex') {
        menuEl.style.display = 'flex'
        menuEl.style.alignItems = 'center'
      }
      menuEl.appendChild(badge)
    }

    if (badge.textContent !== displayCount) {
      badge.textContent = displayCount
    }
  })
}

async function fetchNotificationCount() {
  if (isFetchingNotif) return
  isFetchingNotif = true
  try {
    const dataLogin = readJson('user')
    const token = store?.user?.token || dataLogin?.token
    const tokenType = store?.user?.token_type || dataLogin?.token_type || 'Bearer'
    const backendUrl = store?.server?.url_backend || dataLogin?.server?.url_backend || ''
    if (!token || !backendUrl) return

    const res = await fetch(`${backendUrl}/operation/generate_approval/outstanding?paginate=100`, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${tokenType} ${token}`
      }
    })
    if (res.ok) {
      const json = await res.json()
      const data = json?.data || json
      if (typeof json?.total === 'number') {
        notifCount = json.total
      } else if (Array.isArray(data)) {
        notifCount = data.length
      } else {
        notifCount = 0
      }
      applyNotifBadge()
    }
  } catch (e) {
  } finally {
    isFetchingNotif = false
  }
}

onMounted(async () => {
  const dataLogin = readJson('user')
  if (!(dataLogin?.data?.name || dataLogin?.username)) return

  // Header shell & menu bisa tampil belakangan atau dirender ulang oleh Vue, jadi dipantau terus
  observer = new MutationObserver(() => {
    apply()
    applyNotifBadge()
  })
  observer.observe(document.body, { childList: true, subtree: true, characterData: true })

  // Pakai respo yang tersimpan, atau ambil sendiri kalau belum ada (misalnya tepat setelah login)
  respo = readJson('respo')
  apply()
  if ((!respo || !respo.name) && !fetching) {
    fetching = true
    respo = await fetchRespo(dataLogin)
    fetching = false
    apply()
  }

  // Ambil count notifikasi dan pasang listener update
  window.addEventListener('hris-notif-update', fetchNotificationCount)
  fetchNotificationCount()

  // Polling tiap 30 detik agar selalu sinkron
  notifTimer = setInterval(fetchNotificationCount, 30000)
})

watch(() => route?.path, () => {
  fetchNotificationCount()
})

onBeforeUnmount(() => {
  if (observer) observer.disconnect()
  if (notifTimer) clearInterval(notifTimer)
  window.removeEventListener('hris-notif-update', fetchNotificationCount)
})
