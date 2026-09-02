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
  m_comp_id: 0,
  start_level: 1,
  end_level: 3

})

let initialValues = {};
let nodes = [];

const getDataOld = async () => {
  const dataURL = `${store.server.url_backend}/operation/m_kary`;
  isRequesting.value = true;

  const params = {
    join: true,
    comp_id: values.m_comp_id,
    scopes: "Structure",
    start_level: values.start_level,
    end_level: values.end_level,
    transform: false,
  };
  const fixedParams = new URLSearchParams(params);

  try {
    const res = await fetch(dataURL + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    });
    if (!res.ok) throw new Error("Failed when trying to read data");

    const resultJson = await res.json();
    initialValues = resultJson.data;
    console.log('CEK PERENT', initialValues);

    // 1. Ambil semua nama yang muncul sebagai nama2 (same_level.nama_karyawan)
    const nama2List = initialValues
      .map((item) => {
        const valueRaw = item.m_kary_det_jabatan[0]?.same_level_with ?? null;
        try {
          const parsed = valueRaw ? JSON.parse(valueRaw) : null;
          return parsed?.nama_karyawan;
        } catch {
          return null;
        }
      })
      .filter(Boolean); // hanya nama yang valid

    // 2. Buat datalevel, skip jika name sudah ada di nama2List
    const datalevel = initialValues
    
      .map((item, index) => {
        const valueRaw = item.m_kary_det_jabatan[0]?.same_level_with ?? null;
        let same_level = null;

        try {
          same_level = valueRaw ? JSON.parse(valueRaw) : null;
        } catch (e) {
          console.warn("Invalid JSON format in same_level_with:", valueRaw);
        }

        const nama2 = same_level?.nama_karyawan ?? null;
        const jabatan2 = same_level?.jabatan ?? null;
        const jabatan = item.m_kary_det_jabatan?.[0]?.jabatan ?? null;
        const level = item.m_kary_det_jabatan?.[0]?.level ?? null;
        const m_comp_id = item.m_kary_det_jabatan?.[0]?.m_comp_id ?? null;
        const m_divisi_id = item.m_kary_det_jabatan?.[0]?.m_divisi_id ?? null;
        const m_branch_id = item.m_kary_det_jabatan?.[0]?.m_branch_id ?? null;
        const m_subcomp_id = item.m_kary_det_jabatan?.[0]?.m_subcomp_id ?? null;
        const parent_id = item.m_kary_det_jabatan?.[0]?.parent_id ?? null;

        const nama1 = item.nama_lengkap;

        if (nama2List.includes(nama1)) {
          return null;
        }

        if (jabatan !== null || level !== null) {
          return {
            id: index + 1,
            level: level,
            name: nama1,
            name2: nama2,
            jabatan2: jabatan2,
            jabatan: jabatan,
            m_comp_id: m_comp_id,
            m_subcomp_id: m_subcomp_id,
            m_branch_id: m_branch_id,
            m_divisi_id: m_divisi_id,
            parent_id: parent_id,
          };
        }

        return null;
      })
      .filter(Boolean); // buang yang null


    nodes = [
      { data: { id: 0, level: 0, name: 'DIREKTUR UTAMA', jabatan: '', m_comp_id: null, m_sub_comp_id: null, m_branch_id: null, m_divisi_id: null } }
    ];

    datalevel.forEach(item => {
      nodes.push({ data: item });
    });

    console.log('NODES', nodes);

    await getTree();

  } catch (error) {
    console.error("Error fetching data: ", error);
  } finally {
    isRequesting.value = false;
  }
};

const getTreeOld = async () => {
  cyContainer.value.innerHTML = '';

  cytoscape.use(cytoscapeElk);

  // Define color scheme for different levels
  const levelColors = {
    0: '#2c3e50',     // Root - Dark blue
    1: '#3498db',     // Level 1 - Blue
    2: '#2980b9',     // Level 2 - Darker blue
    3: '#16a085',     // Level 3 - Teal
    4: '#27ae60',     // Level 4 - Green
    5: '#f39c12',     // Level 5 - Orange
    // For levels beyond 5, we'll generate colors programmatically
  };

  // Generate colors for higher levels
  for (let level = 6; level <= 120; level++) {
    const hue = (level * 30) % 360; // Rotate through hue spectrum
    levelColors[level] = `hsl(${hue}, 70%, 60%)`;
  }

  const edges = [];
  nodes.filter(node => node.data.level === 1).forEach(level1 => {
    edges.push({ data: { source: 0, target: level1.data.id } });
  });

  nodes.filter(node => node.data.level === 2).forEach(level2 => {
    nodes.filter(node => node.data.level === 1).forEach(level1 => {
      edges.push({ data: { source: level1.data.id, target: level2.data.id } });
    });
  });

  nodes.filter(node => node.data.level === 3).forEach(level3 => {
    nodes.filter(node => node.data.level === 2 && node.data.m_comp_id === level3.data.m_comp_id).forEach(level2 => {
      edges.push({ data: { source: level2.data.id, target: level3.data.id } });
    });
  });

  nodes.filter(node => node.data.level === 4).forEach(level4 => {
    nodes.filter(node => node.data.level === 3 &&
      node.data.m_comp_id === level4.data.m_comp_id &&
      node.data.m_sub_comp_id === level4.data.m_sub_comp_id)
      .forEach(level3 => {
        edges.push({ data: { source: level3.data.id, target: level4.data.id } });
      });
  });

  nodes.filter(node => node.data.level === 5).forEach(level5 => {
    nodes.filter(node => node.data.level === 4 &&
      node.data.m_comp_id === level5.data.m_comp_id &&
      node.data.m_sub_comp_id === level5.data.m_sub_comp_id &&
      node.data.m_branch_id === level5.data.m_branch_id)
      .forEach(level4 => {
        edges.push({ data: { source: level4.data.id, target: level5.data.id } });
      });
  });

  const maxLevel = 120;
  for (let level = 5; level < maxLevel; level++) {
    const currentLevel = level;
    const nextLevel = level + 1;

    nodes.filter(node => node.data.level === currentLevel).forEach(currentNode => {
      nodes.filter(node => node.data.level === nextLevel)
        .forEach(nextNode => {
          if (nextLevel === 6) {
            if (nextNode.data.parent_id === currentNode.data.m_divisi_id) {
              edges.push({ data: { source: currentNode.data.id, target: nextNode.data.id } });
            }
          } else if (nextLevel > 6) {
            if (nextNode.data.parent_id === currentNode.data.m_divisi_id) {
              edges.push({ data: { source: currentNode.data.id, target: nextNode.data.id } });
            }
          } else {
            if (node.data.m_comp_id === currentNode.data.m_comp_id &&
              node.data.m_sub_comp_id === currentNode.data.m_sub_comp_id &&
              node.data.m_branch_id === currentNode.data.m_branch_id &&
              node.data.m_divisi_id === currentNode.data.m_divisi_id) {
              edges.push({ data: { source: currentNode.data.id, target: nextNode.data.id } });
            }
          }
        });
    });
  }

  const elements = [...nodes, ...edges];

  const cy = cytoscape({
    container: cyContainer.value,
    elements: elements,
    style: [
      {
        selector: 'node',
        style: {
          shape: 'roundrectangle',
          'background-color': function(ele) {
            const level = ele.data('level') || 0;
            return levelColors[level] || '#95a5a6'; // Default color if level not found
          },
          'border-color': '#34495e',
          'border-width': 2,
          label: function (ele) {
            const data = ele.data();
            let label = data.jabatan + "\n" + data.name;

            if (data.jabatan2 && data.name2) {
              label += "\n" + data.jabatan2 + "\n" + data.name2;
            }

            return label;
          },
          color: '#ffffff', // White text for better contrast
          'text-valign': 'center',
          'text-halign': 'center',
          'font-size': '12px',
          'font-family': 'Inter, sans-serif',
          'font-weight': 'bold',
          'padding': '10px',
          'width': '150px',
          'height': '75px', // Auto height based on content
          'text-wrap': 'wrap',
          'text-max-width': '150px',
          'text-outline-color': 'rgba(0,0,0,0.5)',
          'text-outline-width': '1px',
          'text-margin-y': '5px',
          'min-height': '50px',
        },
      },
      {
        selector: 'node:selected',
        style: {
          'border-width': 4,
          'border-color': '#e74c3c'
        }
      },
      {
        selector: 'edge',
        style: {
          width: 3,
          'line-color': '#7f8c8d',
          'target-arrow-color': '#7f8c8d',
          'curve-style': 'taxi',
          'taxi-direction': 'downward',
          'target-arrow-shape': 'triangle',
          'arrow-scale': 0.8,
          'taxi-turn': '20px',
          'taxi-turn-min-distance': '30px'
        }
      },
      {
        selector: 'edge:selected',
        style: {
          'line-color': '#e74c3c',
          'target-arrow-color': '#e74c3c',
          'width': 4
        }
      }
    ],
    layout: {
      name: 'elk',
      elk: {
        algorithm: 'mrtree',
        direction: 'DOWN',
        nodeSpacing: 50,
        levelSpacing: 120,
        edgeSpacingFactor: 0.6,
        separateConnectedComponents: true,
        edgeRouting: 'ORTHOGONAL',
        hierarchyHandling: 'INCLUDE_CHILDREN',
        considerNodeLabels: true
      },
      fit: true,
      padding: 80,
      animate: true,
      animationDuration: 800,
      animationEasing: 'ease-out-quad'
    },
    zoomingEnabled: true,
    minZoom: 0.1,
    maxZoom: 2,
    wheelSensitivity: 0.1,
    boxSelectionEnabled: true
  });

  // Add hover effects
  cy.on('mouseover', 'node', function(event) {
    const node = event.target;
    node.animate({
      style: {
        'background-color': '#f1c40f',
        'border-color': '#e67e22',
        'border-width': 3
      },
      duration: 200
    });
    
    // Highlight connected edges
    node.connectedEdges().animate({
      style: {
        'line-color': '#e67e22',
        'target-arrow-color': '#e67e22',
        'width': 4
      },
      duration: 200
    });
  });

  cy.on('mouseout', 'node', function(event) {
    const node = event.target;
    const level = node.data('level') || 0;
    node.animate({
      style: {
        'background-color': levelColors[level] || '#95a5a6',
        'border-color': '#34495e',
        'border-width': 2
      },
      duration: 200
    });
    
    // Reset connected edges
    node.connectedEdges().animate({
      style: {
        'line-color': '#7f8c8d',
        'target-arrow-color': '#7f8c8d',
        'width': 3
      },
      duration: 200
    });
  });

  // Adjust zoom based on screen size
  const screenWidth = window.innerWidth;
  if (screenWidth < 768) {
    cy.zoom({ level: 0.4 });
  } else if (screenWidth < 1024) {
    cy.zoom({ level: 0.6 });
  } else {
    cy.zoom({ level: 0.8 });
  }
  cy.center();

  // Add a slight pan to make the chart more centered
  cy.panBy({ x: 0, y: -50 });
};

const getData = async () => {
  isRequesting.value = true;
  const dataURL = `${store.server.url_backend}/operation/m_kary`;

  const params = new URLSearchParams({
    join: true,
    comp_id: values.m_comp_id,
    scopes: "Structure",
    start_level: values.start_level,
    end_level: values.end_level,
    transform: false,
  });

  try {
    const res = await fetch(`${dataURL}?${params}`, {
      headers: {
        'Content-Type': 'application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`
      },
    });
    const result = await res.json();

    // Mapping sederhana: satu orang satu node
    const newNodes = result.data.map(item => {
      const det = item.m_kary_det_jabatan?.[0] ?? {};
      return {
        data: {
          id: `kary-${item.id}`,
          name: item.nama_lengkap,
          jabatan: det.jabatan || 'STAFF',
          level: det.level ?? 0,
        }
      };
    });

    nodes = [
      { data: { id: 'root-0', level: 1000, name: 'DIREKTUR UTAMA', jabatan: 'TOP MANAGEMENT' } },
      ...newNodes
    ];

    await getTree();
  } catch (error) {
    console.error(error);
  } finally {
    isRequesting.value = false;
  }
};

const getTree = async () => {
  if (!cyContainer.value) return;
  cyContainer.value.innerHTML = '';

  const edges = [];
  // Ambil semua level unik, urutkan dari paling tinggi (1000, 99, dst)
  const allLevels = [...new Set(nodes.map(n => n.data.level))].sort((a, b) => b - a);

  // Buat garis antar level
  for (let i = 0; i < allLevels.length - 1; i++) {
    const currentLevel = allLevels[i];
    const nextLevel = allLevels[i+1];

    const parents = nodes.filter(n => n.data.level === currentLevel);
    const children = nodes.filter(n => n.data.level === nextLevel);

    parents.forEach(p => {
      children.forEach(c => {
        edges.push({ data: { source: p.data.id, target: c.data.id } });
      });
    });
  }

  const cy = cytoscape({
    container: cyContainer.value,
    elements: { nodes, edges },
    style: [
      {
        selector: 'node',
        style: {
          'shape': 'round-rectangle',
          'background-color': '#3498db',
          'label': ele => `${ele.data('jabatan').toUpperCase()}\n${ele.data('name')}`,
          'text-wrap': 'wrap',
          'text-valign': 'center',
          'text-halign': 'center',
          'color': '#fff',
          'font-size': '10px',
          'width': '140px',
          'height': '60px',
        }
      },
      {
        selector: 'edge',
        style: {
          'width': 1,
          'line-color': '#ccc',
          'target-arrow-shape': 'triangle',
          'curve-style': 'taxi', // Siku-siku
          'taxi-direction': 'downward'
        }
      }
    ],
    layout: {
      name: 'breadthfirst', // Gunakan breadthfirst, lebih stabil buat hierarki level
      directed: true,
      spacingFactor: 1.2,
      padding: 30
    }
  });
};

onBeforeMount(async () => {
  await getData();
  await new Promise(resolve => setTimeout(resolve, 400));
  await getTree();
  await new Promise(resolve => setTimeout(resolve, 400));
});

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))