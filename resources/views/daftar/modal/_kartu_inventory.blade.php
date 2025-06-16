<form id="form-create">
    @csrf
    <div class="modal-header">
        <div class="flex-column align-items-start">
            <h5 class="modal-title" id="exampleModalLabel1">Buat Kartu Inventaris</h5>


            <div class="form-check form-switch ">
                <input class="form-check-input" name="is_otomatis_jurnal" type="checkbox" id="is_otomatis_jurnal" checked />
                <label class="form-check-label" for="is_otomatis_jurnal">Buat Jurnal</label>
            </div>
        </div>
        <button
            type="button"
            class="btn-close position-absolute end-0 top-0 m-3"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
    </div>
    <div class="modal-body">

        <div class="row">
            <div class="col mb-3">
                <label for="quantity" class="form-label">Inventory</label>
                <select class="form-control select-inventory" name="inventory_id">
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="quantity" class="form-label">Tipe Mutasi</label>
                <select class="form-control" onchange="changeType()" id="type-mutasi" name="type_mutasi">
                    <option value="pembelian">Pembelian</option>
                    <option value="penyusutan" selected>Penyusutan</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Description</label>
                <input class="form-control" placeholder="description" name="description" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="akun" id="label-akun" class="form-label">Akun (Debet)</label>
                <select type="text" name="code_group" id="code-group" class="form-control ">

                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="akun" id="label-lawan-akun" class="form-label">Lawan Akun (Kredit)</label>
                <select type="text" name="lawan_code_group" id="lawan-code-group" class="form-control">

                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="" class="form-label">Date</label>
                <input class="form-control" name="date" type="datetime-local" value="{{now()}}" id="date-input" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="unit" class="form-label">Amount</label>
                <input type="text" value="" class="form-control currency-input" name="amount" placeholder="contoh: 17.000.000" />
            </div>
        </div>

    </div>
    <div class=" modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" onclick="submitKartu()" class="btn btn-primary">Simpan</button>
    </div>
</form>



<script>
    console.log('masuk kok');
    initItemSelectManual('.select-inventory', '{{route("aset-tetap.get-item")}}', 'Pilih aset', '#global-modal');
    initCurrencyInput('.currency-input');
    changeType();

    function changeType() {
        type = $('#type-mutasi option:selected').val();
        if (type == 'pembelian') {
            $('#label-lawan-akun').html('Lawan Akun (Kredit)');
            $('#label-akun').html('Akun Aset');
            initItemSelectManual('#code-group', '{{route("chart-account.get-item-keuangan")}}?kind=inventory', 'Pilih Akun Aset', '#global-modal');
            initItemSelectManual('#lawan-code-group', '{{route("chart-account.get-item")}}', 'Pilih Lawan Akun', '#global-modal');

        } else {
            $('#label-lawan-akun').html('Akun Beban Penyusutan');
            $('#label-akun').html('Akun Akumulasi Penyusutan');
            initItemSelectManual('#code-group', '{{route("chart-account.get-item-keuangan")}}?kind=akumulasi_inventory', 'Pilih Akun akumulasi', '#global-modal');
            initItemSelectManual('#lawan-code-group', '{{route("chart-account.get-item-keuangan")}}?kind=beban_inventory', 'Pilih akun penyusutan', '#global-modal');
        }
    }

    function submitKartu() {
        $.ajax({
            url: '{{route("aset-tetap.store-kartu-inventory")}}',
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