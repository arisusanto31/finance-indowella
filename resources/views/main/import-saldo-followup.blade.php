<x-app-layout>

    <div class="card mt-3 rounded-3 mb-3">
        <h5 class="text-primary-dark card-header"> 🔎 <strong> Status Import {{ $task->description }}
                [{{ $task->id }}] </strong> </h5>
        <div class="card-body">

            <p>Saldo Neraca Lajur
                @if (collect($details['saldo_nl'])->where('status', 'success')->count() == 0)
                    <br>
                    <button class="bg-primary btn text-white" onclick="sendJurnal('{{ $task->id }}')"> <i
                            class="fas fa-arrow-up"></i> SEND </button>
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
                                <td> {{ $data->id }} </td>
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
                                <td> {{ $data->id }} </td>
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
                                <td id="status-kartu_stock-{{ $data->id }}">
                                    <span class="badge {{ bgStatus($data->status) }}">{{ $data->status }}</span>
                                    @if ($data->status == 'failed')
                                        <br>
                                        {{ $data->error_message }}
                                    @elseif($data->status == 'success')
                                        <br>{{ $data->journal_number }}
                                    @endif
                                </td>
                                <td id="action-kartu_stock-{{ $data->id }}">
                                    @if ($data->status != 'success')
                                        <input type="hidden" id="import-notyet{{ $data->id }}"
                                            class="import-notyet" value="{{ $data->id }}" />
                                        <button type="button" class="btn btn-primary btn-sm" title="resend data"
                                            onclick="resendData('{{ $data->id }}')"> <i
                                                class="fas fa-arrow-up"></i></button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @php
                $titles = ['Data Hutang', 'Data Inventaris', 'Data Prepaid'];
                $keys = ['kartu_hutang', 'kartu_inventaris', 'kartu_prepaid'];
            @endphp

            @foreach ($titles as $index => $title)
                <p class="mt-2">{{ $title }}</p>
                @php
                    $datatable = $details[$keys[$index]];
                    $payload = $datatable[0] ? $datatable[0]->payload : null;
                    $payloadArr = $payload ? json_decode($payload, true) : [];
                    $allth = collect($payloadArr)->keys();
                    $allthfix = ['No', 'Task ID', ...$allth->toArray(), 'Status', 'Action'];
                @endphp
                <div class="table-responsive">
                    <table id="" class="table table table-bordered table-striped table-hover align-middle">
                        <thead class="bg-white text-dark text-center">
                            <tr>
                                @foreach ($allthfix as $th)
                                    <th>{{ $th }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="body-import-saldo">
                            @foreach ($datatable as $key => $task)
                                @php
                                    $item = json_decode($task->payload, true);
                                @endphp
                                <tr>
                                    @foreach ($allthfix as $th)
                                        @if ($th == 'No')
                                            <td>{{ $key + 1 }}

                                                <input type="hidden" class="bdd" value="{{ $key }}" />
                                            </td>
                                        @elseif($th == 'Task ID')
                                            <td>{{ $task->id }}</td>
                                        @elseif($th == 'Status')
                                            <td id="status-{{ $keys[$index] }}-{{ $task->id }}">
                                                <span
                                                    class="badge {{ bgStatus($task->status) }}">{{ $task->status }}</span>
                                                @if ($task->status == 'failed')
                                                    <br>
                                                    {{ $task->error_message }}
                                                @elseif($task->status == 'success')
                                                    <br>
                                                    {{ $task->journal_number }}
                                                @endif
                                            </td>
                                        @elseif($th == 'Action')
                                            <td id="action-{{ $keys[$index] }}-{{ $task->id }}">
                                                @if ($task->status != 'success')
                                                    <input type="hidden" id="import-notyet{{ $task->id }}"
                                                        class="import-notyet" value="{{ $task->id }}" />
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                        title="resend data"
                                                        onclick="resendData('{{ $task->id }}')"> <i
                                                            class="fas fa-arrow-up"></i></button>
                                                @endif

                                            </td>
                                        @else
                                            <td>{{ $item[$th] }}
                                                <input class="bdd-data{{ $key }}"
                                                    id="bdd-{{ $th }}-{{ $key }}" type="hidden"
                                                    value="{{ $item[$th] }}" />
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

        </div>
    </div>


    @push('scripts')
        <script>
            function resendData(id, notify = true) {

                return new Promise((resolve) => {
                    $.ajax({
                        url: '{{ url('admin/jurnal/resend-import-task') }}/' + id,
                        method: 'get',
                        success: function(res) {
                            console.log(res);
                            if (res.status == 1) {
                                task = res.task;
                                $('#status-' + task.type + '-' + id).html(
                                    '<span class="badge bg-success">success</span>');
                                $('#action-' + task.type + '-' + id).html('');

                                if (notify) {
                                    notification('success', 'Data Task ID: ' + id + ' berhasil diproses');
                                }
                                resolve(res);
                            } else {
                                task = res.task;
                                notification('error', res.msg);
                                $('#status-' + task.type + '-' + id).html(
                                    '<span class="badge bg-danger">failed</span> <br>' +
                                    res.msg);
                                resolve(res);
                            }
                        },
                        error: function(res) {
                            notification('error', 'Terjadi Kesalahan pada server')
                            resolve({
                                status: 0
                            });

                        }

                    });
                });
            }

            function resendAll() {
                swalQuestion({
                    proses: function() {
                        total = $('.import-notyet').length;
                        count = 0;
                        $('.import-notyet').each(async function(i, elem) {
                            count++;
                            id = $(elem).val();
                            res = await resendData(id, notify = false);
                            if (res['status'] == 1) {
                                $('#import-notyet' + id).remove();
                            }
                            showProgressBar(count, total);
                        });
                        setTimeout(() => {
                            hideProgressBar();
                        }, 2000);
                    }
                });
                // taskID = '{{ $task->id }}';
                // $.ajax({
                //     url: '{{ url('admin/jurnal/resend-import-task-all') }}/' + taskID,
                //     method: 'get',
                //     success: function(res) {
                //         console.log(res);
                //         if (res.status == 1) {
                //             Swal.fire('Success', 'Data Taks ' + taskID + ' berhasil di kirim ulang', 'success');
                //         } else {
                //             Swal.fire('Error', 'Data gagal di kirim ulang', 'error');
                //         }
                //     },
                // })
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
                            Swal.fire('Error', res.msg, 'error');
                        }
                    },
                });
            }
        </script>
    @endpush
</x-app-layout>
