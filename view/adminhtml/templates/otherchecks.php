<style>

 

.stock-coloured td.col-t_stock{background: #f03939 !important;}
.stock-coloured:hover td.col-t_stock{background: #f03939 !important;}
#system_messages{display: none;}
    .page-wrapper{width: 100% !important;}
    .pallex-popup{
        background: green none repeat scroll 0 0;
        border: medium none;
        color: #ffffff;
        cursor: pointer;
        display: none;
        font-size: 32px;
        padding: 15% 10%;
        text-align: center;
        text-decoration: none;
    }
    .page-wrapper{
        background-color: #dcdcdc
    }
    .top-bar{
        width: 100%;
        background-color: #3c3d3b;
        padding:10px;
        margin-top: -40px;
        height: 70px;
    }
    .top-bar .left-box, .top-bar .middle-box, .top-bar .right-box{
        display: inline-block;
    }
    .top-bar .left-box{
        width: 20%;
    }
    .top-bar .middle-box{
        width: auto;
    }
    .top-bar .right-box{
        width: auto;
        position: absolute;
    }
    .top-bar .middle-box img{
        width: 113px;
    }
    .top-bar .right-box h2{
        margin: 0px 0px 0px 40px;
        color: #fff;
        font-size: 25px;
        font-weight: bold;
        line-height: 2;
    }
    .top-bar .right-box h2, .top-bar .right-box select{
        display: inline-block;
    }
    .top-bar .right-box select{
        background: #4e4e4d url(/pub/static/adminhtml/Magento/backend/en_US/TM_PalletQueue/images/dp-arrow.png) no-repeat scroll right center;
        border: 0 none;
        border-radius: 0;
        color: #fff;
        font-size: 23px;
        font-weight: normal;
        height: 100%;
        line-height: 1;
        padding: 20px 67px 20px 5px;
        -webkit-appearance: none;
        margin-top: -10px;
        margin-left: 5px;
    }
    .clear{
        clear: both;
    }
    .third-row .col-3-1{
        display: inline-block;
        width: 15%;
        margin-right: 10px;
        padding:10px;
        margin-bottom: 10px;
    }
    .third-row .col-3-1 img{
        width: 100%;
    }
     .third-row .col-3-1 .item-name{
        color: #58595b;
        font-size: 18px;
        font-weight: bold;
        line-height: 22px;
        text-align: center;
        min-height: 44px;
    }
    .third-row .col-3-1 .item-size {
        color: #58595b;
        font-size: 14px;
        text-align: center;
    }
    .third-row .col-3-1 .item-sample {
        font-size: 22px;
        margin-top: 10px !important;
          color: #58595b;
        text-align: center;
    }
    .order-modal .action-close{
        display: none;
    }
    .order-modal .modal-header {
        padding-bottom:  0rem;
        padding-top: 0rem;
    }
    .order-modal .modal-header, .order-modal .modal-content, .order-modal .modal-footer {
        padding: 0 0rem 0rem !important;
    }
    .order-modal .modal-header, .order-modal .modal-content, .order-modal .modal-footer {
        padding-left: 0rem; 
        padding-right: 0rem; 
    }
    
.nav-error-tile {
    width: 150px;
    height: 150px;
    background: #0A5AC3;
    text-align: center;
    line-height: 115px;
    font-size: 40px;
    font-weight: bold;
    position: relative;
    color: #fff;
    margin-top:20px ;
    float: left;
    margin-right: 20px;
}
.nav-error-tile.click-able{cursor: pointer;}
.nav-error-tile .title {
    position: absolute;
bottom: 0;
left: 0;
font-size: 15px;
line-height: normal;
text-align: left;
width: 88%;
padding: 5px 6%;
font-weight: initial;

}
.text-block {
    display: block;
    margin: 0 auto;
    width: 130px;
    text-align: right;
}
.img-block{float: left;
display: inline-block;
margin-top: 30px;
height: 48px;
}
.item-qty {
    display: inline-block;
    background: #000;
    color: #fff;
    width: 35px;
    height: 35px;
    text-align: center;
    line-height: 35px;
    font-size: 20px;
    border-radius: 7px;
    margin-right: 10px;
    margin-bottom: 10px;
}
.item-detail {
    display: block;
}
.new-button {
    float: left;
    width: 100%;
    margin: 15px 0;
}

.new-button span {
    width: 150px;
    height: 35px;
    display: block;
    border: 2px solid;
    font-size: 20px;
    text-align: center;
    font-weight: bold;
    line-height: 35px;
    cursor: pointer;
}

textarea {
    width: 100%;
    height: 200px;
}

.pop-btn {
    margin-bottom:20px;
}
</style>

<div class="top-bar">
    <div class="right-box">
        <div class="search-box">
            <h2>Vehicle Safety Checks</h2>
       </div>
    </div>
</div>
<div id="items_html" class="third-row">
</div>
 
<script>

require(['jquery','mage/url'], function($,url) {

$( document ).ready(function() {
    getgriddetail(1,false);
    loadgrid(1);
}) 

});
 
function getgriddetail(return_type, event){
    
    var data = {"return_type":return_type};
      jQuery.ajax({
                showLoader: true,
                url: '<?php echo $block->getUrl("androidservices/otherchecks/grid") ?>',
                data: data,
                type: "GET",
                dataType: 'json'
            }).done(function (response) {
                jQuery("#items_html").html(response.Block);
                if(event != false)
                {
                    jQuery(".tablinks").removeClass("active");
                    event.target.classList.add("active");
                }
    }).fail(function (errors) {
        console.log(errors);
        
    }); 
}

function loadgrid(return_type){
    console.log("grid loaded");
    var data = {};
    jQuery.ajax({
                showLoader: true,
                url: '<?php echo $block->getUrl("androidservices/otherchecks/grid") ?>',
                data: data,
                type: "GET",
                dataType: 'json'
            }).done(function (response) {
                jQuery("#items_html").html(response.Block);
    }).fail(function (errors) {
        console.log(errors);
        
    }); 
}
 
</script>

<script>
require([
    'jquery',
    "uiRegistry",
    'Magento_Ui/js/modal/alert',
    'prototype'
], function(jQuery, registry, alert) {

//<![CDATA[
function refreshReturnsGrid(grid, gridMassAction, transport) {
    grid.reload();
    gridMassAction.unselectAll();
    // getActivity();
}
window.refreshReturnsGrid = refreshReturnsGrid;
});

</script>

