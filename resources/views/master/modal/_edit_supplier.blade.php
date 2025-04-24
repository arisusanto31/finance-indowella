<form action="{{ route('supplier.main.update',[$supplier->id]) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Supplier</h5>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="name" class="form-label">Nama Supplier</label>
                <input type="text" name="name" id="name" value="{{$supplier->name}}" class="form-control" placeholder="Nama Supplier" required />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="npwp" class="form-label">NPWP</label>
                <input type="number" name="npwp" id="npwp" value="{{$supplier->npwp}}" class="form-control" placeholder="NPWP" required />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="ktp" class="form-label">KTP</label>
                <input type="text" name="ktp" id="ktp" class="form-control" value="{{$supplier->ktp}}" placeholder="KTP" required />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="address" class="form-label">Alamat Lengkap</label>
                <input type="text" name="address" id="address" class="form-control" value="{{$supplier->address}}" placeholder="Alamat" />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="cp_name" class="form-label">Contact Person</label>
                <input type="text" name="cp_name" id="cp_name" class="form-control" value="{{$supplier->cp_name}}" placeholder="Nama Kontak" required />
            </div>
            <div class="col mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <input type="number" name="phone" id="phone" class="form-control" value="{{$supplier->phone}}" placeholder="Telepon" required />
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>