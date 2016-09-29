<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends CI_Controller {
	
	function __construct() {
	     parent::__construct();
	     $this->load->model('admin/m_report');	     
	 	 $this->load->helper('dropdown');
	 }
	 
	 function sales($position=''){
	 	$array_range = NULL;
	 	if($this->input->get('range')){
			$range = str_replace(' ', '', $this->input->get('range'));
			$range = (explode("-",$range));
			$rangf = strtotime(str_replace('/', '-', $range[0]));
			$rangt = strtotime(str_replace('/', '-', $range[1]))+86399;
			$array_range = "`time status` BETWEEN $rangf AND $rangt";
		}else{
			redirect ("admin/report/sales/$position?range=".date('d/m/Y', strtotime('-30 days')).' - '.date('d/m/Y'),'redirect');
		}
	 	$data_table = $this->m_report->sales($array_range,array('status'=>"'$position'"));
	 	$data = array('content'=>'report/sales',
					  'data_table'=>$data_table,
					  'position'=>$position,
					  'date_range'=>$this->input->get('range'),
					);
	 	$this->load->view("admin/index",$data);
	 }
	 
	 function retrieve($code){
	 	$array = $this->_boking_detail($code);
	 	$data = array('content'=>'report/retrieve',
					  'data_detail'=>$array,
					 // 'id_booking'=>$id_booking,
					  'status'=>$this->m_report->get_status_booking($code, $array->id),
					  'data_table'=>NULL,
					  'bandara'=>$this->_bandara(),
					);
		$this->load->view("admin/index",$data);
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
	 private function _boking_detail($code){
	 	$this->load->library('curl');
    	$this->config->load('api');
		$this->curl->http_header('token', $this->config->item('api-token'));
		$this->curl->option('TIMEOUT', 70000);
		$this->url = $this->config->item('api-url') . 'lion';
	 	
		$plorp  = substr(strrchr($this->url,'/'), 1);
		$this->url = substr($this->url, 0, - strlen($plorp));
		$json = $this->curl->simple_get($this->url."manage/book/$code");
		$json = json_decode($json);
		if(empty($json->error))	return $json->results;
			else return NULL;
	}
	 
	 function finance(){
	 	$array_range = NULL;
	 	if($this->input->get('range')){
			$range = str_replace(' ', '', $this->input->get('range'));
			$range = (explode("-",$range));
			$rangf = strtotime(str_replace('/', '-', $range[0]));
			$rangt = strtotime(str_replace('/', '-', $range[1]))+86399;
			$array_range = "`created` BETWEEN $rangf AND $rangt";
			//echo date("Y-m-d H:i:s",$rangf).'|'.date("Y-m-d H:i:s",$rangt);die();
		}else{
			redirect ('admin/report/finance?range='.date('d/m/Y', strtotime('-30 days')).' - '.date('d/m/Y'),'redirect');
		}
	 	$data_table = $this->m_report->finance($array_range);
	 	$data = array('content'=>'report/finance',
					  'data_table'=>$data_table,
					  'payfor'=>$this->m_report->finance_payfor($rangf,$rangt),
					  'date_range'=>$this->input->get('range'),
					);
	 	$this->load->view("admin/index",$data);
	 }
	 
	 function topup_detail($id_topup='00'){
			$data_select = $this->m_report->topup_detail($id_topup);
		 	$data = array('content'=>'report/topup_detail',
		 				  'data_topup'=>$data_select['topup'][0],
		 				  'data_status'=>$data_select['status'],
		 				  'bank'=>listDataCustom('acc bank','id','rek number,bank,account name'),
		 			);
	 	$this->load->view("admin/index",$data);
	 }
	 
	 function invoice($code){
	 	$array = $this->_boking_detail($code);
		$data = array('data_detail'=>$array,
				  'status'=>$this->m_report->get_status_booking($code, $array->id),
				  'bandara'=>$this->_bandara(),
				);
		$this->load->view("admin/report/invoice",$data);
	 }
}