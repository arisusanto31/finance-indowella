<form id="form-edit-detail">
    @csrf

    <div class="modal-header flex-column align-items-start">
        <div class="w-100 d-flex justify-content-between flex-wrap align-items-center gap-2">

            <div class="d-flex align-items-center gap-2">
                <h5 class="modal-title mb-0">
                    Edit Sales Order - {{ $data->customer->name }}
                </h5>
                <span class="fs-8 px-2 rounded-1 bg-success text-white">
                    status: {{ $data->status }}
                </span>
            </div>


            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex align-items-center gap-1">
                    <label class="small text-muted mb-0">Tanggal:</label>
                    <input type="date" name="tanggal_global" value="{{ $data->created_at->format('Y-m-d') }}">
                </div>

                <div class="d-flex align-items-center gap-1">
                    <label for="sales_order_number" class="form-label mb-0">Nomor SO:</label>
                    <input type="text" name="sales_order_number" class="form-control form-control-sm w-auto"
                        value="{{ $data->sales_order_number }}">
                    <input type="hidden" name="sales_order_id" value="{{ $data->id }}">
                </div>
            </div>
        </div>

        <button type="button" class="btn-close position-absolute end-0 top-0 m-2" data-bs-dismiss="modal"
            aria-label="Close"></button>
    </div>


    <div class="modal-body">
        {{-- <form id="form-edit-detail">
        @csrf

        <div class="mb-3">
            <label for="sales_order_number" class="form-label">Nomor Sales Order</label>
            <input type="text" name="sales_order_number" class="form-control" value="{{ $data->sales_order_number }}">
            <input type="hidden" name="sales_order_id" value="{{ $data->id }}">
        </div> --}}

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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['details'] as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td class="text-center">
                                {{ $data->created_at instanceof \Carbon\Carbon ? $data->created_at->format('Y-m-d') : '-' }}
                            </td>

                            <td>{{ $item->stock->name }}</td>

                            <td>
                                <select name="unit[{{ $key }}]" class="form-control form-control-sm">
                                    @foreach ($item->stock->units as $u)
                                        <option value="{{ $u->unit }}"
                                            {{ $u->unit == $item->unit ? 'selected' : '' }}>
                                            {{ ucfirst($u->unit) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <input type="hidden" name="detail_id[{{ $key }}]" value="{{ $item->id }}">

                            <td>
                                <input type="text" name="quantity[{{ $key }}]"
                                    class="form-control form-control-sm text-end qty" value="{{ $item->qtyjadi }}">
                            </td>

                            <td>
                                <input type="text" name="price[{{ $key }}]"
                                    class="form-control form-control-sm text-end price" value="{{ $item->pricejadi }}">
                            </td>

                            <td>
                                <input type="text" name="discount[{{ $key }}]"
                                    class="form-control form-control-sm text-end disc" value="{{ $item->discount }}">
                            </td>

                            <td>
                                <input type="text"
                                    class="form-control form-control-sm text-end bg-light border-0 total-field"
                                    value="{{ $item->total_price }}" readonly>
                            </td>
                            <td>
                                <button type="button" onclick="deleteItem('{{ $item->id }}')" class="btn btn-danger btn-sm btn-delete-detail"
                                    data-detail-id="{{ $item->id }}"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Grand Total</td>
                        <td colspan="2">
                            <input id="grand-total" type="text" id="grand-total"
                                class="form-control form-control-sm text-end"
                                value="{{ format_price($data->total_price) }}" readonly>
                        </td>
                    </tr>

            </table>
        </div>
</form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
    <button type="submit" form="form-edit-detail" class="btn btn-primary">Simpan Perubahan</button>
</div>

<script>
    $(document).on('input', '.qty, .price, .disc', function() {
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
        $('#grand-total').val(
            Array.from(document.querySelectorAll('.total-field'))
            .reduce((sum, input) => sum + formatDB(input.value), 0)
            .toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })
        );
    });

    $('#form-edit-detail').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url('admin/invoice/invoice/update-detail') }}',
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


    function deleteItem(detailId) {
        if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
            $.ajax({
                url: '{{ url('admin/invoice/sales-delete-detail') }}',
                method: 'DELETE',
                data: {
                    detail_id: detailId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    Swal.fire('Berhasil', 'Item berhasil dihapus', 'success');
                
                },
                error: function() {
                        Swal.fire('Gagal', 'Gagal menghapus item', 'error');

                }
            });
        }
    }
</script>
