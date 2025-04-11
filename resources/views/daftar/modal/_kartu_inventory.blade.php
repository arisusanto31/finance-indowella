<form id="mutasi-masuk">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Kartu Inventaris</h5>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Nama</label>
                <input name="name" placeholder="nama aset tetap" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="quantity" class="form-label">Tipe Aset</label>
                <select class="form-control" name="type_aset" >
                    <option value="Tanah">Tanah</option>
                    <option value="Truck">Truck</option>
                    <option value="Mobil">Mobil / Motor</option>
                    <option value="Peralatan">Peralatan</option>
                    <option value="Inventaris Kantor">Inventaris Kantor</option>
                </select>
            </div>

            <div class="col mb-3">
                <label for="unit" class="form-label">Keterangan QTY dan unit</label>
                <input type="text" value="" name="keterangan_qty_unit" placeholder="keterangan qty+unit . contoh: 4 pcs"/>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="mutasi-rupiah" class="form-label">Date</label>
                
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

    function updateTotalRupiah() {
        let quantity = $('#mutasi_quantity').val();
        let totalRupiah = formatDB($('#mutasi-rupiah-total').val()) / quantity;
        let unit = $('#unit').val();
        let keterangan = 'Total Nilai Per unit : ' + formatRupiah(totalRupiah) + ' / ' + unit;
        $('#keterangan').html(keterangan);
        $('#mutasi-rupiah-on-unit').val(totalRupiah );
    }

    function selectStock() {
        let stockid = $('#select-stock option:selected').val();
        if(stockid == '') {
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