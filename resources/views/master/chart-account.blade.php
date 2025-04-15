<x-app-layout>

    <div class="card ">
        <div class="card-body">
            <h5 class="card-title text-primary">📋 Daftar chart account </h5>
            <div class="row pa-5">
                <div class="col-xs-12 bglevel1">
                    <button class=" mb-10 btn-primary" onclick="createNewAccount()"> + Buat Account</button>
                    <div class=" pa-10 bglevel2 br-10 colorblack" style="min-height:70vh" id="container-account">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="div-form-small" style="width:90vw; max-width:400px; border:1px solid #888;position:fixed" class="hidden fixed-center relativepos bglevel3 colorblack br-10 pa-5">
        <div class="absolutepos" style="right:0px; top:0px;">
            <a href="javascript:void(hideFormSmall())">
                <div class="colorwhite flex flex-center" style="border-radius:50%; width:25px; height:25px; background-color:red">
                    <i class="colorwhite fas fa-close"></i>
                </div>
            </a>
        </div>
        <div class="textcenter " id="form-title">
            <p style="font-size:25px;"> FORM </p>
        </div>
        <div id="form-body" class="text-primary-dark">

        </div>

    </div>

    @push('scripts')
    <script>
        function hideFormSmall() {
            $('#div-form-small').addClass('hidden');
        }

        function showFormSmall() {
            $('#div-form-small').removeClass('hidden');
        }


        function editAccount(id) {
            loading(1);
            $.ajax({
                url: '{{url("admin/get-chart-account")}}/' + id,
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        loading(0);

                        data = res.msg;
                        theCodeGrop = data.code_group.split("");

                        html = "";
                        html += '<form id="form-new-account">';
                        html += ' {{csrf_field()}} ';
                        html += '<div class="row"> ';
                        html += '  <div class="col-xs-12">';
                        html += '      <label>parent</label>';
                        html += '        <input type="hidden" name="id" value="' + data.id + '"/>'
                        html += '        <select class="form-control" id="account-parent_id" onchange="getCodeGroup()" name="parent_id" >';
                        if (data.parent_id != null)
                            html += '               <option value="' + data.parent_id + '" >' + data.parent.name + '</option>';
                        html += '        </select>';
                        html += '  </div>';
                        html += '  <div class="col-xs-12">';
                        html += '      <label>Code group</label>';
                        html += '        <div class="row"> ';
                        html += '            <div class="col-xs-2 pa-5">';
                        html += '              <input class="theinput form-control" value="' + theCodeGrop[0] + '" id="code1" onkeyup="moveInput(2)" name="code_group[]" /> ';
                        html += '            </div>';
                        html += '            <div class="col-xs-2 pa-5">';
                        html += '              <input class="theinput form-control" value="' + theCodeGrop[1] + '" id="code2" onkeyup="moveInput(3)" name="code_group[]" /> ';
                        html += '            </div>';
                        html += '            <div class="col-xs-2 pa-5">';
                        html += '              <input class="theinput form-control" value="' + theCodeGrop[2] + '" id="code3" onkeyup="moveInput(4)" name="code_group[]" /> ';
                        html += '            </div>';
                        html += '            <div class="col-xs-2 pa-5">';
                        html += '              <input class="theinput form-control" value="' + theCodeGrop[3] + '" id="code4" onkeyup="moveInput(5)" name="code_group[]" /> ';
                        html += '            </div>';
                        html += '            <div class="col-xs-2 pa-5">';
                        html += '              <input class="theinput form-control" value="' + theCodeGrop[4] + '" id="code5" onkeyup="moveInput(6)" name="code_group[]" /> ';
                        html += '            </div>';
                        html += '            <div class="col-xs-2 pa-5">';
                        html += '              <input class="theinput form-control" value="' + theCodeGrop[5] + '" id="code6" name="code_group[]" /> ';
                        html += '            </div>';
                        html += '        </div>';
                        html += '  </div>';
                        html += '  <div class="col-xs-12">';
                        html += '       <label>Name</label>';
                        html += '       <input class="form-control" value="' + data.name + '" id="account-name" name="name" />';
                        html += '  </div>';
                        html += '  <div class="col-xs-12">';
                        html += '       <label>Account Type</label>';
                        html += '       <select class="form-control" id="account-account_type" name="account_type" >';
                        html += '            <option value="Aset" >Aset</option>';
                        html += '            <option value="Kewajiban" >Kewajiban</option>';
                        html += '            <option value="Ekuitas" >Ekuitas</option>';
                        html += '            <option value="Pendapatan" >Pendapatan</option>';
                        html += '            <option value="Beban" >Beban</option>';
                        html += '       </select>';
                        html += '  </div>';
                        html += '  <div class="col-xs-12">';
                        html += '       <button class="btn-control" type="button" onclick="submitNewAccount()">Submit</button>';
                        html += '  </div>';
                        html += '</div>';
                        html += '</form>';
                        $('#form-body').html(html);
                        $('#account-account_type').val(data.account_type);
                        $('#form-title').html('FORM ADD ACCOUNT');
                        initItemSelectManual('#account-parent_id', '{{url("admin/get-item-chart-account-all")}}');
                        showFormSmall();
                    } else {
                        loading(0);
                        swal('ops', res.msg);
                    }
                },
                error: function(res) {
                    loading(0);
                    swal('ops', 'something error');
                }
            });

        }

        function createNewAccount() {
            html = "";
            html += '<form id="form-new-account">';
            html += ' {{csrf_field()}} ';
            html += '<div class="row"> ';
            html += '  <div class="col-xs-12">';
            html += '      <label>parent</label>';
            html += '       <select class="form-control" id="account-parent_id" onchange="getCodeGroup()" name="parent_id" />';
            html += '  </div>';
            html += '  <div class="col-xs-12">';
            html += '      <label>Code group</label>';
            html += '        <div class="row"> ';
            html += '            <div class="col-xs-2 pa-5">';
            html += '              <input class="theinput form-control" id="code1" onkeyup="moveInput(2)" name="code_group[]" /> ';
            html += '            </div>';
            html += '            <div class="col-xs-2 pa-5">';
            html += '              <input class="theinput form-control" id="code2" onkeyup="moveInput(3)" name="code_group[]" /> ';
            html += '            </div>';
            html += '            <div class="col-xs-2 pa-5">';
            html += '              <input class="theinput form-control" id="code3" onkeyup="moveInput(4)" name="code_group[]" /> ';
            html += '            </div>';
            html += '            <div class="col-xs-2 pa-5">';
            html += '              <input class="theinput form-control" id="code4" onkeyup="moveInput(5)" name="code_group[]" /> ';
            html += '            </div>';
            html += '            <div class="col-xs-2 pa-5">';
            html += '              <input class="theinput form-control" id="code5" onkeyup="moveInput(6)" name="code_group[]" /> ';
            html += '            </div>';
            html += '            <div class="col-xs-2 pa-5">';
            html += '              <input class="theinput form-control" id="code6" name="code_group[]" /> ';
            html += '            </div>';
            html += '        </div>';
            html += '  </div>';
            html += '  <div class="col-xs-12">';
            html += '       <label>Name</label>';
            html += '       <input class="form-control" id="account-name" name="name" />';
            html += '  </div>';
            html += '  <div class="col-xs-12">';
            html += '       <label>Account Type</label>';
            html += '       <select class="form-control" id="account-account_type" name="account_type" >';
            html += '            <option value="Aset" >Aset</option>';
            html += '            <option value="Kewajiban" >Kewajiban</option>';
            html += '            <option value="Ekuitas" >Ekuitas</option>';
            html += '            <option value="Pendapatan" >Pendapatan</option>';
            html += '            <option value="Beban" >Beban</option>';
            html += '       </select>';
            html += '  </div>';
            html += '  <div class="col-xs-12">';
            html += '       <button class="btn-control" type="button" onclick="submitNewAccount()">Submit</button>';
            html += '  </div>';
            html += '</div>';
            html += '</form>';
            $('#form-body').html(html);
            $('#form-title').html('FORM ADD ACCOUNT');
            initItemSelectManual('#account-parent_id', '{{url("admin/get-item-chart-account-all")}}');
            showFormSmall();
        }

        function moveInput(id) {
            $('#code' + id).focus();
        }

        function getCodeGroup() {
            parentID = $('#account-parent_id option:selected').val();

            $.ajax({
                url: '{{url("admin/get-code-group-account")}}/' + parentID,
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        number = res.msg;
                        stringNumber = number.toString();
                        i = 1;
                        $('.theinput').val("");
                        $('.theinput').prop("readonly", false);
                        Array.from(stringNumber).forEach(char => {
                            $('#code' + i).val(char);
                            $('#code' + i).prop("readonly", true);
                            i++
                        });
                        $('#account-account_type').val(res.account_type);
                    }
                },
                error: function(res) {

                }
            });
        }

        function submitNewAccount() {
            $.ajax({
                url: '{{url("admin/master/chart-account")}}',
                method: 'post',
                data: $('#form-new-account').serialize(),
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        swal_success('success', 'data berhasil disubmit');
                        hideFormSmall();
                        setTimeout(getChartAccount, 100);
                    } else {
                        swal("error", res.msg);
                    }
                },
                error: function(res) {
                    swal('opps', 'something wrong');
                }
            });
        }

        setTimeout(getChartAccount, 100);

        function tampilkan(data, master, margin) {
            html = "";
            isParent = master[data.id] != undefined ? true : false;
            stringParent = isParent ? '<i id="arrow' + data.id + '" class="bx bx-chevron-down toggle-icon "></i>' : '';
            if (data.parent_id == null)
                html += `<li class="menu-item " style="margin-left: ${margin}px" >
                         <a class="" href="javascript:void(openToggle('${data.id}'))"> 
                          <div class=""><strong> ${data.code_group} -  ${data.name} </strong>  ${stringParent} 
                                    ${data.reference_model?'<span class="bg-primary px-2 text-white">'+data.reference_model+'</span>':''}
                          </div>
                        </a>
                        </li>`;
            else
                html += `<li class="menu-item " style="margin-left:  ${margin}px" >
                         <a class="" href="javascript:void(openToggle('${data.id}'))"> 
                          <div class=""><strong> ${data.code_group} </strong> -  ${data.name} ${stringParent} 
                               ${data.reference_model?'<span class="bg-primary px-2 text-white">'+data.reference_model+'</span>':''}

                          </div>
                        </a>
                        </li>`;

            if (master[data.id] != undefined) {
                //punya anak coy
                html += '<ul class=" tree-toggle" id="menu-sub' + data.id + '">';
                master[data.id].forEach(function eachDt(child) {
                    html += tampilkan(child, master, margin + 5);
                });
                html += '</ul>';
            }

            return html;
        }


        function getChartAccount() {
            $.ajax({
                url: '{{url("admin/master/chart-account/get-chart-accounts")}}',
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        html = "";
                        margin = 0;
                        html += '<ul class="">';
                        res.msg[""].forEach(function(data) {
                            html += tampilkan(data, res.msg, margin);
                        });
                        html += '</ul>';
                        $('#container-account').html(html);

                    } else {

                    }
                },
                error: function(res) {}
            });
        }

        function openToggle(id) {
            $('#menu-sub' + id).toggleClass('open');
            $('#arrow' + id).toggleClass('open');
        }
    </script>
    @endpush
</x-app-layout>