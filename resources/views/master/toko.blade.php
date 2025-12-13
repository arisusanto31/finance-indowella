<x-app-layout>


    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 🏖️ <strong>TOKO</strong> </h5>

        <div class="card-body">
            <div class="row mt-1">
                <div class="col-md-2">
                    <a href="#" onclick="showModalToko()" class="btn btn-primary btn-big-custom rounded-0">Create
                        Toko</a>
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
                        <th>Default akun kas </th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tokoes as $key => $toko)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $toko->name }}
                                @if (user()->can('edit_data_journal'))
                                    @if ($toko->getParents()->count() > 0)
                                        @foreach($toko->getParents() as $parent)
                                        <br>
                                        <small class="text-primary-dark">
                                            <i class="fas fa-link"></i> {{ $parent->name ?? '??' }}
                                        </small>
                                        @endforeach
                                    @endif
                                @endif
                            </td>
                            <td>{{ $toko->phone }}</td>
                            <td>{{ $toko->address }}</td>
                            <td>
                                <select class="form-control" onchange="changeCodeKas('{{ $toko->id }}')" id="code-kas-{{ $toko->id }}"> 
                                    @foreach($codeGroups as $codeGroup)
                                        <option value="{{ $codeGroup->code_group }}" {{ $toko->default_code_group_kas == $codeGroup->code_group ? 'selected' : '' }}>
                                            {{ $codeGroup->code_group }} - {{ $codeGroup->name }}
                                        </option>
                                    @endforeach
                                </select >
                            </td>
                            <td>
                                <a href="javascript:void(showDetailOnModal(`{{ route('toko.main.edit', $toko->id) }}`))"
                                    class="btn btn-success btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if (user()->can('edit_data_journal'))
                                    <button type="button" class="btn btn-info btn-sm"
                                        onclick="linkMasterToko('{{ $toko->id }}')">
                                        <i class="fas fa-link"></i>
                                    </button>
                                @endif
                                <button type="button" onclick="deleteToko('{{ $toko->id }}')"
                                    class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        @if (session('success'))
        @endif
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            function showModalToko() {
                showDetailOnModal("{{ route('toko.main.create') }}");
            }

            function deleteToko(id) {
                swalDelete('{{ url('admin/master/toko/main/destroy') }}/' + id);
            }

            function changeCodeKas(id){
                let selectedCode= $('#code-kas-'+id+' option:selected').val();
                console.log('change code kas toko id='+id+' to '+selectedCode);
                $.post('{{ route('toko.change-code-kas') }}',{
                    _token: '{{ csrf_token() }}',
                    toko_id: id,
                    code_group_kas: selectedCode
                }, function(res){
                    console.log(res);
                    if(res.status==1){
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil lurrr!',
                            text: 'default akun kas berhasil diubah',
                            confirmButtonText: 'OK'
                        });
                    }else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Ada error lur !😢 ',
                            text: res.msg,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }

            function linkMasterToko(id) {

                asset = '{{ asset('') }}';
                Swal.fire({
                    title: 'Link dengan master toko',
                    showCancelButton: true,
                    html: `
                            <div class=" mb-4" id="swal-link-toko">
                                  <div class="row">
                                    <div class="col-xs-12 mb-3">
                                        <label>Parent Type </label>
                                        <div>
                                            <select id="select-parent-type" onchange="changeParentType()" class="form-control">
                                                <option value="manuf">Manuf Toko</option>
                                                <option value="retail">Retail Toko</option>
                                            </select>
                                        </div>
                                      </div>
                                      <div class="col-xs-12 mb-3">
                                        <label>Parent ID </label>
                                        <div>
                                            <select id="option-parent-id" class="form-control">
                                            </select>
                                        </div>
                                    </div>
                                  </div>
                                
                            </div>
                           `,
                    focusConfirm: false,
                    preConfirm: () => {
                        return {
                            parent_id: document.getElementById('option-parent-id').value,
                            parent_type: document.getElementById('select-parent-type').value,
                            toko_id: id,
                            _token: '{{ csrf_token() }}'
                        }
                    }

                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log(result.value);
                        swalConfirmAndSubmit({
                            url: '{{ route('toko.make-link-parent') }}',
                            data: result.value,
                        });
                    }
                });

            }

            function changeParentType() {

                let parentType = $('#select-parent-type option:selected').val();
                console.log('change parent type = ' + parentType);
                initItemSelectManual('#option-parent-id', '{{ route('toko.get-parent-option') }}?parent_type=' + parentType,
                    'Pilih Parent ID', '#swal-link-toko');
            }

            $(document).ready(function() {
                $('#toko-table').DataTable();
            });


            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil lurrr!',
                    text: '{{ session('success') }}',
                    confirmButtonText: 'OK'
                });
            @elseif (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Ada error lur !😢 ',
                    text: '{{ session('error') }}',
                    confirmButtonText: 'OK'
                });
            @endif
        </script>
    @endpush


</x-app-layout>
