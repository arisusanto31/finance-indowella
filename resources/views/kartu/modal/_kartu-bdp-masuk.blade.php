<form id="mutasi-masuk">
    @csrf
    <div class="modal-header">
        <div class="flex-column align-items-start">
            <h5 class="modal-title" id="exampleModalLabel1">Buat Mutasi BDP Masuk</h5>


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
                <label for="nameBasic" class="form-label">Nomer SO</label>
                <input type="text" name="sales_order_number" id="sales-order-number" class="form-control "
                    placeholder="Nomer SO">
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
                <input type="hidden" name="flow" value="0" />
                <input type="hidden" name="is_custom_rupiah" value="1" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Custom Stock (bahan lain lain)</label>
                <input type="text" name="custom_stock_name" id="custom-stock-name" class="form-control "
                    onchange="kosongkanStockID()" placeholder="Custom Stock / Bahan Lain custom">
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun BDP</label>
                <select type="text" name="code_group" id="code-group" class="form-control">
                    <option value="140003" selected>Persediaan Dalam Proses</option>
                </select>
            </div>
            <div class="col mb-3">
                <label for="akun" class="form-label">Dari Akun </label>
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
            <div class="col mb-3" id="div-unit">
                <label for="unit" class="form-label">Satuan</label>
                <select type="text" id="unit" autocomplete="off" class="form-control" name="unit">
                    <option value="">Pilih Satuan</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="mutasi-rupiah" class="form-label">Total Nilai Rupiah</label>
                <input onchange="updateTotalRupiah()" name="mutasi_rupiah_total" id="mutasi-rupiah-total"
                    class="form-control currency-input" placeholder="total nilai rupiah" />
                <input type="hidden" name="mutasi_rupiah_on_unit" id='mutasi-rupiah-on-unit'
                    class="form-control currency-input" value="" />
                <span id="keterangan"></span>
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
    initCurrencyInput('.currency-input');
    initItemSelectManual('.select-coa', '{{ route('chart-account.get-item-keuangan') }}?kind=persediaan',
        'Pilih Akun Persediaan', '#global-modal');
    initItemSelectManual('.select-stock', '{{ route('stock.get-item') }}', 'Pilih Stock', '#global-modal');


    function kosongkanStockID() {
        if ('#custom-stock-name' != '') {
            $('#select-stock').val('').change();
            initItemSelectManual('.select-coa', '{{ route('chart-account.get-item-keuangan') }}?kind=hutang|kas|pendapatan|beban',
                'Pilih Akun Hutang / Kas', '#global-modal');
            changeUnitDiv('manual');
        } else {
            initItemSelectManual('.select-coa', '{{ route('chart-account.get-item-keuangan') }}?kind=persediaan',
                'Pilih Akun Persediaan', '#global-modal');
            changeUnitDiv('normal');
        }
    }

    function changeUnitDiv(mode = "normal") {
        if (mode == "normal") {
            html = `<label for="unit" class="form-label">Satuan</label>
                   <select type="text" id="unit" autocomplete="off" class="form-control" name="unit">
                     <option value="">Pilih Satuan</option>
                   </select>`;
            $('#div-unit').html(html);

        } else {
            html =
                `<label for="unit" class="form-label">Satuan</label>
                   <input type="text" id="unit" autocomplete="off" placeholder="satuan" class="form-control" name="unit" value="" >`;
            $('#div-unit').html(html);
        }
    }

    function updateTotalRupiah() {
        let quantity = formatDB($('#mutasi_quantity').val());
        let totalRupiah = formatDB($('#mutasi-rupiah-total').val()) / quantity;
        let unit = $('#unit').val();
        let keterangan = 'Total Nilai Per unit : ' + formatRupiah(totalRupiah) + ' / ' + unit;
        $('#keterangan').html(keterangan);
        $('#mutasi-rupiah-on-unit').val(totalRupiah);
    }

    function selectStock() {
        let stockid = $('#select-stock option:selected').val();
        if (stockid == '' || stockid == undefined) {
            return;
        }
        changeUnitDiv('normal');
        $('#custom-stock-name').val('');
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
            url: '{{ route('kartu-bdp.mutasi-store') }}',
            method: 'post',
            data: $('#mutasi-masuk').serialize(),
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
