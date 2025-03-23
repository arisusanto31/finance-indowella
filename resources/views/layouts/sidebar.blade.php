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