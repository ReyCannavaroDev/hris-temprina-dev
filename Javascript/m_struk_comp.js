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
  document.title = 'Struktur Company'
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
      link.download = `STRUKTUR COMPANY PT.TEMPRINA ${formattedDate}.png`;
      link.click();
    });
  }
};

const cyContainer = ref(null);
const values = reactive({
})

let initialValues = {};
let nodes = [];

const getDataOld = async () => {
  const dataURL = `${store.server.url_backend}/operation/m_comp/structure_company`;
  isRequesting.value = true;

  // const params = {};
  // const fixedParams = new URLSearchParams(params);

  const params = new URLSearchParams({
    sbu_id: values.m_comp_id,
  });

  const fixedParams = params;

  try {
    const res = await fetch(dataURL + '?' + fixedParams, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
    });

    if (!res.ok) throw new Error("Failed when trying to read data");

    const resultJson = await res.json();
    initialValues = resultJson;
    console.log('CEK PERENT', initialValues);

    const datalevel = initialValues;
    console.log('Transformed Data:', datalevel);

    const subcomp = [];
    const branchList = [];
    const divisiList = []  // Declare divisiList here

    datalevel.forEach((item) => {
      if (item.subcomp && Array.isArray(item.subcomp)) {
        item.subcomp.forEach(sub => {
          sub.id = `${item.id}_${sub.id}`; // Generate unique ID for subcomp
          subcomp.push(sub);

          // Process branch
          if (Array.isArray(sub.branch)) {
            sub.branch.forEach(branch => {
              branch.id = `${sub.id}_${branch.id}`; // Generate unique ID for branch
              branch.level = 3; // Level 3 for branch
              branchList.push(branch); // Add to branch list

              // Process divisi inside each branch
              if (Array.isArray(branch.divisi)) {
                branch.divisi.forEach(divisi => {
                  divisi.id = `${branch.id}_${divisi.id}`; // Generate unique ID for divisi
                  divisi.level = 4; // Assign level for divisi
                  divisiList.push(divisi); // Add to divisi list
                });
              }
            });
          }
        });
      }
    });

    // Display subcomp, branch, and divisi data
    console.log('All subcomp:', subcomp);
    console.log('All Branch List:', branchList);
    console.log('All Divisi List:', divisiList);

    nodes = [
      { data: { id: 0, level: 0, name: 'STRUKTUR COMPANY TEMPRINA', key: '0' } }
    ];

    // Add data to nodes
    datalevel.forEach(item => {
      nodes.push({ data: item });
    });

    // Add Subcomp to nodes
    subcomp.forEach(sub => {
      nodes.push({ data: sub });
    });

    // Add Branch to nodes
    branchList.forEach(branch => {
      nodes.push({ data: branch });
    });

    // Add Divisi to nodes (now as part of Branch)
    divisiList.forEach(divisi => {
      nodes.push({ data: divisi });
    });

    // Calling getTree to render the graph
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

  const levelColors = {
    0: '#2c3e50',  // Root - Dark blue
    1: '#3498db',  // Level 1 - Blue
    2: '#2980b9',  // Level 2 - Darker blue
    3: '#16a085',  // Level 3 - Teal (Branch)
    4: '#27ae60',  // Level 4 - Green (Divisi)
    5: '#f39c12',  // Level 5 - Orange
  };

  // Generate edges
  const edges = [];

  // Connect Level 1 to Level 2
  nodes.filter(node => node.data.level === 1).forEach(level1 => {
    edges.push({ data: { source: 0, target: level1.data.id } });
  });

  // Connect Level 1 to Level 2
  nodes.filter(node => node.data.level === 1).forEach(level1 => {
    nodes.filter(node => node.data.level === 2).forEach(level2 => {
      const sbuId = level2.data.id.split('_')[0]; // Get '4' from '4_5'

      if (level1.data.id === sbuId) { // Ensure the Level 1 ID matches the prefix of Level 2
        edges.push({
          data: {
            source: level1.data.id,
            target: level2.data.id
          }
        });
      }
    });
  });

  // Connect Level 2 to Level 3 (Subcomp -> Branch)
  nodes.filter(node => node.data.level === 2).forEach(level2 => {
    const sbuId = level2.data.id.split('_')[0]; // Get '4' from '4_5'

    // Connect each branch (level 3) to its subcomp (level 2)
    nodes.filter(node => node.data.level === 3).forEach(level3 => {
      if (level3.data.id.startsWith(`${sbuId}_`)) {
        edges.push({
          data: {
            source: level2.data.id,
            target: level3.data.id
          }
        });
      }
    });
  });

  // Connect Level 3 to Level 4 (Branch -> Divisi)
  nodes.filter(node => node.data.level === 3).forEach(level3 => {
    // Connect each divisi (level 4) to its branch (level 3)
    nodes.filter(node => node.data.level === 4).forEach(divisi => {
      if (divisi.data.id.startsWith(`${level3.data.id}_`)) {
        edges.push({
          data: {
            source: level3.data.id,
            target: divisi.data.id
          }
        });
      }
    });
  });

  // Combine nodes and edges
  const elements = [...nodes, ...edges];
  const cy = cytoscape({
    container: cyContainer.value,
    elements: elements,
    style: [
      {
        selector: 'node',
        style: {
          shape: 'roundrectangle',
          'background-color': function (ele) {
            const level = ele.data('level') || 0;
            return levelColors[level] || '#95a5a6'; // Default color if level not found
          },
          'border-color': '#34495e',
          'border-width': 2,
          label: function (ele) {
            const data = ele.data();
            return data.name;
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
  cy.on('mouseover', 'node', function (event) {
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

  cy.on('mouseout', 'node', function (event) {
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
  const dataURL = `${store.server.url_backend}/operation/m_comp/structure`;
  isRequesting.value = true;

  const params = new URLSearchParams({
    sbu_id: values.m_comp_id,
  });

  try {
    const res = await fetch(dataURL + '?' + params, {
      headers: {
        'Content-Type': 'Application/json',
        Authorization: `${store.user.token_type} ${store.user.token}`,
      },
    });

    if (!res.ok) throw new Error("Failed when trying to read data");

    const resultJson = await res.json();
    const datalevel = resultJson.data; 

    // Reset nodes dengan Root utama
    nodes = [
      { data: { id: 'ROOT_0', level: 0, name: 'STRUKTUR COMPANY', key: '0' } }
    ];

    // Fungsi Rekursif untuk memproses Divisi dan Sub-Divisi
    const processDivisi = (divisi, parentId, level) => {
      const uniqueDivId = `DIV_${divisi.id}`;
      nodes.push({
        data: {
          id: uniqueDivId,
          parentId: parentId, 
          name: divisi.divisi_name || divisi.name,
          level: level
        }
      });

      // Jika ada anak divisi, telusuri lagi secara rekursif
      if (divisi.child_divisi && divisi.child_divisi.length > 0) {
        divisi.child_divisi.forEach(child => {
          processDivisi(child, uniqueDivId, level + 1);
        });
      }
    };

    // Looping Data Utama
    if (Array.isArray(datalevel)) {
      datalevel.forEach((sub) => {
        const subId = `SUB_${sub.id}`;
        nodes.push({
          data: {
            id: subId,
            parentId: 'ROOT_0',
            name: sub.name,
            level: 2
          }
        });

        if (sub.m_branch && Array.isArray(sub.m_branch)) {
          sub.m_branch.forEach(branch => {
            const branchId = `BR_${branch.id}`;
            nodes.push({
              data: {
                id: branchId,
                parentId: subId,
                name: branch.name,
                level: 3
              }
            });

            if (branch.m_divisi && Array.isArray(branch.m_divisi)) {
              branch.m_divisi.forEach(divisi => {
                processDivisi(divisi, branchId, 4);
              });
            }
          });
        }
      });
    }

    console.log('Nodes Prepared:', nodes);
    
    // Panggil render tree setelah data siap
    await getTree();

  } catch (error) {
    console.error("Error fetching data: ", error);
  } finally {
    isRequesting.value = false;
  }
};

const getTree = async () => {
  if (!cyContainer.value) return;
  
  cyContainer.value.innerHTML = '';

  // Pastikan ekstensi ELK sudah terdaftar (biasanya di luar fungsi ini, tapi ditaruh di sini untuk keamanan)
  try {
      cytoscape.use(cytoscapeElk);
  } catch (e) {
      // ignore if already registered
  }

  const levelColors = {
    0: '#2c3e50',  // Root
    1: '#3498db',  // Level 1
    2: '#2980b9',  // Level 2 (Subcomp)
    3: '#16a085',  // Level 3 (Branch)
    4: '#27ae60',  // Level 4 (Divisi Utama)
    5: '#f39c12',  // Level 5 (Sub Divisi)
    6: '#d35400',  // Level 6 (Sub-sub Divisi)
  };

  // Generate edges secara dinamis berdasarkan parentId yang sudah kita siapkan
  const edges = nodes
    .filter(node => node.data.parentId)
    .map(node => ({
      data: {
        source: node.data.parentId,
        target: node.data.id
      }
    }));

  const elements = [...nodes, ...edges];

  const cy = cytoscape({
    container: cyContainer.value,
    elements: elements,
    style: [
      {
        selector: 'node',
        style: {
          shape: 'roundrectangle',
          'background-color': function (ele) {
            const level = ele.data('level') || 0;
            return levelColors[level] || '#95a5a6';
          },
          'border-color': '#34495e',
          'border-width': 2,
          label: 'data(name)',
          color: '#ffffff',
          'text-valign': 'center',
          'text-halign': 'center',
          'font-size': '12px',
          'font-family': 'Inter, sans-serif',
          'font-weight': 'bold',
          'padding': '10px',
          'width': '150px',
          'height': '75px',
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

  // Hover Effects
  cy.on('mouseover', 'node', function (event) {
    const node = event.target;
    node.animate({
      style: { 'background-color': '#f1c40f', 'border-color': '#e67e22', 'border-width': 3 },
      duration: 200
    });
    node.connectedEdges().animate({
      style: { 'line-color': '#e67e22', 'target-arrow-color': '#e67e22', 'width': 4 },
      duration: 200
    });
  });

  cy.on('mouseout', 'node', function (event) {
    const node = event.target;
    const level = node.data('level') || 0;
    node.animate({
      style: { 'background-color': levelColors[level] || '#95a5a6', 'border-color': '#34495e', 'border-width': 2 },
      duration: 200
    });
    node.connectedEdges().animate({
      style: { 'line-color': '#7f8c8d', 'target-arrow-color': '#7f8c8d', 'width': 3 },
      duration: 200
    });
  });

  // Auto-Zoom responsif
  const screenWidth = window.innerWidth;
  if (screenWidth < 768) { cy.zoom({ level: 0.4 }); } 
  else if (screenWidth < 1024) { cy.zoom({ level: 0.6 }); } 
  else { cy.zoom({ level: 0.8 }); }
  
  cy.center();
  cy.panBy({ x: 0, y: -50 });
};

onBeforeMount(async () => {
  await getData();
  await new Promise(resolve => setTimeout(resolve, 400));
  await getTree();
  await new Promise(resolve => setTimeout(resolve, 400));
});

//  @endif -------------------------------------------------END
watchEffect(() => store.commit('set', ['isRequesting', isRequesting.value]))