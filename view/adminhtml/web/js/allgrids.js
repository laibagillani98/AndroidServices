require(["jquery"],function($) {
    $("#login-user").html("Tablet Picking");
    $("body").on("click",".grid_detail",function(event) {
        jQuery('.grid_detail').removeClass("selected");
        $(this).addClass("selected");
        var gridtype = $(this).attr('id');
        $("#"+gridtype).addClass("selected");
        document.cookie = "type="+gridtype;
        getGrid(gridtype);
    });

    $("body").on("click",".comment_dialog",function(event){
        var id = $(this).attr('id');
        var actionToCall = $(this).attr('data-action');
        $("#record_id").val(id);
        $("#action_type").val(actionToCall);
        $("#commentModal").show();
    });
    $("body").on("click",".remove_batch",function(event){
        var id = $(this).attr('id');
        var base_url = window.location.origin;
        var Url = base_url + "/admin/androidservices/dhlgrids/removeuser";
        // var id = 1;
        $.ajax({
            showLoader : true,
            type: "POST",
            data: {id:id},
            url: Url,
            success: function(returndata) {
                console.log(returndata);
                getGrid(getCookie('type'));
            }
        });
    });
    $("body").on("change",".selectUser",function(event){
        var id = $(this).attr('id');
        var user = $(this).val();
        var base_url = window.location.origin;
        var Url = base_url + "/admin/androidservices/gridactions/assignuser";

        $.ajax({
            showLoader : true,
            type: "POST",
            data: {id:id,user:user,form_key: window.FORM_KEY},
            url: Url,
            success: function(returndata) {
                getGrid(getCookie('type'));
            }
        });//ajax end
    });

    $("body").on("click","#call_action",function(event){

        var id = $("#record_id").val();
        var action = $("#action_type").val();
        var comment = $("#admin_comment").val();
        if (comment == "" || comment == null) {
            document.getElementById("admin_comment").style.border = "2px solid red";
            return false;
        }else{
            document.getElementById("admin_comment").style.border = "2px solid black";
        }
        //console.log(id+" sds "+action);
        var base_url = window.location.origin;
        var Url = base_url + "/admin/androidservices/gridactions/"+action;

        $.ajax({
            showLoader : true,
            type: "POST",
            data: {id:id,comment:comment,form_key: window.FORM_KEY},
            url: Url,
            success: function(returndata) {
                $(".modal-close").trigger("click");
                getGrid(getCookie('type'));
            }
        });//ajax end
    });
    $("body").on("click","#call_breakage_action",function(event){

        var id = $("#record_id").val();
        var action = $("#action_type").val();
        var comment = $("#admin_comment").val();
        if (comment == "" || comment == null) {
            document.getElementById("admin_comment").style.border = "2px solid red";
            return false;
        }else{
            document.getElementById("admin_comment").style.border = "2px solid black";
        }
        //console.log(id+" sds "+action);
        var base_url = window.location.origin;
        var Url = base_url + "/admin/androidservices/breakages/"+action;

        $.ajax({
            showLoader : true,
            type: "POST",
            data: {id:id,comment:comment,form_key: window.FORM_KEY},
            url: Url,
            success: function(returndata) {
                $(".modal-close").trigger("click");
                getGrid(getCookie('type'));
            }
        });//ajax end
    });
    $("body").on("click",".modal-close",function(event){

        $("#commentModal").hide();
        $("#record_id").val("");
        $("#action_type").val("");
        $("#admin_comment").val("");
        document.getElementById("admin_comment").style.border = "2px solid black";
    });

    function getGrid(gridtype) {
        var base_url = window.location.origin;
        var gridUrl = base_url + "/admin/androidservices/tabletqueuegrids/ajaxgrids";

        $.ajax({
            //cache: true,
            showLoader : true,
            type: "POST",
            data: {type:gridtype,form_key: window.FORM_KEY},
            url: gridUrl,
            success: function(returndata) {
                $("#grid-detail").html(returndata);
                getdata();
            }

        });
    }

    function getdata(){
        var base_url = window.location.origin;
        var dataUrl = base_url + "/admin/androidservices/main/ajaxgridsdata";
        jQuery.ajax({
            //cache: true,
            type: "POST",
            showLoader : true,
            data: {form_key: window.FORM_KEY},
            dataType: "json",
            url: dataUrl,
            success: function(returndata) {

                jQuery(".broken-count").html(returndata.success.brokencount);
                jQuery(".queued-count").html(returndata.success.queuedcount);
                jQuery(".ontablet-count").html(returndata.success.ontabletcount);
                jQuery(".problem-count").html(returndata.success.problemcount);
                jQuery(".shop-count").html(returndata.success.shopcount);
                jQuery(".checking-count").html(returndata.success.checkingcount);
                jQuery(".return-count").html(returndata.success.returncount);
                jQuery(".blocation-count").html(returndata.success.blocationcount);
                jQuery(".brokentiles-count").html(returndata.success.brokentilescount);
                jQuery(".DHL_Queue").html(returndata.success.DHL_Queue);
                jQuery(".DHLBatch_Queue").html(returndata.success.DHLBatch_Queue);
                jQuery(".PendingCombinePick").html(returndata.success.PendingCombinePick);
                jQuery(".ActiveCombinePick").html(returndata.success.ActiveCombinePick);
                jQuery(".receiving-count").html(returndata.success.receivingcount);
                jQuery(".loading-count").html(returndata.success.loadingcount);

                if(returndata.showrooms){
                    returndata.showrooms.forEach(function (showrooms) {
                        var idSelector = "." + showrooms['id'] + "-count";
                         jQuery(idSelector).html(showrooms['count']);
                    });
                }
                if(returndata.HuskyShopCollection){
                    returndata.HuskyShopCollection.forEach(function (HuskyShopCollection) {
                        var idSelector = "." + HuskyShopCollection['id'] + "-collectioncount";
                         jQuery(idSelector).html(HuskyShopCollection['count']);
                    });
                }
                if(returndata.huskyProblem){
                    returndata.huskyProblem.forEach(function (huskyProblem) {
                        var idSelector = "." + huskyProblem['id'] + "-problemcount";
                         jQuery(idSelector).html(huskyProblem['count']);
                    });
                }
            }
        });
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        // console.log(" decodedCookie ***************** "+cname);
        // console.log(decodedCookie);
        var ca = decodedCookie.split(';');
        //console.log(" CAAAAAAAAAAAAAAAAAAAAAAAAAAA");
        // console.log(ca);
        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            // console.log(" cIndexof *********** "+c.indexOf(name));
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }


    //Start of Document Ready Function
   $(document).ready(function()
   {
       var gridtype = getCookie('type');
       if (gridtype === ''){
           gridtype = "completed_orders";
       }
       $("#"+gridtype).addClass("selected");
       getGrid(gridtype);
        setInterval(function() {
            getGrid(getCookie('type'));
        }, 60000);
   }); // end of document ready function

});


