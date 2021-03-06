<style>
	input[type="number"]::-webkit-outer-spin-button,
	input[type="number"]::-webkit-inner-spin-button {
	    -webkit-appearance: none;
	    margin: 0;
	}
	input[type="number"] {
	    -moz-appearance: textfield;
	}
	.err {
    	color: #ff1313;
	}
</style>
<?php //print_r($data_post['first_name']); ?>
<!-- Horizontal Form -->
  <div class="box box-info">
    <div class="box-header with-border">
      <h3 class="box-title">Pembelian Paket Data</h3>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
    	<form id="form" class="form-horizontal" action="<?= base_url() ?>ppob/confirm/data" method="post">
	      <div class="box-body">
	    	<div class="col-md-12">
	    		<div class="form-group">
		          <label for="nomer" class="col-sm-2 control-label">Nomer</label>
		          <div class="col-sm-4">
		            <input type="text" required class="form-control" value="<?php if($_GET){ echo $_GET['nomer'];} ?>" name="nomer" id="nomer" placeholder="08XXX">
		          </div>
		        </div>
				<div class="form-group">
		          <label for="nominal" class="col-sm-2 control-label">Paket Data</label>
		          <div class="col-sm-4">
		            <select name="nominal" required id="nominal" class="form-control" >
		            	<option value="">Isi Nomor terlebih dahulu</option>
		            </select>
		            <input name="type" id="type" type="hidden" value="pulsa"/>
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
  
	      <div class="box-footer">
	        <div class="col-sm-6">
	          <button id="btn-submit" type="submit" class="btn btn-success pull-right "><i class="fa fa-paper-plane"></i> Submit</button>
	        </div>
	      </div>
     	</form>
   </div>
  <!-- /.box -->
  <script>
  	$( document ).ready(function() {
  		
  		$('#form').validate({
		    rules: {
		        nomer: {
		            required: true,
		            minlength: 5
		        },
		        nominal: {
		            required: true
		        },
		    },
		    errorElement: "span",
	    	errorClass: "err",
		    errorPlacement: function(error, element) {
		    	error.insertAfter(element.parent());
			}	   
		});
  		
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
  			function getUrlVars() {
			    var vars = {};
			    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,    
			    function(m,key,value) {
			      vars[key] = value;
			    });
			    return vars;
			  }

	  		 function get_number(){
				var keytmp = $("#nomer").val().substring(0,4);
		        if($("#nomer").val().length > 3){         
		          var op = '';
		        if(key!=keytmp){
		          key=keytmp;
		          if(no_prefix[key]==null) {
		          	$("#nominal").html("");
		          	$("#nominal").append($('<option>', {value: "", text: "Masukan nomer dengan benar"}));

		          } else{
		          	$("#nominal").html("");
	                $.each(products, function(i, item) {
	                  	var v = item.kode.split(".");
	                	if(v[0]==no_prefix[key].kode_data){
	                		$("#nominal").append($('<option>', {value: item.id+'_'+item.FT, text: item.name +' / Rp '+item.price}));
	                	}
	                	else if(v[0]==no_prefix[key].kode && no_prefix[key].kode_data==null){
	                		$("#nominal").html("");
							$("#nominal").append($('<option>', {value: "", text: "Paket data operator "+item.name+" belum tersedia"}));
							//return false;
						}
	                });          

		          } 
		        }
		      }
			} 
		
		$("#pra_login").on("click", function(event) {
		event.preventDefault();
		$('#modal-content').modal('show');
		var url = window.location.href;	
		var nomer=document.getElementById("nomer").value;
		var nominal=document.getElementById("nominal").value;
		window.history.pushState('obj', 'newtitle', base_url+'ppob/pulsa?nomer='+nomer+'&nominal='+nominal);
		});

		$(window).load(function () {
  		setTimeout(function() {
		  		get_number();
		  		$('#nominal').val(getUrlVars()['nominal']).trigger('change'); 
		  		}, 500);
		});
	});
  </script>