<form action="{{ route('supplier.main.store') }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Supplier</h5>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="name" class="form-label">Nama Supplier</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Nama Supplier" required />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="npwp" class="form-label">NPWP</label>
                <input type="number" name="npwp" id="npwp" class="form-control" placeholder="NPWP"  />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="ktp" class="form-label">KTP</label>
                <input type="text" name="ktp" id="ktp" class="form-control" placeholder="KTP"  />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="address" class="form-label">Alamat Lengkap</label>
                <input type="text" name="address" id="address" class="form-control" placeholder="Alamat" />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="cp_name" class="form-label">Contact Person</label>
                <input type="text" name="cp_name" id="cp_name" class="form-control" placeholder="Nama Kontak" required />
            </div>
            <div class="col mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <input type="number" name="phone" id="phone" class="form-control" placeholder="Telepon" required />
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
