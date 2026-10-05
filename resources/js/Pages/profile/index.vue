<script setup>
    import mainDashboard from '../layouts/mainDashboard.vue';
    import SubNavbar from '../components/SubNavbar.vue';
    import DeleteModal from '../components/DeleteModal.vue';
    import DeleteButton from '../components/buttons/DeleteButton.vue';

    import { usePage, useForm, Link } from '@inertiajs/vue3';
    import { ref } from 'vue';

    const links = [
        { route: 'profile.index', name: 'Perfil', active: true },
    ];

    const user = ref(usePage().props.user);
    const global_use_social_login = ref(usePage().props.global_use_social_login);

    const updateUserForm = useForm({
        name: user.value.name,
        email: user.value.email,
        role: user.value.role,
        can_login: user.value.can_login,
    });

    const submitupdateUser = () => {
        updateUserForm.put(route('profile.update'), {
            onSuccess: (response) => {
                user.value = response.props.user;
                updateUserForm.name = response.props.user.name;
                updateUserForm.email = response.props.user.email;
                updateUserForm.role = response.props.user.role;
                updateUserForm.can_login = response.props.user.can_login;
            },
        });
    };

    const changePasswordForm = useForm({
        new_password: '',
        confirm_new_password: '',
    });

    const submitChangePassword = () => {
        if (changePasswordForm.new_password != changePasswordForm.confirm_new_password) {
            return;
        }

        changePasswordForm.put(route('profile.changePassword'), {
            onSuccess: () => {
                changePasswordForm.reset();
            },
            onFinish: () => {
                const modalElement = document.getElementById('ChangePasswordModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();
            },
        });
    };

    function deleteProviderCallback(response) {
        if (response.props.user) {
            user.value = response.props.user;
        }
    }
</script>

<template>
    <mainDashboard>
        <SubNavbar :links="links" />

        <div class="container">
            <div class="col-12">
                <div class="col-lg-12">
                    <h4>Perfil</h4>
                    <hr class="dorado">
                    <form class="card p-3 mt-2" method="POST" autocomplete="off" @submit.prevent="submitupdateUser">
                        <div class="row">
                            <div class="col-md-6 mt-3">
                                <label for="name">Nombre</label>
                                <input type="text" name="name" id="name" class="form-control" required v-model="updateUserForm.name">
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" name="email" id="email" class="form-control" v-model="updateUserForm.email">
                            </div>
                            <div class="col-md-6">
                                <label for="role">Rol de Usuario</label>
                                <select name="role" id="role" class="form-select" disabled v-model="updateUserForm.role">
                                    <option value="0">Administrador</option>
                                    <option value="1">Usuario</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="can_login">Puede Iniciar Sesión</label>
                                <select name="can_login" id="can_login" class="form-select" disabled v-model="updateUserForm.can_login">
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div class="col-md-12 mt-3 justify-content-between d-flex">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary">Editar</button>
                                </div>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#ChangePasswordModal">Cambiar Contraseña</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="container p-3">
            <ul class="nav nav-tabs flex-row justify-content-end" id="pills-tab" role="tablist" style="border: none;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-modules-tab" data-bs-toggle="pill" data-bs-target="#pills-modules" type="button" role="tab" aria-controls="pills-modules" aria-selected="true">Modulos</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-model-filters-tab" data-bs-toggle="pill" data-bs-target="#pills-model-filters" type="button" role="tab" aria-controls="pills-model-filters" aria-selected="false">Filtros de Información</button>
                </li>
                <li class="nav-item" role="presentation" v-if="global_use_social_login">
                    <button class="nav-link" id="pills-providers-tab" data-bs-toggle="pill" data-bs-target="#pills-providers" type="button" role="tab" aria-controls="pills-providers" aria-selected="true">Proveedores de Sessión</button>
                </li>
            </ul>
        </div>

        <div class="tab-content container" id="pills-tabContent">
            <div class="tab-pane fade" id="pills-modules" role="tabpanel" aria-labelledby="pills-modules-tab">
                <div class="row">
                    <div class="col-lg-12 mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Módulos a los que posee acceso el Usuario</h5>
                        </div>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover m-0" id="modules_table">
                                <thead>
                                    <tr class="table-primary">
                                        <th>Nombre</th>
                                        <th>Acciones</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="userModule in user.user_module" :key="userModule.id">
                                        <td>{{ userModule.module.name }}</td>
                                        <td>
                                            {{ userModule.actions.map(action => action.action_name).join(', ') }}
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <Link class="btn btn-primary btn-sm" :href="route(userModule.module.access_route_name)">
                                                    <i class='bx bxs-show'></i>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade text-primary" id="pills-model-filters" role="tabpanel" aria-labelledby="pills-model-filters-tab">
                <div class="row">
                    <div class="col-lg-12 mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Filtros de Información del Usuario</h5>
                        </div>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover m-0" id="modules_table">
                                <thead>
                                    <tr class="table-primary">
                                        <th>Información Filtrada</th>
                                        <th>Filtro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="userModelFilter in user.user_model_filters">
                                        <td>{{ userModelFilter.model }}</td>
                                        <td>
                                            <span v-if="userModelFilter.comparison_type === 'simple'">
                                                {{ userModelFilter.field }} {{ userModelFilter.operator }} {{ userModelFilter.value }}
                                            </span>
                                            <span v-if="userModelFilter.comparison_type === 'relations'">
                                                {{ userModelFilter.relation }} {{ userModelFilter.field }} {{ userModelFilter.operator }} {{ userModelFilter.value }}
                                            </span>
                                            <span v-if="userModelFilter.comparison_type === 'functions'">
                                                {{ userModelFilter.field }} {{ userModelFilter.operator }} {{ userModelFilter.value }}
                                            </span>
                                            <span v-if="userModelFilter.comparison_type === 'user_own'">
                                                Pertenece al usuario
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-providers" role="tabpanel" aria-labelledby="pills-providers-tab" v-if="global_use_social_login">
                <div class="row">
                    <div class="col-lg-12 mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Proveedores</h5>
                            <div>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#AddProviderModal">
                                    <i class='bx bx-plus'></i>
                                </button>
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover m-0" id="modules_table">
                                <thead>
                                    <tr class="table-primary">
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="userProvider in user.user_providers" :key="userProvider.id">
                                        <td>
                                            <span class="d-flex align-items-center gap-1">
                                                <i :class="userProvider.provider_icon"></i>
                                                <span class="text-align-center">{{ userProvider.provider_name }}</span>
                                            </span>
                                        </td>
                                        <td>
                                            {{ userProvider.provider_email }}
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <DeleteButton
                                                    :url="route('profile.removeProvider', userProvider.id)"
                                                    item-name="Proveedor"
                                                    :item-id="userProvider.id"
                                                    modal-id="deleteProviderModal"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <DeleteModal :callback="deleteProviderCallback"/>
    </mainDashboard>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="ChangePasswordModal" tabindex="-1" aria-labelledby="ChangePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="ChangePasswordModalLabel">Cambiar Contraseña</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitChangePassword" id="changePasswordForm" autocomplete="off">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="new_password" required v-model="changePasswordForm.new_password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_new_password" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="confirm_new_password" required v-model="changePasswordForm.confirm_new_password">
                        </div>
                        <span class="text-danger"
                            v-if="changePasswordForm.new_password != changePasswordForm.confirm_new_password"
                        >
                            Las contraseñas no coinciden
                        </span>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" form="changePasswordForm">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Añadir Proveedor -->
    <div class="modal fade" id="AddProviderModal" tabindex="-1" aria-labelledby="AddProviderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="AddProviderModalLabel">Añadir Proveedor</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <h5>Proveedores disponibles</h5>
                    <hr>

                    <div class="list-group">
                        <a :href="route('socialAuth.redirect', 'google')"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class='bx bxl-google'></i>
                                Google
                            </span>
                            <span class="badge bg-primary rounded-pill">Conectar</span>
                        </a>
                        <a :href="route('socialAuth.redirect', 'facebook')"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class='bx bxl-facebook-square'></i>
                                Facebook
                            </span>
                            <span class="badge bg-primary rounded-pill">Conectar</span>
                        </a>
                        <a :href="route('socialAuth.redirect', 'github')"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class='bx bxl-github'></i>
                                GitHub
                            </span>
                            <span class="badge bg-primary rounded-pill">Conectar</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>