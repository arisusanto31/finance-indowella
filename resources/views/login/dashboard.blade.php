<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Dashboard Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        .rotate {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }

        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dropdown-toggle').forEach(button => {
                button.addEventListener('click', function() {
                    const menu = this.nextElementSibling;
                    menu.classList.toggle('hidden');
                    this.classList.toggle('bg-blue-700');
                    this.classList.toggle('bg-blue-900');
                });
            });
        });
    </script>
</head>

<body class="bg-gray-100">

    <nav class="bg-white shadow-md fixed top-0 left-0 w-full z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="#" class="text-xl font-bold text-blue-600">Finance Indowella</a>
                <div class="hidden md:flex space-x-6">
                    <a href="#" class="text-gray-700 hover:text-blue-600">Home</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600">About</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <a href="#" class="text-gray-700 hover:text-blue-600"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </a>
                    </form>

                </div>
                <button id="mobile-menu-btn" class="md:hidden text-gray-700 focus:outline-none">
                    ☰
                </button>
            </div>
        </div>
    </nav>

    <div class="flex h-screen pt-16">

        <!-- Sidebar -->
        <div class="bg-sky-500 p-6 w-64 space-y-6 py-7 shadow-lg min-h-screen">
            <div class="relative">
                <input class="border border-gray-800 text-gray-800 rounded-lg py-2 px-4 pl-10 w-full focus:outline-none focus:ring-2 focus:ring-blue-900" placeholder="Search..." type="text" />
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <nav>
                <!-- Dropdown Button -->
                <div class="relative">
                    <button class="dropdown-toggle flex items-center justify-between bg-blue-500 font-bold text-white px-4 py-3 w-full shadow-md border border-white rounded-lg cursor-pointer">
                        <div class="flex items-center gap-x-3">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>neraca</span>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="hidden bg-blue-900 border border-yellow-500 rounded mt-1">
                        <a class="block py-2.5 px-4 text-white hover:bg-blue-700" href="#">Test 1</a>
                        <a class="block py-2.5 px-4 text-white hover:bg-blue-700" href="#">Test 2</a>
                        <a class="block py-2.5 px-4 text-white hover:bg-blue-700" href="#">Test 3</a>
                    </div>
                </div>

                <!-- Tombol Biasa -->
                <button class="flex items-center gap-x-3 bg-blue-900 font-bold text-white px-4 py-3 w-full shadow-md border border-white rounded-lg cursor-pointer mt-2">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </button>

                <button class="flex items-center gap-x-3 bg-blue-900 font-bold text-white px-4 py-3 w-full shadow-md border border-white rounded-lg cursor-pointer mt-2">
                    <i class="fas fa-cog"></i>
                    <span>apa le</span>
                </button>

                {{-- <!-- Dropdown Button (Duplikat) -->
                <div class="relative">
                    <button class="dropdown-toggle flex items-center justify-between bg-blue-500 font-bold text-white px-4 py-3 w-full shadow-md border border-white rounded-lg cursor-pointer">
                        <div class="flex items-center gap-x-3">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>neraca</span>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="hidden bg-blue-900 border border-yellow-500 rounded mt-1">
                        <a class="block py-2.5 px-4 text-white hover:bg-blue-700" href="#">Test 1</a>
                        <a class="block py-2.5 px-4 text-white hover:bg-blue-700" href="#">Test 2</a>
                        <a class="block py-2.5 px-4 text-white hover:bg-blue-700" href="#">Test 3</a>
                    </div>
                </div> --}}
            </nav>
        </div>

        <div class="flex-1 p-6">
            <h1 class="text-2xl font-bold text-gray-800">tes le</h1>
        </div>
    </div>

</body>

</html>