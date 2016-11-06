<script src="//cdnjs.cloudflare.com/ajax/libs/list.js/1.3.0/list.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/plugins/tooltip/tooltipster.bundle.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>/assets/plugins/tooltip/tooltipster.bundle.min.css" />
<style>
	.vertical-center {
  display:flex;
    align-items: center;
    text-align: center;
    }
    .row {
			padding: 0px;
		}
	.tooltip_templates { display: none; }
</style>
<div class="box" id="cari">
	<form id="form" method="post" name="form">
		<div class="box-body">
			<div class="col-md-3">
				<div class="form-group">
					<label>LEAVING FROM</label> <select class="form-control bandara" id='from' name='from' style="width: 100%;">
							<option value=""></option>
						</select>
				</div>
			</div>
			<div class="col-md-3">
				<div class="form-group">
					<label>GOING TO</label> <select class="form-control bandara" id='to' name='to' style="width: 100%;">
							<option value=""></option>
						</select>
				</div>
			</div>
			<div class="col-md-2 col-sm-6 col-xs-12">
				<div class="form-group">
					<label>DEPARTING ON</label>
					<div class="input-group">
						<div class="input-group date">
							<div class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</div><input class="form-control pull-right" id="datepicker" required="" name='date' type="text">
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-2 col-sm-6 col-xs-12" style="padding: 0;margin: 0;">
				<div class="col-md-4 col-sm-4 col-xs-4">
					<label>ADULT</label>
					<div class="input-group">
						<select title="adult" class=" form-control" id='adult' name='adult' style="width: 100%;">
							<option selected="selected">
								1
							</option>
							<option>
								2
							</option>
							<option>
								3
							</option>
							<option>
								4
							</option>
							<option>
								5
							</option>
							<option>
								6
							</option>
							<option>
								7
							</option>
						</select>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4">
					<label>CHILDREN</label>
					<div class="input-group">
						<select class="form-control" id='child' name='child' style="width: 100%;">
							<option selected="selected">
								0
							</option>
							<option>
								1
							</option>
							<option>
								2
							</option>
							<option>
								3
							</option>
							<option>
								4
							</option>
							<option>
								5
							</option>
							<option>
								6
							</option>
							<option>
								7
							</option>
						</select>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-4">
					<label>INFANT</label>
					<div class="input-group">
						<select class="form-control" id='infant' name='infant' style="width: 100%;">
							<option selected="selected">
								0
							</option>
							<option>
								1
							</option>
							<option>
								2
							</option>
							<option>
								3
							</option>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-2 col-sm-4 col-xs-4">
				<div class="input-group" style="margin-top: 17px;">
				<button id='btn-search' class="btn btn-flat btn-success btn-lg"><i class="fa fa-search"></i> | SEARCH</button>
				</div>
			</div>
		</div><!-- /.box-body -->
	</form>
	<form id="booking" action="<?php echo base_url()?>airlines/booking" method="post">
		<input id='h_from' name='from' type="hidden" value=''> 
		<input id='h_to' name='to' type="hidden" value=''> 
		<input id='h_date' name='date' type="hidden" value=''> 
		<input id='h_adult' name='adult' type="hidden" value=''> 
		<input id='h_child' name='child' type="hidden" value=''> 
		<input id='h_infant' name='infant' type="hidden" value=''> 
		<input id='h_flight_key' name='key' type="hidden" value=''>
	</form><!-- /.box-footer-->
</div><!-- /.box -->

<div id="result-content" class="box box-primary center-block" style="width: 100%">
	<div class="box-header with-border">
		<h3 class="box-title"></h3>
	</div><!-- /.box-header -->
	<div class="box-body">
		<div id="alert"></div>
		<div class="result" id="result-id">
			 <!--<button class="sort" data-sort="time_depart">Depart time</button>
    		 <button class="sort" data-sort="total">Total</button>-->
			<div class="list"></div>
		</div>
	</div>
</div><!-- /.box-body -->
<div class="tooltip_templates">
    <span id="tooltip_content">
        <span id="flights">This is the content<br> of my tooltip!</span>
    </span>
</div>

<script>
$(document).ready(function(){
    //Date picker
    $('#datepicker').datepicker({
       showOtherMonths: true,
       selectOtherMonths: true,
       dateFormat: 'dd-mm-yy', 
       minDate: 0,
    });
    var flights = [];
    
     $('body').on('mouseenter', '.tooltips:not(.tooltipstered)', function(){
         flights = $(this).attr('data-count').split("_");
         var itenary = '';
         for (a = 0; a < flights[0]; a++) {
         	itenary = itenary + $('#depart_'+flights[1]+'_'+a).text() + ' | ' + $('#timedepart_'+flights[1]+'_'+a).text() + ' - ';
         	itenary = itenary + $('#arrive_'+flights[1]+'_'+a).text() + ' | ' + $('#timearrive_'+flights[1]+'_'+a).text() + '';
         	itenary = itenary + '<br>';
         }
         
         $(this).tooltipster({
            contentAsHTML: true,
            animation: 'grow',
            position: 'top',
            contentCloning: true,
            interactive: true,
            content:'',
            functionFormat: function(instance, helper, content){
		        var displayedContent = itenary;		        
		        return displayedContent;
		    },		    
            trigger: 'custom',
		    triggerOpen: {
		        mouseenter: true,
		        touchstart: true,
		        tap: true
		    },
		    triggerClose: {
		        mouseleave: true,
		    }
         });
         $(this).tooltipster('open');
    });
    
    $('body').on('mouseenter', '.tooltips-harga:not(.tooltipstered)', function(){
        $(this).tooltipster({
            contentAsHTML: true,
            animation: 'grow',
            position: 'top',
            contentCloning: true,
            interactive: true,
            trigger: 'custom',
		    triggerOpen: {
		        mouseenter: true,
		        touchstart: true,
		        tap: true
		    },
		    triggerClose: {
		        mouseleave: true,
		    }
         });
         $(this).tooltipster('open');
    });
    
    $(".bandara").select2();
    var bandara = [] ;
    $.get( base_url+'assets/ajax/iata_bandara.json', function(data) {
        $.each(data, function(i, item) {
            bandara [item.code_route]= item.city + ' ' + item.name_airport ;
            $(".bandara").append($('<option>', {value: item.code_route, text: item.code_route +' - '+ item.city + ' ' + item.name_airport}));
        });
    	$('#from').select2('open');
    });
    
  $("#result-content").hide();
  $("#form").on("submit", function(event) {
        $("#h_from").val($("#from").val());
        $("#h_to").val($("#to").val());
        $("#h_date").val($("#datepicker").val());
        $("#h_adult").val($("#adult").val());
        $("#h_child").val($("#child").val());
        $("#h_infant").val($("#infant").val());
        
        $("#btn-search").removeClass('btn-success');
        $("#btn-search").addClass('btn-warning');
        $("#btn-search").children("i").removeClass('fa-search');
        $("#btn-search").children("i").addClass('fa-refresh fa-spin');
        
        $(over).appendTo("#cari");
        event.preventDefault(); 
        $(".list").empty();
        $.ajax({
            url:  base_url+"airlines/get_bestprice",
            type: "post",
            data: $(this).serialize(),
            success: function(d) {
                $('#overlay').remove();
                json_tabel(d);
                $("#btn-search").removeClass('btn-warning');
		        $("#btn-search").addClass('btn-success');
		        $("#btn-search").children("i").addClass('fa-search');
		        $("#btn-search").children("i").removeClass('fa-refresh fa-spin');
            },
             error: function (request, status, error) {
                $('#overlay').remove();
                $("#btn-search").removeClass('btn-warning');
		        $("#btn-search").addClass('btn-success');
		        $("#btn-search").children("i").addClass('fa-search');
		        $("#btn-search").children("i").removeClass('fa-refresh fa-spin');
                showalert(request.responseText,'warning');
            },
             complete: function() {
        		$("#result-content").show();
             }
        });
        
  });

    function KelasGenerate(j,i,seat){
        var array_kelas = {'promo':['T','V','X','R','O','U'],'ekonomi':['Y','A','G','W','S','B','H','K','L','M','N','Q'],'bisnis':['C','J','D','I','Z']};
        $.each( array_kelas, function( key, value ) {
           var group = $('<optgroup label="' + key + '" />');
                group.appendTo($('#kelas_'+j+'_'+i));
                for (x = 0; x < value.length; x++) {
                    if(seat[value[x]]!==undefined){
                        $($('#kelas_'+j+'_'+i))
                          .append($('<option value="'+seat[value[x]].flight_key+'">')
                          .text('class '+value[x]+' - '+seat[value[x]].available));
                    } else{
                    	$($('#kelas_'+j+'_'+i))
                          .append($('<option disabled value="">')
                          .text('class '+value[x]+' - 0'));
					}
                }
        }); 
    }
    
    function json_tabel(json){
        var j = 0 ;
        var color = '';
        var flight_key = [];
        $.each(json, function() {
            j++;
            data = this;
            var transit = 'Langsung';
            var display = '';
            if(data.flight_count > 1) transit = "Transit " + (parseInt(data.flight_count)-1);
            var tampilan = '<div id="group-panel'+j+'" class="panel-group">'+
                                '<div class="panel panel-info ">'+
                                    '<div id="group'+j+'"><\/div>'+
                                    '<div style="margin:7px;">'+
                                    '<div class="col-md-1 col-xs-6 text-center"><label data-count="'+data.flight_count+'_'+j+'" class="tooltips label bg-green" >'+transit+'</label></div>'+
                                    '<div class="col-md-2 col-xs-6"> '+ 
									  '<div class=" text-center container-fare_'+j+'"><label>Rp <span class="tooltips-harga" title="Rp '+addCommas(data.fare)+'(fare) + Rp '+addCommas(data.tax)+'(tax)" id="total_'+j+'">'+addCommas(data.fare+data.tax)+'<\/span><\/label><\/div>'+
									  '<input type="hidden" class="total" data="'+(data.fare+data.tax)+'"  />' +
									'<\/div>'+
									'</div>'+
									'<div class="col-md-2 col-xs-12"> '+ 
									  '<button flight_key="'+data.flight_key+'" type="button" class="center-block btn-booking button-booking_'+j+' btn btn-flat btn-success btn-sm"><i class="fa fa-book"><\/i> | BOOKING<\/button>' +
									'<\/div>'+
                                    '<div class="row">'+
                                    '<\/div>'+
                                '<\/div>'+                           
                           '<\/div>' ;
            var tampilan2 = '';
            var time_depart = 'time_depart';
            $(tampilan).appendTo($(".list"));
           // $(".container-fare_"+j).hide();
            //$(".container-loading_"+j).hide();
            for (i = 0; i <= data.flight_count-1; i++) {
            	var harga = '';
            	var button = '';
                color = 'bg-success';
                if(i%2 == 0){
                    color = 'bg-info';
                }
                harga = '';
                button = '<div class="col-md-2 col-xs-12"> '+ 
  '<div class="pull-right container-fare_'+j+'">Rp <span id="fare_'+j+'">'+addCommas(data.fare)+'<\/span>(fare)+Rp '+addCommas(data.tax)+'<span id="tax_'+j+'"><\/span>(tax) <label>TOTAL = Rp <span id="total_'+j+'">'+addCommas(data.fare+data.tax)+'<\/span><\/label><\/div>'+
  '<input type="hidden" class="total" data="'+(data.fare+data.tax)+'"  />' +
'<\/div>'+
'<div class="col-md-2 col-xs-12"> '+ 
  '<button flight_key="'+data.flight_key+'" type="button" class="btn-booking button-booking_'+j+' col-md-12 col-sm-12 col-xs-12 btn btn-flat btn-success btn-sm"><i class="fa fa-book"><\/i> | BOOKING<\/button>' +
'<\/div>';
                if(i != 0) { harga = ''; button=''; display='display:none;';}
                tampilan2 = '<div class="panel-body '+' col-md-7 col-xs-12" style="'+display+'">'+
                                '<div class="col-md-6 text-center">'+
                                    '<h4 style="display:none"><span id="depart_'+j+'_'+i+'"><\/span> | <span class="'+time_depart+'" id="timedepart_'+j+'_'+i+'"><\/span> - <span id="arrive_'+j+'_'+i+'"><\/span> | <span id="timearrive_'+j+'_'+i+'"><\/span><\/h4>'+
                                    '<h4><span>'+data.area_depart+'<\/span> | <span>'+data.time_depart+'<\/span> - <span>'+data.area_arrive+'<\/span> | <span>'+data.time_arrive+'<\/span><\/h4>'+
                                '<\/div>'+
                                '<div class="col-md-6 col-xs-12 text-center">'+ 
                                    '<label><img id="image_'+j+'_'+i+'" src="" height="36" alt="" />&nbsp;<span id="flightid_'+j+'_'+i+'"><\/span><\/label> '+                
                                '<\/div>'+
                           '<\/div>';
                $(tampilan2).appendTo($("#group"+j));
                $('#depart_'+j+'_'+i).text(data.segment[i].area_depart);
                $('#arrive_'+j+'_'+i).text(data.segment[i].area_arrive);
                $('#timedepart_'+j+'_'+i).text(data.segment[i].time_depart);
                $('#timearrive_'+j+'_'+i).text(data.segment[i].time_arrive);
                $('#flightid_'+j+'_'+i).text(data.segment[i].flight_id);
                $('#image_'+j+'_'+i).attr("src", data.airline_icon);
                time_depart = '';
            }
        });
        
        $('.kelas').on('change', function(){
            $("#h_flight_key").val('');
            var data = $(this).attr('data').split('_'); // 0.urutan 1.segmen 2.flight_count
            var flightcount = 0;
            for (x = 1; x <= data[2]; x++) {
                if($('#kelas_'+data[0]+'_'+x).val()!=''){
                    flight_key[x] = $('#kelas_'+data[0]+'_'+x).val();
                    flightcount++;
                    if(flightcount == data[2]){
                    	disable("#group-panel"+data[0]);
                        $(".container-loading_"+data[0]).show();
                        $.ajax({
                            url:  base_url+"airlines/get_fare",
                            type: "post",
                            data: {
                                key : flight_key
                            },
                            success: function(d) {
                                disable("#group-panel"+data[0],false);
                                $('#fare_'+data[0]).text(addCommas(d.fare));
                                $('#tax_'+data[0]).text(addCommas(d.tax));
                                $('#total_'+data[0]).text(addCommas(d.fare+d.tax));
                                $(".container-fare_"+data[0]).show();
                                $(".container-loading_"+data[0]).hide();
                                $(".button-booking_"+data[0]).removeClass("disabled");
                                $(".button-booking_"+data[0]).removeClass("btn-default");
                                $(".button-booking_"+data[0]).addClass("btn-success");
                                $(".button-booking_"+data[0]).prop('disabled',false);
                                $(".button-booking_"+data[0]).attr("flight_key", d.flight_key);
                            },
                             error: function (request, status, error) {
                                disable("#group-panel"+data[0],false);
                                $(".button-booking_"+data[0]).addClass("btn-success");                                
                                $(".button-booking_"+data[0]).removeClass("btn-default");
                                showalert(error,'warning');
                                $(".container-loading_"+data[0]).hide();
                            }
                        });
                    }
                }
            }
        });
        
        $('.btn-booking').on('click', function(){
            $("#h_flight_key").val($(this).attr('flight_key'));
			booking();
        });
        
        function booking(){
        	$('#booking').submit()
        }
        
        function disable(elemen,dis=true){
        	$(elemen).css("cursor", "wait");
			$(elemen).find('input, textarea, button, select, img, label').prop('disabled',true);
        	if(dis==false){
				$(elemen).css("cursor", "auto");
				$(elemen).find('input, textarea, button, select, img, label').prop('disabled',false);
			}
        }
        var opt_sort = {
	    	valueNames: [ 'time_depart','total',
	    				  { name: 'total', attr: 'data' },
	    				 ]
		};
		var MySort = new List('result-id', opt_sort);
		MySort.sort('total', { asc: true });
    }
    $('#from').on('change', function(){
    	$('#to').select2('open');
    });
    $('#to').on('change', function(){
    	$("#datepicker").datepicker("show");
    });
    $('#datepicker').on('change', function(){
    	$("#adult").focus();
    });
});
</script>