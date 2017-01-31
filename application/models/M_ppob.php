<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class M_ppob extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('subquery');
	}
	
	function get_price(){
		$this->db->select('*')
			 ->from('`ppob product`')
			 ->where('`kode`',post('nominal'))
			 ->order_by('nilai');		 
		$data = $this->db->get()->row();
		$price=0;
		if($data!=NULL){
			$price = $data->nilai + $data->markup;
		}
		return $price;
	}
	
	function insert_pulsa($trxid,$nomer){
		$data = array('product'=>post('nominal'),
			'ref_trxid'=>$trxid,
			'company'=>$this->session->userdata('company'),
			'msisdn'=>$nomer,
		);
		$this->db->insert('`ppob pulsa`',$data);
		return $this->db->insert_id();
	}
	
	function update_pulsa($data_f){
		$data = array('message'=>$data_f['message'], 'trxid'=>$data_f['trxid'], 
					  'status'=>$data_f['status'], 'created'=>now());
		$this->db->where('ref_trxid', $data_f['ref_trxid']);
		$this->db->update('`ppob pulsa`', $data);
		//return $this->db->affected_rows();
	}
	
	function issued($id,$kode){
		$saldo = saldo();
		$harga_pulsa = hargaPulsa($kode);
			$data=array(
					"company"=>$this->session->userdata('company'),
					"nominal"=>$harga_pulsa,
					"created"=>now(),
					"code"=>'DP',
					"pay for"=>$id,
					"balance"=>$saldo-$harga_pulsa,
			);
			$this->db->insert('acc balance',$data); //<-menambah row di payment topup
		
	}
	
	function refund($ref_trxid){
		$this->db->select('id,company')
			 ->from('`ppob pulsa`')
			 ->where('`ref_trxid`',$ref_trxid); 
		$id = $this->db->get()->row();
		$company = $id->company;
		$id = $id->id;
		
		$this->db->select('nominal')
			 ->from('`acc balance`')
			 ->where('`code`','DP') 
			 ->where('`pay for`',$id); 
		$harga = $this->db->get()->row();
		$harga = $harga->nominal;
		
		$saldo = saldo($company);
		$data=array(
				"company"=>$company,
				"nominal"=>$harga,
				"created"=>now(),
				"code"=>'CP',
				"pay for"=>$id,
				"balance"=>$saldo+$harga,
		);
		$this->db->insert('acc balance',$data); //<-menambah row di payment topup
	
	}

	// TAGIHAN TELKOM
	function insert_tagihan($trxid){
		$data = array(
			'ref_trxid'=>$trxid,
			'product'=> post('oprcode'),

		);
		//print_r($_POST);die();
		$this->db->insert('`ppob tagihan`',$data);
		return $this->db->insert_id();
	}
	function update_tagihan($data_f){
		$data = array('message'=>$data_f['message'], 'trxid'=>$data_f['trxid'], 
					  'status'=>$data_f['status'], 'created'=>now());
		$this->db->where('ref_trxid', $data_f['ref_trxid']);
		$this->db->update('`ppob tagihan`', $data);
	}
	function issuedTagihan($id,$kode){
		$saldo = saldo();
		$nominal = post('nominal');
			$data=array(
					"company"=>$this->session->userdata('company'),
					"nominal"=>$nominal,
					"created"=>now(),
					"code"=>'CP',
					"pay for"=>$id,
					"balance"=>$saldo-$nominal,
			);
			$this->db->insert('acc balance',$data); //<-menambah row di payment topup
		
	}
	function insert_idTelkom(){
		$time = now();
		$idTelkom = post('idpelanggan');
		$informasi = post('informasi');
		$data = array(
			'idTelkom'=> $idTelkom,
			'tgl'=> $time,
			'informasi'=> $informasi,
		);
		$this->db->replace('`ppob idTelkom`',$data);
		return $this->db->insert_id();

	}
	
	function finance($id){
		$this->db->select("*, from_unixtime(created, '%d-%m-%Y %h:%i:%s') as date")
			 ->from('`ppob pulsa`')
			 ->where('`id`',$id)
			 ->where('`company`',$this->session->userdata('company'));
		$r = $this->db->get()->row();
		return $r ;
	}
}