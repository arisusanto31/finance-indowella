<x-app-layout>


    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 🏖️ <strong>TOKO</strong> </h5>

        <div class="card-body">
            <div class="row mt-1">
                <div class="col-md-2">
                    <a href="#" onclick="showModalToko()" class="btn btn-primary btn-big-custom rounded-0">Create Toko</a>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>

            <table id="toko-table" class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>No HP</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tokoes as $key => $toko)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $toko->name }}</td>
                        <td>{{ $toko->phone }}</td>
                        <td>{{ $toko->address }}</td>
                        <td>
                            <a href="javascript:void(showDetailOnModal(`{{route('toko.main.edit', $toko->id)}}`))" class="btn btn-success btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <button type="button" onclick="deleteToko('{{$toko->id}}')" class="btn btn-danger btn-sm" title="Hapus">
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
                function showModalToko() {
                    showDetailOnModal("{{ route('toko.main.create') }}");
                }

                function deleteToko(id) {
                    swalDelete('{{url("admin/master/toko/main/destroy")}}/' + id);
                }

                $(document).ready(function() {
                    $('#toko-table').DataTable();
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