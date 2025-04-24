<x-app-layout>
    <form method="POST" action="{{ route('karyawan.store') }}">
@csrf
    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;">  <strong></strong> </h5>

        <div class="container py-4 p-3 mb-4 card shadow-sm">
            <h2>Daftar Karyawan</h2>

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
                    <th class="border px-4 py-2">NPWP</th>
                    <th class="border px-4 py-2">Jabatan</th>
                    <th class="border px-4 py-2">Tanggal Masuk</th>
                    <th class="border px-4 py-2">Status</th>
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
                    @if (!$karyawan->date_keluar || $karyawan->date_keluar === '0000-00-00')
                        <div class="text-green-600 font-semibold text-sm">Aktif</div>
                        @else
                        <span class="text-sm text-gray-500">Keluar ({{ $karyawan->date_keluar }})</span>
                        @endif
                        </td>
  
                     <td class="border px-4 py-2 space-x-1">
    

                        <a href="javascript:void(0);"
                           onclick="showDetailOnModal('{{ url('admin/daftar/karyawan/edit') }}/{{ $karyawan->id }}', 'xl')"
                             class="btn btn-success btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            <form action="{{ route('karyawans.destroy', $karyawan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            
                          
                        
                            
  
                   @if (!$karyawan->date_keluar || $karyawan->date_keluar === '0000-00-00')
                        <form action="{{ route('karyawans.resign', $karyawan->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-warning btn-sm text-white" title="Resign">
                        Resign
                    </button>
                </form>
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


