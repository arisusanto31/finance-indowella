<form id="mutasi-keluar">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Mutasi Stock Keluar</h5>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Stock</label>
                <select onchange="selectStock()" type="text" name="stock_id" id="select-stock" class="form-control select-stock" placeholder="stock">
                </select>
                <input type="hidden" name="flow" value="1" />
                <input type="hidden" name="is_custom_rupiah" value="0" />
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

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" onclick="submitMutasiStock()" class="btn btn-primary">Simpan</button>
    </div>
</form>



<script>
    console.log('masuk kok');
    initItemSelectManual('.select-stock', '{{route("stock.get-item")}}', 'Pilih Stock', '#global-modal');
    initCurrencyInput('.currency-input');

   

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
            url: '{{route("kartu-stock.mutasi-store")}}',
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