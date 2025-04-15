<form action="{{ route('karyawan.store') }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Create Karyawan</h5>
    </div>

    <div class="modal-body">

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" class="form-control" placeholder="Nama" required />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">NIK</label>
                <input type="text" name="nik" class="form-control" placeholder="NIK" required />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">NPWP</label>
                <input type="text" name="npwp" class="form-control" placeholder="NPWP" />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Jabatan</label>
                <input type="text" name="jabatan" class="form-control" placeholder="Jabatan" required />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Tanggal Masuk</label>
                <input type="date" name="date_masuk" class="form-control" required />
            </div>
            <div class="col mb-3">
                <label class="form-label">Tanggal Keluar</label>
                <input type="date" name="date_keluar" class="form-control" />
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>
