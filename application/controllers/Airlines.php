<?php

defined('BASEPATH') OR exit('No direct script access allowed');
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
class Airlines extends CI_Controller {
	private $url ;
	private $logo ;
	 function __construct() {
	        parent::__construct();
	    $this->load->library('curl');		
		$this->load->library(array('form_validation'));
    	$this->config->load('api');
		$this->curl->http_header('token', $this->config->item('api-token'));
		$this->curl->option('TIMEOUT', 70000);
		$this->load->model('m_booking');
		$this->url = $this->config->item('api-url') . airline();
	 	$this->logo = $this->db->get_where('`auth users`', array('id' => $this->session->userdata('user_id')))->row();
	 	//echo $this->url;die();
	 	//if(empty($this->logo->logo)) $this->logo = '';
	 }
	 
	 function search(){	
		$data = $this->input->post();
		$this->form_validation->set_rules('from', 'Asal Keberangkatan', 'required');
		$this->form_validation->set_rules('to', 'Tujuan', 'required');
		
		$hasil = array();
		$code = 200; //$this->form_validation->run() 
		if ($this->form_validation->run() == FALSE)
		{
			$hasil =  validation_errors();
			$code = 400;
		}else{
			$json = $this->curl->simple_get("$this->url/search?from=$data[from]&to=$data[to]&date=$data[date]&adult=$data[adult]&child=$data[child]&infant=$data[infant]");
			//$json = $this->jsondata();		
			$array = json_decode ($json);
			//echo $this->url;
			//print_r("$this->url/search?from=$data[from]&to=$data[to]&date=$data[date]&adult=$data[adult]&child=$data[child]&infant=$data[infant]");
			
			if( ( empty($array) || $array->code==404 || $array->code==204) ){
				$code = 404;
				$hasil = 'tidak ada penerbangan';
			} else{
				foreach ($array->results->data as $val){
					$segment = array();
					$i = 0;
					foreach ($val->detail as $val_detail){
							$seat = array();
							++$i;
							$segment[$i] =  array (
											'airline_icon' => $val_detail->airline_icon,
											'flight_id' => $val_detail->flight_id,
											'time_depart' => $val_detail->time_depart,
											'time_arrive' => $val_detail->time_arrive,
											'date_depart' => $val_detail->date_depart,
											'date_arrive' => $val_detail->date_arrive,
											'area_depart' => $val_detail->area_depart,
											'area_arrive' => $val_detail->area_arrive,
											'seat' => $seat,
										);
							foreach ($val_detail->seat as $val_seat){				
								$seat[$val_seat->code] = array(
													'available'=>$val_seat->available,
													'class'=>$val_seat->class,
													'flight_key'=>$val_seat->flight_key,
								);
								$segment[$i]['seat'] = $seat ;
								$hasil["$val->id_perjalanan"] = array ('flight_count' => $val->flight_count, 
																 'segment'=> $segment
														);
							}
					}
				}
				$hasil = json_encode($hasil);
			}
			
		}
	return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output($hasil);
	}
	
	function get_bestprice($tipe=''){
		$plorp  = substr(strrchr($this->url,'/'), 1);
		$this->url = substr($this->url, 0, - strlen($plorp));
		
		$data = $this->input->post();
		$this->form_validation->set_rules('from', 'Asal Keberangkatan', 'required');
		$this->form_validation->set_rules('to', 'Tujuan', 'required');
		
		$hasil = array();
		$code = 200; //$this->form_validation->run() 
		if ($this->form_validation->run()  == FALSE)
		{
			$hasil =  validation_errors();
			$code = 400;
		}else{
			$json = $this->curl->simple_get("$this->url/$tipe/search/best_price?from=$data[from]&to=$data[to]&date=$data[date]&adult=$data[adult]&child=$data[child]&infant=$data[infant]");			
			$array = json_decode ($json);
			
			if( ( empty($array) || $array->code==404 || $array->code==204) ){
				if(empty($array)){
					$code = 400;
				} else{
					$code = $array->code;
				}
				$hasil = 'terdapat kesalahan sistem';
			} else{
				foreach ($array->results->data as $key => $val){
					$hasil[$val->id_perjalanan] = array ('airline_icon'=>$val->detail[0]->airline_icon,
														 'area_depart'=>$val->detail[0]->area_depart,
														 'area_arrive'=>$val->detail[0]->area_arrive,
														 'time_depart'=>$val->detail[0]->time_depart,
														 'time_arrive'=>$val->detail[0]->time_arrive,
														 'flight_count'=>$val->flight_count,
														 'id_perjalanan'=>$val->id_perjalanan,
														 
														 'available'=>$val->detail[0]->seat[0]->available,
														 'class'=>$val->detail[0]->seat[0]->class,
														 'flight_key'=>$val->detail[0]->seat[0]->flight_key,
														 'fare'=>$val->detail[0]->seat[0]->best_price->fare,
														 'tax'=>$val->detail[0]->seat[0]->best_price->tax,
														 'segment'=>$val->detail[0]->flight_list,
					 							 );
				}
				//print_r($hasil);die();
				$hasil = json_encode($hasil);
			}
			
		}
	return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output($hasil);
	}
	
	function get_fare(){
		$data = $this->input->post();
		$key = '';
		//$this->form_validation->set_rules('key[]', 'KEY[]', 'required');
				
		$totalPenambahan = $this->_totalMarkup($data['tipe']);
		
		for($i = 1; $i <= count($data['key'])-1; $i++){
			$key .= '|'.$data['key'][$i];
		}
		$key = substr($key, 1);
		//print_r($key);die();
		$json = $this->curl->simple_get("$this->url/get_price?flight_key=$key");
		//$json = $this->getfare();
		
		$array = json_decode ($json);
		$hasil = array();
		$code = 200; //$this->form_validation->run() 
		if (TRUE== FALSE)
		{
			$hasil =  validation_errors();
			$code = 400;
		}else{
			if( ( empty($array) || $array->code==404 || $array->code==204) ){
				$code = 404;
				$hasil = 'seat not available';
				if(empty($array)){
					$code = 404;
				}
			} else{
				$hasil = array('fare'=>$array->results->fare+$totalPenambahan, 
								'tax'=>$array->results->tax, 
								'total_price'=>$array->results->total_price+$totalPenambahan,
								'flight_key'=>$key,);
				$hasil = json_encode($hasil);
			}
			
		}
	return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output($hasil);
	}
	
	function get_form(){	
		$hasil = $this->jsongetform();		
		return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output($hasil);
	}
	
	function booking($bestprice=0){
		$data = $this->input->post();
		//print_r($data);die();
		//parse_str(utf8_decode(urldecode($data['data'])), $output);
		$hasil = array();
		$hasil['key'] = '';
		foreach($data as $key => $val){				
			$hasil[$key] = $data[$key];
		}
		if($bestprice != 0){
			$hasil['key'] = $data['key'][1];
		}
		//$hasil['segmen'] = sizeof($data['flightid']);
		//print_r($hasil);die();
		$data = array('content'=>'airlines/booking', 
					  'title'=>'Booking', 
					  'data'=>$hasil
					  );
		$this->load->view("index",$data);
	}
	
	function booking_save(){
		$data = $this->input->post();
		$tables = $this->config->item('tables','ion_auth');
		unset($data['identity']);
		unset($data['password']);
		unset($data['identity']);
		
		unset($data['full_name']);
		unset($data['email']);
		unset($data['phone']);
		unset($data['password_confirm']);
		unset($data['password_register']);
		
		unset($data['position']);
		unset($data['date']);
		unset($data['from']);
		unset($data['to']);
		unset($data['airline']);
		unset($data['adult_count']);
		unset($data['child_count']);
		unset($data['infant_count']);
		unset($data['passanger_count']);
		
		$this->form_validation->set_rules('contact_title', 'contact title', 'required');
		$this->form_validation->set_rules('contact_name', 'contact name', 'required');
		$this->form_validation->set_rules('contact_phone', 'contact phone', 'required');
		if (!$this->ion_auth->logged_in() && $this->input->post('position')=='lo'){
			$this->form_validation->set_rules('identity', 'email', 'required|valid_email');
			$this->form_validation->set_rules('password', 'password', 'required');
		}
		if (!$this->ion_auth->logged_in() && $this->input->post('position')=='re'){
			$this->form_validation->set_rules('full_name', 'full_name', 'required');
			$this->form_validation->set_rules('email', 'email', 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
			$this->form_validation->set_rules('phone', 'phone', 'required|trim|numeric');
			$this->form_validation->set_rules('password_register', 'password', 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'password_confirm', 'required');
		}
		
		$hasil = '';
		$code = 200; //$this->form_validation->run() 
		if ($this->form_validation->run()  == FALSE)
		{
			$hasil =  validation_errors();
			$code = 400;
		}else{
			if (!$this->ion_auth->logged_in() && $this->input->post('position')=='lo'){
				$remember = (bool) $this->input->post('remember');
				$this->ion_auth->login($this->input->post('identity'), $this->input->post('password'),$remember);
				
			}
			if (!$this->ion_auth->logged_in() && $this->input->post('position')=='re'){
				$identity_column = $this->config->item('identity','ion_auth');
				$email    = strtolower($this->input->post('email'));
		        $identity = ($identity_column==='email') ? $email : $this->input->post('identity');
		        $password = $this->input->post('password_register');

		        $additional_data = array(
		            'full name' => $this->input->post('full_name'),
		            'phone'      => $this->input->post('phone'),
		        );
		        $this->ion_auth->register($identity, $password, $email, $additional_data);
		        $this->ion_auth->login($identity, $password, TRUE);
			}
			if ($this->ion_auth->logged_in()){
				if($this->_cek_double_booking()>0){
					return $this->output
			            ->set_content_type('text/html')
			            ->set_status_header(400)
			            ->set_output('Anda terindikasi double booking');
				}
				if($this->_cek_group_booking()>14){
					return $this->output
			            ->set_content_type('text/html')
			            ->set_status_header(400)
			            ->set_output('Anda terindikasi Group booking, Silahkan kontak Admin');
				}
				//print_r($data); echo "---";die();
				$json = $this->curl->simple_post("$this->url/book", $data, array(CURLOPT_BUFFERSIZE => 10, CURLOPT_TIMEOUT=>800000));
				//echo $this->url/book;
				//$json = $this->jsonbooking();
				
				$array = json_decode ($json);
				//print_r($array);die();
				if( ( ! empty($array) && $array->code==200) ){
					$hasil = $array->results->booking_code;
					$data = array(
				        'company' => $this->session->userdata('company'),
				        'identity' => $this->session->userdata('identity'),
				        'booking code' => $array->results->booking_code,
					);
					$this->m_booking->booking_save($data);
					
				} else{
					$hasil = $array->results;
				}
				$code = $array->code;
			} else{
				$code = 400;
				$hasil = $this->ion_auth->errors();
			}
		}
		return $this->output
	            ->set_content_type('text/html')
	            ->set_status_header($code)
	            ->set_output($hasil);
	}
	
	private function _cek_double_booking($db=TRUE){
		$names = [];
		$i=0;
			foreach($this->input->post() as $key => $val){
				if($db){
					if (preg_match('/title_/',$key)){
						$i++;
						$names[$i] = $val;				
					}
					if (preg_match('/name_/',$key)){
						$names[$i] .= " ".$val;						
					}
				}
			}
			
			$this->db->where('`area depart`', $this->input->post('from'));
			$this->db->where('`area arrive`', $this->input->post('to'));
			$this->db->where('`airline`', $this->input->post('airline'));

			$this->db->select("count(*) AS jml")
					 ->from("booking AS b, booking status AS s, `auth company` AS `c`")
					 ->where("b.id = s.id booking")
					 ->where("b.company = c.id")
					 ->where("status = 'booking'")
					 ->where('company',$this->session->userdata('company'))
					 ->where("(SELECT from_unixtime(`time arrive`, '%d-%m-%Y') FROM `booking flight` 
							  WHERE `id booking` = b.id ORDER BY `time arrive` ASC LIMIT 1)",$this->input->post('date'))
					 ->order_by('s.time status','desc');
			
			$sub = $this->subquery->start_subquery('where');
			$sub->select_max('time status')->from('booking status')->where('`id booking` = b.id');
			$this->subquery->end_subquery('s.time status');
			
			if($db){
				$sub2 = $this->subquery->start_subquery('where');
				$sub2->select('COUNT(*)')->from('booking passenger')
					  ->where('`id booking` = b.id')->where_in('name', $names);
				$this->subquery->end_subquery($this->input->post('passanger_count'));
			}
			$return = $this->db->get()->row();
			return $return->jml;
	}
	private function _cek_group_booking(){
		$area_depart = $this->input->post('from');
		$area_arrive = $this->input->post('to');
		$flight_number = explode(",", $this->input->post('flight_number'));
		$flight_number = $flight_number[0];
		$user  = $this->session->userdata('user_id');
		
		$ids = $this->db->query("
			SELECT DISTINCT(b.id)
			from `booking status` s, booking b, `booking flight` f
			WHERE s.`id booking` = b.id AND f.`id booking`=b.id 
			AND `area depart`='$area_depart' AND `area arrive`='$area_arrive' AND 
			f.`flight number` = '$flight_number' AND
			`status` = 'booking' AND
			s.`time status` = (SELECT MAX(`time status`) FROM `booking status` WHERE id=s.id)
			AND `user`='$user' ORDER BY `time status` DESC
		");
		$ids = $ids->result();
		$ids2=[0];
		foreach($ids as $key => $value){
			$ids2[] = $value->id;
		}
		
		$this->db->select('COUNT(*) as jml')
				 ->where_in('`id booking`', $ids2)
				 ->from('`booking passenger`');
		$jml = $this->db->get()->row()->jml;
		if($jml==NULL) return $this->input->post('passanger_count')*1 ; 
			else return $jml+$this->input->post('passanger_count')*1;
	}
	
	function retrieve($code='00'){
		if(!$this->ion_auth->logged_in()){
			redirect('airlines','refresh');
		}
				
		$bandara = $this->_bandara();
		$array = NULL;
		$data_table = NULL;
		if($code != '00' && !$this->input->get()){
			$array = $this->_boking_detail($code);
			
			$totalPenambahan = $this->_totalMarkup($array->airline);
			
			$array->NTA += $totalPenambahan;
			$array->base_fare += $totalPenambahan;
			
			/* update booking */
			if($array != NULL && $this->ion_auth->logged_in()){
				$data_update = array(
			        'id flight' => $array->id,
			        'booking time' => $array->booking_time,
			        'time limit'=> $array->time_limit,
					'base fare'=> $array->base_fare,
					'tax'=> $array->tax,
					'NTA'=> $array->NTA,
					'name'=> $array->name,
					'phone'=> $array->phone,
					'area depart'=> $array->area_depart,
					'area arrive'=> $array->area_arrive,
					'payment status'=> $array->payment_status,
					'airline'=> $array->airline,
					'flight list'=> $array->flight_list,				
					'passenger list'=> $array->passenger_list,				
					'child'=> $array->child,				
					'infant'=> $array->infant,				
					'adult'=> $array->adult,				
				);		
				$id_booking = $this->m_booking->booking_update($data_update,$code);
			 }
			
			$data = array('content'=>'airlines/retrieve',
					  'data_detail'=>$array,
					  'id_booking'=>$id_booking,
					  'status'=>$this->m_booking->get_status_booking($code),
					  'data_table'=>NULL,
					  'bandara'=>$bandara,
					);
		}elseif(!$this->input->get()){
			$data_table = $this->m_booking->retrieve_list();
			$data = array('content'=>'airlines/retrieve',
					  'data_table'=>$data_table,
					  'data_detail'=>NULL,
					);
		}else{
			$this->cron_expired();
			$data_or = [];
			$string = explode(",",$this->input->get('q'));
			for($i = 0; $i < count($string); $i++){
				$string2 = explode(":",$string[$i]);
				if(!empty($string2[1]) && !empty($string2[0] && $string2[1]!='')){
					if(preg_replace('/\s+/', '', $string2[0])=='bookingcode'){
					$data_or[$i]=array('val'=>$string2[1], 'key'=>'booking code');
					}
					if(preg_replace('/\s+/', '', $string2[0])=='contactname'){
						$data_or[$i]=array('val'=>$string2[1], 'key'=>'name');
					}
					if(preg_replace('/\s+/', '', $string2[0])=='datebooking'){
						$data_or[$i]=array('val'=>date("Y-m-d", strtotime($string2[1])), 'key'=>'FROM_UNIXTIME(`b`.`booking time`,"%Y-%m-%d")');
					}
					if(preg_replace('/\s+/', '', $string2[0])=='areadepart'){
						$data_or[$i]=array('val'=>$string2[1], 'key'=>'area depart');
					}
					if(preg_replace('/\s+/', '', $string2[0])=='areaarrive'){
						$data_or[$i]=array('val'=>$string2[1], 'key'=>'area arrive');
					}
					if(preg_replace('/\s+/', '', $string2[0])=='airline'){
						$data_or[$i]=array('val'=>$string2[1], 'key'=>'airline');
					}
					if(preg_replace('/\s+/', '', $string2[0])=='status'){
						$data_or[$i]=array('val'=>$string2[1], 'key'=>'status');
					}
					if(preg_replace('/\s+/', '', $string2[0])=='datedepart'){
						$data_or[$i]=array('val'=>date("Y-m-d", strtotime($string2[1])), 'key'=>'FROM_UNIXTIME(`f`.`time depart`,"%Y-%m-%d")');
					}
				}elseif($string2[1]!=''){
					$data_or[$i]=array('val'=>$string2[0], 'key'=>'booking code');
				}
			}
			$data_table = $this->m_booking->retrieve_list($data_or);
			$data = array('content'=>'airlines/retrieve',
					  'data_table'=>$data_table,
					  'data_detail'=>NULL,
					);
		}
				
		$this->load->view("index",$data);
	}
	
	function invoice($code, $html=''){
		if(!$this->ion_auth->logged_in()){
			redirect('airlines','refresh');
		}
		$array = $this->_boking_detail($code);
		
		$totalPenambahan = $this->_totalMarkup($array->airline);
			
		$array->NTA += $totalPenambahan;
		$array->base_fare += $totalPenambahan;
		
		$data = array('data_detail'=>$array,
				  'status'=>$this->m_booking->get_status_booking($code),
				  'data_table'=>NULL,
				  'bandara'=>$this->_bandara(),
				  'logo'=> $this->logo,
				);
		if($html == ''){
			//$this->load->view("airlines/invoiceHtml",$data);
			$this->load->view("airlines/invoice",$data);
		}else{
			try {
			    //ob_start();
			    $content=$this->load->view("airlines/invoiceHtml",$data, TRUE);
			    //$content = ob_get_clean();

			    $html2pdf = new Html2Pdf('P', 'A4', 'en');
			    $html2pdf->pdf->SetDisplayMode('fullpage');
			    $html2pdf->writeHTML($content);
			    $html2pdf->Output($code.'.pdf');
			} catch (Html2PdfException $e) {
			    $formatter = new ExceptionFormatter($e);
			    echo $formatter->getHtmlMessage();
			}
		}
	}
	
	function eticket($code, $html=''){
		if(!$this->ion_auth->logged_in()){
			redirect('airlines','refresh');
		}
		$array = $this->_boking_detail($code);
		$data = array('data_detail'=>$array,
				  'bandara'=>$this->_bandara(),
				  'logo'=> $this->logo,
				);
		if($html == ''){
			try {
			    //ob_start();
			    $content=$this->load->view("airlines/eticketHtml",$data, TRUE);
			    //$content = ob_get_clean();

			    $html2pdf = new Html2Pdf('P', 'A4', 'en');
			    $html2pdf->pdf->SetDisplayMode('fullpage');
			    $html2pdf->writeHTML($content);
			    $html2pdf->Output($code.'.pdf');
			} catch (Html2PdfException $e) {
			    $formatter = new ExceptionFormatter($e);
			    echo $formatter->getHtmlMessage();
			}
		}else{
			$this->load->view("airlines/eticket",$data);
		} 
	}
	
	private function _boking_detail($code){
		$plorp  = substr(strrchr($this->url,'/'), 1);
		$this->url = substr($this->url, 0, - strlen($plorp));
		$json = $this->curl->simple_get($this->url."manage/book/$code");
		$json = json_decode($json);
		if(empty($json->error))	return $json->results;
			else return NULL;
	}
	
	private function _bandara(){
		$str = file_get_contents(base_url().'assets/ajax/iata_bandara.json');
		$bandara = json_decode($str,TRUE);
		$return = array();
		foreach($bandara as $val){
			$return[$val['code_route']] = $val;
		}
		return $return;
	}
	
	function index(){
		$data = array(
					'content'=>'airlines/search',
					'title'=>'',		
				);
		$this->load->view("index",$data);
	}
	function search_bestprice(){
		$data = array('content'=>'airlines/search_bestprice2');
		$this->load->view("index",$data);
	}
	
	function cron_expired(){
		$data = $this->m_booking->cron_expired();
		$time =new DateTime();
		$time = $time->getTimestamp();   
		foreach($data as $val){
			//if($val->{'time limit'} < $time) echo date('d-m-Y H:i', $val->{'time limit'})."<br>";
			if($val->{'time limit'} <= $time) {
				$data_insert = array(
				   '`id booking`' => $val->{'id booking'} ,
				   'user' => 0 ,
				   'status' => 'expired',
				   '`time status`' => $time,
				);
				$this->db->insert('`booking status`', $data_insert); 
			}
		}
		
	}
	
	/* Markup From Indsiti to All Companies */
	private function markupFindsiti($to_company=NULL,$id=0){
		$this->db->select("*")
			->from('markup')
			->order_by("`company`");
		
		$to_companyS = "OR `company` = $to_company";
		if($to_company == NULL){
			$to_companyS = '';
		}
		
		if($id==0){
			$this->db->where("(`company`=0 $to_companyS) 
					 AND `markup for` = 'internal' ");
		}else{
			$this->db->where("id",$id);
		}
		$this->db->where("active",'1');
		$data = $this->db->get()->result();
		$r = [];
		foreach($data as $val){
			$r[$val->product] = array(
					"value"=>$val->value,
					"tipe_data"=>$val->{'type'},
					"idFindsiti"=>$val->id,
			);
		}
		return $r;		
	}
	
	/* Markup From Company to All Buyers */
	private function markupTbuyer($to_buyer=NULL, $id=0){
		$this->db->select("*")
			->from('markup')
			->order_by("`company`");
		
		$to_buyerS = "OR `company` = $to_buyer";
		if($to_buyer == NULL){
			$to_buyerS = '';
		}
				
		if($id==0){
			$this->db->where("(`company` =0 $to_buyerS) 
					 AND `markup for` = 'member' ");
		}else{
			$this->db->where("id",$id);
		}
		$this->db->where("active",'1');
		$data = $this->db->get()->result();
		$r = [];
		foreach($data as $val){
			$r[$val->product] = array(
					"value"=>$val->value,
					"tipe_data"=>$val->{'type'},
					"idTbuyer"=>$val->id,
			);
		}
		return $r;
	}
	
	private function _totalMarkup($airline='XX'){
		$this->db->select('id')
			 ->from('product')
			 ->where("product LIKE '$airline%'");
		$id_product = $this->db->get()->row();
		
		$penambahanIndsiti =0; $penambahanCompany=0; $totalPenambahan = 0;
		if(! empty(session('company'))){
			$company = session('company');
			$penambahanIndsiti = $this->markupFindsiti($company);
			$penambahanCompany = $this->markupTbuyer($company);
		}
		$totalPenambahan = $penambahanIndsiti[$id_product->id]['value']+$penambahanCompany[$id_product->id]['value'];
		return $totalPenambahan;	
	}
	
	function route(){
		$this->db->select("id as no, iata as code_route,name as name_airport,
						city,country")
			 ->from('airport')
			 ->where("country",'Indonesia');
		$data = $this->db->get()->result();
		
		return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($data));
	}
	
}


