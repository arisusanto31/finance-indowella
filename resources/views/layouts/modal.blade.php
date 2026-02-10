<div class="modal fade" id="global-modal" data-bs-backdrop="static"  tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" id="global-modal-content">

        </div>
    </div>
</div>

<div id="load" class="hidden fixed-center " style="z-index:5000">
    <img id="img-load" style="width:100px; height:auto;" src="{{ asset('dist/img/Spin_trans_1.gif') }}"></img>
</div>

<div id="div-progressbar" class="hidden fixed-on-bottom" style="z-index:5000; width:200px;">
    <div class="card p-3 br-2" style="box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);">
        <div class="text-center mb-2">Processing...</div>
        <div class="progress">
            <div id="progress-bar-inner" class="progress-bar progress-bar-striped progress-bar-animated"
                role="progressbar" style="width: 80%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">80%</div>
        </div>
    </div>
</div>
