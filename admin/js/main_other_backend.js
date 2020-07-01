var _include = {};

$(function() {
    $(".user.user-menu img").css({ "height": "35px" })
        //select預選
    $("select").each(function() {
        var data_selected = $(this).attr('data_selected');
        if (typeof data_selected !== typeof undefined && data_selected !== false) {
            $(this).children("option[value=" + data_selected + "]").attr("selected", "selected");
        }
    })
    $.each($("select[set=find]"), function(n, v) {
        let y = getUrlParam(v.name)
        $.map($(v).find('option'), function(elt, i) {
            if (y == $(elt).val()) {
                $(elt).attr("selected", true)

            }
        });

    });

    $('input[type="checkbox"],input[type="radio"]').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue',

    });

    //列表批次選取
    $(".checkbox-toggle").click(function() {
        var clicks = $(this).data('clicks');
        if (clicks) {
            //Uncheck all checkboxes
            $("input[type='checkbox']").iCheck("uncheck");
            $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
        } else {
            //Check all checkboxes
            $("input[type='checkbox']").iCheck("check");
            $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
        }
        $(this).data("clicks", !clicks);
    });
    //權限
    $('.iCheck-helper').click(function(event) {
        set_open($(this).prev("input"))
    });
    //###############以下為列表頁面共用 fun#######################//
    if ((location.pathname).indexOf("_list.php") >= 0) {

        //列表批次刪除
        $("[name=data_del]").click(function(event) {
            var encrypt = [];
            var id = $('input[name=box_list]:checked').map(function() {
                encrypt.push($('input[name=encrypt_' + $(this).val() + ']').val());
                return $(this).val();
            }).get().join(",");

            var tables = $(this).attr("tables");
            var field = $(this).attr("field");
            encrypt = encrypt.join(",");

            if (id != "") {
                if (confirm("確定刪除?")) {
                    ajax_pub("ajax_button.php", { tables: tables, field: field, encrypt: encrypt, method: "de_lete", page: getfilename() }, function(data) {
                        console.log(data);
                        alert(data.message);
                        window.location.reload();
                    });
                    return false;

                }
            } else {
                alert("請先勾選要刪除的資料!");
            }
        });

        //後台下拉選單會用到的
        $("[set=find],input[keyword_date]").change(function() {
                FindKeyword();
            })
            //後台搜尋列表會用到的
        $("[name=search_button]").click(function() {
                FindKeyword();
            })
            //排序
        $("input[name=orders]").keypress(function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 2) this.value = this.value.substr(0, 2);

        })



        //儲存
        $("button[set=save]").click(function() {

            var set_field = $(this).attr("set_field");
            var tables = $(this).attr("tables");
            var where_field = $(this).attr("where_field");
            var set_field_encrypt = $(this).attr("set_field_encrypt");
            var tr = $(this).parents("tr");
            var set_field_content = $.trim($(tr).find("[name=" + set_field + "]").val())
                //var set_field_content = $(tr).find("[name="+set_field+"]").val();
            ajax_pub("ajax_button.php", { where_field: where_field, set_field: set_field, tables: tables, set_field_encrypt: set_field_encrypt, set_field_content: set_field_content, method: "save_note" }, function(data) {

                alert(data.message);
                window.location.reload();
            });
            return false;

        });
        /*結尾*/
    }

    if ((location.pathname).indexOf("_add.php") >= 0 || (location.pathname).indexOf("_update.php") >= 0) {
        $("input").attr("disabled", false);

        $.each($("select"), function(n, v) {
            var Val = $(v).attr("value");

            $.map($(v).find('option'), function(elt, i) {
                if (Val == $(elt).val()) {
                    $(elt).attr("selected", true)
                        // console.log($(elt).val())
                }
            });
        })

        $("input[type=checkbox]").change(function() {

            var ckb = $(this).attr("checkbox");
            var tmp = [];
            $("[checkbox=" + ckb + "][type=checkbox]").map(function() {
                if ($(this).prop("checked")) {
                    tmp.push($(this).val());

                }
            })
            $("input[name=" + ckb + "][req=Y]").val(tmp.join(","))
        })

        /*照片處理 */
        $('.file,[set=file-image]').each(function(m, n) {

            if (n.value != "") {

                previewImage(n.id)
            }

        })
        $('.file,[set=file-image]').change(function() {
            previewImage(this.id)

            // var photo=$(this).attr("name");//input的值
            // $("div[name="+photo+"]").attr("photo","")
            // $("input[set_id="+photo+"]").val("")
            // console.log(this.id)
        });

        $(".cancel-file ").click(function() {
            var this2 = $(this);
            var name = $(this).attr("name");
            var img = $(this).attr("value");
            var $str_array = {};
            if (Boolean($("[name=edit]").val())) {

                var photo = $(this).attr("photo");
                var field = $(this).attr("field");
                var tables = $(this).attr("tables");
                var id = $(this).attr("id");
                $str_array["name"] = name;
                $str_array["photo"] = photo;
                $str_array["field"] = field;
                $str_array["id"] = id;
                $str_array["tables"] = tables;
                $str_array["method"] = "de_lete_photo";


                if (Boolean(photo)) {
                    ajax_pub("ajax_button.php", $str_array, function(data) {
                        if (data.state == 1) {

                            $("#" + name).val('');
                            $(this2).prev().css("background-image", "url('')");
                            $(this2).attr("photo", "");
                            $("[set_id=" + name + "]").val('');
                        } else $("#" + name).val(img);
                        alert(data.message);

                    });
                } else {
                    $("#" + name).val('');
                    $(this).prev().css("background-image", "url('')");
                }

            } else {
                $("#" + name).val('');
                $(this).prev().css("background-image", "url('')");
            }
            // $("#" + tmp).val('');
            // $(this).prev().css("background-image", "url('')");
        })

        /*送出 */
        $("[type=submit]").click(function(e) {
            var sum_arr = [];
            $("input[not]").attr("disabled", true);

            $("[req=Y]").each(function() {
                var tooltips = $(this).attr("data-toggle");
                var title = Boolean(tooltips) ? $.trim($(this).attr("data-original-title")) : $.trim($(this).attr("title"));
                if (this.type == "checkbox") {
                    if ($(this).prop("checked") == false) {
                        sum_arr.push(title);
                    }
                }
                if ($(this).val() == "" || $(this).val() == null) {
                    sum_arr.push(title);
                } else {
                    if ((this.name).indexOf("email") >= 0) {
                        if (validateEmail($(this).val()) == false) {
                            sum_arr.push("電子郵件格式錯誤");
                        }
                    }
                }
            })

            if ($("#password1").val() !== $("#password2").val() && $("#password1").val() !== "" && $("#password2").val() !== "") {
                var title = $.trim($(this).attr("title"));
                sum_arr.push("兩次輸入的密碼不同");
            }

            if (sum_arr.length > 0) {
                sum_arr = sum_arr.join("、");
                alert(sum_arr);
                (e.preventDefault) ? e.preventDefault(): e.returnValue = false;
                return false

            }

        });

    }


    if (getfilename() != "") {
        let str = "";
        str = getfilename().replace('.php', '').replace('-', '_');
        if (Boolean(str)) {
            try {
                // Output document.title
                eval('_include.' + str + '();')
                    //console.log(`Title is: ${document.title}`);
            } catch (e) {
                if (e instanceof TypeError) {
                    //console.log(e, true);
                } else {
                    //  console.log(e, false);
                }
            }

        }
    }
})

//侧選單效果
if (getUrlParam("bktitle")) {
    var title_tmp = (getUrlParam("bktitle")).split("-");
    $("header.page-header > h2:first").text(title_tmp[0])
    $("header.panel-heading > h2:first").text(title_tmp[1])
        //後台左選單展開
    $("li.nav-parent > ul.nav-children > li > a").each(function() {
        var bktitle = (getUrlParam("bktitle").split("-"))[1]
        if ($.trim($(this).text()) == bktitle)
            $(this).parents("li.nav-parent").addClass("nav-expanded");
    })
}

//###############以下為特定頁面fun#######################//

var FindKeyword = function() {

        var hl = [];
        var arr = [];
        $.each($("input[keyword_date]"), function(n, v) {
            hl.push(this.name + "=" + this.value);
        })

        $.each($("[set=find]"), function(n, v) {
            if (!hl.includes(this.localName)) {
                hl.push(this.name + "=" + this.value);
            }
        })


        document.location.href = getfilename() + "?" + hl.join("&");
    }
    //啟用開關
function set_open(self) {
    //console.log(self);
    var self_uuid = $(self).attr("self_uuid")
    var self_table = $(self).attr("self_table")
    var self_col = $(self).attr("self_col")
    var where_field = $(self).attr("where_field")
    var self_val = parseInt($(self).parents("tr").find("input[type=radio]:checked").val())

    if (Boolean(self_uuid) && 　Boolean(self_table) && 　Boolean(self_col) && 　Boolean(where_field) && 　(self_val == 0 || self_val == 1)) {
        if (confirm("確定要變更?")) {

            ajax_pub("ajax_button.php", { method: "open", self_uuid: self_uuid, self_val: self_val, self_col: self_col, self_table: self_table, where_field: where_field }, function(dt) {
                alert(dt.message);
                location.reload();
            })

        } else {
            location.reload();
        }
    }
}
//###############以下為特定頁面fun#######################//
function previewFile(pr) {

    select_area = pr
        //console.log(pr)

    if (Boolean($("#" + select_area)[0])) {
        var reader = new FileReader();
        var file = $("#" + select_area)[0].files[0];

        if (Boolean(file)) {

            reader.onload = function(e) {
                if (file.size) {
                    $('div.fileupload-new').removeClass('fileupload-new');
                    $('span.fileupload-preview').text(file.name);
                }

            };
            reader.readAsDataURL(file);
        }

    }


}

function previewImage(pr) {

    select_area = pr
        //console.log(pr)

    if (Boolean($("#" + select_area)[0])) {
        var reader = new FileReader();
        var file = $("#" + select_area)[0].files[0];

        if (Boolean(file)) {


            reader.onload = function(e) {

                var image = new Image();

                image.src = e.target.result;

                $("label[for=" + pr + "]").css("background-image", "url(" + e.target.result + ")");
            };
            reader.readAsDataURL(file);

        }

    }


}


function ajax_pub(url, data, scuess_call) {
    if ('token' in data || 'value' in data) {
        console.log("將使用新的token與value覆蓋原本參數");
    }
    var temp = gettoken_value();
    var value = temp.value;
    var token = temp.token;
    var obj2 = { "value": value, "token": token };

    var promise = $.ajax({
        url: url,
        data: $.extend(data, obj2),
        async: true,
        timeout: 8000,
        method: "POST",
        dataType: "json",
        beforeSend: function() {}
    });
    promise.done(scuess_call);
    promise.fail(function(data) {
        //showAlert("網路異常，請稍後再試");
        console.log(data);
    });
    promise.always(function(data) {});
}

function show_password() { //顯示密碼

    $.each($("[set=password]"), function(a, b) {

        if ($(b).attr("type") == "text") {
            $(b).attr('type', 'password');
            $('#show_hide_password i').addClass("fa-eye-slash");
            $('#show_hide_password i').removeClass("fa-eye");
        } else if ($(b).attr("type") == "password") {
            $(b).attr('type', 'text');
            $('#show_hide_password i').removeClass("fa-eye-slash");
            $('#show_hide_password i').addClass("fa-eye");
        }
    })

}

function set_orders(self) { //設定排序
    //var order = $(self).parents("tr").find("[name=orders]");
    var order = $(self);
    var set_field_int = $.trim(order.val());
    var set_field = $(order).attr("set_field");
    var tables = $(order).attr("tables");
    var where_field = $(order).attr("where_field");
    var where_field_int = $(order).attr("where_field_int");
    var min = parseInt($(order).attr("min"));
    var max = parseInt($(order).attr("max"));

    if (set_field_int == "")
        alert("不可為空");
    else if (set_field_int < min || set_field_int > max)
        alert("資料必須介於" + min + "~" + max + "");

    else {
        ajax_pub("ajax_button.php", { set_field_int: set_field_int, set_field: set_field, tables: tables, where_field: where_field, where_field_int: where_field_int, method: "orders" }, function(data) {

            //alert(data.message);
            window.location.reload();
        });
        return false;

    }
}