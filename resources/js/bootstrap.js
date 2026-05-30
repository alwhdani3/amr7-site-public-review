import axios from 'axios'

window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// CSRF (اختياري — Laravel غالبًا يضبطها عبر cookie، لكن هذا يزيد الأمان)
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token
}
