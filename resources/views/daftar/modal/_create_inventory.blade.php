<form id="form-create">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Kartu Inventaris</h5>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Nama</label>
                <input name="name" class="form-control" placeholder="nama aset tetap" />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun Aset</label>
                <select type="text" name="code_group" id="code-group" class="form-control select-coa">

                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="quantity" class="form-label">Tipe Aset</label>
                <select class="form-control" name="type_aset">
                    <option value="Tanah">Tanah</option>
                    <option value="Truck">Truck</option>
                    <option value="Mobil">Mobil / Motor</option>
                    <option value="Peralatan">Peralatan</option>
                    <option value="Inventaris Kantor">Inventaris Kantor</option>
                    <option value="Inventaris Lain">Inventaris Lain</option>

                </select>
            </div>

        </div>
        <div class="row">

            <div class="col mb-3">
                <label for="unit" class="form-label">Keterangan QTY dan unit</label>
                <input type="text" value="" class="form-control" name="keterangan_qty_unit" placeholder="contoh: 2 pcs" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="" class="form-label">Date</label>
                <input class="form-control" type="date" value="{{Date('Y-m-d')}}" name="date" id="date-input" />
            </div>
        </div>
        <div class="row">

            <div class="col mb-3">
                <label for="unit" class="form-label">Nilai perolehan</label>
                <input type="text" value="" class="form-control currency-input" name="nilai_perolehan" placeholder="contoh: 17.000.000" />
            </div>
        </div>
        <div class="row">

            <div class="col mb-3">
                <label for="unit" class="form-label">Periode Penyusutan (tahun)</label>
                <input type="text" value="" class="form-control" name="periode" placeholder="contoh: 4" />
            </div>
        </div>


    </div>
    <div class=" modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" onclick="submitInventory()" class="btn btn-primary">Simpan</button>
    </div>
</form>



<script>
    console.log('masuk kok');
    initItemSelectManual('.select-stock', '{{route("stock.get-item")}}', 'Pilih Stock', '#global-modal');
    initCurrencyInput('.currency-input');
    initItemSelectManual('.select-coa', '{{route("chart-account.get-item-keuangan")}}?kind=inventory', 'Pilih Akun Aset', '#global-modal');
  

    function submitInventory() {
        $.ajax({
            url: '{{route("aset-tetap.store-inventory")}}',
            method: 'post',
            data: $('#form-create').serialize(),
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