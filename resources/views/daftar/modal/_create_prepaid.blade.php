<form id="form-create">
    @csrf
    <div class="modal-header">
        <div class="flex-column align-items-start">
            <h5 class="modal-title" id="exampleModalLabel1">Buat BDD</h5>


            <div class="form-check form-switch ">
                <input class="form-check-input" name="is_otomatis_jurnal" type="checkbox" id="is_otomatis_jurnal" checked />
                <label class="form-check-label" for="is_otomatis_jurnal">Buat Jurnal</label>
            </div>

        </div>
        <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
            aria-label="Close"></button>
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
                <label class="form-label">Deskripsi jurnal</label>
                <input class="form-control" placeholder="description" name="description" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label class="form-label">TOKO</label>
                <select type="text" name="toko_id" id="toko" class="form-control select-toko">
                </select>
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
                <input class="form-control" type="datetime-local" value="{{ now() }}" name="date"
                    id="date-input" />
            </div>
        </div>
        <div class="row">

            <div class="col mb-3">
                <label for="unit" class="form-label">Nilai perolehan</label>
                <input type="text" value="" class="form-control currency-input" name="nilai_perolehan"
                    placeholder="contoh: 17.000.000" />
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
    initItemSelectManual('#code-group', '{{ route('chart-account.get-item-keuangan') }}?kind=prepaid', 'Pilih Akun Bdd',
        '#global-modal');
    initItemSelectManual('#lawan-code-group', '{{ route('chart-account.get-item') }}?kind=prepaid', 'Pilih Akun Lawan',
        '#global-modal');
    initItemSelectManual('.select-toko', '{{ route('toko.get-item') }}', 'Pilih Toko', '#global-modal');

    function submitPrepaid() {
        $.ajax({
            url: '{{ route('bdd.store-prepaid') }}',
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
