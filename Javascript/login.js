import { useRouter } from 'vue-router'

import { ref, reactive, inject, onMounted, onBeforeMount, onBeforeUnmount } from 'vue'

const router = useRouter()
const store = inject('store')
const isRequesting = ref(false)
const errorMsg = ref('')
const capsOn = ref(false)
const brandLogo = store.server.url_backend + '/temprina.png'

const showPass = ref(false)
const values = reactive({
  username: null, password: null
})

// Class Windi hanya tersedia dari hasil build, jadi CSS halaman ini dipasang sendiri
const STYLE_ID = 'hris-login-style'
const css = `
.hl-page { min-height: 100vh; display: flex; background: #f4f6fb; color: #0b1736; }
.hl-brand { position: relative; overflow: hidden; flex: 1 1 56%; display: flex; flex-direction: column; padding: 40px 56px; color: #fff; background: linear-gradient(90deg, #060d20 0%, #0c1a3f 55%, #14275f 100%); }
.hl-glow { position: absolute; border-radius: 50%; filter: blur(70px); pointer-events: none; }
.hl-glow-a { width: 380px; height: 380px; top: -120px; right: -90px; background: rgba(59, 130, 246, .2); }
.hl-glow-b { width: 420px; height: 420px; bottom: -170px; left: -110px; background: rgba(37, 99, 235, .18); }
.hl-brand-body { position: relative; margin: auto 0; max-width: 560px; padding: 32px 0; }
.hl-org { display: block; width: 100%; max-width: 460px; height: auto; margin-bottom: 36px; }
.hl-node { animation: hl-float 6s ease-in-out infinite; }
.hl-node-2 { animation-delay: .6s; }
.hl-node-3 { animation-delay: 1.2s; }
.hl-node-4 { animation-delay: 1.8s; }
.hl-headline { margin: 0 0 12px; font-size: 38px; line-height: 1.18; font-weight: 800; letter-spacing: -.5px; }
.hl-sub { margin: 0 0 24px; font-size: 16px; line-height: 1.6; color: rgba(255, 255, 255, .8); }
.hl-tags { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; padding: 0; list-style: none; }
.hl-tags li { padding: 6px 13px; font-size: 13px; border-radius: 999px; color: #fff; background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .2); }
.hl-panel { flex: 1 1 44%; min-width: 0; display: flex; align-items: center; justify-content: center; padding: 40px 24px; background: #fff; }
.hl-form { width: 100%; max-width: 380px; }
.hl-form-logo { margin-bottom: 26px; }
.hl-form-logo img { display: block; width: 200px; height: auto; filter: brightness(.3); }
.hl-title { margin: 0 0 6px; font-size: 30px; font-weight: 800; letter-spacing: -.4px; color: #0b1736; }
.hl-desc { margin: 0 0 28px; font-size: 15px; line-height: 1.5; color: #56637f; }
.hl-alert { margin-bottom: 18px; padding: 11px 14px; font-size: 14px; line-height: 1.4; border-radius: 10px; color: #a12b2b; background: #fdeeee; border: 1px solid #f6cfcf; }
.hl-label { display: block; margin-bottom: 7px; font-size: 14px; font-weight: 600; color: #25345a; }
.hl-input-wrap { position: relative; margin-bottom: 18px; }
.hl-input-icon { position: absolute; top: 50%; left: 14px; width: 18px; height: 18px; fill: #8492ad; transform: translateY(-50%); pointer-events: none; }
.hl-input { display: block; width: 100%; height: 48px; padding: 0 14px 0 42px; font-size: 15px; color: #0b1736; background: #f7f9fc; border: 1px solid #d6ddea; border-radius: 10px; outline: none; transition: border-color .15s, box-shadow .15s, background .15s; }
.hl-input::placeholder { color: #94a0b8; }
.hl-input:hover { border-color: #b6c2d8; }
.hl-input:focus { background: #fff; border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29, 78, 216, .18); }
.hl-input-pass { padding-right: 46px; }
.hl-eye { position: absolute; top: 50%; right: 6px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: none; border: 0; border-radius: 8px; cursor: pointer; transform: translateY(-50%); }
.hl-eye svg { width: 19px; height: 19px; fill: #6b7a99; }
.hl-eye:hover { background: #edf1f8; }
.hl-eye:focus-visible { outline: 2px solid #1d4ed8; outline-offset: 1px; }
.hl-caps { margin: -10px 0 16px; font-size: 13px; color: #a3620a; }
.hl-submit { width: 100%; height: 48px; margin-top: 6px; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 15px; font-weight: 700; color: #fff; background: linear-gradient(90deg, #0c1a3f, #1e3a8a); border: 0; border-radius: 10px; cursor: pointer; transition: filter .15s, transform .15s, box-shadow .15s; box-shadow: 0 8px 18px rgba(12, 26, 63, .28); }
.hl-submit:hover:not(:disabled) { filter: brightness(1.08); transform: translateY(-1px); }
.hl-submit:focus-visible { outline: 3px solid rgba(29, 78, 216, .45); outline-offset: 2px; }
.hl-submit:disabled { opacity: .72; cursor: wait; }
.hl-spinner { width: 17px; height: 17px; border-radius: 50%; border: 2px solid rgba(255, 255, 255, .4); border-top-color: #fff; animation: hl-spin .8s linear infinite; }
.hl-copy { margin: 28px 0 0; font-size: 12.5px; line-height: 1.5; text-align: center; color: #7886a3; }
.hl-copy a { color: inherit; text-decoration: underline; }
@keyframes hl-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
@keyframes hl-spin { to { transform: rotate(360deg); } }
@media (max-width: 960px) {
  .hl-brand { display: none; }
  .hl-panel { flex-basis: 100%; min-height: 100vh; padding: 32px 20px; }
  .hl-form-logo img { margin: 0 auto; }
  .hl-title, .hl-desc { text-align: center; }
}
@media (max-height: 760px) {
  .hl-org { max-width: 330px; margin-bottom: 22px; }
  .hl-headline { font-size: 30px; }
  .hl-brand { padding: 28px 48px; }
}
@media (prefers-reduced-motion: reduce) {
  .hl-node, .hl-spinner { animation: none; }
  .hl-input, .hl-submit { transition: none; }
}
`

onBeforeMount(() => {
  document.title = 'Login | HRIS Temprina'
  if (!document.getElementById(STYLE_ID)) {
    const el = document.createElement('style')
    el.id = STYLE_ID
    el.textContent = css
    document.head.appendChild(el)
  }
})

onBeforeUnmount(() => {
  document.getElementById(STYLE_ID)?.remove()
})

function onCaps(e) {
  capsOn.value = !!e.getModifierState?.('CapsLock')
}

const ini_fcm = localStorage.getItem('fcmToken')

async function onLogin() {
  errorMsg.value = ''
  if (!values.username || !values.password) {
    errorMsg.value = 'Username dan password wajib diisi.'
    return
  }

  isRequesting.value = true
  let result
  try {
    result = await fetch(store.server.url_backend + '/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'Application/json'
      },
      body: JSON.stringify({
        username: values.username,
        password: values.password,
        token_fcm: ini_fcm
      })
    })
  } catch (err) {
    isRequesting.value = false
    errorMsg.value = 'Tidak dapat terhubung ke server. Coba beberapa saat lagi.'
    return
  }

  if (!result.ok) {
    isRequesting.value = false
    errorMsg.value = result.status >= 500
      ? 'Terjadi gangguan pada server. Coba beberapa saat lagi.'
      : 'Username atau password salah.'
    return
  }

  try {
    const userValue = await result.json()
    window.localStorage.removeItem('respo')
    window.localStorage.user = JSON.stringify(userValue)
    store.commit('set', ['user', userValue])
  } catch (err) {
    isRequesting.value = false
    errorMsg.value = 'Terjadi gangguan pada server. Coba beberapa saat lagi.'
    return
  }
  isRequesting.value = false
  await router.push('/dashboard')
}

onMounted(async () => {
  if (localStorage.user) {
    const tempJson = JSON.parse(localStorage.user)
    values.username = tempJson.data?.username

    try {
      isRequesting.value = true
      const result = await fetch(store.server.url_backend + `${store.server.url_user_check}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'Application/json',
          Authorization: `${tempJson.token_type} ${tempJson.token}`
        }
      })
      if (!result.ok) {
        throw new Error(await result.text())
      }
      const refreshedUser = await result.json();
      Object.assign(tempJson, { data: refreshedUser })
      store.commit('set', ['user', tempJson])
      store.commit('set', ['userWasRefreshed', true])
      window.localStorage.user = JSON.stringify(tempJson)
      router.replace('/dashboard')
    } catch (err) { }
    isRequesting.value = false
  }
})
