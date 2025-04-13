<x-app-layout>
    <form method="POST" action="{{ route('supplier.invoice-sale.store') }}">
        @csrf

        <div class="container py-4 p-3 mb-4 card shadow-sm">
            <h2>Create Invoice Sales ya</h2>
            <div class="mb-3 mt-2">
                <button type="button" class="btn btn-success" id="addDebit">+Tambah</button>
            </div>

            <div id="div-debet" class="debet-wrapper">
                <div class="card border shadow-sm rounded p-3 mb-3 position-relative rowdebet">
                    <button type="button" class="btn-close-card" onclick="removeDebetRow(this)">×</button>
                    <div class="row g-2">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Invoice</label>
                            <input type="text" class="form-control" value="{{ now()->format('Y-m-d H:i:s') }}" readonly>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Nomor Invoice</label>
                            <input name="price_unit[]" type="text" step="0.01" class="form-control" placeholder="Nomor Invoice">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nama Produk</label>
                            <select name="stock_id[]" class="form-control select2-stock" required></select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Qty</label>
                            <input name="quantity[]" type="number" step="0.01" class="form-control" placeholder="Qty">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Satuan</label>
                            <select name="unit[]" class="form-control">
                                <option value="pcs">PCS</option>
                                <option value="slop">Slop</option>
                                <option value="pack">Pack</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga Satuan</label>
                            <input name="price_unit[]" type="number" step="0.01" class="form-control" placeholder="Harga">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total Harga</label>
                            <input name="total_price[]" type="number" step="0.01" class="form-control" placeholder="Total">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Diskon</label>
                            <input name="discount[]" type="number" class="form-control" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary w-100">Submit Invoice</button>
            </div>
        </div>
    </form>

    @push('styles')
        <style>
            .btn-close-card {
                position: absolute;
                top: -12px;
                right: -12px;
                width: 30px;
                height: 30px;
                background-color: red;
                border: none;
                border-radius: 50%;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                color: #fff;
                z-index: 10;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function initSelectProdukAjax(context = document) {
                $(context).find('.select2-stock').select2({
                    placeholder: '-- Pilih Produk --',
                    allowClear: true,
                    ajax: {
                        url: '{{ route("admin.stock.produk-get-item") }}',
                        dataType: 'json',
                        delay: 250,
                        processResults: function (data) {
                            return {
                                results: data.map(item => ({
                                    id: item.id,
                                    text: item.text
                                }))
                            };
                        },
                        cache: true
                    }
                });
            }

            $(document).ready(function () {
                initSelectProdukAjax();

                document.getElementById('addDebit').addEventListener('click', function () {
                    const debitWrapper = document.getElementById('div-debet');
                    const newRow = document.createElement('div');
                    newRow.classList.add('card', 'border', 'shadow-sm', 'rounded', 'p-3', 'mb-3', 'position-relative', 'rowdebet');

                    newRow.innerHTML = `
                        <button type="button" class="btn-close-card" onclick="removeDebetRow(this)">×</button>
                        <div class="row g-2">
                            <div class="mb-3">
                                 <label class="form-label">Tanggal Invoice</label>
                                <input type="text" class="form-control" value="{{ now()->format('Y-m-d H:i:s') }}" readonly>

                                </div>

                              <div class="col-md-2">
                            <label class="form-label">Nomor Invoice</label>
                            <input name="price_unit[]" type="text" step="0.01" class="form-control" placeholder="Nomor Invoice">
                        </div>
                            <div class="col-md-3">
                                <label class="form-label">Nama Produk</label>
                                <select name="stock_id[]" class="form-control select2-stock" required></select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Qty</label>
                                <input name="quantity[]" type="number" step="0.01" class="form-control" placeholder="Qty">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Satuan</label>
                                <select name="unit[]" class="form-control">
                                    <option value="pcs">PCS</option>
                                    <option value="slop">Slop</option>
                                    <option value="pack">Pack</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Harga Satuan</label>
                                <input name="price_unit[]" type="number" step="0.01" class="form-control" placeholder="Harga">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Total Harga</label>
                                <input name="total_price[]" type="number" step="0.01" class="form-control" placeholder="Total">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Diskon</label>
                                <input name="discount[]" type="number" class="form-control" placeholder="0">
                            </div>
                        </div>
                    `;

                    debitWrapper.appendChild(newRow);
                    initSelectProdukAjax(newRow);
                });
            });

            function removeDebetRow(btn) {
                const card = btn.closest('.rowdebet');
                const totalRows = document.querySelectorAll('.rowdebet').length;
                if (totalRows > 1) {
                    card.remove();
                } else {
                    Swal.fire('Oops!', 'Minimal harus ada satu baris lur hehe!', 'warning');
                }
            }
        </script>
    @endpush
</x-app-layout>
