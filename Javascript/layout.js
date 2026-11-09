import { useRouter, useRoute } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount } from 'vue'

const store = inject('store')
const RESPO_ID = 'hris-respo-label'
const LABEL_SELECTOR = 'label.flex.gap-1.items-start'
let respo = null
let observer = null
let fetching = false

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
  style.textContent = '@media (max-width: 767px) { #scroll-nav-bar { z-index: 60 !important; } }'
  document.head.appendChild(style)
}

onMounted(async () => {
  const dataLogin = readJson('user')
  if (!(dataLogin?.data?.name || dataLogin?.username)) return

  // Header shell bisa tampil belakangan atau dirender ulang oleh Vue, jadi dipantau terus
  observer = new MutationObserver(() => apply())
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
})

onBeforeUnmount(() => {
  if (observer) observer.disconnect()
})
