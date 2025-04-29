<x-app-layout>
    {{-- @push('styles')
        {{-- <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet"> --}}
    {{-- @endpush --}}

    {{-- <div class="d-flex flex-wrap justify-content-between align-items-center mb-3"> --}}
    {{-- <a href="#" onclick="ShowModalSupplier()" class="btn btn-primary btn-big-custom rounded-0">Create Supplier</a>
    </div> --}}
    {{-- <pre>{{ dd($suppliers) }}</pre> --}}


    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 🦸 <strong>Other-Person ya L</strong> </h5>

        <div class="card-body mt-2">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="row mt-1">
                <div class="col-md-2">
                    <a href="#" onclick="ShowModalOtherPerson()"
                        class="btn btn-primary btn-big-custom rounded-0">Create Other-Person</a>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>

            <table id="other-table" class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Lengkap</th>
                        <th>Alamat</th>
                        <th>No HP</th>
                        <th>aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($otherPersons as $key => $otherPerson)

                    <tr>
                        <td>{{ $key + 1 }}</td> 
                        <td>{{ $otherPerson->created_at ? $otherPerson->created_at->format('Y-m-d H:i') : '-' }}</td> <!-- Tanggal -->
                        <td>{{ $otherPerson->name }}</td> <
                        <td>{{ $otherPerson->address }}</td> 
                        <td>{{ $otherPerson->phone }}</td> 
                        <td>
                            <a href="javascript:void(showDetailOnModal(`{{ route('other-person.main.edit', $otherPerson->id) }}`))" class="btn btn-success btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
        
                        </td>
                        
                        
                    </tr>
                @endforeach
                </tbody>
            </table>

            @push('scripts')
                @if (session('success'))
                @endif
                <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                <script>
                    function ShowModalOtherPerson() {
                        showDetailOnModal("{{ route('other-person.main.create') }}");


                    }

                    function deleteSupplier(id) {
                        swalDelete('{{ url('admin/master/supplier/main/destroy') }}/' + id);
                    }

                    $(document).ready(function() {
                        $('#supplier-table').DataTable();
                    });


                    @if (session('success'))
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil lurrr!',
                            text: '{{ session('success') }}',
                            confirmButtonText: 'OK'
                        });
                    @elseif (session('error'))
                       
                        $(document).ready(function() {
                            $('other-table').modal('show'); 
                        });

                </script>
            @elseif(session('error'))
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Ada error lur 😢',
                        text: '{{ session('error') }}',
                        confirmButtonText: 'OK'
                    });
                    @endif
                </script>
            @endpush


</x-app-layout>
