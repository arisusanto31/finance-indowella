<div class="modal-header flex-column align-items-start">
    <h5 class="modal-title" id="exampleModalLabel">Detail Invoice {{$data->invoice_number}} - {{$data->person->name}} 
        <span class="fs-8 px-2 rounded-1 bg-primary text-white"> {{getModel($data->person_type)}} </span></h5>
        <button
        type="button"
        class="btn-close position-absolute end-0 top-0 m-3"
        data-bs-dismiss="modal"
        aria-label="Close"></button>
</div>
   <!-- Baris input sejajar kiri -->
   <div class="d-flex w-100 gap-3">
    <div class="d-flex align-items-center gap-2">
        <label class="small text-muted mb-0">Nomor SO:</label>
        <input type="hidden" name="original_invoice_number" value="{{ $data->invoice_number }}">
        <input type="text" name="sales_order_number_pack" class="form-control form-control-sm" value="{{ $data->invoice_number }}">
    </div>
    <div class="d-flex align-items-center gap-2">
        <label class="small text-muted mb-0">Tanggal:</label>
        <input type="date" name="tanggal_global" class="form-control form-control-sm" value="{{ $data->created_at->format('Y-m-d') }}">
    </div>
</div>
</div>



<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <h5>List Detail</h5>
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="bg-white text-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Diskon</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="body-detail-invoice">
                    @foreach($data['details'] as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            <input type="hidden" name="detail_id[]" value="{{ $item->id }}">
                            <input type="text" class="form-control" value="{{ $item->stock->name }}" readonly>
                        </td>
                        <td>
                            <input type="number" name="quantity[]" class="form-control qty" value="{{ $item->quantity }}">
                        </td>
                        <td>
                            <input type="number" name="price[]" class="form-control price" value="{{ $item->price }}">
                        </td>
                        <td>
                            <input type="number" name="discount[]" class="form-control discount" value="{{ $item->discount }}">
                        </td>
                        <td>
                            <input type="text" name="total_price[]" class="form-control total" 
                                   value="{{ number_format(($item->quantity * $item->price) - $item->discount, 0) }}" readonly>
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
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" onclick="" class="btn btn-primary">Simpan</button>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('#body-detail-invoice tr');

    rows.forEach(row => {
        const qtyInput = row.querySelector('.qty');
        const priceInput = row.querySelector('.price');
        const discountInput = row.querySelector('.discount');
        const totalInput = row.querySelector('.total');

        const updateTotal = () => {
            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;
            const subtotal = (qty * price) - discount;
            totalInput.value = formatRupiah(subtotal);
            updateGrandTotal();
        };

        qtyInput.addEventListener('input', updateTotal);
        priceInput.addEventListener('input', updateTotal);
        discountInput.addEventListener('input', updateTotal);
        updateTotal(); // kalkulasi awal saat modal dibuka
    });

    function updateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.total').forEach(input => {
            const val = parseFloat(input.value.replace(/\./g, '').replace(',', '.')) || 0;
            grandTotal += val;
        });
        const grandTotalField = document.querySelector('#grand-total');
        if (grandTotalField) {
            grandTotalField.textContent = formatRupiah(grandTotal);
        }
    }

    function formatRupiah(angka) {
        return angka.toLocaleString('id-ID', { minimumFractionDigits: 0 });
    }
});
</script>
@endpush
