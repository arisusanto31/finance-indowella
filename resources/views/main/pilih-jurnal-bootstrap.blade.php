<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profile Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


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

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="bg-blue-200 min-h-screen flex items-center justify-center p-6">
  <div class="bg-white rounded-md w-full max-w-5xl shadow-md">
    <!-- Header -->
    <header class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 3v18m9-9H3" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
        <span class="font-semibold text-gray-800 text-base select-none">Profile Fiinance Indowella</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="font-semibold text-gray-800 text-base select-none">{{ user()->email }}</span>
        <div class="relative">
          <img class="w-8 h-8 rounded-full object-cover" src="https://storage.googleapis.com/a1aa/image/043db412-f976-4810-e807-4bc5d5e1b256.jpg" alt="Profile" />
          <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="px-6 py-8 space-y-10">
  <h2 class="text-xl font-bold text-gray-800">👋Haii selamat datang {{ user()->name }}  ,Silahkan pilih Buku nya ya SEMANGAT🤗😊</h2>
  <!-- Profile Section -->
  <div class="flex flex-col md:flex-row gap-10">
    <!-- Left Sidebar -->
    <aside class="flex-shrink-0 w-full md:w-64 space-y-6">
      <img class="w-full rounded-md object-cover" src="https://storage.googleapis.com/a1aa/image/04b46c82-649a-434d-fa92-11d3dc4b6cb5.jpg" alt="Cover" />
    </aside>
    <!-- Right Content -->
    <section class="flex-1">
      <form class="space-y-6 max-w-md">
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Nama</label>
          <input class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-700" name="first_name" type="text" value="{{ user()->name }}" readonly />
        </div>
        <div>
          <label class="block font-semibold text-gray-700 mb-1">E-mail</label>
          <input class="w-full border border-gray-300 rounded-md px-3 py-2 text-gray-700" name="email" type="email" value="{{ user()->email }}" readonly />
        </div>
      </form>
    </section>
  </div>



<div class="bg-white rounded-lg shadow-md p-6">
  <h4 class="text-center font-bold text-lg mb-6">Pilih Buku Jurnal</h4>


  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
    @foreach($books as $book)
    @php
      $isToko = str_contains(strtolower($book->name), 'toko');
      $isManufaktur = str_contains(strtolower($book->name), 'manufaktur');
      $bgColor = $isToko ? 'bg-green-600' : ($isManufaktur ? 'bg-blue-600' : 'bg-gray-700');
    @endphp
    <div class="h-full">
      <button onclick="pilihBook('{{ $book->id }}')"
              class="rounded-xl text-white px-4 py-4 w-full min-h-[220px] shadow-sm flex flex-col items-center space-y-2 {{ $bgColor }}">
        <img src="{{ asset('assets/img/openboox-removebg.png') }}" alt="Book Icon" class="w-20 h-20 mb-2" />
        <div class="font-semibold">{{ $book->name }}</div>
        <p class="text-xs text-center">{{ $book->description }}</p>
      </button>
    </div>
    @endforeach

   
    <div class="h-full">
      <button onclick="pilihBook('{{ $thebook->id }}')"
              class="bg-gray-500 text-white rounded-xl px-4 py-4 w-full min-h-[220px] shadow-md flex flex-col items-center space-y-2">
        <img src="{{ asset('assets/img/openboox-removebg.png') }}" alt="Book Icon" class="w-20 h-20 mb-2" />
        <div class="font-semibold">Buku {{ user()->name }}</div>
        <p class="text-xs text-center">Buku {{ user()->name }}, bisa untuk coba coba yaa</p>
      </button>
    </div>
  </div>
</div>



<div class="mt-10 px-6">
  <div class="bg-white rounded-lg shadow-md p-6 space-y-8">
   
    <div>
      <h4 class="text-lg font-bold mb-4">Create Permission</h4>
      <div class="flex flex-col md:flex-row gap-4 mb-4">
        <input type="text" id="permission-name" class="form-control flex-1 border px-3 py-2 rounded" placeholder="Nama permission" />
        <button class="btn btn-primary px-4 py-2 rounded" onclick="storePermission()">Store</button>
      </div>
      <table class="table-auto w-full text-left border">
        <thead>
          <tr>
            <th class="border px-2 py-1">No</th>
            <th class="border px-2 py-1">Nama</th>
          </tr>
        </thead>
        <tbody id="table-permission"></tbody>
      </table>
    </div>

    <div>
      <h4 class="text-lg font-bold mb-4">Create Role</h4>
      <div class="flex flex-col md:flex-row gap-4 mb-4">
        <input type="text" id="role-name" class="form-control flex-1 border px-3 py-2 rounded" placeholder="Nama role" />
        <button class="btn btn-primary px-4 py-2 rounded" onclick="storeRole()">Store</button>
      </div>
      <table class="table-auto w-full text-left border">
        <thead>
          <tr>
            <th class="border px-2 py-1">No</th>
            <th class="border px-2 py-1">Nama</th>
            <th class="border px-2 py-1">Permissions</th>
            <th class="border px-2 py-1">+ Add</th>
          </tr>
        </thead>
        <tbody id="table-role"></tbody>
      </table>
    </div>

    <div>
      <h4 class="text-lg font-bold mb-4">Create User</h4>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <input type="text" id="user-name" class="form-control border px-3 py-2 rounded" placeholder="Username" />
        <input type="text" id="user-email" class="form-control border px-3 py-2 rounded" placeholder="Email" />
        <button type="button" class="btn btn-primary px-4 py-2 rounded" onclick="storeUser()">Store</button>
      </div>
      <table class="table-auto w-full text-left border">
        <thead>
          <tr>
            <th class="border px-2 py-1">No</th>
            <th class="border px-2 py-1">Nama User</th>
            <th class="border px-2 py-1">Email</th>
            <th class="border px-2 py-1">Role</th>
            <th class="border px-2 py-1">+ Add</th>
          </tr>
        </thead>
        <tbody id="table-user"></tbody>
      </table>
    </div>
  </div>
</div>
</main>
  </div>

  <script>
  const defaultImg = "https://via.placeholder.com/100";

  function previewFoto(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => document.getElementById('preview-image').src = e.target.result;
      reader.readAsDataURL(input.files[0]);
    }
  }
  

  function openCardCreate() {
      $('#card-create').toggleClass('open');
      $('#icon-create').toggleClass('open');
    }

  function resetFoto() {
    document.getElementById('preview-image').src = defaultImg;
    document.querySelector('input[type="file"]').value = '';
  }
  
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
