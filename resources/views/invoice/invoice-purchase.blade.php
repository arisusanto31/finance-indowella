<x-app-layout>
    <div>
        Kredit
        <button type="button" class="btn btn-sm btn-primary-light ms-2" id="addKredit">+tambah</button>
      </div>
      <div id="div-kredit" class="kredit-wrapper">
        <!-- Baris kredit pertama -->
        <div id="kredit1" class="row  rowkredit g-2 mb-2 ">
          <div class="col-md-4">
            <select id="kcodegroup1" class="form-control select-coa">

            </select>
          </div>
          <div class="col-md-4">
            <input id="knote1" type="text" class="form-control" placeholder="Note">
          </div>
          <div class="col-md-4">
            <input id="kamount1" type="text" class="form-control currency-input" placeholder="Amount">
          </div>
        </div>
      </div>
    </x-app-layout>