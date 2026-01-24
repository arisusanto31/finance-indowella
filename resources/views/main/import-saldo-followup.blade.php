<x-app-layout>

    <div class="card mt-3 rounded-3 mb-3">
        <h5 class="text-primary-dark card-header"> 🔎 <strong> Status Import {{ $task->description }}
                [{{ $task->id }}] </strong> </h5>
        <div class="card-body">

            <p>Saldo Neraca Lajur
                @if(collect($details['saldo_nl'])->where('status','success')->count()==0)
                <br>
                <button class="bg-primary btn text-white" onclick="sendJurnal('{{ $task->id }}')"> <i class="fas fa-arrow-up"></i> SEND </button>
                @endif
            </p>

            <div class="table-responsive">
                <table id="" class="table table table-bordered table-striped table-hover align-middle">
                    <thead class="bg-white text-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>TaskID </th>
                            <th>Kode COA </th>
                            <th>Nama COA </th>
                            <th>Nilai Saldo</th>
                            <th>Status</th>
                            {{-- <th>Action</th> --}}
                        </tr>
                    </thead>
                    <tbody id="body-import-saldo">
                        @foreach ($details['saldo_nl'] as $key => $data)
                            @php
                                $item = json_decode($data->payload, true);
                            @endphp
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td> {{$data->id}} </td>
                                <td>{{ $item['code_group'] }}
                                </td>
                                <td>{{ $item['name'] }}
                                </td>
                                <td>{{ format_price($item['amount']) }}
                                </td>
                                <td>
                                    <span class="badge {{ bgStatus($data->status) }}">{{ $data->status }}</span>
                                    @if ($data->status == 'failed')
                                        <br>
                                        {{ $data->error_message }}
                                    @elseif($data->status == 'success')
                                        <br>
                                        {{ $data->journal_number }}
                                    @endif
                                </td>
                                {{-- <td> --}}
                                {{-- @if ($data->status != 'success')
                                        <button type="button" class="btn btn-primary btn-sm" title="resend data"
                                            onclick="resendData('{{ $data->id }}')"> <i
                                                class="fas fa-arrow-up"></i></button>
                                    @endif --}}
                                {{-- </td> --}}
                            </tr>
                        @endforeach
                    </tbody>


                </table>

            </div>

            <p class="mt-2">Data Saldo Stock
                <br>
                <button class="bg-primary btn  text-white" onclick="resendAll()"><i class="fas fa-arrow-up"></i> resend
                    All</button>
            </p>


            <div class="table-responsive">
                <table id="" class="table table table-bordered table-striped table-hover align-middle">
                    <thead class="bg-white text-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>TaskID </th>
                            <th>Ref ID </th>
                            <th>Nama Stock </th>
                            <th>Saldo Qty </th>
                            <th>Satuan</th>
                            <th>Nilai Saldo</th>
                            <th>Status </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="body-import-saldo">
                        @foreach ($details['kartu_stock'] as $key => $data)
                            @php
                                $item = json_decode($data->payload, true);
                            @endphp
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td> {{$data->id}} </td>
                                <td>{{ $item['ref_id'] }}
                                </td>
                            
                                <td>{{ $item['name'] }}
                                </td>
                                <td>{{ $item['quantity'] }}
                                </td>
                                <td>{{ $item['unit'] }}
                                </td>

                                <td>{{ format_price($item['amount']) }}
                                </td>
                                <td>
                                    <span class="badge {{ bgStatus($data->status) }}">{{ $data->status }}</span>
                                    @if ($data->status == 'failed')
                                        <br>
                                        {{ $data->error_message }}
                                    @elseif($data->status == 'success')
                                        <br>{{ $data->journal_number }}
                                    @endif
                                </td>
                                <td>
                                    @if ($data->status != 'success')
                                        <button type="button" class="btn btn-primary btn-sm" title="resend data"
                                            onclick="resendData('{{ $data->id }}')"> <i
                                                class="fas fa-arrow-up"></i></button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>


                </table>

                <!-- <div class="row">
            <div class="col-xs-12">
            </div>
          </div> -->
            </div>

        </div>
    </div>


    @push('scripts')
        <script>
            function resendData(id) {
                $.ajax({
                    url: '{{ url('admin/jurnal/resend-import-task') }}/' + id,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            Swal.fire('Success', 'Data berhasil di kirim ulang', 'success');
                        } else {

                        }
                    },
                    error: function(res) {}
                });
            }

            function resendAll() {
                taskID = '{{ $task->id }}';
                $.ajax({
                    url: '{{ url('admin/jurnal/resend-import-task-all') }}/' + taskID,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            Swal.fire('Success', 'Data Taks ' + taskID + ' berhasil di kirim ulang', 'success');
                        } else {
                            Swal.fire('Error', 'Data gagal di kirim ulang', 'error');
                        }
                    },
                })
            }

            function sendJurnal(taskID) {
                loading(1);
                $.ajax({
                    url: '{{ url('admin/jurnal/send-import-task-jurnal') }}/' + taskID,
                    method: 'get',
                    success: function(res) {
                        loading(0);
                        console.log(res);
                        if (res.status == 1) {
                            Swal.fire('Success', 'Data Taks ' + taskID + ' berhasil di proses kirim jurnal',
                                'success');
                        } else {
                            Swal.fire('Error', 'Data gagal di proses kirim jurnal', 'error');
                        }
                    },
                });
            }
        </script>
    @endpush
</x-app-layout>
