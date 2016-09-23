<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Payment extends CI_Controller {
	 function __construct() {
	     parent::__construct();
	     $this->load->library(array('form_validation'));	     
		 $this->load->model('m_payment');
		 $this->load->helper('dropdown');
	 }
	 
	 function topup(){
	 	$unique = mt_rand(1,999);
	 	$message = '';
	 	$id_bank=FALSE;
	 	if(!$this->ion_auth->logged_in()){
			redirect('auth2/login', 'refresh');
		}
	 	if($this->input->post()){
	 		$data = $this->input->post();
			$this->form_validation->set_rules('nominal', 'nominal', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('unique', 'unique number', 'required|is_natural_no_zero');
			$this->form_validation->set_rules('id_bank_to', 'transfer to', 'required|is_natural');
			if($this->input->post('payment_tipe')==0){
				$this->form_validation->set_rules('rek_number', 'rekening number', 'required');
				$this->form_validation->set_rules('bank', 'bank', 'required');
				$this->form_validation->set_rules('account_name', 'account name', 'required');
			}
			if ($this->form_validation->run()  == FALSE){
				$message =  validation_errors();
			}else{
				if($this->input->post('payment_tipe')==0){
					$id_bank = $this->m_payment->insert_bank();
				}else{
					$id_bank = $this->input->post('bank_account');
				}
				if($id_bank!==FALSE){
					$id =$this->m_payment->insert_topup($id_bank); 
					if($id){
						$message= 'success topup';
						redirect("payment/topup_list/$id",'refresh');
					}else{
						$message= 'fail topup';
					}
				}
			}
		}
		$data = array('content'=>'payment/topup',
					  'unique'=>$unique,
					  'message'=>$message,
					  'bank'=>listDataCustom('acc bank','id','rek number,bank,account name','where company=0 and enable=1'),
					  'bank_account'=>listDataCustom('acc bank','id','rek number,bank,account name',"where enable=1 and company= ".$this->session->userdata('company')),
					);
		$this->load->view("index",$data);				
	 }
	 
	 function topup_list($id_topup='00'){
	 	if(!$this->ion_auth->logged_in()){
			redirect('auth2/login', 'refresh');
		}
		if($id_topup=='00' || $id_topup==NULL){
			$data_select = $this->m_payment->topup_list();
		 	$data = array('content'=>'payment/topup_list',
		 				  'data_table'=>$data_select,
		 				  'bank'=>listDataCustom('acc bank','id','rek number,bank,account name'),
		 			);
		}elseif($id_topup!=NULL){
			$data_select = $this->m_payment->topup_list_detail($id_topup);
		 	$data = array('content'=>'payment/topup_list_detail',
		 				  'data_topup'=>$data_select['topup'][0],
		 				  'data_status'=>$data_select['status'],
		 				  'bank'=>listDataCustom('acc bank','id','rek number,bank,account name'),
		 			);
		}
	 	$this->load->view("index",$data);
	 }
	 
	 function topup_change_status($status=''){
	 	$status_array = array('cancel','submit');	 	
	 	if (in_array($status, $status_array)){
		 	if ($this->m_payment->topup_change_status($status)){
				$hasil['message'] = 'status Changed';
				$hasil['data']=1;
				$code = 200;
			}else{
				$hasil['message'] = 'status Not Changed';
				$hasil['data']=0;
				$code = 400;
			}
		}else{
			$hasil['message'] = 'Status not found';
			$hasil['data']=0;
			$code = 400;
		}
	 	
	 	return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode($hasil));
	 }
	 
	 function issued(){
	 	$this->load->model('m_booking');
	 	$id_booking = $this->input->post('id');
	 	$NTA = $this->m_booking->retrieve_list(NULL,array('b.id'=>$id_booking));
	 	$hasil['message'] = 'id or user not found';
		$hasil['data']=0;
		$code = 400;
	 	if(empty($NTA[0]->NTA)){
			$hasil['message'] = 'id or user not found';
			$hasil['data']=0;
			$code = 400;
		}elseif(saldo()>$NTA[0]->NTA){
			if($this->m_payment->issued($id_booking,$NTA[0]->NTA)){
				//$data_booking = $this->_issued($NTA[0]->{'booking code'});
				
				$hasil['message'] = 'Berhasil issued';
				$hasil['data']=1;
				$code = 200;
				$this->session->set_flashdata('message','Berhasil Issued');
			}
		}
		else{
			$hasil['message'] = 'saldo TIDAK cukup - silahkan melakukan topup terlebih dahulu <br> <a href="'.base_url().'payment/topup" type="button" class="btn btn-success" >TOPUP</a>';
			$hasil['data']=0;
			$code = 400;
		}
	 	return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode($hasil));
	 }
	 
	 private function _issued($booking_code){
	 	$this->load->library('curl');
    	$this->config->load('api');
		$this->curl->http_header('token', $this->config->item('api-token'));
		$this->curl->option('TIMEOUT', 70000);	
		$this->url = $this->config->item('api-url') . 'lion';
		$data=array(
			'booking_code'=>$booking_code
		);
		$json = $this->curl->simple_post("$this->url/pay", $data, array(CURLOPT_BUFFERSIZE => 10, CURLOPT_TIMEOUT=>800000));
	 	$this->_write($json);
	 	return json_encode($json);
	 }
	 
	 function report_sales(){
	 	
	 	$data = array('content'=>'payment/report_sales',
					  'bank'=>listDataCustom('payment_bank','id','rek_number,bank,account_name','where type=0'),
					  'bank_account'=>listDataCustom('payment_bank','id','rek_number,bank,account_name',"where enable=1 and id_user= ".$this->session->userdata('user_id')),
					);
		$this->load->view("index",$data);
	 }
	 
	 private function _write ($json){	 	
		$this->load->helper('file');
		$data = 'Some file data';
	    if ( ! write_file('./assets/ajax/data.txt', "\n".$json."\n", "a+"))
	    {
	        //echo 'Unable to write the file';
	    }
	    else
	    {
	        //echo 'File written!';
	    }
	 }
	 
}