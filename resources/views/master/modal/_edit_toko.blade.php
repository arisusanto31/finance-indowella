<form action="{{ route('toko.main.update',[$toko->id]) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Toko</h5>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" name="name" id="name" value="{{$toko->name}}" class="form-control" placeholder="Nama Toko" required />
            </div>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label for="address" class="form-label">Alamat Lengkap</label>
                <input type="text" name="address" id="address" class="form-control" value="{{$toko->address}}" placeholder="Alamat" />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <input type="number" name="phone" id="phone" class="form-control" value="{{$toko->phone}}" placeholder="Telepon" required />
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>