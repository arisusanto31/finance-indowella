<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pilih Buku Jurnal</title>
    <!-- Sneat Bootstrap Core CSS -->
    <link href="{{ asset('assets/vendor/css/core.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendor/css/theme-default.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/demo.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/js/own-helper.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<style>
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
</style>

<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand text-primary fw-bold" href="#">Finance</a>
            <div class="d-flex">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Log Out
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container py-5 mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg rounded-4">
                    <div class="card-body">
                        <h4 class="text-center fw-bold mb-4">Pilih Buku Jurnal</h4>
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            @foreach($books as $book)
                            @php
                            $isToko = str_contains(strtolower($book->name), 'toko');
                            $isManufaktur = str_contains(strtolower($book->name), 'manufaktur');
                            $bgColor = $isToko ? '#71dd37' : ($isManufaktur ? '#007bff' : '993932');
                            @endphp
                            <div class="col text-center">
                                <button onclick="pilihBook('{{ $book->id }}')"
                                    style="background-color:{{$bgColor}}" class="btn  text-white rounded-3 px-4 py-4 w-100 h-100 shadow-sm">
                                    <img src="{{ asset('assets/img/openboox-removebg.png') }}" alt="Book Icon"
                                        class="img-fluid mb-2" style="max-height: 80px;">
                                    <div class="fw-semibold">{{ $book->name }}</div>
                                    <p style="font-size:10px">{{$book->description}}</p>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-12 mt-4">
                                <div class="col text-center">
                                    <button onclick="pilihBook('{{$thebook->id}}')"
                                        class="btn text-white rounded-3 px-4 py-4 w-100 h-100 shadow-sm bg-secondary">
                                        <img src="{{ asset('assets/img/openboox-removebg.png') }}" alt="Book Icon"
                                            class="img-fluid mb-2" style="max-height: 80px;">
                                        <div class="fw-semibold">Buku {{user()->name}} </div>
                                        <p style="font-size:10px">buku {{user()->name}}, bisa untuk coba coba yaa</p>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="">Create permission</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" id="permission-name" class="form-control" placeholder="nama permission" />
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary" onclick="storePermission()"> store</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <table id="" class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-permission">

                                    </tbody>
                                </table>
                            </div>
                        </div>


                    </div>

                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h6 style="margin-top:10px"> Create Role</h6>
                        <div class="row">
                            <div class="col-xs-4 col-md-4">
                                <input type="text" id="role-name" class="form-control" placeholder="nama role" />
                            </div>
                            <div class="col-xs-4 col-md-4">
                                <button class="btn btn-primary" onclick="storeRole()"> store</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Permissions</th>
                                            <th>+ Add </th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-role">

                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 style="margin-top:10px"> User</h6>
                        <div class="row">
                            <div class="col-xs-4 col-md-4">
                                <input type="text" id="user-name" class="form-control" placeholder="username" />
                            </div>
                            <div class="col-xs-4 col-md-4">
                                <input type="text" id="user-email" class="form-control" placeholder="email" />
                            </div>

                            <div class="col-xs-4 col-md-4">
                                <button type="button" class="btn btn-primary" onclick="storeUser()">Store</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <table id="" class="table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama User</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>+ Add</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-user">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script pilih book -->
    <script>
        function pilihBook(bookid) {
            $.ajax({
                url: '{{ url("book/login-jurnal") }}/' + bookid,
                method: 'get',
                success: function(data) {
                    if (data.status == 1)
                        window.location.href = '{{ url("admin/dashboard") }}';
                    else
                        Swal.fire('Opss', 'something error: ' + data.msg, 'warning');
                },
                error: function() {
                    Swal.fire('Opss', 'something error', 'warning');
                }
            });
        }

        function getRole() {
            $.ajax({
                url: '{{route("profile.get-role")}}',
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        let html = '';
                        res.msg.forEach(function each(data, i) {
                            html += `
                                <tr>
                                    <td>${i+1}</td>
                                    <td>${data.name} [${data.id}]</td>
                                     <td>${data.permissions.map((item)=>{ return '<span > <i class="fas fa-circle"></i> '+item.name+' </span>'}).join('<br>')} </td>
                                     <td><select id="select-permission${data.id}" class="select-permission form-control"></select> <button onclick="addPermission(${data.id})"> add</button> </td>
                                </tr>
                                `;
                        });
                        $('#table-role').html(html);
                        initItemSelectManual('.select-permission', '{{route("profile.get-item-permission")}}');
                    }
                },
                error: function(res) {

                }
            });
        }

        function getPermission() {
            $.ajax({
                url: '{{route("profile.get-permission")}}',
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        let html = '';
                        res.msg.forEach(function each(data, i) {
                            html += `
                                <tr>
                                    <td>${i+1}</td>
                                    <td>${data.name} [${data.id}]</td>
                                </tr>
                                `;
                        });
                        $('#table-permission').html(html);

                    }
                },
                error: function(res) {

                }
            });
        }

        function getUser() {
            $.ajax({
                url: '{{route("profile.get-user")}}',
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        let html = '';
                        res.msg.forEach(function each(data, i) {
                            html += `
                                <tr>
                                    <td>${i+1}</td>
                                    <td>${data.name} [${data.id}]</td>
                                    <td>${data.email}</td>
                                    <td>${data.role.map((item)=>{return '<span> <i class="fas fa-circle"></i> '+item+'</span>'}).join('<br>')}</td>
                                    <td><select id="select-role${data.id}" class="select-role form-control"></select> <button onclick="addRole(${data.id})"> add</button> </td>
                                </tr>
                                `;
                        });
                        $('#table-user').html(html);
                        initItemSelectManual('.select-role', '{{route("profile.get-item-role")}}');
                    }
                },
                error: function(res) {

                }
            })
        }

        function storePermission() {
            swalConfirmAndSubmit({
                url: '{{route("profile.create-permission")}}',
                data: {
                    name: $('#permission-name').val(),
                    _token: '{{csrf_token()}}'
                },
                successText: "berhasil submit",
                onSuccess: (res) => {
                    getPermission();
                }
            });
        }

        function storeUser() {
            swalConfirmAndSubmit({
                url: '{{route("profile.create-user")}}',
                data: {
                    name: $('#user-name').val(),
                    email: $('#user-email').val(),
                    _token: '{{csrf_token()}}'
                },
                successText: "berhasil submit",
                onSuccess: (res) => {
                    getUser();
                }
            });

        }

        function storeRole() {
            swalConfirmAndSubmit({
                url: '{{route("profile.create-role")}}',
                data: {
                    name: $('#role-name').val(),
                    _token: '{{csrf_token()}}'
                },
                successText: "berhasil submit",
                onSuccess: (res) => {
                    getRole();
                }
            });
        }

        function addRole(id) {
            roleID = $('#select-role' + id + ' option:selected').val();
            swalConfirmAndSubmit({
                url: '{{route("profile.add-role-user")}}',
                data: {
                    user_id: id,
                    role_id: roleID,
                    _token: '{{csrf_token()}}'
                },
                successText: "berhasil submit",
                onSuccess: (res) => {
                    getUser();
                }
            });
        }

        function addPermission(id) {
            permissionID = $('#select-permission' + id + ' option:selected').val();
            swalConfirmAndSubmit({
                url: '{{route("profile.add-permission-role")}}',
                data: {
                    role_id: id,
                    permission_id: permissionID,
                    _token: '{{csrf_token()}}'
                },
                successText: "berhasil submit",
                onSuccess: (res) => {
                    getRole();
                }
            });
        }

        getPermission();
        getUser();
        getRole();
    </script>
</body>

</html>