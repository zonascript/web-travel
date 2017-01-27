<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ppob extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('admin/m_ppob');	
	}
	
	function transaction(){
	 	$array_range = NULL;
	 	if($this->input->get('range')){
			$range = str_replace(' ', '', $this->input->get('range'));
			$range = (explode("-",$range));
			$rangf = strtotime(str_replace('/', '-', $range[0]));
			$rangt = strtotime(str_replace('/', '-', $range[1]))+86399;
			$array_range = "`created` BETWEEN $rangf AND $rangt";
			//echo date("Y-m-d H:i:s",$rangf).'|'.date("Y-m-d H:i:s",$rangt);die();
		}else{
			redirect ('admin/ppob/transaction?range='.date('d/m/Y', strtotime('-30 days')).' - '.date('d/m/Y'),'redirect');
		}
	 	$data_table = $this->m_ppob->transaction($array_range);
	 	$data = array('content'=>'ppob/transaction',
					  'data_table'=>$data_table,
					  'date_range'=>$this->input->get('range'),
					);
	 	$this->load->view("admin/index",$data);
	 }
	
}