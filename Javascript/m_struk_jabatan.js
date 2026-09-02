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

    // Mapping nodes karyawan
    const newNodes = dataList.map(item => {
      const det = (Array.isArray(item.m_kary_det_jabatan) ? item.m_kary_det_jabatan[0] : item.m_kary_det_jabatan) ?? {};
      const lvl = Number(det.level ?? 1);
      return {
        data: {
          id: `kary-${item.id}`,
          raw_id: item.id,
          name: item.nama_lengkap || '',
          jabatan: det.jabatan || 'STAFF',
          level: lvl,
          divisi_id: det.m_divisi_id || 0,
          divisi_name: det.nama_divisi || '',
          atasan_id: item.atasan_id ? Number(item.atasan_id) : null
        }
      };
    });

    const maxLevelInData = Math.max(...newNodes.map(n => n.data.level), 1);

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
        atasan_id: null
      }
    };

    nodes = [rootNode, ...newNodes];

    await getTree();
  } catch (error) {
    console.error("Error fetching data:", error);
    if (cyContainer.value) {
      cyContainer.value.innerHTML = '<div class="flex items-center justify-center h-full text-red-500 text-sm font-medium">Terjadi kesalahan saat memuat data struktur jabatan.</div>';
    }
  } finally {
    isRequesting.value = false;
  }
};

const getTree = async () => {
  if (!cyContainer.value || nodes.length === 0) return;
  cyContainer.value.innerHTML = '';

  const edges = [];
  const nodeMap = new Map();
  nodes.forEach(n => nodeMap.set(n.data.id, n.data));

  const karyNodes = nodes.filter(n => n.data.id !== 'root-0');

  // Kelompokkan karyawan per Divisi
  const divisiGroups = {};
  karyNodes.forEach(n => {
    const divKey = n.data.divisi_id || 'unassigned';
    if (!divisiGroups[divKey]) divisiGroups[divKey] = [];
    divisiGroups[divKey].push(n.data);
  });

  karyNodes.forEach(node => {
    let parentId = null;

    // 1. Cek Atasan Langsung eksplisit (atasan_id)
    if (node.atasan_id && nodeMap.has(`kary-${node.atasan_id}`)) {
      parentId = `kary-${node.atasan_id}`;
    } else {
      // 2. Cari pejabat di divisi yang sama dengan level lebih tinggi
      const sameDivisi = divisiGroups[node.divisi_id || 'unassigned'] || [];
      const superiorsInDiv = sameDivisi.filter(p => p.level > node.level && p.id !== node.id);

      if (superiorsInDiv.length > 0) {
        const minSupLevel = Math.min(...superiorsInDiv.map(s => s.level));
        const closestSuperiors = superiorsInDiv.filter(s => s.level === minSupLevel);
        parentId = closestSuperiors[0].id;
      } else {
        // 3. Jika pejabat tertinggi di divisinya, hubungkan ke Root
        parentId = 'root-0';
      }
    }

    if (parentId && parentId !== node.id) {
      edges.push({
        data: {
          id: `edge-${parentId}-${node.id}`,
          source: parentId,
          target: node.id
        }
      });
    }
  });

  const elements = [...nodes, ...edges];

  const levelColors = {
    100: '#1e293b',
    6: '#1e40af',  // Direktur (Biru Tua)
    5: '#2563eb',  // GM (Biru)
    4: '#0d9488',  // Manager (Teal)
    3: '#16a34a',  // Kadiv (Hijau)
    2: '#d97706',  // Karu (Amber)
    1: '#64748b',  // Staff (Abu-abu Slate)
  };

  const cy = cytoscape({
    container: cyContainer.value,
    elements: elements,
    style: [
      {
        selector: 'node',
        style: {
          'shape': 'round-rectangle',
          'background-color': function (ele) {
            const lvl = ele.data('level') || 1;
            return levelColors[lvl] || '#475569';
          },
          'border-color': '#ffffff',
          'border-width': 1.5,
          'label': function (ele) {
            const data = ele.data();
            const jab = data.jabatan ? data.jabatan.toUpperCase() : '';
            const div = data.divisi_name ? `[${data.divisi_name}]` : '';
            const name = data.name || '';
            return div ? `${jab} ${div}\n${name}` : `${jab}\n${name}`;
          },
          'text-wrap': 'wrap',
          'text-valign': 'center',
          'text-halign': 'center',
          'color': '#ffffff',
          'font-size': '10px',
          'font-family': 'Inter, sans-serif',
          'font-weight': '600',
          'padding': '8px',
          'width': '160px',
          'height': '65px',
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
    layout: {
      name: 'breadthfirst',
      directed: true,
      spacingFactor: 1.4,
      padding: 40,
      animate: true,
      animationDuration: 500
    },
    zoomingEnabled: true,
    minZoom: 0.1,
    maxZoom: 3,
    wheelSensitivity: 0.2
  });

  cy.fit(null, 40);
  cy.center();
};

onBeforeMount(async () => {
  await getData();
});

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))