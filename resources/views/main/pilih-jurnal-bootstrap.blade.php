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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

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
                            $bgColor = $isToko ? 'bg-danger' : ($isManufaktur ? 'bg-primary' : 'bg-secondary');
                            @endphp
                            <div class="col text-center">
                                <button onclick="pilihBook('{{ $book->id }}')"
                                    class="btn {{ $bgColor }} text-white rounded-3 px-4 py-4 w-100 h-100 shadow-sm">
                                    <img src="{{ asset('assets/img/openboox-removebg.png') }}" alt="Book Icon"
                                        class="img-fluid mb-2" style="max-height: 80px;">
                                    <div class="fw-semibold">{{ $book->name }}</div>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-12 mt-4">
                                <div class="col text-center">
                                    <button onclick="pilihBook(5)"
                                        class="btn text-white rounded-3 px-4 py-4 w-100 h-100 shadow-sm bg-secondary">
                                        <img src="{{ asset('assets/img/openboox-removebg.png') }}" alt="Book Icon"
                                            class="img-fluid mb-2" style="max-height: 80px;">
                                        <div class="fw-semibold">Buku {{user()->name}} </div>
                                        <p style="font-size:10px">bisa untuk coba coba yaa</p>
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
                        <h5 class="text-center">Create permission</h5>
                        <div class="row">
                            <div class="col-xs-4">
                                
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
    </script>
</body>

</html>