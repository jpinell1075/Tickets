<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h4>Usuarios</h4>
            <hr>
            <div class="p-3">
                <table class="table table-striped table-hover m-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(App\Models\User::all() as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->rol() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>