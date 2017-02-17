<style>
  input[type="number"]::-webkit-outer-spin-button,
  input[type="number"]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
  }
  input[type="number"] {
      -moz-appearance: textfield;
  }
</style>
<?php //print_r($data_post['first_name']); ?>
<!-- Horizontal Form -->
  <div class="box box-info">
    <div class="box-header with-border">
      <h3 class="box-title">Pembelian Pulsa PLN</h3>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
    <form id="form" class="form-horizontal" action="" method="post">
      <div class="box-body">
      <div class="col-md-12">
        <div class="form-group">
            <label for="nomer" class="col-sm-2 control-label">Nomer</label>
            <div class="col-sm-4">
              <input type="number" required class="form-control" value="" name="nomer" id="nomer" placeholder="08XXX" onkeyup='saveValue(this);' >
            </div>
          </div>
      <div class="form-group" hidden>
            <label for="nominal" class="col-sm-2 control-label">Nominal</label>
            <div class="col-sm-4" >
              <select name="nominal" id="nominal" class="form-control" >
                <option value="">Isi Nomor terlebih dahulu</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="nominal" class="col-sm-2 control-label">Jumlah Tagihan (Rp)</label>
            <div class="col-sm-4">
              <input type="number" required class="form-control" value="" name="nominalbayar" id="nominalbayar" placeholder=""  >
            </div>
          </div>
          <div class="form-group">
            <label for="contact" class="col-sm-2 control-label">No. Hp</label>
            <div class="col-sm-4">
              <input type="text" required class="form-control" value="" name="contact" id="contact" placeholder=""  >
            </div>
          </div>
          <div class="form-group">
            <label for="email" class="col-sm-2 control-label">Email (optional)</label>
            <div class="col-sm-4">
              <input type="text"  class="form-control" value="" name="email" id="email" placeholder=""  >
            </div>
          </div>
          <div class="form-group">
            <label for="first_name" class="col-sm-2 control-label"></label>
            <div class="col-sm-4">
            <div id="warn"></div>
            </div>
          </div>
          
        </div>
        <!-- /.col -->
      </div>
      <!-- /.box-body -->
       <?php if($this->ion_auth->logged_in()){ ?>
      <div class="box-footer">
        <div class="col-sm-6">
          <button id="btn-submit" type="submit" class="btn btn-success pull-right "><i class="fa fa-paper-plane"></i> Submit</button>
        </div>
      </div>
      <?php } ?>
      <?php if(!$this->ion_auth->logged_in()){ ?>
      <div class="box-footer">
        <div class="col-sm-6">
          <a href="#" id="login-header" type="submit" class=" show-modal btn btn-success pull-right" 
          data-placement="top" data-toggle="popover" data-trigger="hover" data-content="You must login !" ><i class="fa fa-lock"></i> Submit</a>
        </div>
      </div>
      <?php } ?>
      <!-- /.box-footer -->
    </form>
  </div>
  <!-- /.box -->
  <script>
    $( document ).ready(function() {
      var no_prefix = [] ; var products = [];
      $.get( base_url+'assets/ajax/no_prefix.json', function(data) {
          $.each(data, function(i, item) {
              no_prefix [item.number]= item;
          });
      });
      
      get_products();
     
      function get_products(){
      $.get( base_url+'ppob/get_products', function(data) {
            $.each(data, function(i, item) {
                products[i]= item;
            });
        });
    }
      
      var key = '';
      $("#nomer").on("keyup", function(event) {
        get_number();
      });
      $("#nomer").on("mouseover", function(event) {
          get_number();
      });
       $("#nominalbayar").on("mouseover", function(event) {
          get_number();
      });
      function get_number(){
      var keytmp = $("#nomer").val().substring(0,4);
          //if($("#nomer").val().length > 3){
          if(key!=keytmp){
            key=keytmp;
            $("#nominal").html("");
              $.each(products, function(i, item) {
                  var v = item.kode.split(".");
                  if(v[0]=='BAYAR'){
                     $("#nominal").append($('<option>', {value: item.id+'_'+item.FT, text: 'pln'.toUpperCase() +' - '+ v[1] +'000 / '+ item.nta +' - '+item.base_price}));
                  }
              });
          }
         // } 
    }
      
      $("#form").on("submit", function(event) {       
        $("#btn-submit").removeClass('btn-success');
          $("#btn-submit").addClass('btn-warning');
          $("#btn-submit").attr('disabled',true);
          $("#btn-submit").children("i").removeClass('fa-paper-plane');
          $("#btn-submit").children("i").addClass('fa-refresh fa-spin');
          event.preventDefault(); 
          $.ajax({
              url:  base_url+"ppob/bayarTelkom",
              type: "post",
              data: $(this).serialize(),
              success: function(d, textStatus, xhr) {
                  
                showalert(d.message,'success','#warn',60000000);
                get_products();
                $("#btn-submit").addClass('btn-success');
              $("#btn-submit").removeClass('btn-warning');
              $("#btn-submit").attr('disabled',false);
              $("#btn-submit").children("i").addClass('fa-paper-plane');
              $("#btn-submit").children("i").removeClass('fa-refresh fa-spin');
              },
               error: function (request, status, error) {
                  
              }
          });
          
    });
  });

document.getElementById("nomer").value = getSavedValue("nomer");
function saveValue(e){
            var id = e.id;  
            var val = e.value; 
            localStorage.setItem(id, val);
        }
function getSavedValue  (v){
            if (localStorage.getItem(v) === null) {
                return "";
            }
            return localStorage.getItem(v);
        }
  </script>