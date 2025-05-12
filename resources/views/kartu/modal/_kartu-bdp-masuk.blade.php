<form id="mutasi-masuk">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Mutasi BDP Masuk</h5>
        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
    </div>
    <div class="modal-body">


        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Nomer SO</label>
                <input type="text" name="sales_order_number" id="sales-order-number" class="form-control " placeholder="Nomer SO">
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Stock</label>
                <select onchange="selectStock()" type="text" name="stock_id" id="select-stock" class="form-control select-stock" placeholder="stock">
                </select>
                <input type="hidden" name="flow" value="0" />
                <input type="hidden" name="is_custom_rupiah" value="1" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun Persediaan</label>
                <select type="text" name="code_group" id="code-group" class="form-control select-coa">

                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="quantity" class="form-label">Jumlah Mutasi</label>
                <input type="text" name="mutasi_quantity" id="mutasi_quantity" autocomplete="off" class="form-control currency-input" placeholder="jumlah" />
            </div>
            <div class="col mb-3">
                <label for="unit" class="form-label">Satuan</label>
                <select type="text" id="unit" autocomplete="off" class="form-control" name="unit">
                    <option value="">Pilih Satuan</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="mutasi-rupiah" class="form-label">Total Nilai Rupiah</label>
                <input onchange="updateTotalRupiah()" name="mutasi_rupiah_total" id="mutasi-rupiah-total" class="form-control currency-input" placeholder="total nilai rupiah" />
                <input type="hidden" name="mutasi_rupiah_on_unit" id='mutasi-rupiah-on-unit' class="form-control currency-input" value="" />
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
    initItemSelectManual('.select-coa', '{{route("chart-account.get-item-keuangan")}}?kind=persediaan', 'Pilih Akun Persediaan', '#global-modal');
    initItemSelectManual('.select-stock', '{{route("stock.get-item")}}', 'Pilih Stock', '#global-modal');

    function updateTotalRupiah() {
        let quantity = $('#mutasi_quantity').val();
        let totalRupiah = formatDB($('#mutasi-rupiah-total').val()) / quantity;
        let unit = $('#unit').val();
        let keterangan = 'Total Nilai Per unit : ' + formatRupiah(totalRupiah) + ' / ' + unit;
        $('#keterangan').html(keterangan);
        $('#mutasi-rupiah-on-unit').val(totalRupiah);
    }

    function selectStock() {
        let stockid = $('#select-stock option:selected').val();
        if (stockid == '') {
            return;
        }
        $.ajax({
            url: '{{url("admin/master/stock/get-info")}}/' + stockid,
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
            url: '{{route("kartu-bdp.mutasi-store")}}',
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