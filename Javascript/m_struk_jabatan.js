import { useRouter, useRoute, RouterLink } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount, watchEffect, onActivated } from 'vue'

const router = useRouter()
const route = useRoute()
const store = inject('store')
const swal = inject('swal')

const isRead = route.params.id && route.params.id !== 'create'
const actionText = ref(route.params.id === 'create' ? 'Tambah' : route.query.action)
const isBadForm = ref(false)
const isRequesting = ref(false)
const modulPath = route.params.modul
const currentMenu = store.currentMenu
const apiTable = ref(null)
const exportHtml = ref(false)
const formErrors = ref({})
const activeTabIndex = ref(0)
const tsId = `ts=` + (Date.parse(new Date()))

// ------------------------------ PERSIAPAN
onBeforeMount(() => {
  document.title = 'Struktur Jabatan'
})

//  @if( $id )------------------- VALUES FORM ! PENTING JANGAN DIHAPUS

//  @else----------------------- LANDING


const exportAsImage = () => {
  const diagramElement = cyContainer.value;

  if (diagramElement) {
    const today = new Date();
    const day = today.getDate();
    const year = today.getFullYear();
    const monthNames = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    const monthName = monthNames[today.getMonth()];
    const formattedDate = `${day}-${monthName}-${year}`;
    html2canvas(diagramElement, {
      backgroundColor: '#ffffff',
      useCORS: true,
    }).then((canvas) => {
      const image = canvas.toDataURL("image/png");
      const link = document.createElement("a");
      link.href = image;
      link.download = `STRUKTUR ORGANISASI PT.TEMPRINA ${formattedDate}.png`;
      link.click();
    });
  }
};

const cyContainer = ref(null);

// const startLevel = ref(1);
// const endLevel = ref(3);
const values = reactive({
  m_comp_id: null,
  start_level: 1,
  end_level: 6
})

let initialValues = {};
let nodes = [];

const getData = async () => {
  isRequesting.value = true;
  const dataURL = `${store.server.url_backend}/operation/m_kary`;

  const params = new URLSearchParams({
    join: 'true',
    scopes: "Structure",
    transform: 'false',
    paginate: '500'
  });

  if (values.m_comp_id) {
    params.append('comp_id', values.m_comp_id);
  }
  if (values.start_level !== null && values.start_level !== undefined && values.start_level !== '') {
    params.append('start_level', values.start_level);
  }
  if (values.end_level !== null && values.end_level !== undefined && values.end_level !== '') {
    params.append('end_level', values.end_level);
  }

  try {
    const res = await fetch(`${dataURL}?${params}`, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    });

    if (!res.ok) throw new Error("Gagal mengambil data struktur jabatan");
    const result = await res.json();
    const dataList = Array.isArray(result.data) ? result.data : (result.data?.data || []);

    if (dataList.length === 0) {
      nodes = [];
      if (cyContainer.value) {
        cyContainer.value.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500 text-sm font-medium">Tidak ada data struktur jabatan yang ditemukan untuk filter ini.</div>';
      }
      return;
    }

    const levelColors = {
      100: '#1e293b',
      6: '#1e40af',  // Direktur (Biru Tua)
      5: '#2563eb',  // GM (Biru)
      4: '#0d9488',  // Manager (Teal)
      3: '#16a34a',  // Kadiv (Hijau)
      2: '#d97706',  // Karu (Amber)
      1: '#64748b',  // Staff (Slate)
    };

    // 1. Buat node data karyawan
    const karyDataList = dataList.map(item => {
      const detList = Array.isArray(item.m_kary_det_jabatan) ? item.m_kary_det_jabatan : (item.m_kary_det_jabatan ? [item.m_kary_det_jabatan] : []);
      const det = detList.find(j => j.is_primary) || detList[0] || {};
      const lvl = Number(det.level ?? 1);
      const jab = (det.jabatan || 'STAFF').trim();
      const div = (det.nama_divisi || '').trim();
      const name = (item.nama_lengkap || '').trim();

      let labelText = jab.toUpperCase();
      if (div) labelText += `\n[${div}]`;
      if (name) labelText += `\n${name}`;

      return {
        id: `kary-${item.id}`,
        raw_id: item.id,
        name: name,
        jabatan: jab,
        level: lvl,
        divisi_id: det.m_divisi_id || 0,
        divisi_name: div,
        atasan_id: item.atasan_id ? Number(item.atasan_id) : null,
        label: labelText,
        bgColor: levelColors[lvl] || '#64748b',
        parentId: null
      };
    });

    const nodeMap = new Map();
    karyDataList.forEach(k => nodeMap.set(k.id, k));

    // Kelompokkan per Divisi
    const divisiGroups = {};
    karyDataList.forEach(k => {
      const divKey = k.divisi_id || 'unassigned';
      if (!divisiGroups[divKey]) divisiGroups[divKey] = [];
      divisiGroups[divKey].push(k);
    });

    // 2. Tentukan parentId untuk setiap karyawan
    karyDataList.forEach(node => {
      if (node.atasan_id && nodeMap.has(`kary-${node.atasan_id}`)) {
        node.parentId = `kary-${node.atasan_id}`;
      } else {
        const sameDivisi = divisiGroups[node.divisi_id || 'unassigned'] || [];
        const superiorsInDiv = sameDivisi.filter(p => p.level > node.level && p.id !== node.id);

        if (superiorsInDiv.length > 0) {
          const minSupLevel = Math.min(...superiorsInDiv.map(s => s.level));
          const closestSuperiors = superiorsInDiv.filter(s => s.level === minSupLevel);
          node.parentId = closestSuperiors[0].id;
        } else {
          node.parentId = 'root-0';
        }
      }
    });

    const maxLevelInData = Math.max(...karyDataList.map(n => n.level), 1);

    // Root Node Pucuk Pimpinan
    const rootNode = {
      data: {
        id: 'root-0',
        raw_id: 0,
        level: maxLevelInData + 1,
        name: 'DIREKSI / HOLDING',
        jabatan: 'TOP MANAGEMENT',
        divisi_id: null,
        divisi_name: '',
        atasan_id: null,
        label: 'DIREKSI / HOLDING\n[TOP MANAGEMENT]',
        bgColor: '#1e293b',
        parentId: null
      }
    };

    nodes = [
      rootNode,
      ...karyDataList.map(k => ({ data: k }))
    ];

    await getTree();
  } catch (error) {
    console.error("Error fetching data:", error);
    if (cyContainer.value) {
      cyContainer.value.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-red-500 text-sm font-medium gap-2">
        <span>Terjadi kesalahan saat memuat data struktur jabatan.</span>
        <span class="text-xs text-gray-500">${error.message || error}</span>
      </div>`;
    }
  } finally {
    isRequesting.value = false;
  }
};

const getTree = async () => {
  if (!cyContainer.value || nodes.length === 0) return;
  cyContainer.value.innerHTML = '';

  const edges = nodes
    .filter(node => node.data && node.data.parentId && node.data.parentId !== node.data.id)
    .map(node => ({
      data: {
        id: `edge-${node.data.parentId}-${node.data.id}`,
        source: node.data.parentId,
        target: node.data.id
      }
    }));

  const elements = [...nodes, ...edges];

  // Cek dan daftarkan layout ELK jika tersedia
  let layoutOption = {
    name: 'breadthfirst',
    directed: true,
    spacingFactor: 1.3,
    padding: 50,
    animate: true,
    animationDuration: 500
  };

  try {
    if (typeof cytoscapeElk !== 'undefined' && typeof cytoscape !== 'undefined') {
      cytoscape.use(cytoscapeElk);
      layoutOption = {
        name: 'elk',
        elk: {
          algorithm: 'mrtree',
          direction: 'DOWN',
          nodeSpacing: 40,
          levelSpacing: 80,
          edgeSpacingFactor: 0.6,
          separateConnectedComponents: true,
          edgeRouting: 'ORTHOGONAL',
          hierarchyHandling: 'INCLUDE_CHILDREN',
          considerNodeLabels: true
        },
        fit: true,
        padding: 50,
        animate: true,
        animationDuration: 500
      };
    }
  } catch (e) {
    console.warn("ELK layout not available, using breadthfirst fallback:", e);
  }

  const cyInstance = (window.cytoscape || cytoscape);
  const cy = cyInstance({
    container: cyContainer.value,
    elements: elements,
    style: [
      {
        selector: 'node',
        style: {
          'shape': 'roundrectangle',
          'background-color': 'data(bgColor)',
          'border-color': '#334155',
          'border-width': 2,
          'label': 'data(label)',
          'text-wrap': 'wrap',
          'text-valign': 'center',
          'text-halign': 'center',
          'color': '#ffffff',
          'font-size': '11px',
          'font-family': 'Inter, sans-serif',
          'font-weight': '600',
          'padding': '10px',
          'width': '160px',
          'height': '70px',
          'text-max-width': '150px'
        }
      },
      {
        selector: 'node:selected',
        style: {
          'border-width': 3,
          'border-color': '#f59e0b'
        }
      },
      {
        selector: 'edge',
        style: {
          'width': 2,
          'line-color': '#94a3b8',
          'target-arrow-color': '#94a3b8',
          'target-arrow-shape': 'triangle',
          'arrow-scale': 0.8,
          'curve-style': 'taxi',
          'taxi-direction': 'downward',
          'taxi-turn': '15px'
        }
      }
    ],
    layout: layoutOption,
    zoomingEnabled: true,
    minZoom: 0.1,
    maxZoom: 3,
    wheelSensitivity: 0.2
  });

  cy.fit(null, 50);
  cy.center();
};

onBeforeMount(async () => {
  await getData();
});

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))