<x-app-layout>
    {{-- @push('styles')
        {{-- <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet"> --}}
    {{-- @endpush --}}

    {{-- <div class="d-flex flex-wrap justify-content-between align-items-center mb-3"> --}}
    {{-- <a href="#" onclick="ShowModalSupplier()" class="btn btn-primary btn-big-custom rounded-0">Create Supplier</a>
    </div> --}}
    {{-- <pre>{{ dd($suppliers) }}</pre> --}}


    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 🦸 <strong>Create Supplier</strong> </h5>

        <div class="card-body">
            <div class="row mt-1">
                <div class="col-md-2">
                    <a href="#" onclick="ShowModalSupplier()" class="btn btn-primary btn-big-custom rounded-0">Create Supplier</a>
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
                        <td>{{ $supplier->created_at ? $supplier->created_at->format('Y-m-d H:') : '-' }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->address }}</td>
                        <td>{{ $supplier->phone }}</td>
                        <td>{{ $supplier->ktp }}</td>
                        <td>{{ $supplier->npwp }}</td>
                        <td>
                            <a href="{{ route('supplier.main.edit', $supplier->id) }}" class="btn btn-success btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <button type="button" onclick="deleteSupplier('{{$supplier->id}}')" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
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
                function ShowModalSupplier() {
                    showDetailOnModal("{{ route('supplier.main.create') }}");
                }

                function deleteSupplier(id) {
                    swalDelete('{{url("admin/master/supplier/main/destroy")}}/' + id);
                }

                $(document).ready(function() {
                    $('#supplier-table').DataTable();
                });

                
                @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil lurrr!',
                    text: '{{ session("success")}}',
                    confirmButtonText: 'OK'
                });
                @elseif(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Ada error lur !😢 ',
                    text: '{{ session("error")}}',
                    confirmButtonText: 'OK'
                });
                @endif

              
            </script>
            @endpush


</x-app-layout>