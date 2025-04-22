<x-app-layout>
    <form method="POST" action="{{ route('karyawan.store') }}">
@csrf
    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;">  <strong></strong> </h5>

        <div class="card-body">
            <div class="row mt-1">
                <div class="col-md-2">
                    <a href="#" onclick="ShowModalKaryawan()" class="btn btn-primary btn-big-custom rounded-0">Create Karyawan</a>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>

            
            <table id="supplier-table" class="table table-bordered table-striped">
                  <thead class="table-light">
                  <tr>
                    <th class="border px-4 py-2">No</th>
                    <th class="border px-4 py-2">Nama</th>
                    <th class="border px-4 py-2">Nik</th>
                    <th class="border px-4 py-2">npwp</th>
                    <th class="border px-4 py-2">Jabatan</th>
                    <th class="border px-4 py-2">Tanggal Masuk</th>
                    <th class="border px-4 py-2">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($karyawans as $karyawan)
                  <tr>
                    <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                    <td class="border px-4 py-2">{{ $karyawan->nama }}</td>
                    <td class="border px-4 py-2">{{ $karyawan->nik }}</td>
                    <td class="border px-4 py-2">{{ $karyawan->npwp }}</td>
                    <td class="border px-4 py-2">{{ $karyawan->jabatan }}</td>
                    <td class="border px-4 py-2">{{ $karyawan->date_masuk }}</td>
                    <td class="border px-4 py-2">
                      @if(!$karyawan->date_keluar)
{{--             
                      <form action="{{ route('karyawans.resign', $karyawan->id) }}" method="POST" class="inline"> --}}
                        @csrf
                        @method('PUT')
                        <button type="submit" class="bg-red-500 text-white px-3 py-1 text-sm rounded hover:bg-red-600">
                          Resign
                        </button>
                      </form>
                      @else
                      <span class="text-gray-500 text-sm">aktif</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>

            @push('scripts')
    @if(session('success'))
    @endif
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        function ShowModalKaryawan(){
            showDetailOnModal(`{{ route('karyawan.create') }}`);
        }
    
        $(document).ready(function () {
            $('#supplier-table').DataTable();
        });
    
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil lurrr!',
                text: '{{ session("success")}}',
                confirmButtonText: 'OK'
            });
        @endif
    </script>
    @endpush
    
               
</x-app-layout>


