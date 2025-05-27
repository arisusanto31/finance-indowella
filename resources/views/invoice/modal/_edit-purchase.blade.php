<div class="modal-header flex-column align-items-start">
    <h5 class="modal-title" id="exampleModalLabel">Detail Invoice {{ $data->invoice_number }} - {{ $data->person->name }}
        <span class="fs-8 px-2 rounded-1 bg-primary text-white"> {{ getModel($data->person_type) }} </span>
    </h5>
    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
        aria-label="Close"></button>
</div>
<!-- Baris input sejajar kiri -->



<form id="form-edit-purchase">

    <div class="modal-body">
        <div class="d-flex w-100 gap-3">
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Nomor SO:</label>
                <input type="hidden" name="original_invoice_number" value="{{ $data->invoice_number }}">
                <input type="text" name="new_invoice_number" class="form-control form-control-sm"
                    value="{{ $data->invoice_number }}">
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Tanggal:</label>
                <input type="datetime-local" name="date" class="form-control form-control-sm"
                    value="{{ $data->created_at }}">
            </div>
        </div>
        <div class="row">
            <div class="col-12">

                <h5>List Detail</h5>

                {{ csrf_field() }}
                <div class="table-responsive mt-2">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="bg-white text-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Nama Custom</th>
                                <th>Qty</th>
                                <th>Unit </th>
                                <th>Harga</th>
                                <th>Diskon</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="body-detail-invoice">
                            @foreach ($data['details'] as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="detail_id[]" value="{{ $item->id }}">
                                        <input type="text" class="form-control" value="{{ $item->stock->name }}"
                                            readonly>
                                    </td>
                                    <td>
                                        <input type="text" name="custom_stock_name[]" class="form-control"
                                            value="{{ $item->custom_stock_name }}" />


                                    </td>
                                    <td>
                                        <input onchange="updateHargaEdit(this)" type="text" name="quantity[]"
                                            class="form-control rupiah qty"
                                            value="{{ format_price($item->quantity) }}" />

                                    </td>
                                    <td>
                                        {{ $item->unit }}
                                    </td>
                                    <td>
                                        <input onchange="updateHargaEdit(this)" type="text" name="price[]"
                                            class="form-control rupiah price"
                                            value="{{ format_price($item->price) }}" />
                                    </td>
                                    <td>
                                        <input onchange="updateHargaEdit(this)" type="text" name="discount[]"
                                            class="form-control rupiah discount"
                                            value="{{ format_price($item->discount) }}" />
                                    </td>
                                    <td>
                                        <input type="text" name="total_price[]" class="form-control rupiah total"
                                            value="{{ format_price($item->total_price) }}" readonly />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-center fw-bold">Grand Total</td>
                                <td class="fw-bold" id="grand-total">{{ format_price($data->total_price) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
</form>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
    <button type="button" onclick="updatePurchase()" class="btn btn-primary">Simpan</button>
</div>
</div>


<script>
    initCurrencyInput('.rupiah');

    function updateHargaEdit(input) {
        const row = input.closest('tr');
        const qty = formatDB(row.querySelector('.qty').value) || 0;
        const price = formatDB(row.querySelector('.price').value) || 0;
        const discount = formatDB(row.querySelector('.discount').value) || 0;

        const total = (qty * price) - discount;
        row.querySelector('.total').value = formatRupiah(total);
        updateGrandTotal();
    }

    function updateGrandTotalEdit() {
        let grandTotal = 0;
        document.querySelectorAll('#body-detail-invoice .total').forEach(function(totalInput) {
            grandTotal += formatDB(totalInput.value) || 0;
        });
        $('#grand-total').html(formatRupiah(grandTotal));
    }

    function updatePurchase() {
        swalConfirmAndSubmit({
            url: '{{ url('admin/invoice/invoice-purchase-update') }}',
            data: $('#form-edit-purchase').serialize(),
            onSuccess: function(res) {
                $('#global-modal').modal('hide');
            }
        });
    }
</script>
