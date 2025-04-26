<form action="{{ route('other-person.main.store') }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Other-Person</h5>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Nama Supplier" required/>
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="phone" class="form-label">No HandPhone</label>
                <input type="number" name="phone" id="phone" class="form-control" placeholder="Nomor HP" required />                
            </div>
        </div>


        <div class="row">
            <div class="col mb-3">
                <label for="address" class="form-label">Alamat Lengkap</label>
                <input type="text" name="address" id="address" class="form-control" placeholder="Alamat" required/>
            </div>
        </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
