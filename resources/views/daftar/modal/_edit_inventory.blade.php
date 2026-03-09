<form id="form-create">
    @csrf
    <div class="modal-header">
        <div class="flex-column align-items-start">
            <h5 class="modal-title" id="exampleModalLabel1">Edit Inventaris</h5>
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
                <label class="form-label">Nama</label>
                <input class="form-control" placeholder="name" name="name" value="{{ $inventory->name }}" />
                <input type="hidden" name="id" value="{{ $inventory->id }}" />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Deskripsi jurnal</label>
                <input class="form-control" placeholder="description" name="description" value="{{ $kartu?$kartu->description:"" }}" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label class="form-label">TOKO</label>
                <select type="text" name="toko_id" id="toko" class="form-control select-toko">
                    <option value="{{ $kartu->toko_id }}" selected>{{ $kartu->toko->name ?? '-' }}</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun Aset</label>
                <select type="text" name="code_group" id="code-group" class="form-control select-coa">
                    {{-- <option value="{{ $kartu->code_group }}" selected>{{ $kartu->codeGroup->name ?? '-' }}</option> --}}
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun Lawan (Kredit)</label>
                <select type="text" name="lawan_code_group" id="lawan-code-group" class="form-control">
                    {{-- <option value="{{ $kartu->lawan_code_group }}" selected>{{ $kartu->lawanCodeGroup->name ?? '-' }}</option> --}}
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
                    <option value="{{ $inventory->type_aset }}" selected>{{ $inventory->type_aset }}</option>
                </select>
            </div>

        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="unit" class="form-label">Keterangan QTY dan unit</label>
                <input type="text" value="{{ $inventory->keterangan_qty_unit }}" class="form-control" name="keterangan_qty_unit"
                    placeholder="contoh: 2 pcs" />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="" class="form-label">Date</label>
                <input class="form-control" type="datetime-local" value="{{$inventory->date }}" name="date" id="date-input" />
            </div>
        </div>
        <div class="row">

            <div class="col mb-3">
                <label for="unit" class="form-label">Nilai perolehan</label>
                <input type="text" value="{{ $inventory->nilai_perolehan }}" class="form-control currency-input" name="nilai_perolehan"
                    placeholder="contoh: 17.000.000" />
            </div>
        </div>
        <div class="row">

            <div class="col mb-3">
                <label for="unit" class="form-label">Periode Penyusutan (tahun)</label>
                <input type="text" value="{{ $inventory->periode }}" class="form-control" name="periode" placeholder="contoh: 4" />
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
    initCurrencyInput('.currency-input');
    initItemSelectManual('#code-group', '{{ route('chart-account.get-item-keuangan') }}?kind=inventory',
        'Pilih Akun Aset');
    initItemSelectManual('#lawan-code-group', '{{ route('chart-account.get-item') }}', 'Pilih Akun Lawan',);
    initItemSelectManual('.select-stock', '{{ route('stock.get-item') }}', 'Pilih Stock');
    initItemSelectManual('.select-toko', '{{ route('toko.get-item') }}', 'Pilih Toko');

    function submitInventory() {
        $.ajax({
            url: '{{ route('aset-tetap.update-inventory') }}',
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

    function initData(){

         console.log('init datan toko '+ '{{ $inventory->toko_id }}');
        $('#code-group').html(`<option value="{{ $kartu->code_group ?? '' }}" selected >{{ $aliasChart[$kartu->code_group] ?? '-' }}</option>`);
        $('#lawan-code-group').html(`<option value="{{ $kartu->lawan_code_group ?? '' }}" selected >{{ $aliasChart[$kartu->lawan_code_group] ?? '-' }}</option>`);
        $('#toko').html(`<option value="{{ $inventory->toko_id }}" selected >{{ $inventory->toko->name ?? '-' }}</option>`);
        $('#is_otomatis_jurnal').prop('checked', {{ $inventory->is_otomatis_jurnal ? 'true' : 'false' }});
    }
    setTimeout(() => {
        initData();
    }, 500);
</script>
