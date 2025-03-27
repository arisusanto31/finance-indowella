<x-app-layout>

  <div class="container mt-4">
    <h4 class="mb-4">Kartu Kas</h4>
    <div class="card shadow-sm rounded-3 bg-light">
      <div class="card-body">


        <h5>
          Debit
          <button type="button" class="btn btn-sm btn-success ms-2" id="addDebit">+tambah</button>
        </h5>
        <div id="debitWrapper">
          <div class="row g-2 mb-2 debit-row">
            <div class="col-md-4">
              <select class="form-select">
                <option>Option A</option>
                <option>Option B</option>

              </select>
            </div>
            <div class="col-md-4">
              <input type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input type="number" class="form-control" placeholder="Amount">
            </div>
          </div>
        </div>

        <hr>

        <h5>
          Kredit
          <button type="button" class="btn btn-sm btn-primary-light ms-2" id="addKredit">+tambah</button>
        </h5>
        <div id="kreditWrapper">
          <!-- Baris kredit pertama -->
          <div class="row g-2 mb-2 kredit-row">
            <div class="col-md-4">
              <select class="form-select">
                <option>Option A</option>
                <option>Option B</option>
              </select>
            </div>
            <div class="col-md-4">
              <input type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input type="number" class="form-control" placeholder="Amount">
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button class="btn btn-primary w-100">Submit Journal</button>
        </div>
      </div>
    </div>
  </div>


  @push('scripts')
  <script>
    document.getElementById('addDebit').addEventListener('click', function() {
      const debitWrapper = document.getElementById('debitWrapper');
      const newRow = document.createElement('div');
      newRow.classList.add('row', 'g-2', 'mb-2', 'debit-row');
      newRow.innerHTML = `
            <div class="col-md-4">
              <select class="form-select">
                <option>chart account</option>
              </select>
            </div>
            <div class="col-md-4">
              <input type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input type="number" class="form-control" placeholder="Amount">
            </div>
          `;
      debitWrapper.appendChild(newRow);
    });

    document.getElementById('addKredit').addEventListener('click', function() {
      const kreditWrapper = document.getElementById('kreditWrapper');
      const newRow = document.createElement('div');
      newRow.classList.add('row', 'g-2', 'mb-2', 'kredit-row');
      newRow.innerHTML = `
            <div class="col-md-4">
              <select class="form-select">
                <option>chart account</option>
              </select>
            </div>
            <div class="col-md-4">
              <input type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input type="number" class="form-control" placeholder="Amount">
            </div>
          `;
      kreditWrapper.appendChild(newRow);
    });

;
  </script>
  @endpush
</x-app-layout>