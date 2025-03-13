<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Dashboard-finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.dropdown-toggle').forEach(button => {
                button.addEventListener('click', function () {
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
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-white p-6 rounded-lg shadow-lg w-64 space-y-6 py-7 px-2">
            <div class="text-center">
                <h2 class="text-gray-800 font-bold font-serif text-xl">Finance Indowella</h2>
            </div>
            
            <div class="relative">
                <input class="border border-gray-800 text-gray-800 rounded-lg py-2 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-transparent w-full" placeholder="Search..." type="text"/>
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <nav>
                <div class="relative" id="coba-css">
                    <button class="dropdown-toggle flex items-center justify-between bg-blue-500 font-bold text-white px-4 py-3 w-full shadow-md border border-white rounded-lg cursor-pointer transition duration-900 ease-in-out">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                        <i id="chevron-icon" class="fas fa-chevron-down float-right mt-1"></i>

                    </button>
                    
                    <div class="hidden bg-blue-900 border border-yellow-500 rounded mt-1" id="dropdown-menu">
                        <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white" href="#">test 1</a>
                        <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white" href="#">test  2</a>
                        <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 text-white" href="#">test 3</a>
                    </div>
                </div>
                <div class="relative">
                <button class="dropdown-toggle flex items-center justify-between bg-green-500 font-bold text-white px-4 py-3 w-full shadow-md border border-white rounded-lg cursor-pointer transition duration-200 ease-in-out mt-2">
                    <i class="fas fa-file-invoice mr-1"></i>
                    neraca
                    <i id="chevron-icon" class="fas fa-chevron-down float-right mt-1"></i>
                </button>
                <div class="hidden bg-blue-800 border border-yellow-900 rounded mt-1" id="dropdown-menu">
                    <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-yellow-500 text-white" href="#">test 1</a>
                    <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-yellow-500 text-white" href="#">test  2</a>
                    <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-yellow-500 text-white" href="#">test 3</a>
                </div>
                </div>
                <button class="flex items-center justify-between bg-purple-500 font-bold text-white px-4 py-3 w-full shadow-md border border-white rounded-lg cursor-pointer transition duration-900 ease-in-out mt-2">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </button>

                <a class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-200 text-gray-900 mt-2" href="#">
                    <i class="text-gray-900 fas fa-users mr-3"></i>
                    Users
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex-1 p-10">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-4xl font-bold font-serif">Selamat datang di website finance indowella</h1>
                <div class="relative">
                    <input class="border border-gray-300 rounded-lg py-2 px-4 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search..." type="text"/>
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-500 text-white mr-4">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="text-2xl font-semibold">1TRILIUN</h4>
                            <p class="text-gray-600">tes</p>
                        </div>
                    </div>
                </div>  
         </div>
         </div>
         </div>
         </div>  
         <script>
            public function chevron-icon() {
                

                

            } 
         </script>
</body>
</html>
