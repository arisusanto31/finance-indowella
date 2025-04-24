<form id="form-create">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Buat Kartu Inventaris</h5>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col mb-3">
                <label for="nameBasic" class="form-label">Nama</label>
                <input name="name" class="form-control" placeholder="nama aset tetap" />
            </div>
        </div>

        <div class="row">
            <div class="col mb-3">
                <label for="akun" class="form-label">Akun Aset</label>
                <select type="text" name="code_group" id="code-group" class="form-control select-coa">

                </select>
            </div>
        </div>