<form id="form-create">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Kartu BDD</h5>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Nama</label>
                <input name="name" class="form-control" placeholder="nama bdd" />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun BDD</label>
                <select type="text" name="code_group" id="code-group" class="form-control">

                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun Lawan (Kredit)</label>
                <select type="text" name="lawan_code_group" id="lawan-code-group" class="form-control">

                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="quantity" class="form-label">Tipe BDD</label>
                <select class="form-control" name="type_bdd">
                    <option value="Biaya Sewa">Biaya Sewa</option>
                    <option value="Biaya Lain">Biaya Lain</option>
                </select>
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
                <label for="unit" class="form-label">Periode amortisasi (tahun)</label>
                <input type="text" value="" class="form-control" name="periode" placeholder="contoh: 4" />
            </div>
        </div>


    </div>
    <div class=" modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" onclick="submitPrepaid()" class="btn btn-primary">Simpan</button>
    </div>
</form>



<script>
    initCurrencyInput('.currency-input');
    initItemSelectManual('#code-group', '{{route("chart-account.get-item-keuangan")}}?kind=prepaid', 'Pilih Akun Bdd', '#global-modal');
    initItemSelectManual('#lawan-code-group', '{{route("chart-account.get-item")}}?kind=prepaid', 'Pilih Akun Lawan', '#global-modal');
    

    function submitPrepaid() {
        $.ajax({
            url: '{{route("bdd.store-prepaid")}}',
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