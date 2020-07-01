function set_after(self){//設定排序
    var order=$(self).parents("tr").find("[name=orders]");
    var set_field_int = $.trim(order.val());
    var set_field = $(order).attr("set_field");
    var tables = $(order).attr("tables");
    var where_field = $(order).attr("where_field");
    var where_field_int = $(order).attr("where_field_int");
    var min = parseInt($(order).attr("min"));
    var max = parseInt($(order).attr("max"));
   
   if (set_field_int!="" && (set_field_int < min || set_field_int > max))
        alert("資料必須介於" + min + "~" + max + "");
    else {
        ajax_pub("ajax_button.php", { set_field_int: set_field_int, set_field: set_field, tables: tables, where_field: where_field, where_field_int: where_field_int,method:"orders" }, function(data) {
       
            alert(data.message);
            window.location.reload();
      });
      return false;
       
    }
}