<?php 
$color = array(
        'processing'=>'#0145d1', //sedang diproses
        'succes'=>'#00bd30', //berhasil
        'waiting SN'=>'#d3ce0a', //menunggu SN operator
        'refund'=>'#fc2cae', //refund
        'failed'=>'#ff2025', //faild
    );
$tulisan = array(
        '1111'=>'processing', //sedang diproses
        '0'=>'succes', //berhasil
        '2222'=>'waiting SN', //menunggu SN operator
        '1001'=>'refund', //refund
        '999'=>'failed', //faild
    ); 
?>
<style>
	.control-span {
  padding-top: 7px;
  font-weight: normal !important;
}
</style>
<!-- Horizontal Form -->
  <div class="box box-info">
    <div class="box-header with-border">
      <h3 class="box-title">PPOB Detail</h3>
    </div>
    <!-- /.box-header -->
    <div id="warning"></div>
    <!-- form start -->
    <div class="form-horizontal">
      <div class="box-body"> 
    	<div class="col-md-12">
			<div class="form-group">
	          <label for="email" class="col-sm-2 control-label">MSISDN</label>
	          <div class="col-sm-4">
	             <label class="control-span"><?php echo $data->msisdn; ?></label>
	          </div>
	        </div>
        </div>
        <!-- /.col -->
        <div class="col-md-12">
			<div class="form-group">
	          <label for="email" class="col-sm-2 control-label">Product</label>
	          <div class="col-sm-4">
	             <label class="control-span"><?php echo  strtoupper($product[$data->product]['operator'].' - '.$product[$data->product]['nilai']); ?></label>
	          </div>
	        </div>
        </div>
        <!-- /.col -->
        <div class="col-md-12">
			<div class="form-group">
	          <label for="email" class="col-sm-2 control-label">Price to agent</label>
	          <div class="col-sm-4">
	             <label class="control-span"><?php echo number_format($product[$data->product]['markup']); ?></label>
	          </div>
	        </div>
        </div>
        <!-- /.col -->
        <div class="col-md-12">
			<div class="form-group">
	          <label for="email" class="col-sm-2 control-label">Price to costumer</label>
	          <div class="col-sm-4">
	             <label class="control-span"><?php echo number_format($product[$data->product]['markup_default']); ?></label>
	          </div>
	        </div>
        </div>
        <!-- /.col -->
        <div class="col-md-12">
			<div class="form-group">
	          <label for="email" class="col-sm-2 control-label">TRXID</label>
	          <div class="col-sm-4">
	             <label class="control-span"><?php echo $data->trxid; ?></label>
	          </div>
	        </div>
        </div>
        <!-- /.col -->
        <div class="col-md-12">
			<div class="form-group">
	          <label for="email" class="col-sm-2 control-label">Ref TRXID</label>
	          <div class="col-sm-4">
	             <label class="control-span"><?php echo $data->ref_trxid; ?></label>
	          </div>
	        </div>
        </div>
        <div class="col-md-12">
			<div class="form-group">
	          <label for="email" class="col-sm-2 control-label">SN Operator</label>
	          <div class="col-sm-4">
	             <label class="control-span"><?php echo $string = ($data->sn_operator === null) ? '--' : $data->sn_operator; ?></label>
	          </div>
	        </div>
        </div>
        <div class="col-md-12">
			<div class="form-group">
	          <label for="status" class="col-sm-2 control-label">Status</label>
	          <div class="col-sm-4">
	          		<?php 
		          	 foreach($status as $val) {  ?>
		             <label class="control-span"> &bull; <?php echo $val['status']."&nbsp;&nbsp;&nbsp;". date("d-m-Y H:i:s", $val['created']) ; ?></label><br>
		             <?php } ?>
	          </div>
	        </div>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.box-body -->
      <div class="box-footer">
      	<div class="col-sm-6 col-md-6">
	        <a href="#" onclick="window.history.go(-1); return false;" class="btn btn-info"><i class="fa fa-arrow-circle-left"></i> Back</a>
	        
      	</div>
      </div>
      <!-- /.box-footer -->
    </div>
  </div>
  <!-- /.box -->
<script>
$(function () {
	
});
</script>