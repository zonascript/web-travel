<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class M_report extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('subquery');
	}
	
	function sales($betwen =NULL,$data_where=NULL){
		if($data_where!=NULL){
			foreach ($data_where as $key=> $val2){
				$this->db->where($key, $val2,FALSE);
			}
		}
		if($betwen!=NULL){
			$this->db->where($betwen);
		}
		$this->db->select(" b.*, `status`,time status")
				 ->from("booking AS b, booking status AS s")
				 ->where("b.id = s.id booking")
				 ->where("status",'issued')
				 ->where('company',$this->session->userdata('company'))
				 ->order_by('s.time status','desc');
		$sub = $this->subquery->start_subquery('where');
		$sub->select_max('time status')->from('booking status')->where('`id booking` = b.id');
		$this->subquery->end_subquery('s.time status');
		return $this->db->get()->result();
	}
	
	function finance($betwen =NULL,$data_where=NULL){
		if($data_where!=NULL){
			foreach ($data_where as $key=> $val2){
				$this->db->where($key, $val2,FALSE);
			}
		}
		if($betwen!=NULL){
			$this->db->where($betwen);
		}
		$this->db->select(" b.id, `code`, b.`pay for`,
							if(`code`='CT' OR `code`='CP',nominal,0)as credit,
							if(`code`='DI' OR `code`='DP',nominal,0)as debet,
							if(`code`='CT', (SELECT `unique` FROM `acc topup` WHERE id = b.`pay for`),
							  if(`code`='DP' OR code = 'CP',(SELECT `ref trxid` FROM `ppob trx` WHERE id = b.`pay for`),
							    (SELECT `booking code` FROM `booking` WHERE id = b.`pay for`)) )as payfor, 
							b.created as created, `pay for`")
				 ->from("acc balance AS b")
				 ->where('b.company',$this->session->userdata('company'));
		return $this->db->get()->result();
	}
	
	function finance_payfor($from , $to){
		$payfor=[];
		$company = $this->session->userdata('company');	
		//for CT
		$payfor['CT']=listData('acc topup','id','unique',"where company = $company and `created` BETWEEN $from AND $to");
		$payfor['DI']=listData('booking','id','booking code',"where company = $company and `booking time` BETWEEN $from AND $to");
		
		return $payfor;
	}

	function sales_list($data_or=NULL,$betwen=NULL,$data_where=NULL){
		if($data_or!=NULL){
			foreach ($data_or as $val){
				$this->db->like($val['key'], $val['val'],FALSE);
			}
		}

		if($data_where!=NULL){
			foreach ($data_where as $key=> $val2){
				$this->db->where($key, $val2,FALSE);
			}
		}

		if($betwen!=NULL){
			$this->db->where($betwen);
		}
		$this->db->select(" b.*, `status`,time status")
				 ->from("booking AS b, booking status AS s")
				 ->where("b.id = s.id booking")
				 ->where("status",'issued')
				 ->where('company',$this->session->userdata('company'))
				 ->order_by('s.time status','desc');
		$sub = $this->subquery->start_subquery('where');
		$sub->select_max('time status')->from('booking status')->where('`id booking` = b.id');
		$this->subquery->end_subquery('s.time status');
		return $this->db->get()->result();
	}
	
	function sales_ppob($data_or=NULL,$betwen=NULL,$data_where=NULL){
		if($betwen!=NULL){
			$this->db->where($betwen);
		}
		$this->db->select("`t`.*, pr.`product` as productt, pr.kode")
				 ->from("`ppob trx` AS `t`, `ppob status` AS `s`, `product` AS `pr`")
				 ->where("t.id = s.`id trx`")
				 ->where("t.product = pr.id")
				 ->where("status != 'failed'")
				 ->where("status != 'refund'")
				 ->where('company',$this->session->userdata('company'))
				 ->order_by('s.created','desc');
		$sub = $this->subquery->start_subquery('where');
			$sub->select_max('created')->from('ppob status')->where('`id trx` = t.id');
		$this->subquery->end_subquery('s.created');
		return $this->db->get()->result();
	}
}