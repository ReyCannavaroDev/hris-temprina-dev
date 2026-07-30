import { useRouter, useRoute } from 'vue-router'
import { ref, readonly, reactive, inject, onMounted, onBeforeMount } from 'vue'

onMounted(() => {
  const dataRespo = JSON.parse(localStorage.getItem('respo') || '{}');
  const dataLogin = JSON.parse(localStorage.getItem('user') || '{}');

  // console.log(dataRespo.name)
  //console.log(dataLogin.data.name)
  
  if (dataLogin && (dataLogin?.data?.name || dataLogin?.username)) {
    const label = document.querySelector('label.flex.gap-1.items-start');
    if (label) {
      const textNode = [...label.childNodes].find(n => n.nodeType === Node.TEXT_NODE);
      if (textNode) {
        textNode.textContent = ` ${dataLogin?.data?.name || dataLogin?.username}`;
      }
    }
    
    if (dataRespo && Object.keys(dataRespo).length > 0) {
      const respoDiv = document.createElement('div');
      respoDiv.textContent = dataRespo.name;
      respoDiv.classList.add(
        'ml-6',
        'text-sm',
        'text-blue-600',
        'font-medium',
        'opacity-90'
      );
      
      label.insertAdjacentElement('afterend', respoDiv);
    }
  }
});//   javascript