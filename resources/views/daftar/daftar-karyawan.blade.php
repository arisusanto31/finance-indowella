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
                <th>No</th>
                <th>Tanggal</th>
                <th>Nik</th>
                <th>Nama</th>
                <th>Npwp</th>
                <th>Jabatan</th>
                <th>Tanggal Masuk</th>
                <th>Tanggal Keluar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>

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


