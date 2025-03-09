<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>
        Dashboard-Finance
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>

        .active {
            background: linear-gradient(90deg, rgba(59,130,246,1) 0%, rgba(37,99,235,1) 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100 font-roboto">
<div class="flex">
    <!-- Sidebar -->
    <div class="w-64 bg-blue-600 h-screen shadow-lg">
        <div class="p-6">
            <div class="flex items-center space-x-2">
                {{-- <img src="{{ asset('build/assets/indowella.png') }}" alt="Logo Indowella" class="logo">
                --}}
            </div>
            <div class="mt-6">
                <input class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="Search framework..." type="text"/>
            </div>
        </div>
        <nav class="mt-6">
            <ul id="menu">
                <li class="menu-item px-6 py-2 text-white hover:bg-blue-700 cursor-pointer rounded-md transition duration-200 text-lg">
                    <i class="fas fa-tachometer-alt text-xl"></i>
                    <span class="ml-2 font-bold text-2xl font-serif">Dashboard</span>
                </li>
                <li class="menu-item px-6 py-2 text-white hover:bg-blue-700 cursor-pointer rounded-md transition duration-200 text-lg">
                    <i class="fas fa-chart-line text-xl"></i>
                    <span class="ml-2 font-bold text-2xl font-serif">Analytics</span>
                </li>
                <li class="menu-item px-6 py-2 text-white hover:bg-blue-700 cursor-pointer rounded-md transition duration-200 text-lg">
                    <i class="fas fa-clock text-xl"></i>
                    <span class="ml-2 font-bold text-2xl font-serif">Neraca</span>
                    <ul class="submenu hidden mt-2 ml-4">
                        <li class="px-4 py-2 text-white hover:bg-blue-700 cursor-pointer rounded-md transition duration-200 text-lg">
                            Option 1
                        </li>
                        <li class="px-4 py-2 text-white hover:bg-blue-700 cursor-pointer rounded-md transition duration-200 text-lg">
                            Option 2
                        </li>
                        <li class="px-4 py-2 text-white hover:bg-blue-700 cursor-pointer rounded-md transition duration-200 text-lg">
                            Option 3
                        </li>
                    </ul>
                </li>
                <li class="menu-item px-6 py-2 text-white hover:bg-blue-900 cursor-pointer rounded-md transition duration-200 text-lg">
                    <i class="fas fa-user-graduate text-xl"></i>
                    <span class="ml-2 font-bold font-serif">Apa le</span>
                    <ul class="submenu hidden mt-2 ml-4">
                        <li class="px-4 py-2 text-white hover:bg-blue-700 cursor-pointer rounded-md transition duration-200 text-lg">
                            Option 1
                        </li>
                        <li class="px-4 py-2 text-white hover:bg-blue-700 cursor-pointer rounded-md transition duration-200 text-lg">
                            Option 2
                        </li>
                        <li class="px-4 py-2 text-white hover:bg-blue-700 cursor-pointer rounded-md transition duration-200 text-lg">
                            Option 3
                        </li>
            
        </nav>
        <div class="mt-6">
          
              
         </ul>
         </li>
         </ul>
         </div>
    </div>
    <div class="flex-1 p-6">
        <div class="bg-white p-10 rounded-md shadow-md mb-6">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-blue-600">
                    
                </h1>
            </div>
        </div>
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <!-- Additional content can be added here -->
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', () => {
            document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            const submenu = item.querySelector('.submenu');
            if (submenu) {
                submenu.classList.toggle('hidden');
            }
        });
    });
</script>
</body>
</html>