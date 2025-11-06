<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Recepción | Agenda de salud</title>
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/agenda/recepcion.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900">
        <div class="min-h-screen pb-16">
            <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="mx-auto flex max-w-5xl flex-col gap-2 px-6 py-8 sm:px-10">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">Recepción clínica</p>
                    <h1 class="text-3xl font-semibold text-slate-900 sm:text-4xl">Lectura de agenda por código QR</h1>
                    <p class="max-w-3xl text-sm text-slate-600 sm:text-base">
                        Escanee el código QR presentado por la paciente para acceder a su ficha de agenda. Esta información
                        es de carácter informativo y está destinada exclusivamente al personal médico autorizado.
                    </p>
                </div>
            </header>

            <main class="mx-auto mt-10 flex max-w-5xl flex-col gap-10 px-6 sm:px-10 lg:flex-row">
                <noscript>
                    <div class="mx-auto w-full rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        Para utilizar el lector de códigos QR es necesario habilitar JavaScript en el navegador.
                    </div>
                </noscript>
                <section class="lg:w-5/12">
                    <div class="flex h-full flex-col gap-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Escáner de credencial</h2>
                            <p class="mt-1 text-sm text-slate-600">
                                Active la cámara del dispositivo y centre el código QR dentro del recuadro para iniciar la
                                verificación.
                            </p>
                        </div>

                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-900/90">
                            <video id="qr-video" class="hidden h-full w-full object-cover" autoplay muted playsinline></video>
                            <div
                                id="video-placeholder"
                                class="flex aspect-video h-full min-h-[220px] w-full items-center justify-center px-8 text-center text-sm font-medium text-slate-100"
                            >
                                La cámara se activará al iniciar el escaneo.
                            </div>
                            <div class="pointer-events-none absolute inset-4 rounded-3xl border-2 border-white/40"></div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                id="start-scan"
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                            >
                                Iniciar escaneo
                            </button>
                            <button
                                id="stop-scan"
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-400 disabled:cursor-not-allowed disabled:opacity-60"
                                disabled
                            >
                                Detener
                            </button>
                            <span id="scan-status" class="text-sm font-medium text-slate-500">Preparando escáner…</span>
                        </div>

                        <div
                            id="scan-error"
                            class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                        ></div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-xs text-amber-900">
                            <p class="font-semibold uppercase tracking-[0.24em] text-amber-700">Privacidad y uso clínico</p>
                            <p class="mt-2 leading-relaxed">
                                El acceso a esta vista está restringido a personal médico. La información mostrada no sustituye
                                la valoración clínica y deberá confirmarse durante la consulta presencial.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="flex-1">
                    <div class="flex h-full flex-col gap-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-2xl font-semibold text-slate-900">Resumen de la agenda</h2>
                                <p class="text-sm text-slate-600">
                                    Los datos se actualizarán automáticamente al validar el código QR proporcionado por la paciente.
                                </p>
                            </div>
                            <span id="scan-badge" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                                Pendiente de escanear
                            </span>
                        </div>

                        <dl id="agenda-details" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Public ID</dt>
                                <dd data-field="public_id" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nombre completo</dt>
                                <dd data-field="nombres_completo" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nacionalidad</dt>
                                <dd data-field="nacionalidad" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Identificación</dt>
                                <dd data-field="rut" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha de nacimiento</dt>
                                <dd data-field="fecha_nacimiento" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Origen</dt>
                                <dd data-field="es_originario" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Detalle origen</dt>
                                <dd data-field="descripcion_originario" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono principal</dt>
                                <dd data-field="telefono1" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teléfono alterno</dt>
                                <dd data-field="telefono2" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Correo electrónico</dt>
                                <dd data-field="correo_electronico" class="mt-1 break-all text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ocupación</dt>
                                <dd data-field="ocupacion" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Domicilio</dt>
                                <dd data-field="domicilio" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nivel educativo registrado</dt>
                                <dd data-field="escolaridad" class="mt-1 text-base font-medium text-slate-900">—</dd>
                            </div>
                        </dl>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                            <p class="font-semibold text-slate-700">Recomendaciones para el personal médico</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                <li>Verifique presencialmente la identidad de la paciente antes de proceder.</li>
                                <li>Confirme los datos de contacto y actualícelos en caso de discrepancias.</li>
                                <li>Utilice esta información como apoyo y regístrela en la historia clínica correspondiente.</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
