<form id="form-create">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Kartu BDD</h5>
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
                <label for="akun" class="form-label">Akun BDD</label>
                <select type="text" name="code_group" id="code-group" class="form-control select-coa">

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
                <label for="" class="form-label">Date</label>
                <input class="form-control" name="date" type="date" value="{{Date('Y-m-d')}}" id="date-input" />
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