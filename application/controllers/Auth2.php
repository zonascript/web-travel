<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth2 extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->load->helper(array('language'));
		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
		$this->lang->load('auth');
		$this->load->model('admin/m_markup');
	}
	
	public function login_ajax(){
		//validate form input
		$this->form_validation->set_rules('identity', str_replace(':', '', $this->lang->line('login_identity_label')), 'required');
		$this->form_validation->set_rules('password', str_replace(':', '', $this->lang->line('login_password_label')), 'required');
		
		$code = 400;
		$hasil['data']=0;
		$hasil['message']='error';
		if ($this->form_validation->run() == true)
		{
			// check to see if the user is logging in
			// check for "remember me"
			$remember = (bool) $this->input->post('remember');

			if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'),$remember))
			{
				//if the login is successful
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				$code = 200;
				$hasil['message'] = $this->ion_auth->messages();
				$hasil['data']=1;
				$hasil['user']=$this->session->userdata('identity');
			}
			else
			{
				// if the login was un-successful
				$hasil['message'] = $this->ion_auth->errors();
				$hasil['data']=0;
			}
		}else{
			$hasil['message'] = validation_errors();
			$hasil['data']=0;
		}
		return $this->output
            ->set_content_type('application/json')
            ->set_status_header($code)
            ->set_output(json_encode($hasil));
	}
	
	// register
	public function register(){
		$data_post = NULL;
		$message = '';
		if($this->input->post()){
			$data_post = $this->input->post();
			$tables = $this->config->item('tables','ion_auth');
	        $identity_column = $this->config->item('identity','ion_auth');
	        $this->data['identity_column'] = $identity_column;
	        
	        // validate form input
		    $this->form_validation->set_rules('full_name', $this->lang->line('create_user_validation_fname_label'), 'required');
		    if($identity_column!=='email')
		    {
		        $this->form_validation->set_rules('identity',$this->lang->line('create_user_validation_identity_label'),'required|is_unique['.$tables['users'].'.'.$identity_column.']');
		        $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email');
		    }
		    else
		    {
		        $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
		    }
		    $this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'trim|numeric');
		    $this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'trim|required');
		    $this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
		    $this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');

		    if ($this->form_validation->run() == true)
		    {
		        $email    = strtolower($this->input->post('email'));
		        $identity = ($identity_column==='email') ? $email : $this->input->post('identity');
		        $password = $this->input->post('password');

		        $additional_data = array(
		            'full name' => $this->input->post('full_name'),
		            'phone'      => $this->input->post('phone'),
		        );
		    }
		    if ($this->form_validation->run() == true && 
		    	$this->ion_auth->register($identity, $password, $email, $additional_data))
		    {
		        $message =  $this->ion_auth->messages();
		        $this->ion_auth->login($identity, $password,FALSE);        
		    }
		    else
		    {
		        // display the create user form
		        // set the flash data error message if there is one
		        $message = (validation_errors() ? validation_errors() : 
		        						  ($this->ion_auth->errors() ? $this->ion_auth->errors() : 
		        						  $this->session->flashdata('message')));
		    }
		}
		if($this->ion_auth->logged_in()) redirect('auth2/profile', 'refresh');	
		$data_view = array(
					'content'=>'auth/register',
					'data_post'=> $data_post,
					'message'=> $message,
				);
		$this->load->view("index",$data_view);
	}
	
	function profile(){		
		$user = NULL;
		$message = '';
		$id = $this->session->userdata('id');
		if (!$this->ion_auth->logged_in())
		{
			redirect('auth2/register/', 'refresh');
		}
		
		$user = $this->ion_auth->user($id)->row();
		$groups=$this->ion_auth->groups()->result_array();
		$currentGroups = $this->ion_auth->get_users_groups($id)->result();
		// validate form input
		$this->form_validation->set_rules('full_name', $this->lang->line('edit_user_validation_fname_label'), 'required');
		$this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'required');
		
		if (isset($_POST) && !empty($_POST))
		{
			// update the password if it was posted
			if ($this->input->post('password'))
			{
				$this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
				$this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
			}
			if ($this->form_validation->run() === TRUE)
			{
				$data = array(
					'full name' => $this->input->post('full_name'),
					'phone'      => $this->input->post('phone'),
				);

				// update the password if it was posted
				if ($this->input->post('password'))
				{
					$data['password'] = $this->input->post('password');
				}
				
				$config['upload_path'] = './assets/dist/img/logo/';
				$config['allowed_types'] = 'jpg|jpeg|png';
				$config['max_size']     = '2048';
				$config['max_width'] = '700';
				$config['max_height'] = '700';
				$config['overwrite'] = TRUE;
				$config['file_name'] = $user->id.'-logo-company';
				$this->load->library('upload', $config);
				
				if ( ! $this->upload->do_upload('logo'))
		        {
		            $message = $this->upload->display_errors();
		        }else{
					$data_u = $this->upload->data();
					$data['logo'] = $data_u['file_name'];
				}				
				
				// check to see if we are updating the user
			   if($this->ion_auth->update($user->id, $data))
			    {
			    	$message .= $this->ion_auth->messages();
			    }else{
					$message .= $this->ion_auth->errors();
				}
			}
			$this->session->flashdata('message', $message);
			redirect('auth2/profile/', 'refresh');
		}
		$data_select = $data_select = $this->m_markup->readMarkupMember();
		$data_markup = $this->m_markup-> readMarkupCompany();
		$user = $this->ion_auth->user($id)->row_array();
		$this->load->helper('dropdown');
		$data_view = array(
					'content'=>'auth/profile',
					'data_post'=> $user,
					'company'=> listData('auth company','id','brand',"where `id` = '".$this->session->userdata('company')."'"),
					'bank'=> listDataCustom('acc bank','id','bank,account name,rek number,enable',"where `company` = '".$this->session->userdata('company')."' order by enable desc"),
					//'markup'=> listDataCustom('markup','id','product,value,type',"where `markup for` like 'member'"),
					'markup'=>$data_select,
					'markupCompany'=>$data_markup,
					'message'=> $message,		
				);
		$this->load->view("index",$data_view);
	}
	
	function bank_detail($id){
		if(isset($_POST) && !empty($_POST)){
			$this->load->model('m_payment');
			$this->m_payment->change_status_bank($this->input->post('id'),$this->input->post('status'));
			redirect('auth2/profile/','refresh');
		} else{
			$this->load->helper('dropdown');
			$data_view = array(
						'content'=>'auth/bank_detail',
						'bank'=> listDataCustom('acc bank','id','bank,account name,rek number,enable',"where company = '".$this->session->userdata('company')."' and id = $id"),
						'id'=>$id,
			);
			$this->load->view("index",$data_view);	
		}
		
	}
	
	// log the user out
	public function logout()
	{
		$this->data['title'] = "Logout";

		// log the user out
		$logout = $this->ion_auth->logout();

		// redirect them to the login page
		$this->session->set_flashdata('message', $this->ion_auth->messages());
		redirect('airlines/', 'refresh');
	}
	
	// forgot password
	public function forgot_password()
	{
		// setting validation rules by checking whether identity is username or email
		if($this->config->item('identity', 'ion_auth') != 'email' )
		{
		   $this->form_validation->set_rules('identity', $this->lang->line('forgot_password_identity_label'), 'required');
		}
		else
		{
		   $this->form_validation->set_rules('identity', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
		}


		if ($this->form_validation->run() == false)
		{
			$this->data['type'] = $this->config->item('identity','ion_auth');
			// setup the input
			$this->data['identity'] = array('name' => 'identity',
				'id' => 'identity',
			);

			if ( $this->config->item('identity', 'ion_auth') != 'email' ){
				$this->data['identity_label'] = $this->lang->line('forgot_password_identity_label');
			}
			else
			{
				$this->data['identity_label'] = $this->lang->line('forgot_password_email_identity_label');
			}

			// set any errors and display the form
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
			//$this->_render_page('auth/forgot_password', $this->data);
			$this->data['content'] = 'auth/forgot_password';
			$this->load->view("index",$this->data);
		}
		else
		{
			$identity_column = $this->config->item('identity','ion_auth');
			$identity = $this->ion_auth->where($identity_column, $this->input->post('identity'))->users()->row();

			if(empty($identity)) {

	            		if($this->config->item('identity', 'ion_auth') != 'email')
		            	{
		            		$this->ion_auth->set_error('forgot_password_identity_not_found');
		            	}
		            	else
		            	{
		            	   $this->ion_auth->set_error('forgot_password_email_not_found');
		            	}

		                $this->session->set_flashdata('message', $this->ion_auth->errors());
                		redirect("auth2/forgot_password", 'refresh');
            		}

			// run the forgotten password method to email an activation code to the user
			$forgotten = $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

			if ($forgotten)
			{
				// if there were no errors
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				redirect(base_url(), 'refresh'); //we should display a confirmation page here instead of the login page
			}
			else
			{
				$this->session->set_flashdata('message', $this->ion_auth->errors());
				redirect("auth2/forgot_password", 'refresh');
			}
		}
	}
	
	// reset password - final step for forgotten password
	public function reset_password($code = NULL)
	{
		if (!$code)
		{
			show_404();
		}

		$user = $this->ion_auth->forgotten_password_check($code);

		if ($user)
		{
			// if the code is valid then display the password reset form

			$this->form_validation->set_rules('new', $this->lang->line('reset_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[new_confirm]');
			$this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required');

			if ($this->form_validation->run() == false)
			{
				// display the form

				// set the flash data error message if there is one
				$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

				$this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
				$this->data['new_password'] = array(
					'name' => 'new',
					'id'   => 'new',
					'type' => 'password',
					'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
				);
				$this->data['new_password_confirm'] = array(
					'name'    => 'new_confirm',
					'id'      => 'new_confirm',
					'type'    => 'password',
					'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
				);
				$this->data['user_id'] = array(
					'name'  => 'user_id',
					'id'    => 'user_id',
					'type'  => 'hidden',
					'value' => $user->id,
				);
				$this->data['csrf'] = $this->_get_csrf_nonce();
				$this->data['code'] = $code;

				// render
				//$this->_render_page('auth2/reset_password', $this->data);
				$this->data['content']='auth/reset_password';
				$this->load->view("index",$this->data);
			}
			else
			{
				// do we have a valid request?
				if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id'))
				{

					// something fishy might be up
					$this->ion_auth->clear_forgotten_password_code($code);

					show_error($this->lang->line('error_csrf'));

				}
				else
				{
					// finally change the password
					$identity = $user->{$this->config->item('identity', 'ion_auth')};

					$change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

					if ($change)
					{
						// if the password was successfully changed
						$this->session->set_flashdata('message', $this->ion_auth->messages());
						redirect(base_url(), 'refresh');
					}
					else
					{
						$this->session->set_flashdata('message', $this->ion_auth->errors());
						redirect('auth2/reset_password/' . $code, 'refresh');
					}
				}
			}
		}
		else
		{
			// if the code is invalid then send them back to the forgot password page
			$this->session->set_flashdata('message', $this->ion_auth->errors());
			redirect("auth2/forgot_password", 'refresh');
		}
	}
	
	public function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}
	
	public function _valid_csrf_nonce()
	{
		if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
			$this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}
