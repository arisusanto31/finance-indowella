<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Finance Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>

<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-white shadow-md fixed top-0 left-0 w-full z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="#" class="text-xl font-bold text-blue-600">Finance Indowella</a>
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
        document.getElementById('mobile-menu-btn').addEventListener('click', function () {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>

    <!-- Wrapper agar login form tidak tertutup navbar -->
    <div class="flex items-center justify-center min-h-screen mt-20 relative">
        
        <!-- Background Decorative -->
        <div class="absolute inset-0 overflow-hidden">
            <img alt="Decorative background" class="absolute top-0 left-0 w-1/2 h-full object-cover opacity-10" src="https://storage.googleapis.com/a1aa/image/3TpRiQE7VHilqSBgt-qqBmBbJ6gmsQ3Y7jolQ_BuA_4.jpg"/>
            <img alt="Decorative background" class="absolute bottom-0 right-0 w-1/2 h-full object-cover opacity-10" src="https://storage.googleapis.com/a1aa/image/3TpRiQE7VHilqSBgt-qqBmBbJ6gmsQ3Y7jolQ_BuA_4.jpg"/>
        </div>

        <!-- Form Login -->
        <div class="bg-white shadow-lg rounded-lg flex max-w-4xl w-full relative z-10">
            <div class="w-1/2 p-8 flex items-center justify-center">
                <img alt="Illustration of financial animation" class="w-full h-auto" src="https://storage.googleapis.com/a1aa/image/iWORPy8MaoAQ-fu2WrsQZsMcnoiO093s4FkDUE1NocY.jpg"/>
            </div>
            <div class="w-1/2 bg-blue-600 p-8 flex flex-col justify-center">
                <h1 class="text-white text-center font-serif text-6xl font-bold mb-6">Login</h1>
                <form class="space-y-6">
                    <div class="relative">
                        <input class="w-full p-3 pl-10 bg-white shadow-lg rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Masukkan Nama" type="text"/>
                        <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="relative">
                        <input class="w-full p-3 pl-10 bg-white shadow-lg rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Password" type="password"/>
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="flex space-x-4 mt-6">
                        <button class="bg-yellow-400 text-white py-2 px-10 rounded-full font-bold" type="button">Submit</button>
                        <button class="bg-transparent border border-white text-white py-2 px-10 rounded-full font-bold" type="button">Sign in</button>
                    </div>
                </form>
                <div class="text-center mt-10">
                    <p class="text-white text-lg font-semibold font-serif">CV Indoko Packaging</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
