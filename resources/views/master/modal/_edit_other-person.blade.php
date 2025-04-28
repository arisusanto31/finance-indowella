<div class="modal-header">
    <h5 class="modal-title">Edit Other Person</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form action="{{ route('other-person.main.update', $otherPerson->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="modal-body">
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" value="{{ $otherPerson->name }}" class="form-control" placeholder="Nama Lengkap" required>
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <input type="text" name="alamat" value="{{ $otherPerson->address }}" class="form-control" placeholder="Alamat">
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">No HP</label>
            <input type="text" name="phone" value="{{ $otherPerson->phone }}" class="form-control" placeholder="No HP">
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>
