<script setup>

    import dashboard from '../../layouts/dashboard.vue';
    import SubNavbar from '../../components/SubNavbar.vue';
    import SearchSelect from '../../components/inputs/searchSelect.vue';
    import INPUT from '../../components/inputs/input.vue';
    import SELECT from '../../components/inputs/select.vue';
    import TEXTAREA from '../../components/inputs/textarea.vue';
    import Print from '../../components/buttons/Print.vue';
    import Tabs from '../../components/Tabs.vue';
    import Modal from '../../components/Modal.vue';
    import DeleteModal from '../../components/DeleteModal.vue';
    import DeleteButton from '../../components/buttons/DeleteButton.vue';
    import AdjustableText from '../../components/AdjustableText.vue';
    import fileInput from '../../components/inputs/fileInput.vue';
    import FormattedDate from '../../components/accounting/FormattedDate.vue';
    import { usePage, useForm, Link } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import { Caracteristics } from '../../../Composables/Caracteristics.js';

    const props = defineProps({
        historialAreas: {
            type: Object,
            default: () => ({ data: [] }),
        },
    });

    const { available_caracteristics, available_components, types_available_values } = Caracteristics();

    const componentMap = {
        INPUT,
        SELECT,
    };

    const links = [
        { route: 'equipos.index', name: 'Equipos', active: true },
    ];

    const equipo = ref(usePage().props.equipo);

    const initialCaracteristicas = {};
    (equipo.value?.caracteristicas || []).forEach((caracteristica) => {
        initialCaracteristicas[caracteristica.nombre] = caracteristica.valor;
    });

    const initialComponentes = {};
    (equipo.value?.componentes || []).forEach((componente) => {
        initialComponentes[componente.nombre] = {};

        (componente.caracteristicas || []).forEach((caracteristica) => {
            initialComponentes[componente.nombre][caracteristica.nombre] = caracteristica.valor;
        });
    });

    const form = useForm({
        id_usuario: equipo.value?.id_usuario ?? null,
        id_area: equipo.value?.id_area ?? null,
        nombre: equipo.value?.nombre ?? null,
        marca: initialCaracteristicas.marca ?? null,
        modelo: initialCaracteristicas.modelo ?? null,
        num_serie: initialCaracteristicas.num_serie ?? null,
        cod_inventario: equipo.value?.cod_inventario ?? null,
        estado_funcional: initialCaracteristicas.estado_funcional ?? null,
        estado: equipo.value?.estado ?? null,
        tipo: equipo.value?.tipo ?? null,
        caracteristicas: initialCaracteristicas,
        componentes: initialComponentes,
    });

    function getComponentGroup(groupKey) {
        if (!form.componentes[groupKey]) {
            form.componentes[groupKey] = {};
        }

        return form.componentes[groupKey];
    }

    function submitForm() {
        form.put(route('equipos.update', { equipo: equipo.value.id }), {
            onSuccess: (response) => {
                equipo.value = response.props.equipo;
            },
        });
    }

    watch(() => form.tipo, (newTipo) => {
        if (newTipo) {
            form.caracteristicas = {};
            form.componentes = {};
        }
    });

    function update(response) {
        equipo.value = response.props.equipo;
    }

    //añadir comentario
    const modalAddCommentRef = ref(null);
    const deleteModalRef = ref(null);

    const formAddComment = useForm({
        comentario: '',
        equipo_id: equipo.value?.id ?? null,
    });

    function submitAddCommentForm() {
        formAddComment.post(route('equipos.addComment'), {
            onSuccess: (response) => {
                equipo.value = response.props.equipo;
                modalAddCommentRef.value.closeModal();
            },
        });
    }

    //añadir archivo
    const modalAddFileRef = ref(null);

    const formAddFile = useForm({
        nombre: '',
        archivo: null,
        equipo_id: equipo.value?.id ?? null,
    });

    function submitAddFileForm(){
        formAddFile.post(route('equipos.addFile'), {
            onSuccess: (response) => {
                equipo.value = response.props.equipo;
                modalAddFileRef.value.closeModal();
            },
        });
    }

    //añadir mantenimiento
    const modalAddMaintenanceRef = ref(null);

    const formAddMaintenance = useForm({
        fecha: new Date().toISOString().slice(0, 16),
        tipo: 'preventivo',
        id_encargado: null,
        observacion: '',
        equipo_id: equipo.value?.id ?? null,
    });

    function submitAddMaintenanceForm() {
        formAddMaintenance.post(route('equipos.addMaintenance'), {
            onSuccess: (response) => {
                equipo.value = response.props.equipo;
                formAddMaintenance.reset('observacion', 'id_encargado');
                formAddMaintenance.tipo = 'preventivo';
                formAddMaintenance.fecha = new Date().toISOString().slice(0, 16);
                modalAddMaintenanceRef.value.closeModal();
            },
        });
    }

    //añadir periferico
    const modalAddPeripheralRef = ref(null);
    const selectedPeripheralForImage = ref(null);
    const showImagePreviewModal = ref(false);

    const formAddPeripheral = useForm({
        tipo: 'teclado',
        marca: '',
        modelo: '',
        num_serie: '',
        cod_inventario: '',
        estado: 'operativo',
        observacion: '',
        imagen: null,
        equipo_id: equipo.value?.id ?? null,
    });

    const peripheralTypes = {
        'teclado': 'Teclado',
        'mouse': 'Mouse / Ratón',
        'parlantes': 'Parlantes / Altavoces',
        'auriculares': 'Auriculares / Diadema (Headset)',
        'webcam': 'Cámara Web / Webcam',
        'microfono': 'Micrófono',
        'monitor_secundario': 'Monitor Secundario / Adicional',
        'lector_codigo': 'Lector de Código de Barras / QR',
        'impresora_usb': 'Impresora Local USB',
        'ups_dedicada': 'UPS / Regulador Dedicado',
        'otro': 'Otro Periférico',
    };

    function submitAddPeripheralForm() {
        formAddPeripheral.post(route('equipos.addPeripheral'), {
            onSuccess: (response) => {
                equipo.value = response.props.equipo;
                formAddPeripheral.reset('marca', 'modelo', 'num_serie', 'cod_inventario', 'observacion');
                formAddPeripheral.tipo = 'teclado';
                formAddPeripheral.estado = 'operativo';
                formAddPeripheral.imagen = null;
                modalAddPeripheralRef.value.closeModal();
            },
        });
    }

    function openPeripheralImage(periferico) {
        selectedPeripheralForImage.value = periferico;
        showImagePreviewModal.value = true;
    }

    //editar periferico
    const modalEditPeripheralRef = ref(null);

    const formEditPeripheral = useForm({
        id: null,
        tipo: 'teclado',
        marca: '',
        modelo: '',
        num_serie: '',
        cod_inventario: '',
        estado: 'operativo',
        observacion: '',
        imagen: null,
        remove_imagen: false,
        existing_imagen: null,
    });

    function openEditPeripheralModal(periferico) {
        formEditPeripheral.id = periferico.id;
        formEditPeripheral.tipo = periferico.tipo;
        formEditPeripheral.marca = periferico.marca ?? '';
        formEditPeripheral.modelo = periferico.modelo ?? '';
        formEditPeripheral.num_serie = periferico.num_serie ?? '';
        formEditPeripheral.cod_inventario = periferico.cod_inventario ?? '';
        formEditPeripheral.estado = periferico.estado ?? 'operativo';
        formEditPeripheral.observacion = periferico.observacion ?? '';
        formEditPeripheral.imagen = null;
        formEditPeripheral.remove_imagen = false;
        formEditPeripheral.existing_imagen = periferico.imagen ?? null;
        modalEditPeripheralRef.value.openModal();
    }

    function submitEditPeripheralForm() {
        formEditPeripheral.post(route('equipos.updatePeripheral', { periferico: formEditPeripheral.id }), {
            onSuccess: (response) => {
                equipo.value = response.props.equipo;
                modalEditPeripheralRef.value.closeModal();
            },
        });
    }

    // Préstamos entre áreas
    const modalAddLoanRef = ref(null);
    const modalReturnLoanRef = ref(null);
    const selectedLoan = ref(null);

    const formAddLoan = useForm({
        equipo_id: equipo.value?.id ?? null,
        id_area_destino: null,
        id_usuario_responsable: null,
        fecha_prestamo: new Date().toISOString().split('T')[0],
        fecha_devolucion_estimada: '',
        motivo: '',
    });

    function openAddLoanModal() {
        formAddLoan.reset();
        formAddLoan.equipo_id = equipo.value?.id ?? null;
        formAddLoan.fecha_prestamo = new Date().toISOString().split('T')[0];
        formAddLoan.fecha_devolucion_estimada = '';
        formAddLoan.motivo = '';
        modalAddLoanRef.value.openModal();
    }

    function submitAddLoanForm() {
        formAddLoan.post(route('equipos.addLoan'), {
            onSuccess: (response) => {
                equipo.value = response.props.equipo;
                modalAddLoanRef.value.closeModal();
                formAddLoan.reset();
            },
        });
    }

    const formReturnLoan = useForm({
        fecha_devolucion_real: new Date().toISOString().split('T')[0],
        observacion_devolucion: '',
    });

    function openReturnLoanModal(prestamo) {
        selectedLoan.value = prestamo || equipo.value?.prestamo_activo;
        formReturnLoan.reset();
        formReturnLoan.fecha_devolucion_real = new Date().toISOString().split('T')[0];
        formReturnLoan.observacion_devolucion = '';
        modalReturnLoanRef.value.openModal();
    }

    function submitReturnLoanForm() {
        if (!selectedLoan.value) return;
        formReturnLoan.post(route('equipos.returnLoan', { prestamo: selectedLoan.value.id }), {
            onSuccess: (response) => {
                equipo.value = response.props.equipo;
                modalReturnLoanRef.value.closeModal();
                formReturnLoan.reset();
                selectedLoan.value = null;
            },
        });
    }
</script>

<template>
    <dashboard :appName="'inventory_app'">
        <SubNavbar :links="links" />

        <div class="container mx-auto px-4">
            <!-- Banner destacado si el equipo se encuentra actualmente en calidad de préstamo -->
            <div 
                v-if="equipo.prestamo_activo" 
                class="mb-5 p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/60 border-l-4 border-l-amber-500 text-amber-900 dark:text-amber-200 rounded-r-lg shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4 transition-colors"
            >
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-300 rounded-lg text-2xl">
                        <i class='bx bx-transfer-alt'></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h5 class="font-bold text-sm md:text-base text-amber-900 dark:text-amber-100">
                                Equipo en Calidad de Préstamo
                            </h5>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-200 text-amber-900 dark:bg-amber-800 dark:text-amber-100 uppercase tracking-wide">
                                Préstamo Activo
                            </span>
                        </div>
                        <p class="text-xs md:text-sm mt-1 text-amber-800 dark:text-amber-200/90 leading-relaxed">
                            <span>Área Propietaria: <strong>{{ equipo.area?.nombre || 'Sin área asignada' }}</strong></span>
                            <span class="mx-2">&bull;</span>
                            <span>Prestado actualmente a: <strong>{{ equipo.prestamo_activo.area_destino?.nombre || 'N/A' }}</strong></span>
                            <span v-if="equipo.prestamo_activo.responsable" class="block sm:inline sm:ml-2">
                                &bull; Responsable: <strong>{{ equipo.prestamo_activo.responsable.name }}</strong>
                            </span>
                            <span v-if="equipo.prestamo_activo.fecha_prestamo" class="block sm:inline sm:ml-2">
                                (Desde: {{ equipo.prestamo_activo.fecha_prestamo.split(' ')[0] }})
                            </span>
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs md:text-sm font-semibold rounded-lg shadow-sm transition-colors whitespace-nowrap self-start md:self-auto"
                    @click="openReturnLoanModal(equipo.prestamo_activo)"
                >
                    <i class='bx bx-check-circle text-lg'></i>
                    <span>Registrar Devolución</span>
                </button>
            </div>

            <Tabs :tabs="[
                { id: 'editar', label: 'Editar' },
                { id: 'prestamos', label: 'Préstamos' },
                { id: 'historial_areas', label: 'Historial de Áreas' },
                { id: 'perifericos', label: 'Periféricos' },
                { id: 'comentarios', label: 'Comentarios' },
                { id: 'archivos', label: 'Archivos' },
                { id: 'mantenimientos', label: 'Mantenimientos' },
            ]" :storageKey="'equipo-tabs_' + equipo.id">
                <template #editar>
                    <div class="w-full">
                        <div class="flex justify-between items-center mb-4">
                            <h4>Editar Equipo</h4>
                            <Print 
                                :view-name="'equipos.print.show'" 
                                :title="equipo.nombre ?? equipo.cod_inventario" 
                                :page-properties="{
                                    'pagedjs': true, 
                                    'pagecounter': true, 
                                    'openInNewTab': true, 
                                    'loadBootstrap': true,
                                    'margin': '15px',
                                }" 
                                :params="{equipo_id: equipo.id}"/>
                        </div>
                        <hr class="mb-4">
                        <form class="space-y-4 p-3 shadow rounded" method="POST" autocomplete="off" @submit.prevent="submitForm">
                            <div class="flex flex-wrap -mx-2">
                                <div class="w-full md:w-1/2 mt-3 px-2">
                                    <SearchSelect
                                        name="Usuario"
                                        input-name="id_usuario"
                                        :model="'User'"
                                        :required="false"
                                        select_name="id_usuario"
                                        :default-value="equipo.usuario ? { id: equipo.usuario.id, name: equipo.usuario.name } : null"
                                        v-model:select_value="form.id_usuario"
                                    />
                                </div>
                                <div class="w-full md:w-1/2 mt-3 px-2">
                                    <SearchSelect
                                        name="Área"
                                        input-name="id_area"
                                        :model="'Area'"
                                        :required="false"
                                        select_name="id_area"
                                        :default-value="equipo.area ? { id: equipo.area.id, name: equipo.area.nombre } : null"
                                        v-model:select_value="form.id_area"
                                    />
                                </div>
                                <INPUT :field="{
                                    id: 'nombre',
                                    label: 'Nombre',
                                    required: false,
                                    type: 'text',
                                    name: 'nombre',
                                    readonly: false,
                                }" v-model:value="form.nombre"/>
                                <INPUT :field="{
                                    id: 'marca',
                                    label: 'Marca',
                                    required: false,
                                    type: 'text',
                                    name: 'marca',
                                    readonly: false,
                                }" v-model:value="form.marca"/>
                                <INPUT :field="{
                                    id: 'modelo',
                                    label: 'Modelo',
                                    required: false,
                                    type: 'text',
                                    name: 'modelo',
                                    readonly: false,
                                }" v-model:value="form.modelo"/>
                                <INPUT :field="{
                                    id: 'num_serie',
                                    label: 'Número de Serie',
                                    required: false,
                                    type: 'text',
                                    name: 'num_serie',
                                    readonly: false,
                                }" v-model:value="form.num_serie"/>
                                <INPUT :field="{
                                    id: 'cod_inventario',
                                    label: 'Código de Inventario',
                                    required: false,
                                    type: 'text',
                                    name: 'cod_inventario',
                                    readonly: false,
                                }" v-model:value="form.cod_inventario"/>
                                <SELECT :field="{
                                    id: 'estado_funcional',
                                    label: 'Estado Funcional',
                                    required: true,
                                    name: 'estado_funcional',
                                    readonly: false,
                                    options: {
                                        'funcional': 'Funcional',
                                        'no_funcional': 'No Funcional',
                                    },
                                }" v-model:value="form.estado_funcional"/>
                                <SELECT :field="{
                                    id: 'estado',
                                    label: 'Estado',
                                    required: true,
                                    name: 'estado',
                                    readonly: false,
                                    options: {
                                        'activo': 'Activo',
                                        'inactivo': 'Inactivo',
                                        'mantenimiento': 'Mantenimiento',
                                    },
                                }" v-model:value="form.estado"/>
                                <SELECT :field="{
                                    id: 'tipo',
                                    label: 'Tipo',
                                    required: true,
                                    name: 'tipo',
                                    readonly: false,
                                    options: {
                                        'cpu': 'CPU',
                                        'monitor': 'MONITOR',
                                        'ups': 'BATERIAS UPS / ESTABILIZADOR',
                                        'switches': 'SWITCHES',
                                        'aps': 'CONEXIÓN INALÁMBRICA (AP´S)',
                                        'telefonia_ip': 'TELEFONIA IP',
                                        'impresoras': 'IMPRESORA, FOTOCOPIADORA (IMPRESORAS MATRICIALES)',
                                        'reloj_biometrico': 'RELOJ BIOMETRICO',
                                        'data_show': 'DATA SHOW',
                                        'pantalla_proyector': 'PANTALLA DE PROYECCIÓN',
                                        'laptops': 'LAPTOPS',
                                    },
                                }" v-model:value="form.tipo"/>
                                <template v-for="(caracteristica, key) in available_caracteristics" :key="key">
                                    <component
                                        :is="componentMap[caracteristica.component]"
                                        :field="{
                                            id: caracteristica.name,
                                            label: caracteristica.label,
                                            required: false,
                                            type: caracteristica.type || 'text',
                                            name: caracteristica.name,
                                            readonly: false,
                                            options: caracteristica.options || null,
                                        }"
                                        v-model:value="form.caracteristicas[key]"
                                        v-if="form.tipo && types_available_values[form.tipo] && types_available_values[form.tipo].characteristics.includes(key)"
                                    />
                                </template>
                                <template v-for="(componente, key) in available_components" :key="key">
                                    <template v-if="form.tipo && types_available_values[form.tipo] && types_available_values[form.tipo].components.includes(key)">
                                        <div class="w-full mt-3 px-2">
                                            <h5>{{ componente.label }}</h5>
                                        </div>
                                        <template v-for="(comp, k) in componente.options" :key="comp.name">
                                            <component
                                                :is="componentMap[comp.component]"
                                                :field="{
                                                    id: comp.name,
                                                    label: comp.label,
                                                    required: false,
                                                    type: comp.type || 'text',
                                                    name: comp.name,
                                                    readonly: false,
                                                    options: comp.options || null,
                                                }"
                                                v-model:value="getComponentGroup(key)[comp.name]"
                                            />
                                        </template>
                                    </template>
                                </template>
                            </div>
                            <div class="px-3 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700">
                                <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                    Aceptar
                                </button>
                            </div>
                        </form>
                    </div>
                </template>

                <template #prestamos>
                    <div class="w-full">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                            <div>
                                <h4>Préstamos entre Áreas</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Área propietaria actual: <strong class="text-gray-800 dark:text-gray-200">{{ equipo.area?.nombre || 'Sin área asignada' }}</strong>
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button 
                                    v-if="!equipo.prestamo_activo"
                                    type="button" 
                                    class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs md:text-sm py-2 px-3.5 rounded-lg transition-colors inline-flex items-center gap-1.5 shadow-sm font-medium" 
                                    @click="openAddLoanModal"
                                    aria-label="Registrar Préstamo"
                                >
                                    <i class='bx bx-plus text-base'></i>
                                    <span>Prestar Equipo</span>
                                </button>
                                <button 
                                    v-else
                                    type="button" 
                                    class="bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs md:text-sm py-2 px-3.5 rounded-lg transition-colors inline-flex items-center gap-1.5 shadow-sm font-medium" 
                                    @click="openReturnLoanModal(equipo.prestamo_activo)"
                                    aria-label="Registrar Devolución"
                                >
                                    <i class='bx bx-check-circle text-base'></i>
                                    <span>Registrar Devolución</span>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-blue-100 dark:bg-gray-900">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">
                                            Estado
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">
                                            Área Origen (Dueña)
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">
                                            Área Destino (Préstamo)
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">
                                            Responsable Receptor
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">
                                            Fecha Préstamo
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">
                                            Fecha Devolución
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">
                                            Motivo / Observación
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 transition-colors">
                                    <tr v-if="!equipo.prestamos || equipo.prestamos.length === 0">
                                        <td colspan="8" class="px-6 py-6 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                                            No se han registrado préstamos de este equipo hacia otras áreas.
                                        </td>
                                    </tr>
                                    <tr 
                                        v-for="prestamo in equipo.prestamos" 
                                        :key="prestamo.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700/60 even:bg-gray-50/50 dark:even:bg-gray-800/50 transition-colors"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                v-if="prestamo.estado === 'activo'" 
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-700"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                En Préstamo
                                            </span>
                                            <span 
                                                v-else 
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700"
                                            >
                                                <i class='bx bx-check'></i>
                                                Devuelto
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-100 font-medium">
                                            {{ prestamo.area_origen?.nombre || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-blue-600 dark:text-blue-400 font-semibold">
                                            {{ prestamo.area_destino?.nombre || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 text-xs">
                                            {{ prestamo.responsable?.name || 'No especificado' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                            <FormattedDate v-if="prestamo.fecha_prestamo" :date="prestamo.fecha_prestamo.split(' ')[0]" />
                                            <span v-else>N/A</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 text-xs">
                                            <div v-if="prestamo.fecha_devolucion_real">
                                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">Devuelto:</span>
                                                <FormattedDate :date="prestamo.fecha_devolucion_real.split(' ')[0]" class="ml-1" />
                                            </div>
                                            <div v-else-if="prestamo.fecha_devolucion_estimada">
                                                <span class="text-gray-500">Previsto:</span>
                                                <FormattedDate :date="prestamo.fecha_devolucion_estimada.split(' ')[0]" class="ml-1" />
                                            </div>
                                            <div v-else>
                                                <span class="text-amber-600 dark:text-amber-400 font-medium">Sin fecha límite</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-700 dark:text-gray-300 max-w-xs">
                                            <p class="font-medium text-gray-900 dark:text-gray-100 mb-0.5">
                                                <strong>Motivo:</strong>
                                                <AdjustableText :text="prestamo.motivo" :max-length="90" :allow-expand="true" />
                                            </p>
                                            <p v-if="prestamo.observacion_devolucion" class="text-emerald-700 dark:text-emerald-300 italic">
                                                <strong>Retorno:</strong>
                                                <AdjustableText :text="prestamo.observacion_devolucion" :max-length="90" :allow-expand="true" />
                                            </p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                                            <button 
                                                v-if="prestamo.estado === 'activo'"
                                                type="button" 
                                                class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-md transition-colors inline-flex items-center gap-1 font-medium shadow-xs"
                                                @click="openReturnLoanModal(prestamo)"
                                                title="Registrar retorno del equipo"
                                            >
                                                <i class='bx bx-check-circle text-base'></i>
                                                <span>Devolver</span>
                                            </button>
                                            <span v-else class="text-gray-400 italic text-[11px]">
                                                Cerrado
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <template #historial_areas>
                    <div class="w-full">
                        <div class="flex justify-between items-center mb-4">
                            <h4>Historial de Cambio de Áreas</h4>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-blue-100 dark:bg-gray-900">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">
                                            Área Asignada
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">
                                            Fecha de Asignación
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">
                                            Fecha de Desasignación
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">
                                            Observación / Motivo
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wider whitespace-nowrap transition-colors">
                                            Registrado Por
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 transition-colors">
                                    <tr v-if="!historialAreas?.data || historialAreas.data.length === 0">
                                        <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                                            No se han registrado cambios de área para este equipo.
                                        </td>
                                    </tr>
                                    <tr v-for="historial in (historialAreas?.data || [])" :key="historial.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 even:bg-gray-50/50 dark:even:bg-gray-800/50">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100 transition-colors">
                                            {{ historial.area?.nombre || 'Área no encontrada' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 transition-colors">
                                            <FormattedDate v-if="historial.fecha_asignacion" :date="historial.fecha_asignacion.split(' ')[0]" />
                                            <span v-else>N/A</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300 transition-colors">
                                            <FormattedDate v-if="historial.fecha_fin" :date="historial.fecha_fin.split(' ')[0]" />
                                            <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                                Actual / Vigente
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            <AdjustableText :text="historial.observacion" :max-length="120" :allow-expand="true" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            {{ historial.creator?.name || 'Sistema' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <!-- Paginación Simple -->
                            <div v-if="historialAreas && (historialAreas.prev_page_url || historialAreas.next_page_url)" class="m-2 flex justify-center sm:justify-start">
                                <nav aria-label="Navegación de páginas">
                                    <ul class="inline-flex -space-x-px rounded-md shadow-sm select-none text-sm">
                                        <li>
                                            <Link
                                                v-if="historialAreas.prev_page_url" :href="historialAreas.prev_page_url"
                                                preserve-scroll preserve-state :only="['historialAreas']" replace
                                                class="inline-flex items-center px-4 py-2 text-sm font-medium border rounded-l-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                                                >
                                                Anterior
                                            </Link>
                                            <button
                                                v-else
                                                class="inline-flex items-center px-4 py-2 text-sm font-medium border rounded-l-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors opacity-50 cursor-not-allowed" 
                                                >
                                                Anterior
                                            </button>
                                        </li>
                                        <li>
                                            <Link 
                                                v-if="historialAreas.next_page_url" :href="historialAreas.next_page_url"
                                                preserve-scroll preserve-state :only="['historialAreas']" replace
                                                class="inline-flex items-center px-4 py-2 text-sm font-medium border border-l-0 rounded-r-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                                >
                                                Siguiente
                                            </Link>
                                            <button 
                                                v-else
                                                class="inline-flex items-center px-4 py-2 text-sm font-medium border border-l-0 rounded-r-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors opacity-50 cursor-not-allowed"
                                                >
                                                Siguiente
                                            </button>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </template>

                <template #perifericos>
                    <div class="w-full">
                        <div class="flex justify-between items-center mb-4">
                            <h4>Periféricos Conectados</h4>

                            <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors" 
                                    @click="modalAddPeripheralRef.openModal()"
                                    aria-label="Registrar Periférico"
                                >
                                    <i class='bx bx-plus'></i>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th style="width: 170px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                                        <th style="width: 200px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Marca & Modelo</th>
                                        <th style="width: 170px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">N° Serie / Cód. Inv.</th>
                                        <th style="width: 120px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Observaciones</th>
                                        <th style="width: 90px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-if="!equipo.perifericos || equipo.perifericos.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                                            No hay periféricos registrados para este equipo.
                                        </td>
                                    </tr>
                                    <tr v-for="(periferico, index) in equipo.perifericos" :key="index">
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            <div class="flex items-center gap-3">
                                                <div v-if="periferico.imagen" class="relative group cursor-pointer flex-shrink-0" @click="openPeripheralImage(periferico)">
                                                    <img 
                                                        :src="'/storage/' + periferico.imagen" 
                                                        :alt="periferico.tipo" 
                                                        class="w-10 h-10 object-cover rounded-lg border border-gray-200 dark:border-gray-700 shadow-xs hover:opacity-80 transition-opacity"
                                                    />
                                                    <div class="absolute inset-0 bg-black/30 rounded-lg opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white">
                                                        <i class='bx bx-zoom-in text-sm'></i>
                                                    </div>
                                                </div>
                                                <div v-else class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0 border border-gray-200 dark:border-gray-600">
                                                    <i v-if="periferico.tipo === 'teclado'" class='bx bx-keyboard text-xl text-blue-500'></i>
                                                    <i v-else-if="periferico.tipo === 'mouse'" class='bx bx-mouse text-xl text-indigo-500'></i>
                                                    <i v-else-if="periferico.tipo === 'parlantes'" class='bx bx-volume-full text-xl text-amber-500'></i>
                                                    <i v-else-if="periferico.tipo === 'auriculares'" class='bx bx-headphone text-xl text-purple-500'></i>
                                                    <i v-else-if="periferico.tipo === 'webcam'" class='bx bx-webcam text-xl text-rose-500'></i>
                                                    <i v-else-if="periferico.tipo === 'microfono'" class='bx bx-microphone text-xl text-red-500'></i>
                                                    <i v-else-if="periferico.tipo === 'monitor_secundario'" class='bx bx-desktop text-xl text-cyan-500'></i>
                                                    <i v-else-if="periferico.tipo === 'lector_codigo'" class='bx bx-barcode text-xl text-emerald-500'></i>
                                                    <i v-else-if="periferico.tipo === 'impresora_usb'" class='bx bx-printer text-xl text-teal-500'></i>
                                                    <i v-else class='bx bx-devices text-xl text-gray-500'></i>
                                                </div>
                                                <div>
                                                    <div class="font-bold">{{ peripheralTypes[periferico.tipo] || periferico.tipo }}</div>
                                                    <span v-if="periferico.imagen" class="text-[10px] text-blue-600 dark:text-blue-400 flex items-center gap-0.5 cursor-pointer hover:underline" @click="openPeripheralImage(periferico)">
                                                        <i class='bx bx-image'></i> Ver foto
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ periferico.marca || 'Marca no especificada' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ periferico.modelo || 'Sin modelo' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-700 dark:text-gray-300">
                                            <div v-if="periferico.num_serie"><strong>S/N:</strong> {{ periferico.num_serie }}</div>
                                            <div v-if="periferico.cod_inventario" class="text-blue-600 dark:text-blue-400 font-semibold"><strong>Inv:</strong> {{ periferico.cod_inventario }}</div>
                                            <div v-if="!periferico.num_serie && !periferico.cod_inventario" class="text-gray-400 italic">S/R</div>
                                        </td>
                                        <td class="px-6 py-4 text-xs">
                                            <span v-if="periferico.estado === 'operativo'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                                Operativo
                                            </span>
                                            <span v-else-if="periferico.estado === 'en_revision'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                                                En Revisión
                                            </span>
                                            <span v-else-if="periferico.estado === 'danado'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-200 border border-rose-300 dark:border-rose-700">
                                                Dañado
                                            </span>
                                            <span v-else-if="periferico.estado === 'obsoleto'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600">
                                                Obsoleto
                                            </span>
                                            <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600">
                                                {{ periferico.estado }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            <AdjustableText :text="periferico.observacion" :max-length="120" :allow-expand="true" />
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <button 
                                                    type="button" 
                                                    class="min-w-[35px] h-[35px] inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded transition-colors text-sm px-3 py-1.5 inline-flex items-center rounded bg-blue-100 dark:bg-blue-700 text-blue-700 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-600 transition-colors" 
                                                    @click="openEditPeripheralModal(periferico)"
                                                    title="Editar periférico"
                                                    aria-label="Editar periférico"
                                                >
                                                    <i class='bx bx-edit text-lg'></i>
                                                </button>
                                                <DeleteButton 
                                                    :url="route('equipos.deletePeripheral', { periferico: periferico.id })" 
                                                    :deleteModal="deleteModalRef"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <template #comentarios>
                    <div class="w-full">
                        <div class="flex justify-between items-center mb-4">
                            <h4>Comentarios</h4>

                            <div class="flex items-center gap-2">
                                <Print 
                                    :view-name="'equipos.print.comments'" 
                                    :title="'Comentarios - ' + (equipo.nombre ?? equipo.cod_inventario)" 
                                    :page-properties="{
                                        'pagedjs': true, 
                                        'pagecounter': true, 
                                        'openInNewTab': true, 
                                        'loadBootstrap': true,
                                        'margin': '15px',
                                    }" 
                                    :params="{ equipo_id: equipo.id }"
                                />

                                <button 
                                    type="button" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors" 
                                    @click="modalAddCommentRef.openModal()"
                                    aria-label="Agregar Comentario"
                                >
                                    <i class='bx bx-plus'></i>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Comentario</th>
                                        <th style="width: 180px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                        <th style="width: 100px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(comentario, index) in equipo.comentarios" :key="index">
                                        <td class="px-6 py-4">
                                            <AdjustableText :text="comentario.comentario" :max-length="100" :allow-expand="true" />
                                        </td>
                                        <td class="px-6 py-4">{{ new Date(comentario.updated_at).toLocaleString('es-Ni',{ year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: '2-digit', }) }}</td>
                                        <td class="px-6 py-4">
                                            <DeleteButton 
                                                :url="route('equipos.deleteComment', { comentario: comentario.id })" 
                                                :deleteModal="deleteModalRef"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <template #archivos>
                    <div class="w-full">
                        <div class="flex justify-between items-center mb-4">
                            <h4>Archivos</h4>

                            <div class="flex items-center gap-2">
                                <Print 
                                    :view-name="'equipos.print.files'" 
                                    :title="'Archivos - ' + (equipo.nombre ?? equipo.cod_inventario)" 
                                    :page-properties="{
                                        'pagedjs': true, 
                                        'pagecounter': true, 
                                        'openInNewTab': true, 
                                        'loadBootstrap': true,
                                        'margin': '15px',
                                    }" 
                                    :params="{ equipo_id: equipo.id }"
                                />

                                <button 
                                    type="button" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors" 
                                    @click="modalAddFileRef.openModal()"
                                    aria-label="Agregar Archivo"
                                >
                                    <i class='bx bx-plus'></i>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                                        <th style="width: 200px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(archivo, index) in equipo.archivos" :key="index">
                                        <td class="px-6 py-4">
                                            {{ archivo.nombre }}
                                        </td>
                                        <td class="px-6 py-4 gap-2 flex">
                                            <a 
                                                :href="route('equipos.downloadFile', { archivo: archivo.id })" 
                                                class="min-w-[35px] h-[35px] inline-flex items-center justify-center bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white rounded transition-colors"
                                                :title="'Descargar Archivo'"
                                            >
                                                <i class='bx bx-download'></i>
                                            </a>
                                            <DeleteButton 
                                                :url="route('equipos.deleteFile', { archivo: archivo.id })" 
                                                :deleteModal="deleteModalRef"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <template #mantenimientos>
                    <div class="w-full">
                        <div class="flex justify-between items-center mb-4">
                            <h4>Mantenimientos</h4>

                            <div class="flex items-center gap-2">
                                <Print 
                                    :view-name="'equipos.print.maintenances'" 
                                    :title="'Mantenimientos - ' + (equipo.nombre ?? equipo.cod_inventario)" 
                                    :page-properties="{
                                        'pagedjs': true, 
                                        'pagecounter': true, 
                                        'openInNewTab': true, 
                                        'loadBootstrap': true,
                                        'margin': '15px',
                                    }" 
                                    :params="{ equipo_id: equipo.id }"
                                />

                                <button 
                                    type="button" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded transition-colors" 
                                    @click="modalAddMaintenanceRef.openModal()"
                                    aria-label="Registrar Mantenimiento"
                                >
                                    <i class='bx bx-plus'></i>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th style="width: 160px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                        <th style="width: 130px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                                        <th style="width: 180px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Técnico Responsable</th>
                                        <th style="width: 140px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ticket Origen</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Detalle / Trabajo Realizado</th>
                                        <th style="width: 90px;" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-if="!equipo.mantenimientos || equipo.mantenimientos.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                                            No hay mantenimientos registrados para este equipo.
                                        </td>
                                    </tr>
                                    <tr v-for="(mantenimiento, index) in equipo.mantenimientos" :key="index">
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            {{ new Date(mantenimiento.fecha).toLocaleString('es-Ni', { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: '2-digit' }) }}
                                        </td>
                                        <td class="px-6 py-4 text-xs">
                                            <span v-if="mantenimiento.tipo === 'preventivo'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                                Preventivo
                                            </span>
                                            <span v-else-if="mantenimiento.tipo === 'correctivo'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                                                Correctivo
                                            </span>
                                            <span v-else-if="mantenimiento.tipo === 'predictivo'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-200 border border-blue-300 dark:border-blue-700">
                                                Predictivo
                                            </span>
                                            <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600">
                                                {{ mantenimiento.tipo ? (mantenimiento.tipo.charAt(0).toUpperCase() + mantenimiento.tipo.slice(1)) : 'Preventivo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ mantenimiento.encargado?.name ?? 'Sin asignar' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span v-if="mantenimiento.ticket" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200">
                                                #{{ mantenimiento.ticket.id }}
                                            </span>
                                            <span v-else class="text-xs text-gray-400 dark:text-gray-500 italic">
                                                Directo
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            <AdjustableText :text="mantenimiento.observacion" :max-length="120" :allow-expand="true" />
                                        </td>
                                        <td class="px-6 py-4">
                                            <DeleteButton 
                                                :url="route('equipos.deleteMaintenance', { mantenimiento: mantenimiento.id })" 
                                                :deleteModal="deleteModalRef"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </Tabs>
        </div>

        <Modal :title="'Añadir Comentario'" :id="'AddCommentModal'" ref="modalAddCommentRef" :accept-callback="submitAddCommentForm">
            <form id="AddCommentForm" @submit.prevent="" class="space-y-4 px-2 pb-2">
                <div class="flex flex-wrap -mx-2">
                    <TEXTAREA :field="{
                        id: 'comentario',
                        label: 'Comentario',
                        required: true,
                        type: 'text',
                        name: 'comentario',
                        readonly: false,
                    }" v-model:value="formAddComment.comentario" />
                </div>
            </form>
        </Modal>

        <Modal :title="'Añadir Archivo'" :id="'AddFileModal'" ref="modalAddFileRef" :accept-callback="submitAddFileForm">
            <form id="AddFileForm" @submit.prevent="" class="space-y-4 px-2 pb-2">
                <div class="flex flex-wrap -mx-2">
                    <INPUT :field="{
                        id: 'nombre',
                        label: 'Nombre del Archivo',
                        required: true,
                        type: 'text',
                        name: 'nombre',
                        readonly: false,
                    }" v-model:value="formAddFile.nombre" />
                </div>

                <fileInput :field="{
                    id: 'files',
                    name: 'files',
                    required: false,
                    readonly: false,
                    multiple: false,
                    accept: '*',
                }" v-model:file="formAddFile.archivo" />
            </form>
        </Modal>

        <Modal :title="'Registrar Mantenimiento'" :id="'AddMaintenanceModal'" ref="modalAddMaintenanceRef" :accept-callback="submitAddMaintenanceForm">
            <form id="AddMaintenanceForm" @submit.prevent="" class="space-y-4 px-2 pb-2">
                <div class="flex flex-wrap -mx-2">
                    <INPUT :field="{
                        id: 'fecha_mantenimiento',
                        label: 'Fecha y Hora',
                        required: true,
                        type: 'datetime-local',
                        name: 'fecha',
                        readonly: false,
                    }" v-model:value="formAddMaintenance.fecha" />

                    <SELECT :field="{
                        id: 'tipo_mantenimiento',
                        label: 'Tipo de Mantenimiento',
                        required: true,
                        name: 'tipo',
                        readonly: false,
                        options: {
                            'preventivo': 'Preventivo (Rutinario / Planificado)',
                            'correctivo': 'Correctivo (Falla / Reparación)',
                            'predictivo': 'Predictivo (Monitoreo / Diagnóstico)',
                            'otro': 'Otro',
                        },
                    }" v-model:value="formAddMaintenance.tipo" />

                    <div class="w-full md:w-1/2 mt-3 px-2">
                        <SearchSelect 
                            name="Técnico Responsable" 
                            input-name="id_encargado" 
                            :model="'User'" 
                            :required="false" 
                            select_name="id_encargado"
                            v-model:select_value="formAddMaintenance.id_encargado"
                        />
                    </div>

                    <TEXTAREA :field="{
                        id: 'observacion_mantenimiento',
                        label: 'Detalle / Trabajo Realizado',
                        required: true,
                        type: 'text',
                        name: 'observacion',
                        readonly: false,
                    }" v-model:value="formAddMaintenance.observacion" />
                </div>
            </form>
        </Modal>

        <Modal :title="'Añadir Periférico'" :id="'AddPeripheralModal'" ref="modalAddPeripheralRef" :accept-callback="submitAddPeripheralForm">
            <form id="AddPeripheralForm" @submit.prevent="" class="space-y-4 px-2 pb-2">
                <div class="flex flex-wrap -mx-2">
                    <SELECT :field="{
                        id: 'tipo_periferico',
                        label: 'Tipo de Periférico',
                        required: true,
                        name: 'tipo',
                        readonly: false,
                        options: {
                            'teclado': 'Teclado',
                            'mouse': 'Mouse / Ratón',
                            'parlantes': 'Parlantes / Altavoces',
                            'auriculares': 'Auriculares / Diadema (Headset)',
                            'webcam': 'Cámara Web / Webcam',
                            'microfono': 'Micrófono',
                            'monitor_secundario': 'Monitor Secundario / Adicional',
                            'lector_codigo': 'Lector de Código de Barras / QR',
                            'impresora_usb': 'Impresora Local USB',
                            'ups_dedicada': 'UPS / Regulador Dedicado',
                            'otro': 'Otro Periférico',
                        },
                    }" v-model:value="formAddPeripheral.tipo" />

                    <SELECT :field="{
                        id: 'estado_periferico',
                        label: 'Estado Funcional',
                        required: true,
                        name: 'estado',
                        readonly: false,
                        options: {
                            'operativo': 'Operativo / Buen Estado',
                            'en_revision': 'En Revisión / Diagnóstico',
                            'danado': 'Dañado / Con Fallas',
                            'obsoleto': 'Obsoleto / Descontinuado',
                        },
                    }" v-model:value="formAddPeripheral.estado" />

                    <INPUT :field="{
                        id: 'marca_periferico',
                        label: 'Marca',
                        required: false,
                        type: 'text',
                        name: 'marca',
                        placeholder: 'Ej: Logitech, Genius, HP, Dell...',
                        readonly: false,
                    }" v-model:value="formAddPeripheral.marca" />

                    <INPUT :field="{
                        id: 'modelo_periferico',
                        label: 'Modelo',
                        required: false,
                        type: 'text',
                        name: 'modelo',
                        placeholder: 'Ej: K120, G203, LifeCam HD-3000...',
                        readonly: false,
                    }" v-model:value="formAddPeripheral.modelo" />

                    <INPUT :field="{
                        id: 'num_serie_periferico',
                        label: 'Número de Serie (S/N)',
                        required: false,
                        type: 'text',
                        name: 'num_serie',
                        placeholder: 'Número de serie de fábrica',
                        readonly: false,
                    }" v-model:value="formAddPeripheral.num_serie" />

                    <INPUT :field="{
                        id: 'cod_inventario_periferico',
                        label: 'Código de Inventario / Activo',
                        required: false,
                        type: 'text',
                        name: 'cod_inventario',
                        placeholder: 'Código de etiqueta o placa de inventario',
                        readonly: false,
                    }" v-model:value="formAddPeripheral.cod_inventario" />

                    <TEXTAREA :field="{
                        id: 'observacion_periferico',
                        label: 'Observaciones / Detalles Técnicos',
                        required: false,
                        type: 'text',
                        name: 'observacion',
                        placeholder: 'Conector USB/Inalámbrico, longitud de cable, estado físico, etc.',
                        readonly: false,
                    }" v-model:value="formAddPeripheral.observacion" />

                    <div class="w-full px-2">
                        <fileInput :field="{
                            id: 'imagen_periferico',
                            label: 'Fotografía / Imagen del Periférico (Opcional)',
                            name: 'imagen',
                            required: false,
                            readonly: false,
                            multiple: false,
                            accept: 'image/*',
                        }" v-model:file="formAddPeripheral.imagen" />
                    </div>
                </div>
            </form>
        </Modal>

        <Modal :title="'Editar Periférico'" :id="'EditPeripheralModal'" ref="modalEditPeripheralRef" :accept-callback="submitEditPeripheralForm">
            <form id="EditPeripheralForm" @submit.prevent="" class="space-y-4 px-2 pb-2">
                <div class="flex flex-wrap -mx-2">
                    <SELECT :field="{
                        id: 'edit_tipo_periferico',
                        label: 'Tipo de Periférico',
                        required: true,
                        name: 'tipo',
                        readonly: false,
                        options: {
                            'teclado': 'Teclado',
                            'mouse': 'Mouse / Ratón',
                            'parlantes': 'Parlantes / Altavoces',
                            'auriculares': 'Auriculares / Diadema (Headset)',
                            'webcam': 'Cámara Web / Webcam',
                            'microfono': 'Micrófono',
                            'monitor_secundario': 'Monitor Secundario / Adicional',
                            'lector_codigo': 'Lector de Código de Barras / QR',
                            'impresora_usb': 'Impresora Local USB',
                            'ups_dedicada': 'UPS / Regulador Dedicado',
                            'otro': 'Otro Periférico',
                        },
                    }" v-model:value="formEditPeripheral.tipo" />

                    <SELECT :field="{
                        id: 'edit_estado_periferico',
                        label: 'Estado Funcional',
                        required: true,
                        name: 'estado',
                        readonly: false,
                        options: {
                            'operativo': 'Operativo / Buen Estado',
                            'en_revision': 'En Revisión / Diagnóstico',
                            'danado': 'Dañado / Con Fallas',
                            'obsoleto': 'Obsoleto / Descontinuado',
                        },
                    }" v-model:value="formEditPeripheral.estado" />

                    <INPUT :field="{
                        id: 'edit_marca_periferico',
                        label: 'Marca',
                        required: false,
                        type: 'text',
                        name: 'marca',
                        placeholder: 'Ej: Logitech, Genius, HP, Dell...',
                        readonly: false,
                    }" v-model:value="formEditPeripheral.marca" />

                    <INPUT :field="{
                        id: 'edit_modelo_periferico',
                        label: 'Modelo',
                        required: false,
                        type: 'text',
                        name: 'modelo',
                        placeholder: 'Ej: K120, G203, LifeCam HD-3000...',
                        readonly: false,
                    }" v-model:value="formEditPeripheral.modelo" />

                    <INPUT :field="{
                        id: 'edit_num_serie_periferico',
                        label: 'Número de Serie (S/N)',
                        required: false,
                        type: 'text',
                        name: 'num_serie',
                        placeholder: 'Número de serie de fábrica',
                        readonly: false,
                    }" v-model:value="formEditPeripheral.num_serie" />

                    <INPUT :field="{
                        id: 'edit_cod_inventario_periferico',
                        label: 'Código de Inventario / Activo',
                        required: false,
                        type: 'text',
                        name: 'cod_inventario',
                        placeholder: 'Código de etiqueta o placa de inventario',
                        readonly: false,
                    }" v-model:value="formEditPeripheral.cod_inventario" />

                    <TEXTAREA :field="{
                        id: 'edit_observacion_periferico',
                        label: 'Observaciones / Detalles Técnicos',
                        required: false,
                        type: 'text',
                        name: 'observacion',
                        placeholder: 'Conector USB/Inalámbrico, longitud de cable, estado físico, etc.',
                        readonly: false,
                    }" v-model:value="formEditPeripheral.observacion" />

                    <div v-if="formEditPeripheral.existing_imagen && !formEditPeripheral.remove_imagen" class="w-full px-2 mt-2">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img :src="'/storage/' + formEditPeripheral.existing_imagen" alt="Foto actual" class="w-12 h-12 object-cover rounded-md border" />
                                <div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">Foto actual adjunta</p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Puedes cargar una nueva abajo para reemplazarla o marcar la casilla para quitarla.</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-1.5 text-xs text-rose-600 dark:text-rose-400 cursor-pointer font-medium hover:underline">
                                <input type="checkbox" v-model="formEditPeripheral.remove_imagen" class="rounded text-rose-600 focus:ring-rose-500" />
                                Quitar foto
                            </label>
                        </div>
                    </div>

                    <div class="w-full px-2">
                        <fileInput :field="{
                            id: 'edit_imagen_periferico',
                            label: formEditPeripheral.existing_imagen ? 'Reemplazar Fotografía / Imagen (Opcional)' : 'Fotografía / Imagen del Periférico (Opcional)',
                            name: 'imagen',
                            required: false,
                            readonly: false,
                            multiple: false,
                            accept: 'image/*',
                        }" v-model:file="formEditPeripheral.imagen" />
                    </div>
                </div>
            </form>
        </Modal>

        <!-- Visor Modal para ver imagen de periférico en tamaño completo -->
        <div 
            v-if="showImagePreviewModal && selectedPeripheralForImage" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-xs p-4"
            @click.self="showImagePreviewModal = false"
        >
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl max-w-2xl w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800/80">
                    <div class="flex items-center gap-2">
                        <i class='bx bx-image text-blue-500 text-xl'></i>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                {{ peripheralTypes[selectedPeripheralForImage.tipo] || selectedPeripheralForImage.tipo }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ selectedPeripheralForImage.marca || '' }} {{ selectedPeripheralForImage.modelo || '' }} 
                                <span v-if="selectedPeripheralForImage.num_serie">(S/N: {{ selectedPeripheralForImage.num_serie }})</span>
                            </p>
                        </div>
                    </div>
                    <button 
                        @click="showImagePreviewModal = false" 
                        class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        aria-label="Cerrar"
                    >
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>
                <div class="p-4 flex justify-center items-center bg-gray-100/50 dark:bg-gray-900/60 min-h-[250px]">
                    <img 
                        :src="'/storage/' + selectedPeripheralForImage.imagen" 
                        :alt="selectedPeripheralForImage.tipo" 
                        class="max-h-[70vh] w-auto max-w-full object-contain rounded-lg shadow-md"
                    />
                </div>
                <div v-if="selectedPeripheralForImage.observacion" class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300">
                    <strong>Observación:</strong> {{ selectedPeripheralForImage.observacion }}
                </div>
            </div>
        </div>

        <!-- Modal para Registrar Préstamo de Equipo -->
        <Modal :title="'Prestar Equipo a Otra Área'" :id="'AddLoanModal'" ref="modalAddLoanRef" :accept-callback="submitAddLoanForm">
            <form id="AddLoanForm" @submit.prevent="" class="space-y-4 px-2 pb-2">
                <div class="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-lg text-xs text-blue-900 dark:text-blue-200 leading-relaxed">
                    <p>
                        <strong>Importante:</strong> El equipo continuará perteneciendo formalmente a su área actual (<strong>{{ equipo.area?.nombre || 'Sin área asignada' }}</strong>). Se registrará una custodia temporal a favor del área receptora seleccionada.
                    </p>
                </div>

                <div class="flex flex-wrap -mx-2">
                    <div class="w-full md:w-1/2 mt-3 px-2">
                        <SearchSelect
                            name="Área Receptora (Destino)"
                            input-name="id_area_destino"
                            :model="'Area'"
                            :required="true"
                            select_name="id_area_destino"
                            v-model:select_value="formAddLoan.id_area_destino"
                        />
                    </div>

                    <div class="w-full md:w-1/2 mt-3 px-2">
                        <SearchSelect
                            name="Usuario Responsable (Receptor)"
                            input-name="id_usuario_responsable"
                            :model="'User'"
                            :required="false"
                            select_name="id_usuario_responsable"
                            v-model:select_value="formAddLoan.id_usuario_responsable"
                        />
                    </div>

                    <INPUT :field="{
                        id: 'fecha_prestamo',
                        label: 'Fecha de Entrega / Préstamo',
                        required: true,
                        type: 'date',
                        name: 'fecha_prestamo',
                        readonly: false,
                    }" v-model:value="formAddLoan.fecha_prestamo" />

                    <INPUT :field="{
                        id: 'fecha_devolucion_estimada',
                        label: 'Fecha Prevista de Devolución (Opcional)',
                        required: false,
                        type: 'date',
                        name: 'fecha_devolucion_estimada',
                        readonly: false,
                    }" v-model:value="formAddLoan.fecha_devolucion_estimada" />

                    <TEXTAREA :field="{
                        id: 'motivo_prestamo',
                        label: 'Motivo / Justificación del Préstamo',
                        required: true,
                        name: 'motivo',
                        readonly: false,
                        placeholder: 'Indica detalladamente por qué se realiza el préstamo a esta área...',
                    }" v-model:value="formAddLoan.motivo" />
                </div>
            </form>
        </Modal>

        <!-- Modal para Registrar Devolución del Préstamo -->
        <Modal :title="'Registrar Devolución de Equipo'" :id="'ReturnLoanModal'" ref="modalReturnLoanRef" :accept-callback="submitReturnLoanForm">
            <form id="ReturnLoanForm" @submit.prevent="" class="space-y-4 px-2 pb-2">
                <div v-if="selectedLoan" class="p-3 bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 rounded-lg text-xs space-y-1.5 text-gray-700 dark:text-gray-300">
                    <p><strong>Equipo:</strong> {{ equipo.nombre || equipo.cod_inventario }}</p>
                    <p><strong>Área en que estuvo prestado:</strong> <span class="font-semibold text-blue-600 dark:text-blue-400">{{ selectedLoan.area_destino?.nombre || 'N/A' }}</span></p>
                    <p v-if="selectedLoan.responsable"><strong>Responsable que lo recibió:</strong> {{ selectedLoan.responsable.name }}</p>
                    <p><strong>Fecha inicial de préstamo:</strong> {{ selectedLoan.fecha_prestamo ? selectedLoan.fecha_prestamo.split(' ')[0] : 'N/A' }}</p>
                    <p><strong>Área a la que retorna:</strong> <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ equipo.area?.nombre || 'Área propietaria' }}</span></p>
                </div>

                <div class="flex flex-wrap -mx-2">
                    <INPUT :field="{
                        id: 'fecha_devolucion_real',
                        label: 'Fecha Real de Devolución',
                        required: true,
                        type: 'date',
                        name: 'fecha_devolucion_real',
                        readonly: false,
                    }" v-model:value="formReturnLoan.fecha_devolucion_real" />

                    <TEXTAREA :field="{
                        id: 'observacion_devolucion',
                        label: 'Condición de Devolución / Observaciones (Opcional)',
                        required: false,
                        name: 'observacion_devolucion',
                        readonly: false,
                        placeholder: 'Indica el estado físico y operativo en que se recibe el equipo (ej: equipo devuelto completo y funcionando correctamente)...',
                    }" v-model:value="formReturnLoan.observacion_devolucion" />
                </div>
            </form>
        </Modal>

        <DeleteModal ref="deleteModalRef" :callback="update"/>
    </dashboard>
</template>