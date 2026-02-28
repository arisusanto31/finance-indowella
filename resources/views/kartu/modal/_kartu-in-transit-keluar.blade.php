<form id="mutasi-keluar">
    @csrf
    <div class="modal-header">
        <div class="flex-column align-items-start">
            <h5 class="modal-title" id="exampleModalLabel1">Buat Mutasi In Transit Keluar</h5>


            <div class="form-check form-switch ">
                <input class="form-check-input" type="checkbox" name="is_otomatis_jurnal" id="is_otomatis_jurnal" checked />
                <label class="form-check-label" for="is_otomatis_jurnal">Buat Jurnal</label>
            </div>

        </div>
        <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
            aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Date</label>
                <input type="datetime-local" name="date" id="date" class="form-control "
                    value="{{ now() }}" placeholder="date">
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Nomer Invoice </label>
                <input type="text" name="invoice_pack_number" id="invoice-pack-number" class="form-control "
                    placeholder="Nomer Invoice">
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Nomer Produksi</label>
                <input type="text" name="production_number" id="production_number" class="form-control "
                    placeholder="Nomer Produksi">
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Stock</label>
                <select onchange="selectStock()" type="text" name="stock_id" id="select-stock"
                    class="form-control select-stock" placeholder="stock">
                </select>
                <input type="hidden" name="flow" value="1" />
                <input type="hidden" name="is_custom_rupiah" value="0" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun IN TRANSIT</label>
                <select type="text" name="code_group" id="code-group" class="form-control select-coa">
                    <option value="140002" selected>Persediaan In Transit</option>
                </select>
            </div>
            <div class="col mb-3">
                <label for="akun" class="form-label">Ke Akun </label>
                <select type="text" name="lawan_code_group" id="lawan-code-group" class="form-control select-coa">
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="quantity" class="form-label">Jumlah Mutasi</label>
                <input type="text" name="mutasi_quantity" id="mutasi_quantity" autocomplete="off"
                    class="form-control currency-input" placeholder="jumlah" />
            </div>
            <div class="col mb-3">
                <label for="unit" class="form-label">Satuan</label>
                <select type="text" id="unit" autocomplete="off" class="form-control" name="unit">
                    <option value="">Pilih Satuan</option>
                </select>
            </div>
        </div>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" onclick="submitMutasiStock()" class="btn btn-primary">Simpan</button>
    </div>
</form>



<script>
    console.log('masuk kok');
    initItemSelectManual('.select-stock', '{{ route('stock.get-item') }}', 'Pilih Stock');
    initItemSelectManual('.select-coa', '{{ route('chart-account.get-item-keuangan') }}?kind=persediaan|hutang|kas',
        'Pilih Akun Persediaan');
    initCurrencyInput('.currency-input');



    function selectStock() {
        let stockid = $('#select-stock option:selected').val();
        if (stockid == '') {
            return;
        }
        $.ajax({
            url: '{{ url('admin/master/stock/get-info') }}/' + stockid,
            method: 'get',
            success: function(res) {
                if (res.status == 1) {
                    html = "";
                    res.msg.units.forEach(function(item) {
                        html += `<option value="${item.unit}">${item.unit}</option>`;
                    });
                    $('#unit').html(html);
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                Swal.fire("opps", "something error", 'error');
            }
        });
    }

    function submitMutasiStock() {
        $.ajax({
            url: '{{ route('kartu-in-transit.mutasi-store') }}',
            method: 'post',
            data: $('#mutasi-keluar').serialize(),
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    Swal.fire('success', 'berhasil masuk', 'success');
                    hideModal();
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                Swal.fire("opps", "something error", 'error');
            }
        });
    }
</script>
