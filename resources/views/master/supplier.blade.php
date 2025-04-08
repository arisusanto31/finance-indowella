<x-app-layout>
    @push('styles')
        {{-- <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet"> --}}
    @endpush

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <a href="#" onclick="ShowModalSupplier()" class="btn btn-primary btn-big-custom rounded-0">Create Supplier</a>
    </div>
    {{-- <pre>{{ dd($suppliers) }}</pre> --}}

    <table id="supplier-table" class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>No HP</th>
                <th>KTP</th>
                <th>NPWP</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $key => $supplier)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $supplier->created_at ? $supplier->created_at->format('Y-m-d H:i') : '-' }}</td>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->address }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td>{{ $supplier->ktp }}</td>
                    <td>{{ $supplier->npwp }}</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" class="btn btn-sm btn-danger">Delete</a>
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
            function ShowModalSupplier(){
                showDetailOnModal("{{ route('supplier.main.create') }}");
            }

            $(document).ready(function () {
                $('#supplier-table').DataTable();
            });

            Swal.fire({
            icon: 'success',
            title: 'Berhasil lurrr!',
            text: '{{ session("success")}}',
            confirmButtonText: 'OK'
        });
        </script>
    @endpush
   
</x-app-layout>
