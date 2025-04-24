<form action="{{ route('toko.main.store') }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Toko</h5>
        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="name" class="form-label">Nama Toko</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Nama Toko" required />
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