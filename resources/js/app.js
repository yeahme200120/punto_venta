import './bootstrap';
import 'cropperjs/dist/cropper.css';
import Cropper from 'cropperjs';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';
import axios from 'axios';
import Chart from 'chart.js/auto';

// Configurar Axios globalmente
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
window.Chart = Chart;

// Hacer SweetAlert2 disponible globalmente
window.Swal = Swal;
// Hacer Cropper disponible globalmente
window.Cropper = Cropper;

Alpine.plugin(collapse);
window.Alpine = Alpine;

Alpine.start();
