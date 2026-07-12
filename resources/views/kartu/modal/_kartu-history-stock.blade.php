<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Mutasi Stock {{ $title }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">

    <div class="table-responsive mt-2">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Qty Debet</th>
                    <th>Rupiah Debet</th>
                    <th>Qty Kredit</th>
                    <th>Rupiah Kredit</th>
                    <th>Qty Saldo</th>
                    <th>Rupiah Saldo</th>
                    <th>Journal Number</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $data)
                <tr>
                    <td>{{ $data->created_at }}</td>
                    <td>[uid:{{$data->id}}]<br>{{ $data->description }}</td>
                    <td class="text-success">
                        @if ($data->qty_debet != 0)
                        {{ format_price($data->qty_debet) }} {{ $data->unit }}
                        @else
                        -
                        @endif
                    </td>
                    <td class="text-success">{{ format_price($data->rupiah_debet) }}</td>
                    <td class="text-danger">
                        @if($data->qty_kredit!=0)
                        {{ format_price($data->qty_kredit) }} {{ $data->unit }}
                        @else
                        -
                        @endif
                    </td>
                    <td class="text-danger">{{ format_price($data->rupiah_kredit) }}</td>
                    <td>{{ format_price($data->qty_saldo) }} {{ $data->unit }}</td>
                    <td>{{ format_price($data->rupiah_saldo) }}</td>
                    <td>{{ $data->journal_number }}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="recalculate('{{ $data->id }}')">
                            <i class="fas fa-refresh"></i> recalculate
                        </button>

                        <button class="btn btn-primary btn-sm mt-2" onclick="reEvaluateHPP('{{ $data->id }}')">
                            <i class="fas fa-coins"></i> re-evaluate HPP
                        </button>

                        <button class="btn btn-danger btn-sm mt-2" onclick="pindahkan('{{ $data->id }}')">
                            <i class="fas fa-money-bill-wave"></i> Pindahkan
                        </button>

                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">Total</th>
                    <th class="text-success">{{ format_price($datas->sum('qty_debet')) }} {{ $data->unit }}</th>
                    <th class="text-success">{{ format_price($datas->sum('rupiah_debet')) }}</th>
                    <th class="text-danger">{{ format_price($datas->sum('qty_kredit')) }} {{ $data->unit }}</th>
                    <th class="text-danger">{{ format_price($datas->sum('rupiah_kredit')) }}</th>
                    <th>{{ format_price($datas->last()->qty_saldo) }} {{ $data->unit }}</th>
                    <th>{{ format_price($datas->last()->rupiah_saldo) }}</th>
                    <th></th>
                    <th>
                        @if(($model=='kartu-bdp')&& $datas->last()->qty_saldo > 0)
                        <button onclick="bebankan('{{ $productionNumber }}', '{{ $stockId }}')">bebankan</button>
                        @endif
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>


<script>
    function bebankan(productionNumber, stockId) {
        swalConfirmAndSubmit({
            url: '{{ url("admin/kartu/".$model."/bebankan") }}',
            data: {
                _token: '{{ csrf_token() }}',
                production_number: productionNumber,
                stock_id: stockId
            },
            onSuccess: function(response) {
                // Handle success response
                console.log("Bebankan successful:", response);
                if (response.status == 1) {

                } else {

                }
                // Optionally, you can refresh the modal or update the UI
            },
        });

    }

    accountPersediaan = <?php echo json_encode($accountPersediaan ?? []); ?>;

    function pindahkan(kartuId) {

        const oldEnforceFocus = $.fn.modal.Constructor.prototype._enforceFocus;

        $.fn.modal.Constructor.prototype._enforceFocus = function() {};
        console.log('version modal',$.fn.modal.Constructor.VERSION)
        console.log('version swal',Swal.version)
        const modal = bootstrap.Modal.getInstance(document.getElementById('global-modal'));

        modal._focustrap.deactivate();

        Swal.fire({
            title: 'Pindahkan stock',
            html: `
                    <input id="swal-date" class="swal2-input" type="datetime-local" placeholder="Tanggal">
                    <select id="swal-account" class="swal2-select">
                        <option value="">Pilih Akun</option>
                        ${collect(accountPersediaan).map((account,key) => `<option value="${account.code_group}">${account.name}</option>`).join('')}
                    </select>
                `,
            didClose: () => {
                $.fn.modal.Constructor.prototype._enforceFocus = oldEnforceFocus;
            },
            showCancelButton: true,
            confirmButtonText: 'Pindahkan',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            focusConfirm: false,
            preConfirm: () => {
                return {
                    date: document.getElementById('swal-date').value,
                    account_code: document.getElementById('swal-account').value
                };
            }
        }).then((result) => {
             modal._focustrap.activate();
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("admin/kartu/".$model."/pindahkan") }}',
                    method: 'post',
                    data: {
                        _token: '{{ csrf_token() }}',
                        kartu_id: kartuId,
                        account_code: result.value.account_code,
                        date: result.value.date
                    },
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            notification('success', 'pindah akun berhasil disimpan');
                            refreshIsiModal();
                        } else {
                            notification('error', 'pindah akun gagal disimpan');
                        }
                    }
                });
            }
        });
    }

    function recalculate(id) {
        // Call your API or perform your calculation logic here
        console.log("Recalculating for ID:", id);
        swalConfirmAndSubmit({
            url: '{{ url("admin/kartu/".$model."/recalculate") }}',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
            onSuccess: function(response) {
                // Handle success response
                console.log("Recalculation successful:", response);
                notification('success', 'Recalculation successful', 'success');
                // Optionally, you can refresh the modal or update the UI
            },
        });
    }

    function reEvaluateHPP(id) {
        // Call your API or perform your calculation logic here
        console.log("Re-evaluating HPP for ID:", id);
        swalConfirmAndSubmit({
            url: '{{ url("admin/kartu/".$model."/re-evaluate-hpp") }}',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
            onSuccess: function(res) {
                // Handle success response
                console.log("Re-evaluation of HPP successful:", res);
                if (res.status == 1) {

                    notification('success', 'Re-evaluation of HPP successful', 'success');
                } else {
                    notification('error', 'Re-evaluation of HPP failed', 'error');
                }
                // Optionally, you can refresh the modal or update the UI
            },
        });
    }
</script>