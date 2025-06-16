<form id="form-create">
    @csrf
    <div class="modal-header">
        <div class="flex-column align-items-start">
            <h5 class="modal-title" id="exampleModalLabel1">Buat Kartu BDD</h5>


            <div class="form-check form-switch ">
                <input class="form-check-input" type="checkbox" id="is_otomatis_jurnal" checked />
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
                <label for="quantity" class="form-label">BDD</label>
                <select class="form-control select-prepaid" name="prepaid_expense_id">
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="quantity" class="form-label">Tipe Mutasi</label>
                <select class="form-control" name="type_mutasi">
                    <option value="pembayaran">Pembayaran</option>
                    <option value="amortisasi">Amortisasi</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Deskripsi</label>
                <input class="form-control" placeholder="description" name="description" />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun BDD</label>
                <select type="text" name="code_group" id="code-group" class="form-control select-coa">

                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun Lawan </label>
                <select type="text" name="lawan_code_group" id="lawan-code-group" class="form-control">

                </select>
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="" class="form-label">Date</label>
                <input class="form-control" name="datetime-local" type="date" value="{{now()}}" id="date-input" />
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
    initItemSelectManual('.select-prepaid', '{{route("bdd.get-item")}}', 'Pilih bdd', '#global-modal');
    initCurrencyInput('.currency-input');
    initItemSelectManual('.select-coa', '{{route("chart-account.get-item-keuangan")}}?kind=prepaid', 'Pilih Akun Bdd', '#global-modal');
    initItemSelectManual('#lawan-code-group', '{{route("chart-account.get-item")}}?kind=prepaid', 'Pilih Akun Lawan', '#global-modal');


    function submitKartu() {
        $.ajax({
            url: '{{route("bdd.store-kartu-prepaid")}}',
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