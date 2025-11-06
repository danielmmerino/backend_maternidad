import '../bootstrap';

const axios = window.axios;

document.addEventListener('DOMContentLoaded', () => {
    if (!axios) {
        console.error('Axios no se ha inicializado correctamente.');
        return;
    }

    const video = document.getElementById('qr-video');
    const videoPlaceholder = document.getElementById('video-placeholder');
    const startButton = document.getElementById('start-scan');
    const stopButton = document.getElementById('stop-scan');
    const statusElement = document.getElementById('scan-status');
    const errorElement = document.getElementById('scan-error');
    const badgeElement = document.getElementById('scan-badge');
    const detailsContainer = document.getElementById('agenda-details');

    const detailElements = new Map(
        Array.from(detailsContainer?.querySelectorAll('[data-field]') || []).map((element) => [
            element.dataset.field,
            element,
        ]),
    );

    const statusVariants = {
        muted: 'text-sm font-medium text-slate-500',
        active: 'text-sm font-medium text-emerald-600',
        loading: 'text-sm font-medium text-amber-600',
        error: 'text-sm font-medium text-rose-600',
    };

    const badgeVariants = {
        muted: 'inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600',
        success: 'inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700',
        info: 'inline-flex items-center gap-1 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-700',
        warning: 'inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700',
        error: 'inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-rose-700',
    };

    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d', { willReadFrequently: true });

    let barcodeDetector = null;
    let activeStream = null;
    let animationFrameId = null;
    let scanning = false;

    const nacionalidades = new Map();
    let nacionalidadesLoaded = false;

    const setStatus = (text, variant = 'muted') => {
        if (!statusElement) {
            return;
        }

        const variantClass = statusVariants[variant] || statusVariants.muted;
        statusElement.className = variantClass;
        statusElement.textContent = text;
    };

    const setBadge = (label, variant = 'muted') => {
        if (!badgeElement) {
            return;
        }

        const variantClass = badgeVariants[variant] || badgeVariants.muted;
        badgeElement.className = variantClass;
        badgeElement.textContent = label;
    };

    const showError = (message) => {
        if (!errorElement) {
            return;
        }

        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    };

    const hideError = () => {
        if (!errorElement) {
            return;
        }

        errorElement.textContent = '';
        errorElement.classList.add('hidden');
    };

    const clearAgenda = () => {
        detailElements.forEach((element) => {
            element.textContent = '—';
        });
        setBadge('Pendiente de escanear', 'muted');
    };

    const formatDate = (value) => {
        if (!value) {
            return '—';
        }

        const parsed = new Date(value);

        if (Number.isNaN(parsed.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('es-EC', {
            year: 'numeric',
            month: 'long',
            day: '2-digit',
        }).format(parsed);
    };

    const booleanLabel = (value) => (value ? 'Sí' : 'No');

    const deriveEscolaridad = (agenda) => {
        const niveles = [];

        if (agenda.escolaridad_basica) {
            niveles.push('Educación básica');
        }

        if (agenda.escolaridad_media) {
            niveles.push('Educación media');
        }

        if (agenda.escolaridad_superior) {
            niveles.push('Educación superior');
        }

        if (!niveles.length) {
            return 'Sin registro';
        }

        return niveles.join(' · ');
    };

    const determineAgendaStatus = (idEstado) => {
        switch (idEstado) {
            case 1:
                return { label: 'Agenda activa', variant: 'success' };
            case 2:
                return { label: 'Agenda en seguimiento', variant: 'info' };
            case 3:
                return { label: 'Agenda finalizada', variant: 'muted' };
            default:
                return { label: `Estado ${idEstado ?? 'desconocido'}`, variant: 'muted' };
        }
    };

    const ensureNacionalidades = async () => {
        if (nacionalidadesLoaded) {
            return;
        }

        try {
            const response = await axios.get('/api/catalogos/nacionalidades');
            const catalogo = response.data?.data || [];

            catalogo.forEach((item) => {
                nacionalidades.set(item.id, item.nombre_nacionalidad);
            });

            nacionalidadesLoaded = true;
        } catch (error) {
            console.error('No se pudieron cargar las nacionalidades', error);
        }
    };

    const renderAgenda = async (agenda) => {
        await ensureNacionalidades();

        const nacionalidadId = agenda.id_nacionalidad;
        const nacionalidad =
            nacionalidadId !== null && nacionalidadId !== undefined
                ? nacionalidades.get(nacionalidadId) || `ID ${nacionalidadId}`
                : 'Sin registro';
        const escolaridad = deriveEscolaridad(agenda);

        const values = {
            public_id: agenda.public_id,
            nombres_completo: agenda.nombres_completo,
            nacionalidad,
            rut: agenda.rut,
            fecha_nacimiento: formatDate(agenda.fecha_nacimiento),
            es_originario: booleanLabel(agenda.es_originario),
            descripcion_originario: agenda.es_originario
                ? agenda.descripcion_originario || 'Sin descripción'
                : 'No aplica',
            telefono1: agenda.telefono1 || 'Sin registro',
            telefono2: agenda.telefono2 || 'Sin registro',
            correo_electronico: agenda.correo_electronico || 'Sin registro',
            ocupacion: agenda.ocupacion || 'Sin registro',
            domicilio: agenda.domicilio || 'Sin registro',
            escolaridad,
        };

        Object.entries(values).forEach(([key, value]) => {
            const element = detailElements.get(key);

            if (!element) {
                return;
            }

            element.textContent = value ?? '—';
        });

        const estado = determineAgendaStatus(agenda.id_estado);
        setBadge(estado.label, estado.variant);
    };

    const stopScanner = (showIdleMessage = false) => {
        scanning = false;

        if (animationFrameId !== null) {
            cancelAnimationFrame(animationFrameId);
            animationFrameId = null;
        }

        if (activeStream) {
            activeStream.getTracks().forEach((track) => track.stop());
            activeStream = null;
        }

        if (video) {
            try {
                video.pause();
            } catch (error) {
                console.debug('No se pudo pausar el video', error);
            }

            video.srcObject = null;
            video.classList.add('hidden');
        }

        if (videoPlaceholder) {
            videoPlaceholder.classList.remove('hidden');
        }

        if (stopButton) {
            stopButton.disabled = true;
        }

        if (startButton) {
            startButton.disabled = false;
            startButton.textContent = 'Iniciar escaneo';
        }

        if (showIdleMessage) {
            setStatus('Escaneo detenido. Puedes reanudarlo cuando estés listo.', 'muted');
        }
    };

    const scanFrame = () => {
        if (!scanning || !barcodeDetector || !video || !context) {
            return;
        }

        if (video.readyState !== HTMLMediaElement.HAVE_ENOUGH_DATA) {
            animationFrameId = requestAnimationFrame(scanFrame);
            return;
        }

        if (canvas.width !== video.videoWidth || canvas.height !== video.videoHeight) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
        }

        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        barcodeDetector
            .detect(canvas)
            .then((codes) => {
                if (!scanning) {
                    return;
                }

                const match = codes.find((code) => Boolean(code.rawValue));

                if (match && match.rawValue) {
                    stopScanner(false);
                    handleQrValue(match.rawValue);
                    return;
                }

                if (scanning) {
                    animationFrameId = requestAnimationFrame(scanFrame);
                }
            })
            .catch((error) => {
                console.debug('Error durante la detección del QR', error);

                if (scanning) {
                    animationFrameId = requestAnimationFrame(scanFrame);
                }
            });
    };

    const startScanner = async () => {
        if (!('mediaDevices' in navigator) || !navigator.mediaDevices?.getUserMedia) {
            showError('Este dispositivo no permite acceder a la cámara desde el navegador.');
            setStatus('Cámara no disponible', 'error');
            return;
        }

        if (!barcodeDetector) {
            showError('El navegador no soporta la lectura nativa de códigos QR.');
            setStatus('Lectura no soportada', 'error');
            return;
        }

        hideError();
        clearAgenda();

        if (startButton) {
            startButton.disabled = true;
            startButton.textContent = 'Escaneando…';
        }

        if (stopButton) {
            stopButton.disabled = false;
        }

        setStatus('Activando cámara…', 'loading');

        try {
            activeStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
                audio: false,
            });

            if (video) {
                video.srcObject = activeStream;
                video.playsInline = true;

                await video.play();

                video.classList.remove('hidden');
            }

            if (videoPlaceholder) {
                videoPlaceholder.classList.add('hidden');
            }

            scanning = true;
            setStatus('Buscando código QR…', 'active');
            animationFrameId = requestAnimationFrame(scanFrame);
        } catch (error) {
            console.error('No se pudo iniciar la cámara', error);
            showError('No se pudo acceder a la cámara. Verifica los permisos del navegador e inténtalo nuevamente.');
            setStatus('No fue posible iniciar la cámara', 'error');
            stopScanner(false);
            if (startButton) {
                startButton.disabled = false;
                startButton.textContent = 'Reintentar escaneo';
            }
        }
    };

    const isValidUuid = (value) =>
        /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(value);

    const fetchAgenda = async (publicId) => {
        hideError();
        setStatus('Consultando agenda…', 'loading');
        setBadge('Validando información', 'warning');

        try {
            const response = await axios.post('/api/obtener_agenda', { public_id: publicId });
            const agenda = response.data?.data;

            if (!agenda) {
                throw new Error('Respuesta inesperada del servicio de agenda.');
            }

            await renderAgenda(agenda);
            setStatus('Agenda obtenida correctamente', 'active');
            if (startButton) {
                startButton.textContent = 'Escanear nuevamente';
            }
        } catch (error) {
            console.error('Error al obtener la agenda', error);
            const apiMessage = error.response?.data?.error || error.response?.data?.message;
            const message = apiMessage || 'No se encontró información asociada al código leído.';
            showError(message);
            setStatus('No se pudo obtener la agenda', 'error');
            setBadge('Sin resultados', 'error');
        }
    };

    const handleQrValue = (rawValue) => {
        if (!rawValue) {
            setStatus('El código QR está vacío. Intenta nuevamente.', 'error');
            return;
        }

        const cleanedValue = rawValue.trim();

        if (!isValidUuid(cleanedValue)) {
            showError('El código escaneado no corresponde a una credencial válida.');
            setStatus('Código inválido detectado', 'error');
            return;
        }

        fetchAgenda(cleanedValue);
    };

    const initializeDetector = async () => {
        if (!('BarcodeDetector' in window)) {
            showError(
                'El navegador no soporta lectura nativa de códigos QR. Utiliza la versión más reciente de Chrome, Edge o Safari.',
            );
            setStatus('Funcionalidad no compatible', 'error');
            return false;
        }

        try {
            const supportedFormats = await window.BarcodeDetector.getSupportedFormats();

            if (!supportedFormats.includes('qr_code')) {
                showError('El dispositivo no soporta la lectura de códigos QR.');
                setStatus('Funcionalidad no compatible', 'error');
                return false;
            }

            barcodeDetector = new window.BarcodeDetector({ formats: ['qr_code'] });
            return true;
        } catch (error) {
            console.error('No se pudo inicializar el detector de códigos QR', error);
            showError('No se pudo preparar el lector de códigos QR en este dispositivo.');
            setStatus('Funcionalidad no disponible', 'error');
            return false;
        }
    };

    if (startButton) {
        startButton.addEventListener('click', () => {
            startScanner();
        });
    }

    if (stopButton) {
        stopButton.addEventListener('click', () => {
            stopScanner(true);
        });
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden && scanning) {
            stopScanner(false);
            setStatus('Escaneo en pausa. Reanúdalo cuando regreses.', 'muted');
        }
    });

    clearAgenda();
    setStatus('Escáner listo. Inicia el proceso cuando estés preparado.', 'muted');

    (async () => {
        const detectorDisponible = await initializeDetector();

        if (!detectorDisponible) {
            if (startButton) {
                startButton.disabled = true;
                startButton.textContent = 'No compatible';
                startButton.classList.add('cursor-not-allowed', 'opacity-60');
            }
            if (stopButton) {
                stopButton.disabled = true;
            }
            return;
        }

        try {
            await startScanner();
        } catch (error) {
            console.debug('El escaneo automático no se pudo iniciar', error);
        }
    })();
});
