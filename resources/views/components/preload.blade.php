{{-- resources/views/components/preload.blade.php --}}

<div
    x-data="preloadComponent()"
    x-init="initPreload"
    x-show="loading"
    x-transition.opacity.duration.300ms
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/40 backdrop-blur-md"
    style="display:none">

    <div class="w-full max-w-md mx-4 overflow-hidden bg-white shadow-2xl rounded-3xl">
        {{-- HEADER --}}
        <div class="px-6 py-5 text-white bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-500">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-2xl">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4 8 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Punto de Venta ERP</h3>
                    <p class="text-sm text-white/80">Procesando información...</p>
                </div>
            </div>
        </div>

        {{-- BODY --}}
        <div class="p-6">
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="w-20 h-20 border-4 rounded-full border-slate-200"></div>
                    <div class="absolute inset-0 w-20 h-20 border-4 border-indigo-600 rounded-full animate-spin border-t-transparent"></div>
                    <div class="absolute flex items-center justify-center w-12 h-12 transform -translate-x-1/2 -translate-y-1/2 rounded-xl bg-indigo-50 top-1/2 left-1/2">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l4 4-4 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- MENSAJE --}}
            <div class="text-center">
                <h4 class="mb-1 text-lg font-semibold text-slate-800" x-text="message"></h4>
                <p class="text-sm text-slate-500">Por favor espere un momento</p>
            </div>

            {{-- PROGRESO --}}
            <div class="mt-6">
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full transition-all duration-300 rounded-full bg-gradient-to-r from-indigo-600 to-cyan-500" :style="'width:' + progress + '%'"></div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-slate-400">
                    <span>Progreso</span>
                    <span x-text="progress + '%'"></span>
                </div>
            </div>

            {{-- ESTADO --}}
            <div class="flex items-center justify-between p-3 mt-5 rounded-xl bg-slate-50">
                <div>
                    <p class="text-xs text-slate-400">Estado</p>
                    <p class="font-medium text-slate-700" x-text="message"></p>
                </div>
                <div x-show="elapsedTime > 0" class="text-right">
                    <p class="text-xs text-slate-400">Tiempo</p>
                    <p class="font-semibold text-indigo-600" x-text="elapsedTime + 's'"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function preloadComponent() {
    return {
        loading: false,
        message: 'Cargando...',
        progress: 0,
        activeRequests: 0,
        elapsedTime: 0,
        progressInterval: null,
        timerInterval: null,
        isDownloading: false,

        messages: [
            'Cargando información...',
            'Preparando datos...',
            'Consultando registros...',
            'Procesando solicitud...',
            'Aplicando cambios...',
            'Sincronizando información...',
            'Validando datos...',
            'Finalizando operación...'
        ],

        initPreload() {
            this.interceptFetch();
            this.interceptAxios();
            this.interceptForms();
            this.interceptLinks();
            this.interceptLivewire();
        },

        show(message = 'Cargando...') {
            if (this.loading) return;
            this.loading = true;
            this.message = message;
            this.progress = 0;
            this.elapsedTime = 0;
            this.startProgressSimulation();
            this.startTimer();
        },

        hide() {
            this.progress = 100;
            setTimeout(() => {
                this.loading = false;
                this.stopProgressSimulation();
                this.stopTimer();
                this.progress = 0;
                this.elapsedTime = 0;
            }, 400);
        },

        startProgressSimulation() {
            this.stopProgressSimulation();
            this.progressInterval = setInterval(() => {
                if (this.progress < 90) {
                    let incremento = this.elapsedTime > 10 ? 1 : 3;
                    this.progress = Math.min(this.progress + incremento, 90);
                }
            }, 300);
        },

        stopProgressSimulation() {
            clearInterval(this.progressInterval);
        },

        startTimer() {
            this.stopTimer();
            this.timerInterval = setInterval(() => {
                this.elapsedTime++;
                if (this.elapsedTime % 4 === 0) {
                    let index = Math.floor((this.elapsedTime / 4) % this.messages.length);
                    this.message = this.messages[index];
                }
            }, 1000);
        },

        stopTimer() {
            clearInterval(this.timerInterval);
        },

        // Detectar si una URL es de descarga
        isDownloadUrl(url) {
            if (typeof url !== 'string') return false;
            const downloadPatterns = [
                '/exportar/', '/exportar/excel', '/exportar/sql',
                'exportar', '.xlsx', '.xls', '.csv', '.sql',
                'respaldos/exportar'
            ];
            return downloadPatterns.some(pattern => url.includes(pattern));
        },

        interceptFetch() {
            const originalFetch = window.fetch;
            const self = this;

            window.fetch = async (...args) => {
                const url = args[0];
                
                // Verificar si es una descarga
                if (self.isDownloadUrl(url)) {
                    self.isDownloading = true;
                    return originalFetch(...args);
                }

                self.activeRequests++;
                if (self.activeRequests === 1 && !self.isDownloading) {
                    self.show('Cargando datos...');
                }

                try {
                    const response = await originalFetch(...args);
                    
                    // Verificar si la respuesta es una descarga por headers
                    const contentDisposition = response.headers.get('Content-Disposition');
                    if (contentDisposition && contentDisposition.includes('attachment')) {
                        self.isDownloading = true;
                        self.activeRequests--;
                        if (self.activeRequests <= 0) {
                            self.activeRequests = 0;
                            self.hide();
                        }
                        return response;
                    }
                    
                    return response;
                } finally {
                    self.activeRequests--;
                    if (self.activeRequests <= 0 && !self.isDownloading) {
                        self.activeRequests = 0;
                        self.hide();
                    }
                    self.isDownloading = false;
                }
            };
        },

        interceptAxios() {
            if (!window.axios) return;
            const self = this;

            axios.interceptors.request.use(config => {
                const url = config.url || '';
                
                // Ignorar descargas
                if (self.isDownloadUrl(url) || config.responseType === 'blob' || config.responseType === 'arraybuffer') {
                    self.isDownloading = true;
                    return config;
                }

                self.activeRequests++;
                if (self.activeRequests === 1 && !self.isDownloading) {
                    self.show('Procesando solicitud...');
                }
                return config;
            });

            axios.interceptors.response.use(
                response => {
                    const isDownload = response.config?.responseType === 'blob' ||
                                      response.config?.responseType === 'arraybuffer' ||
                                      response.headers?.['content-disposition']?.includes('attachment');
                    
                    if (!isDownload) {
                        self.activeRequests--;
                        if (self.activeRequests <= 0 && !self.isDownloading) {
                            self.activeRequests = 0;
                            self.hide();
                        }
                    } else {
                        self.isDownloading = true;
                        self.hide();
                    }
                    return response;
                },
                error => {
                    self.activeRequests--;
                    if (self.activeRequests <= 0) {
                        self.activeRequests = 0;
                        self.hide();
                    }
                    return Promise.reject(error);
                }
            );
        },

        
    };
}
</script>