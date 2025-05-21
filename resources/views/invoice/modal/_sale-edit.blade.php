<div class="modal-header">
    <h5 class="modal-title">
        Edit Sales Order - {{ $data->customer->name }}
        <span class="fs-8 px-2 rounded-1 bg-primary text-white">
            status: {{ $data->status }}
        </span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <form id="form-edit-detail">
        @csrf

        <div class="mb-3">
            <label for="sales_order_number" class="form-label">Nomor Sales Order</label>
            <input type="text" name="sales_order_number" class="form-control" value="{{ $data->sales_order_number }}">
            <input type="hidden" name="sales_order_id" value="{{ $data->id }}">
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="bg-white text-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Barang</th>
                        <th>Unit</th>
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
                        <td>
                            <input type="date" name="tanggal[{{ $key }}]"
                                   value="{{ $item->created_at->format('Y-m-d') }}"
                                   class="form-control form-control-sm text-end">
                        </td>
                        
                        <td>{{ $item->stock->name }}</td>

                        <td>
                            <select name="unit[{{ $key }}]" class="form-control form-control-sm">
                                @foreach ($item->stock->units as $u)
                                    <option value="{{ $u->unit }}" {{ $u->unit == $item->unit ? 'selected' : '' }}>
                                        {{ ucfirst($u->unit) }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                
                        <input type="hidden" name="detail_id[{{ $key }}]" value="{{ $item->id }}">

                        <td>
                            <input type="number" name="quantity[{{ $key }}]" class="form-control form-control-sm text-end qty"
                                value="{{ $item->quantity }}">
                        </td>
                        <td>
                            <input type="number" name="price[{{ $key }}]" class="form-control form-control-sm text-end price"
                                value="{{ $item->price }}">
                        </td>
                        <td>
                            <input type="number" name="discount[{{ $key }}]" class="form-control form-control-sm text-end disc"
                                value="{{ $item->discount }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm text-end bg-light border-0 total-field"
                                value="{{ number_format(($item->quantity * $item->price) - $item->discount, 2, ',', '.') }}"
                                readonly>
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

<script>
$(document).on('input', '.qty, .price, .disc', function () {
    const row = $(this).closest('tr');
    const qty = parseFloat(row.find('.qty').val()) || 0;
    const price = parseFloat(row.find('.price').val()) || 0;
    const disc = parseFloat(row.find('.disc').val()) || 0;
    const total = (qty * price) - disc;

    row.find('.total-field').val(
        total.toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })
    );
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
