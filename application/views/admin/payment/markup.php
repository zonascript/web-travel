
<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/sweetalert/sweetalert.css'); ?>">
<script type="text/javascript" src="<?php echo base_url('assets/sweetalert/sweetalert.min.js'); ?>"></script>

<style type="text/css">

td {
	cursor: pointer;
}

.editor{
	display: none;
}

</style>


<div class="box box-primary" style="width: 100%">
    <div class="box-header with-border">
      <h3 class="box-title">Markup</h3>
    </div>
    <!-- /.box-header -->

		<div class="container">

			<div class="row">
				<div class="col-md-12">


				<!--<button class="btn btn-info" id="tambah-data"><i class="glyphicon glyphicon-plus-sign"></i> Tambah </button>-->
					<a data-toggle="modal" data-target="#tambah-data" class="btn btn-primary"><i class="glyphicon glyphicon-plus-sign"></i> Tambah</a>
					<br>
					<br>
					<br>
					<table id="table-data" class="table table-striped">

					<thead>
					<tr>
					<th>Product</th>
					<th>Kode</th>
					<th>Value</th>
					<th>Type</th>
					<th>Hapus</th>
					</tr>
					</thead>

					<tbody id="table-body">
					<?php 

					foreach ($markup as $member) {
						echo "<tr data-id='$member[id]'>
								<td><span class='span-product ' data-id='$member[id]'>$member[product]</span> </td>
								<td><span class='span-kode ' data-id='$member[id]'>$member[kode]</span> </td>
								<td><span class='span-value caption' data-id='$member[id]'>$member[value]</span> <input type='text' class='field-value form-control editor' value='$member[value]' data-id='$member[id]' /></td>
								<td><div class='col-sm-5'>
									<span class='span-type caption' data-id='$member[id]'>$member[type]</span> 
									<input type='text' id='typetxt' class='field-type form-control editor typetxt' value='$member[type]' data-id='$member[id]'/>
									</div>
									<div>
									<select class='field-type form-control typecmb' id='typecmb' style='width:5%;'>
										<option></option>
										<option value='persen'>persen</option>
										<option value='decimal'>decimal</option>	
									</select>
									</div>
								</td>

								<td><button class='btn btn-xs btn-danger hapus-member' data-id='$member[id]'><i class='glyphicon glyphicon-remove'></i> Hapus</button></td>
								</tr>";
					}


					 ?>
					</tbody>

					</table>

					</div>
				</div>

		</div>

</div>

 <!-- Modal Tambah -->
 <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="tambah-data" class="modal fade">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                 <h4 class="modal-title">Tambah Markup</h4>
             </div>
             <form class="form-horizontal" action="<?php echo base_url('admin/markup/tambahData')?>" method="post" enctype="multipart/form-data" role="form">
             <div class="modal-body">
                     <div class="form-group">
                         <label class="col-lg-2 col-sm-2 control-label">Kode Product</label>
                          <div class="col-lg-10">
                        
				                <?php
				                $dd_product_attribute = 'class="form-control select2"';
				                echo form_dropdown('product', $dd_product, $product_selected, $dd_product_attribute);
				                ?>
				          
				          </div>
                     </div>
                     <div class="form-group">
                         <label class="col-lg-2 col-sm-2 control-label">Markup For</label>
                         <div class="col-lg-10">
                          <select id="markupFor" name="markupFor" class="form-control">
                          	<option>--</option>
                          	<option>internal</option>
                          	<option>member</option>
                          </select>
                         </div>
                     </div>
                     <div class="form-group">
                         <label class="col-lg-2 col-sm-2 control-label">Value</label>
                         <div class="col-lg-10">
                             <input type="text" class="form-control" name="value" placeholder="">
                         </div>
                     </div>
                       <div class="form-group">
                         <label class="col-lg-2 col-sm-2 control-label">Type</label>
                         <div class="col-lg-10">
                          <select id="type" name="type" class="form-control">
                          	<option>--</option>
                          	<option>decimal</option>
                          	<option>persen</option>
                          </select>
                         </div>
                     </div>
                 </div>
                 <div class="modal-footer">
                     <button class="btn btn-info" type="submit"> Simpan&nbsp;</button>
                     <button type="button" class="btn btn-warning" data-dismiss="modal"> Batal</button>
                 </div>
                 </form>
             </div>
         </div>
     </div>
 </div>
 <!-- END Modal Tambah -->



<script type="text/javascript">

$(function(){

		$.ajaxSetup({
			type:"post",
			cache:false,
			dataType: "json"
		})
		
        $(this).find("select[id~='typecmb']").hide();
		$(document).on("click","td",function(){
			$(this).find("span[class~='caption']").hide();
			$(this).find("input[class~='editor']").fadeIn().focus();
			$(this).find("select[id~='typecmb']").show();
		});
		

/*
		$("#tambah-data").click(function(){
			$.ajax({
				url:"<?php echo base_url('index.php/crud/create'); ?>",
				success: function(a){
				var ele="";
				ele+="<tr data-id='"+a.id+"'>";
				ele+="<td><span class='span-nama caption' data-id='"+a.id+"'></span> <input type='text' class='field-nama form-control editor'  data-id='"+a.id+"' /></td>";
				ele+="<td><span class='span-email caption' data-id='"+a.id+"'></span> <input type='text' class='field-email form-control editor' data-id='"+a.id+"' /></td>";
				ele+="<td><span class='span-phone caption' data-id='"+a.id+"'></span> <input type='text' class='field-phone form-control editor'  data-id='"+a.id+"' /></td>";
				ele+="<td><button class='btn btn-xs btn-danger hapus-member' data-id='"+a.id+"'><i class='glyphicon glyphicon-remove'></i> Hapus</button></td>";
				ele+="</tr>";

				var element=$(ele);
				element.hide();
				element.prependTo("#table-body").fadeIn(1500);

				}
			});
		});
*/
		$(document).on("keydown",".editor",function(e){
			if(e.keyCode==13){
				var target=$(e.target);
				var value=target.val();
				var id=target.attr("data-id");
				var data={id:id,value:value};
				if(target.is(".field-product")){
					data.modul="product";
				}else if(target.is(".field-value")){
					data.modul="value";
				}else if(target.is(".field-type")){
					data.modul="type";
				}	

				$.ajax({
					data:data,
					url:"<?php echo base_url('admin/markup/update'); ?>",
					success: function(a){
					 target.hide();
					 target.siblings("span[class~='caption']").html(value).fadeIn();
					 target.siblings("select[class~='typecmb']").hide();
					}

				})

			}	

		});
		$(document).on("click",".typecmb",function(e){
			if(e.keyCode==13){
				var target=$(e.target);
				var value=target.val();
				var id=target.attr("data-id");
				var data={id:id,value:value};
				if(target.is(".field-type")){
					data.modul="type";
				}	

				$.ajax({
					data:data,
					url:"<?php echo base_url('admin/markup/update'); ?>",
					success: function(a){
					 target.hide();
					 target.siblings("span[class~='caption']").html(value).fadeIn();
					 target.siblings("select[id~='typecmb']").hide();
					}

				})

			}	

		});


		$('.typecmb').click(function(event){
    		 event.stopPropagation();
 		});

		$(document).on("click",".hapus-member",function(){
			var id=$(this).attr("data-id");
			swal({
				title:"Hapus Data ",
				text:"Yakin akan menghapus markup ini?",
				type: "warning",
				showCancelButton: true,
				confirmButtonText: "Hapus",
				closeOnConfirm: true,
			},
				function(){
				 $.ajax({
					url:"<?php echo base_url('admin/markup/delete'); ?>",
					data:{id:id},
					success: function(){
						$("tr[data-id='"+id+"']").fadeOut("fast",function(){
							$(this).remove();
						});
					}
				 });
			});
		});

});
$(document).ready(function () {
       	var mytextbox = $('.typetxt');
    	var mydropdown = $('.typecmb');
    	$(".typecmb").on("change", function(event) {
    		mytextbox.val($(this).val());
    	});

	   // mydropdown.onchange = function(){
	        //  mytextbox.value = this.value; //to appened
	         //mytextbox.innerHTML = this.value;
	    //}         
                
});


</script>
