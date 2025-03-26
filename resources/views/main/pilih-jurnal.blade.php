<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Finance Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-white shadow-md fixed top-0 left-0 w-full z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="#" class="text-xl font-bold text-blue-600">Finance</a>
                <div class="hidden md:flex space-x-6">
                    <a href="#" class="text-gray-700 hover:text-blue-600">Home</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600">About</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600">Services</a>
                </div>
                <button id="mobile-menu-btn" class="md:hidden text-gray-700 focus:outline-none">
                    ☰
                </button>
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

    <!-- Wrapper agar login form tidak tertutup navbar -->
    <div class="flex items-center justify-center min-h-screen mt-20 relative">
        <div class="flex flex-col ">

            <div class="flex justify-center">
                <p class="" style="font-size:30px; "><strong>Login Buku Jurnal </strong>
                <p>
            </div>
            <div>
                <!-- Background Decorative -->
                <div class="absolute inset-0 overflow-hidden">
                    <img alt="Decorative background" class="absolute top-0 left-0 w-1/2 h-full object-cover opacity-10" src="https://storage.googleapis.com/a1aa/image/3TpRiQE7VHilqSBgt-qqBmBbJ6gmsQ3Y7jolQ_BuA_4.jpg" />
                    <img alt="Decorative background" class="absolute bottom-0 right-0 w-1/2 h-full object-cover opacity-10" src="https://storage.googleapis.com/a1aa/image/3TpRiQE7VHilqSBgt-qqBmBbJ6gmsQ3Y7jolQ_BuA_4.jpg" />
                </div>

                <!-- Form Login -->


                <div class="bg-white shadow-lg rounded-lg flex max-w-4xl w-full relative z-10">
                    @foreach($books as $row=>$book)
                    <div class="w-1/2 p-8 flex @if($row%2==0) bg-blue-600 @endif  items-center justify-center">
                        <button class="w-1/2 p-4" onclick="pilihBook('{{$book->id}}')">
                            <i class="fas fa-book" style="font-size:80px; margin-bottom:10px;"></i>

                            {{$book->name}}
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</body>

<script>
    function pilihBook(bookid) {
        $.ajax({
            url: '{{url("book/login-jurnal")}}/' + bookid,
            method: 'get',
            success: function(data) {
                console.log(data);
                if (data.status == 1)
                    window.location.href = '{{url("admin/dashboard")}}';
                else {
                    Swal.fire('Opss', 'something error:' + data.msg, 'warning');

                }
            },
            error: function(res) {
                console.log(res);
                Swal.fire('Opss', 'something error', 'warning');
            }
        });
    }
</script>

</html>