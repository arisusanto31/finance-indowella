<form id="form-edit-detail">
    @csrf
    <div class="modal-header flex-column align-items-start">
        <div>
            <h5 class="modal-title mb-2">
                Detail Invoice {{ $data->invoice_number }} - {{ $data->person->name }}
                <span class="fs-8 px-2 rounded-1 bg-success text-white ms-2">
                    {{ getModel($data->person_type) }}
                </span>
            </h5>
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
            <div class="col-xs-12 col-md-12">
                <h5>Edit Invoice Sales</h5>
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="bg-white text-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Total</th>
                            <th>Status</th>
                            {{-- <th></th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['details'] as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <input type="hidden" name="detail_id[{{ $i }}]" value="{{ $item->id }}">
                                <td>{{ $item->stock->name ?? '-' }}</td>
                                <td>
                                    <input type="number" name="quantity[{{ $i }}]" class="form-control qty"
                                        value="{{ $item->quantity }}">
                                </td>
                                <td>
                                    <input type="number" name="price[{{ $i }}]" class="form-control price"
                                        value="{{ $item->price }}">
                                </td>
                                <td>
                                    <input type="number" name="discount[{{ $i }}]"
                                        class="form-control disc" value="{{ $item->discount }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control total-field"
                                        value="{{ number_format($item->quantity * $item->price - $item->discount, 0, ',', '.') }}"
                                        readonly>
                                </td>
                                <td>
                                    <input type="text"  class="form-control total-field"
                                        value="{{ $data->invoice_number }}" readonly/>
                                </td>
                                {{-- <td>
                                    {{ $item->parent ? $item->parent->status : '??' }}

                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-center">Total</td>
                            <td><input type="text" id="grand-total" class="form-control text-end"
                                    value="{{ format_price($data->total_price) }}" readonly></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Simpan Perubahan
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Tutup
        </button>
    </div>

    </div>
    </div>
    </div>
    </div>

    @push('scripts')
        <script>
            $('#form-edit-detail').on('submit', function(e) {
                e.preventDefault();
                const data = $(this).serialize();
                const invoiceNumber = $('input[name="original_invoice_number"]').val(); // yang lama

                $.post("{{ url('admin/invoice/invoice-sales/update') }}/" + invoiceNumber, data, function(res) {
                    console.log(res);
                    if (res.success) {
                        Swal.fire('Berhasil', 'Invoice berhasil diupdate!', 'success');
                        $('#editModal').modal('hide');
                        location.reload();
                    } else {
                        Swal.fire('Gagal', res.message ?? 'Gagal menyimpan data.', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                });
            });


            function hitungTotal(row) {
                const qty = parseFloat(row.find('.qty').val()) || 0;
                const price = parseFloat(row.find('.price').val()) || 0;
                const disc = parseFloat(row.find('.disc').val()) || 0;
                const total = (qty * price) - disc;
                const formatted = new Intl.NumberFormat('id-ID').format(total);
                row.find('.total-field').val(formatted);
            }

            function hitungGrandTotal() {
                let total = 0;
                $('.total-field').each(function() {
                    const val = parseFloat($(this).val().replace(/\./g, '')) || 0;
                    total += val;
                });
                $('#grand-total').val(new Intl.NumberFormat('id-ID').format(total));
            }

            $(document).on('input', '.qty, .price, .disc', function() {
                const row = $(this).closest('tr');
                hitungTotal(row);
                hitungGrandTotal();
            });
        </script>
