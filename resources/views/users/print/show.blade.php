@php
    $user = App\Models\User::find($params['user_id']);
@endphp
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h4>Usuario</h4>
            <hr>
            <div class="p-3">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong> {{ $user->name }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <h4>Módulos</h4>
            <hr>
            <div class="p-3">
                <table class="table table-striped table-hover m-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Enlace</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->userModule as $userModule)
                            <tr>
                                <td>{{ $userModule->module->name }}</td>
                                <td>
                                    {{ Route::has($userModule->module->access_route_name) ? route($userModule->module->access_route_name) : ''}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>