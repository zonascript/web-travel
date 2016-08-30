<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class M_payment extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('subquery');
	}
	
	function insert_bank(){
		$rek_number = preg_replace('/[^0-9]/', "", $this->input->post('rek_number'));
		$data=array(
				"id_user"=>$this->session->userdata('user_id'),
				"rek_number"=> $rek_number,
				"bank"=>$this->input->post('bank'),
				"account_name"=>$this->input->post('account_name'),
				"type"=>1,
		);
		$this->db->insert('payment_bank',$data);
		return ($this->db->affected_rows()>0) ? $this->db->insert_id() : FALSE;
	}
	
	function insert_topup($id_bank){
		$date = date_create();
		$data=array(
				"id_user"=>$this->session->userdata('user_id'),
				"nominal"=>$this->input->post('nominal'),
				"unique"=>$this->input->post('unique'),
				"id_bank_to"=>$this->input->post('id_bank_to'),
				"created"=>$date->getTimestamp(),
				"id_bank"=>$id_bank,
		);
		$this->db->insert('payment_topup',$data);
		$this->_set_status_topup($this->db->insert_id(),'pending');
		return ($this->db->affected_rows()>0) ? TRUE : FALSE;
	}
	
	private function _set_status_topup($id_topup,$status){
		$date = date_create();
		$data=array(
				"status"=>$status,
				"time_status"=>$date->getTimestamp(),
				"id_topup"=>$id_topup,
		);
		$this->db->insert('payment_status_topup',$data);
	}
	
	function topup_list(){
		$this->db->select(" t.id, t.id_user, t.nominal, t.`unique`, t.id_bank, t.id_bank_to, s.`status`")
				 ->from("payment_topup t, payment_status_topup s")
				 ->where("s.id_topup = t.id")
				 ->where('id_user',$this->session->userdata('user_id'))
				 ->order_by('s.time_status','desc');
		$sub = $this->subquery->start_subquery('where');
		$sub->select_max('time_status')->from('payment_status_topup')->where('id_topup = t.id');
		$this->subquery->end_subquery('s.time_status');
		return $this->db->get()->result();
	}
}