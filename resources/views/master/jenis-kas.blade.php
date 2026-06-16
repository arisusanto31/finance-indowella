<x-app-layout>


    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 💰 <strong>Jenis Kas</strong> </h5>

        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>

            <table id="toko-table" class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Jenis Kas sales</th>
                        <th>Kode Akun</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cashkinds as $name => $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $name }}</td>
                        <td> 

                          
                            <select class="form-control select-coa-kas" onchange="linkJenisKas('{{ $name }}', this.value)">
                                @if($data)
                                    <option value="{{ $data->code_group }}" selected>{{ $data->account_name }}</option>
                                @endif
                            </select>


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
        initItemSelectManual('.select-coa-kas', '{{route("chart-account.get-item-keuangan")}}?kind=kas', 'pilih akun kas');

        function linkJenisKas(name, code_group) {
            $.ajax({
                url: '{{route("jenis-kas.link")}}',
                method: 'post',
                data: {
                    name: name,
                    code_group: code_group,
                    _token: '{{csrf_token()}}'
                },
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        notification('success', 'link berhasil disimpan');

                    } else {
                        notification('error', 'link gagal disimpan');
                    }
                },
                error: function() {
                    notification('error', 'something error');
                }
            });
        }
    </script>
    @endpush


</x-app-layout>