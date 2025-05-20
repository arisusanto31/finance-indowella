<div class="modal-header">
    <h5 class="modal-title">
        Edit Sales Order {{ $data->sales_order_number }} - {{ $data->customer->name }}
        <span class="fs-8 px-2 rounded-1 bg-primary text-white">
            status: {{ $data->status }}
        </span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <form id="form-edit-detail">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="bg-white text-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No Tr</th>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Diskon</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['details'] as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->created_at->format('Y-m-d') }}</td>
                    
                       
                        <input type="hidden" name="detail_id[{{ $key }}]" value="{{ $item->id }}">
                    
                        <td>
                            <input type="text" name="sales_order_number[{{ $key }}]" value="{{ $data->sales_order_number }}">
                        </td>
                        <td>{{ $item->stock->name }}</td>
                        <td>
                            <input type="number" name="quantity[{{ $key }}]" value="{{ $item->quantity }}">
                        </td>
                        <td>
                            <input type="number" name="price[{{ $key }}]" value="{{ $item->price }}">
                        </td>
                        <td>
                            <input type="number" name="discount[{{ $key }}]" value="{{ $item->discount }}">
                        </td>
                        <td>
                            <input type="text" class="total-field" value="{{ format_price(($item->quantity * $item->price) - $item->discount) }}" readonly>
                        </td>
                    </tr>
                    @endforeach
                    
                </tbody>
            </table>
        </div>
    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
    <button type="submit" form="form-edit-detail" class="btn btn-primary">Simpan Perubahan</button>
</div>
@push('scripts')
<script>


$(document).on('input', 'input[name^="quantity"], input[name^="price"], input[name^="discount"]', function() {
    const row = $(this).closest('tr');
    const qty = parseFloat(row.find('input[name^="quantity"]').val()) || 0;
    const price = parseFloat(row.find('input[name^="price"]').val()) || 0;
    const disc = parseFloat(row.find('input[name^="discount"]').val()) || 0;

    const total = (qty * price) - disc;
    row.find('.total-field').val(total.toLocaleString('id-ID'));
});



$('#form-edit-detail').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
    url: '{{ url("admin/invoice/invoice/update-detail") }}',
    method: 'POST',
    data: $(this).serialize(),
    success: function(res) {
        alert('Berhasil disimpan');
        $('#editModal').modal('hide');
        location.reload();
    },
    error: function() {
        alert('Gagal menyimpan');
    }
});

});



</script>


