export function Caracteristics() {
    const available_caracteristics = {
        'operative_system': {
            name: 'operative_system',
            label: 'Sistema Operativo',
            options: {
                'windows': 'Windows',
                'linux': 'Linux',
                'macos': 'MacOS',
            },
            component: 'SELECT',
        },
        'operative_system_arquitecture': {
            name: 'operative_system_arquitecture',
            label: 'Arquitectura del Sistema Operativo',
            options: {
                'x32': 'X32',
                'x64': 'X64',
            },
            component: 'SELECT',
        },
        'operative_system_version': {
            name: 'operative_system_version',
            label: 'Versión del Sistema Operativo',
            type: 'text',
            component: 'INPUT',
        },
        'size': {
            name: 'size',
            label: 'Tamaño',
            type: 'text',
            component: 'INPUT',
        },
        'color': {
            name: 'color',
            label: 'Color',
            type: 'text',
            component: 'INPUT',
        },
        'ports_number': {
            name: 'ports_number',
            label: 'Número de Puertos',
            type: 'number',
            component: 'INPUT',
        },
        'switch_canal': {
            name: 'switch_canal',
            label: 'Fibra Canal',
            options: {
                'fibra': 'Fibra',
                'lan': 'LAN',
            },
            component: 'SELECT',
        },
        'mac_address': {
            name: 'mac_address',
            label: 'Dirección MAC',
            type: 'text',
            component: 'INPUT',
        },
    };

    const available_components = {
        cpu: {
            label: 'Procesador',
            options: {
                model: {
                    name: 'cpu_model',
                    label: 'Modelo',
                    type: 'text',
                    component: 'INPUT',
                },
            }
        },
        storage: {
            label: 'Almacenamiento',
            options: {
                type: {
                    name: 'storage_type',
                    label: 'Tipo',
                    options: {
                        'hdd': 'HDD',
                        'ssd': 'SSD',
                        'nvme': 'NVMe',
                    },
                    component: 'SELECT',
                },
                capacity: {
                    name: 'storage_capacity',
                    label: 'Capacidad',
                    type: 'text',
                    component: 'INPUT',
                },
            }
        },
        ram: {
            label: 'Memoria RAM',
            options: {
                type: {
                    name: 'ram_type',
                    label: 'Tipo',
                    options: {
                        'sdram': 'SDRAM',
                        'lpddr': 'LPDDR',
                        'ddr': 'DDR',
                        'ddr2': 'DDR2',
                        'ddr3': 'DDR3',
                        'ddr4': 'DDR4',
                        'ddr5': 'DDR5',
                        'ddr6': 'DDR6',
                    },
                    component: 'SELECT',
                },
                capacity: {
                    name: 'ram_capacity',
                    label: 'Capacidad',
                    type: 'text',
                    component: 'INPUT',
                },
                speed: {
                    name: 'ram_speed',
                    label: 'Velocidad',
                    type: 'text',
                    component: 'INPUT',
                },
            }
        },
        gpu: {
            label: 'Tarjeta Gráfica',
            options: {
                model: {
                    name: 'gpu_model',
                    label: 'Modelo',
                    type: 'text',
                    component: 'INPUT',
                },
                memory: {
                    name: 'gpu_memory',
                    label: 'Memoria',
                    type: 'text',
                    component: 'INPUT',
                },
            }
        },
        wifi_card: {
            label: 'Tarjeta de Red Inalámbrica',
            options: {
                model: {
                    name: 'wifi_card_model',
                    label: 'Modelo',
                    type: 'text',
                    component: 'INPUT',
                },
                speed: {
                    name: 'wifi_card_speed',
                    label: 'Velocidad',
                    type: 'text',
                    component: 'INPUT',
                },
            }
        },
    };

    const types_available_values = {
        cpu: {
            components: ['cpu', 'storage', 'ram', 'gpu', 'wifi_card'],
            characteristics: ['operative_system', 'operative_system_arquitecture', 'operative_system_version'],
        },
        monitor: {
            components: [],
            characteristics: ['size', 'color'],
        },
        ups: {
            components: [],
            characteristics: ['size', 'color'],
        },
        switches: {
            components: [],
            characteristics: ['ports_number', 'switch_canal'],
        },
        aps: {
            components: [],
            characteristics: [],
        },
        telefonia_ip: {
            components: [],
            characteristics: ['mac_address'],
        },
        impresoras: {
            components: [],
            characteristics: [],
        },
        reloj_biometrico: {
            components: [],
            characteristics: [],
        },
        data_show: {
            components: [],
            characteristics: [],
        },
        pantalla_proyector: {
            components: [],
            characteristics: [],
        },
        laptops: {
            components: [],
            characteristics: [],
        },
    };

    return {
        available_caracteristics,
        available_components,
        types_available_values,
    };
}