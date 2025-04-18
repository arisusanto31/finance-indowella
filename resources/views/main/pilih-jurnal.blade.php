<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Finance Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>

<body class="bg-purple-300">

    <!-- Navbar -->
    <nav class="bg-white shadow-md fixed top-0 left-0 w-full z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="#" class="text-xl font-bold text-blue-600">Finance</a>
                <div class="hidden md:flex space-x-6">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Log Out</span>
                        </button>
                    </form>
                    </li>
                </div>
                <button id="mobile-menu-btn" class="md:hidden text-gray-700 focus:outline-none">☰</button>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-100">Home</a>
            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-100">About</a>
            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-100">Services</a>

        </div>
    </nav>

    <script>
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>

    <div class="flex items-center justify-center min-h-screen m-0 p-0 px-4 bg-purple-300">
        <div class="bg-white shadow-2xl rounded-2xl px-10 py-10 inline-block">
            <h1 class="text-2xl font-bold font-serif text-center text-gray-800 mb-8">
                Pilih Buku Jurnal
            </h1>

            <div class="grid grid-cols-2 divide-x divide-gray-400 border border-gray-300 rounded-lg">
                @foreach($books as $row => $book)
                @php
                $isToko = str_contains(strtolower($book->name), 'toko');
                $isManufaktur = str_contains(strtolower($book->name), 'manufaktur');
                $color = $isToko ? 'bg-pink-500 hover:bg-pink-600' :
                ($isManufaktur ? 'bg-purple-600 hover:bg-purple-700' :
                'bg-gray-200 hover:bg-gray-300');
                @endphp

                <div class="flex items-center justify-center p-6">
                    <button
                        onclick="pilihBook('{{ $book->id }}')"
                        class="group w-44 h-44 {{ $color }} text-white border border-gray-300 hover:shadow-xl transition-all duration-300 rounded-lg p-4 text-center hover:scale-105">
                        <img src="{{ asset('assets/img/openboox-removebg.png') }}" alt="Book Icon"
                            class="w-24 h-24 mb-3 mx-auto group-hover:opacity-90" />
                        <div class="font-serif text-base font-semibold text-white">
                            {{ $book->name }}
                        </div>
                    </button>
                </div>

                @endforeach
                <div class="flex items-center justify-center  p-10">
                    <button
                        onclick="pilihBook('{{ $book->id }}')"
                        class="group w-44 h-44 {{ $color }} text-white border border-gray-300 hover:shadow-xl transition-all duration-300 rounded-lg p-4 text-center hover:scale-105">
                        <img src="{{ asset('assets/img/openboox-removebg.png') }}" alt="Book Icon"
                            class="w-24 h-24 mb-3 mx-auto group-hover:opacity-90" />
                        <div class="font-serif text-base font-semibold text-white">
                            {{-- {{ $book->name }} --}}
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

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