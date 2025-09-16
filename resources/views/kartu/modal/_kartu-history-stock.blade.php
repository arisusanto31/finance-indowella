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
                        <td>{{ $data->description }}</td>
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
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>


<script>
  
    function recalculate(id) {
        // Call your API or perform your calculation logic here
        console.log("Recalculating for ID:", id);
        swalConfirmAndSubmit({
            url: '{{ url('admin/kartu/$model/recalculate') }}',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
            onSuccess: function(response) {
                // Handle success response
                console.log("Recalculation successful:", response);
                // Optionally, you can refresh the modal or update the UI
            },
        });
    }
</script>
