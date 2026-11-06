@verbatim
<main class="hl-page">
  <section class="hl-brand" aria-hidden="true">
    <div class="hl-glow hl-glow-a"></div>
    <div class="hl-glow hl-glow-b"></div>

    <div class="hl-brand-body">
      <svg class="hl-org" viewBox="0 0 460 250" role="presentation">
        <g fill="none" stroke="rgba(255,255,255,.28)" stroke-width="2" stroke-linecap="round">
          <path d="M230 74 V112 M230 112 H86 V146 M230 112 H374 V146 M230 112 V146" />
          <path d="M86 196 V212 M86 212 H42 V222 M86 212 H130 V222" stroke-dasharray="2 6" />
          <path d="M374 196 V212 M374 212 H330 V222 M374 212 H418 V222" stroke-dasharray="2 6" />
        </g>
        <g class="hl-node hl-node-1">
          <circle cx="230" cy="44" r="30" fill="#60a5fa" />
          <circle cx="230" cy="36" r="10" fill="#0b1736" />
          <path d="M210 62 a20 16 0 0 1 40 0 z" fill="#0b1736" />
        </g>
        <g class="hl-node hl-node-2">
          <circle cx="86" cy="170" r="26" fill="#93c5fd" />
          <circle cx="86" cy="163" r="9" fill="#0b1736" />
          <path d="M69 186 a17 14 0 0 1 34 0 z" fill="#0b1736" />
        </g>
        <g class="hl-node hl-node-3">
          <circle cx="230" cy="170" r="26" fill="#ffffff" />
          <circle cx="230" cy="163" r="9" fill="#0b1736" />
          <path d="M213 186 a17 14 0 0 1 34 0 z" fill="#0b1736" />
        </g>
        <g class="hl-node hl-node-4">
          <circle cx="374" cy="170" r="26" fill="#38bdf8" />
          <circle cx="374" cy="163" r="9" fill="#0b1736" />
          <path d="M357 186 a17 14 0 0 1 34 0 z" fill="#0b1736" />
        </g>
        <g fill="rgba(255,255,255,.55)">
          <circle cx="42" cy="232" r="9" /><circle cx="130" cy="232" r="9" />
          <circle cx="330" cy="232" r="9" /><circle cx="418" cy="232" r="9" />
        </g>
      </svg>

      <h2 class="hl-headline">Semua urusan karyawan, dalam satu tempat.</h2>
      <p class="hl-sub">Dari absensi dan penggajian sampai pelatihan dan penilaian kinerja.</p>

      <ul class="hl-tags">
        <li>Absensi</li>
        <li>Payroll</li>
        <li>Perjalanan Dinas</li>
        <li>Recruitment</li>
        <li>Pelatihan</li>
        <li>Penilaian</li>
      </ul>
    </div>
  </section>

  <section class="hl-panel">
    <form class="hl-form" @submit.prevent="onLogin" novalidate>
      <div class="hl-form-logo">
        <img :src="brandLogo" alt="Logo Temprina" />
      </div>

      <h1 class="hl-title">Selamat datang</h1>
      <p class="hl-desc">Masuk dengan akun HRIS Anda untuk melanjutkan.</p>

      <div v-if="errorMsg" class="hl-alert" role="alert">{{ errorMsg }}</div>

      <label class="hl-label" for="hl-username">Username</label>
      <div class="hl-input-wrap">
        <svg class="hl-input-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-4.4 0-8 2.3-8 5.2 0 1 .8 1.8 1.8 1.8h12.4c1 0 1.8-.8 1.8-1.8 0-2.9-3.6-5.2-8-5.2Z"/></svg>
        <input
          id="hl-username"
          class="hl-input"
          type="text"
          v-model.trim="values.username"
          autocomplete="username"
          autocapitalize="none"
          spellcheck="false"
          placeholder="Masukkan username"
          required
        />
      </div>

      <label class="hl-label" for="hl-password">Password</label>
      <div class="hl-input-wrap">
        <svg class="hl-input-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 9V7A5 5 0 0 0 7 7v2a3 3 0 0 0-3 3v7a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-7a3 3 0 0 0-3-3ZM9 7a3 3 0 0 1 6 0v2H9V7Z"/></svg>
        <input
          id="hl-password"
          class="hl-input hl-input-pass"
          :type="showPass ? 'text' : 'password'"
          v-model="values.password"
          autocomplete="current-password"
          placeholder="Masukkan password"
          required
          @keyup="onCaps"
          @keydown="onCaps"
          @blur="capsOn = false"
        />
        <button
          type="button"
          class="hl-eye"
          :aria-label="showPass ? 'Sembunyikan password' : 'Tampilkan password'"
          :aria-pressed="showPass"
          @click="showPass = !showPass"
        >
          <svg v-if="!showPass" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5C6.5 5 2.3 9.4 1 12c1.3 2.6 5.5 7 11 7s9.7-4.4 11-7c-1.3-2.6-5.5-7-11-7Zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"/></svg>
          <svg v-else viewBox="0 0 24 24" aria-hidden="true"><path d="M3.3 2 2 3.3l3.4 3.4C3.3 8.2 1.8 10.3 1 12c1.3 2.6 5.5 7 11 7 1.9 0 3.6-.5 5.1-1.3l3.6 3.6 1.3-1.3L3.3 2ZM12 16a4 4 0 0 1-3.9-4.9l4.9 4.8c-.3.1-.7.1-1 .1Zm0-11c-1.3 0-2.6.3-3.7.7l2.7 2.7a4 4 0 0 1 4.6 4.6l3.5 3.5c1.8-1.3 3.2-3 3.9-4.5C21.7 9.4 17.5 5 12 5Z"/></svg>
        </button>
      </div>
      <p v-if="capsOn" class="hl-caps">Caps Lock sedang aktif</p>

      <button type="submit" class="hl-submit" :disabled="isRequesting">
        <span v-if="isRequesting" class="hl-spinner" aria-hidden="true"></span>
        {{ isRequesting ? 'Memproses...' : 'Masuk' }}
      </button>

      <p class="hl-copy">©2026 Copyright <a href="https://temprina.com" target="_blank" rel="noopener">temprina.com</a>. All Rights Reserved</p>
    </form>
  </section>
</main>
@endverbatim
