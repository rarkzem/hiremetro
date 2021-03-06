<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hiremetro extends CI_Controller {

	
	public function __construct(){
		
		parent::__construct();
		
		//model name & nickname
		$this->load->model('hiremetro_model','hiremetrodbase');
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->library('pagination');  
	}
	
	public function index(){
		
		$data['title'] = "Hiremetro";		
		$this->load->view('include/header', $data);
		$this->load->view('hiremetro/home', $data);
		$this->load->view('include/footer', $data);
		
	}
	
	public function login(){
		$data = array(
		'username' => $this->input->post('username'),
		'password' => $this->input->post('password')
		);
		
		$result = $this->hiremetrodbase->login($data);
		
		$infos = array(
			'employee_id' => $result[0]['employee_id']
		);
		
		$id = $infos['employee_id'];
		
		if($result != FALSE){
			
			$data['title'] = "Welcome: Hiremetro";
			
			$result = $id;
			
			$newdata = array(
                   'username'  => $_POST['username'],
                   'logged_in' => TRUE,
				   'id' => $result
               );
			
			$this->session->set_userdata($newdata);
			
			$table = "employee_information";
			
		$ei = $this->hiremetrodbase->get_employee_information($result,$table);
			
		$table = "login_credentials";
			
		$lc = $this->hiremetrodbase->get_employee_information($result,$table);
		
		$table = "work_details";
		
		$wd = $this->hiremetrodbase->get_employee_information($result,$table);
		
		$infos = array(
				'work_title' => $wd[0]['work_title'],
				'work_description' => $wd[0]['work_description'],
				'worker_location' => $wd[0]['worker_location'],
				'work_pay' => $wd[0]['work_pay'],
				'work_language' => $wd[0]['work_language'],
				'status' => $wd[0]['status'],
				'fname' => $ei[0]['fname'],
				'mname' => $ei[0]['mname'],
				'lname' => $ei[0]['lname'],
				'email' => $ei[0]['email'],
				'contact' => $ei[0]['contact'],
				'username' => $lc[0]['username'],
				'password' => $lc[0]['password'],
				'picture' => $ei[0]['picture']
			);
			
			$data['employee'] = $infos;
		
		$this->load->view('include/header');
		$this->load->view('hiremetro/profile', $data);
		$this->load->view('include/footer', $data);
		
			
		}elseif($result == FALSE){
			$this->session->set_userdata('login_FALSE', TRUE);
			$this->index();
		}
		
		
	}
	
	public function login_forgotpassword(){
		
		if(isset($_GET['forgotpassword'])){
			$this->session->set_userdata('forgot_password', 1);
			$data['title'] = "Hiremetro";		
			$this->load->view('include/header', $data);
			$this->load->view('hiremetro/home', $data);
			$this->load->view('include/footer', $data);
		}
		elseif(isset($_POST['email'])){
			$email = $_POST['email'];
			$result = $this->hiremetrodbase->confirm_email($email);
			
			$this->session->set_userdata('forgot_password', 0);
			$this->session->set_userdata('forgot_password', $result);
			
			
			$data['title'] = "Hiremetro";		
			$this->load->view('include/header', $data);
			$this->load->view('hiremetro/home', $data);
			$this->load->view('include/footer', $data);
		}
		
		if(isset($_POST['retry'])){
			$this->session->set_userdata('forgot_password', 1);
			$data['title'] = "Hiremetro";		
			$this->load->view('include/header', $data);
			$this->load->view('hiremetro/home', $data);
			$this->load->view('include/footer', $data);
		}
		
	}
	
	public function signup1(){
		// Sessions
		
		$bday = $_POST['birthday'];
		$m = substr($bday,0,2);
		$d = substr($bday,3,2);
		$y = substr($bday,6,4);
		
		$bday = $y.'-'.$m.'-'.$d;
		
		$newdata = array(
			'fname' => ucfirst($_POST['firstname']),
			'mname' => ucfirst($_POST['middlename']),
			'lname' => ucfirst($_POST['lastname']),
			'address' => $_POST['address'],
			'contact' => $_POST['contact_number'],
			'email' => $_POST['email'],
			'birthday' => $bday,
			'sex' => $_POST['sex'],
			'signup1' => TRUE
		);
		
		$this->session->set_userdata($newdata);
		
		$this->load->view('include/header');
		$this->load->view('hiremetro/home');
		
	}
	
	public function signup2(){
		
		$this->session->set_userdata('signup1', FALSE);
		
		if($_POST['back'] == 1){
			$this->session->set_userdata('back', TRUE);
			$this->load->view('include/header');
			$this->load->view('hiremetro/home');
			$this->load->view('include/footer');
		}else{
			
			$table = "login_credentials";
			
			$id = $this->hiremetrodbase->getid($table);
			
			$table = "employee_information";
			
			$id = $id+2;
			
			$config['upload_path'] = './images/user_image/'; //The path where the image will be save
			$config['allowed_types'] = 'jpeg|jpg|png'; //Images extensions accepted
			$config['overwrite'] = TRUE; //If exists an image with the same name it will overwrite. Set to  false if don't want to overwrite
			$this->load->library('upload', $config); //Load the upload CI library
			
			if (!$this->upload->do_upload('userfile')){
				$uploadError = array('upload_error' => $this->upload->display_errors()); 
				$this->set_flashdata('uploadError', $uploadError, 'hiremetro/home.php'); //If for some reason the upload could not be done, returns the error in a flashdata and redirect to the page you specify in $urlYouWantToReturn
				exit;
			};
			
			$file_info = $this->upload->data('');
			$file_name = $file_info['file_name'];
			$config['image_library'] = 'gd2';
			$config['source_image'] = '/images/user_image/'.$file_name.'';
			$config['maintain_ratio'] = TRUE;
			$config['width'] = 164;
			$config['height'] = 163;
			$file_name = '/images/user_image/'.$file_name.'';
			
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
			
			$data = array(
				'employee_id' => ($id),
				'fname' => $this->session->userdata('fname'),
				'mname' => $this->session->userdata('mname'),
				'lname' => $this->session->userdata('lname'),
				'address' => $this->session->userdata('address'),
				'birthday' => $this->session->userdata('birthday'),
				'sex' => $this->session->userdata('sex'),
				'contact' => $this->session->userdata('contact'),
				'email' => $this->session->userdata('email'),
				'picture' => ($file_name),
			);
			
			$this->hiremetrodbase->signup($data, $table);
			
			$table = "work_details";
			
			$data = array(
				'employee_id' => ($id),
				'work_title' => $this->input->post('work_title'),
				'work_description' => $this->input->post('work_description'),
				'work_pay' => $this->input->post('work_pay'),
				'worker_location' => $this->input->post('work_location'),
				'work_language' => $this->input->post('work_language'),
				'date_posted' => (date("Y-m-d")),
				'status' => 'show'
			);
			
			$this->hiremetrodbase->signup($data, $table);
			
			$table = "login_credentials";
			
			$data = array(
				'employee_id' => ($id),
				'username' => $this->input->post('username'),
				'password' => $this->input->post('password'),
				'email' => $this->session->userdata('email')
			);
			
			$this->hiremetrodbase->signup($data, $table);
			
			$newdata = array(
                   'username'  => $_POST['username'],
                   'logged_in' => TRUE,
				   'id' => $id
               );
			
			$this->session->set_userdata($newdata);
			
			$employee = array(
				'fname' => $this->session->userdata('fname'),
				'mname' => $this->session->userdata('mname'),
				'lname' => $this->session->userdata('lname'),
				'email' => $this->session->userdata('email'),
				'worker_location' => $this->input->post('work_location'),
				'contact' => $this->session->userdata('contact'),
				'work_description' => $this->input->post('work_description'),
				'work_pay' => $this->input->post('work_pay'),
				'work_language' => $this->input->post('work_language'),
				'status' => 'show',
				'username' => $this->input->post('username'),
				'password' => $this->input->post('password'),
				'work_title' => $this->input->post('work_title'),
				'picture' => $file_name
			);
			
			$data['employee'] = $employee;
			
			$this->load->view('include/header');
			$this->load->view('hiremetro/profile', $data);
			$this->load->view('include/footer', $data);
			
		}
	}	
	public function logout(){
			$this->session->sess_destroy();
			$this->session->set_userdata('logged_in', FALSE);
			
			$data['title'] = "Hiremetro";
			
			$this->load->view('include/header', $data);
			$this->load->view('hiremetro/home', $data);
			$this->load->view('include/footer', $data);
			
	}
	
	
	public function search(){
		$data['title']= "hiremetro";
		
		if(isset($_POST['search'])){
			$valueToSearch = $_POST['valueToSearch'];
			
			$result = $this->hiremetrodbase->search_employees($valueToSearch);
			
			$employees = null;
			
			if($result != 'false'){
			
				foreach($result as $r){
					$id = $r['employee_id'];
					
				}
				
				foreach($result as $r){
					
					$id = $r['employee_id'];
					$username = $this->hiremetrodbase->get_username($id);
					$table = "work_details";
					$wd = $this->hiremetrodbase->get_employee_information($id,$table);
					
					$info = array(
						'employee_id' => $r['employee_id'],
						'lname' => $r['lname'],
						'fname' => $r['fname'],
						'mname' => $r['mname'],
						'address' => $r['address'],
						'sex' => $r['sex'],
						'birthday' => $r['birthday'],
						'description' => $wd[0]['work_description'],
						'username' => ($username),
						'work_title' => $wd[0]['work_title'],
						'picture' => $r['picture'],
						'status' => $wd[0]['status']
					);
					$employees[] = $info;
				}
				$data['employees'] = $employees;
				
			}
			else{
				$data['noresult'] = "No results found";
			}
		}
		
		$this->load->view('include/header', $data);
		$this->load->view('hiremetro/search', $data);
		$this->load->view('include/footer', $data);
	}
	
	public function view_about(){
		
		$data['title'] = "About: Hiremetro";
		
		$this->load->view('include/header', $data);
		$this->load->view('hiremetro/about');
		$this->load->view('include/footer', $data);
	}
	
	public function search_category(){
		
		$category = $_GET['category'];
		
		$result = $this->hiremetrodbase->search_category($category);
		
		if ($result != 'false'){
		
			foreach($result as $r){
				$id = array(
					'employee_id' => $r['employee_id'],
					'work_description' => $r['work_description'],
					'work_title' => $r['work_title'],
					'status' => $r['status']
				);
				
				$work[] = $id;
			}
			
			$c = count($work);
			
			for($a=0;$a<$c;$a++){
				
				$result = $this->hiremetrodbase->search_by_id($work[$a]['employee_id']);
				$username = $this->hiremetrodbase->get_username($work[$a]['employee_id']);
				
				$details = array(
					'fname' => $result[0]['fname'],
					'lname' => $result[0]['lname'],
					'mname' => $result[0]['mname'],
					'address' => $result[0]['address'],
					'birthday' => $result[0]['birthday'],
					'description' => $work[$a]['work_description'],
					'sex' => $result[0]['sex'],
					'username' => $username,
					'work_title' => $work[$a]['work_title'],
					'status' => $work[$a]['status'],
					'picture' => $result[0]['picture']
				);
				
				$employee[] = $details;
			}
			
			$data['employees'] = $employee;
		}
		else{
				$data['noresult'] = "No results found";
		}
		
		$this->load->view('include/header');
		$this->load->view('hiremetro/search' ,$data);
		$this->load->view('include/footer', $data);
		
	}
	
	public function view_profile(){
		
		$table = "employee_information";
		
		$id = $this->session->userdata('id');
			
		$ei = $this->hiremetrodbase->get_employee_information($id,$table);
			
		$table = "login_credentials";
			
		$lc = $this->hiremetrodbase->get_employee_information($id,$table);
		
		$table = "work_details";
		
		$wd = $this->hiremetrodbase->get_employee_information($id,$table);
		
		$infos = array(
				'work_title' => $wd[0]['work_title'],
				'work_description' => $wd[0]['work_description'],
				'worker_location' => $wd[0]['worker_location'],
				'work_pay' => $wd[0]['work_pay'],
				'work_language' => $wd[0]['work_language'],
				'status' => $wd[0]['status'],
				'fname' => $ei[0]['fname'],
				'mname' => $ei[0]['mname'],
				'lname' => $ei[0]['lname'],
				'email' => $ei[0]['email'],
				'contact' => $ei[0]['contact'],
				'username' => $lc[0]['username'],
				'password' => $lc[0]['password'],
				'picture' => $ei[0]['picture']
			);
			
			$data['employee'] = $infos;
		
		$this->load->view('include/header');
		$this->load->view('hiremetro/profile', $data);
		$this->load->view('include/footer', $data);
		
	}
	
	public function update_employee(){
		
		if(isset($_POST['update'])){
		
		$id = $this->session->userdata('id');
		
		$config['upload_path'] = './images/user_image/'; //The path where the image will be save
		$config['allowed_types'] = 'jpeg|jpg|png'; //Images extensions accepted
		$config['overwrite'] = TRUE; //If exists an image with the same name it will overwrite. Set to  false if don't want to overwrite
		$this->load->library('upload', $config); //Load the upload CI library
			
		if (!$this->upload->do_upload('userfile')){
			$uploadError = array('upload_error' => $this->upload->display_errors()); 
			$this->set_flashdata('uploadError', $uploadError, 'hiremetro/home.php'); //If for some reason the upload could not be done, returns the error in a flashdata and redirect to the page you specify in $urlYouWantToReturn
			exit;
		};
			
		$file_info = $this->upload->data('');
		$file_name = $file_info['file_name'];
			
		$config['image_library'] = 'gd2';
		$config['source_image'] = '/images/user_image/'.$file_name.'';
		$config['maintain_ratio'] = TRUE;
		$config['width'] = 164;
		$config['height'] = 163;
		
		$file_name = '/images/user_image/'.$file_name.'';
			
		$this->load->library('image_lib', $config);
		$this->image_lib->resize();
		
		$table = "employee_information";
		
		$info = array(
			'fname' => $this->input->post('fname'),
			'mname' => $this->input->post('mname'),
			'lname' => $this->input->post('lname'),
			'picture' => $file_name
		);

		$this->hiremetrodbase->update_details ($id,$table,$info);
		
		$table = "login_credentials";
		
		$info = array(
			'username' => $this->input->post('username'),
			'password' => $this->input->post('password')
		);
		
		$this->hiremetrodbase->update_details ($id,$table,$info);
		
		$info = array(
			'work_title' => $this->input->post('work_title'),
			'work_description' => $this->input->post('work_description'),
			'worker_location' => $this->input->post('worker_location'),
			'work_pay' => $this->input->post('work_pay'),
			'fname' => $this->input->post('fname'),
			'mname' => $this->input->post('mname'),
			'lname' => $this->input->post('lname'),
			'username' => $this->input->post('username'),
			'password' => $this->input->post('password')
		);
		
		$data['employee'] = $info;
		
		$this->load->view('include/header');
		$this->load->view('hiremetro/profile', $data);
		$this->load->view('include/footer', $data);
		
		}else{
			$info = array(
			'work_title' => $this->input->post('work_title'),
			'work_description' => $this->input->post('work_description'),
			'worker_location' => $this->input->post('worker_location'),
			'work_pay' => $this->input->post('work_pay'),
			'fname' => $this->input->post('fname'),
			'mname' => $this->input->post('mname'),
			'lname' => $this->input->post('lname'),
			'username' => $this->input->post('username'),
			'password' => $this->input->post('password')
		);
		
		$data['employee'] = $info;
		
		$this->load->view('include/header');
		$this->load->view('hiremetro/profile', $data);
		$this->load->view('include/footer', $data);
		}
	}
		
		public function update_work(){
			
			$id = $this->session->userdata('id');
			$table = "work_details";
			
			$info = array(
			'work_title' => $this->input->post('work_title'),
			'work_description' => $this->input->post('work_description'),
			'worker_location' => $this->input->post('worker_location'),
			'work_pay' => $this->input->post('work_pay'),
			'work_language' => $this->input->post('work_language')
			);
			
			$this->hiremetrodbase->update_details($id,$table,$info);
			
			$table = "employee_information";
			
			$ei = $this->hiremetrodbase->get_employee_information($id,$table);
			
			$table = "login_credentials";
			
			$lc = $this->hiremetrodbase->get_employee_information($id,$table);
			
			$infos = array(
				'work_title' => $this->input->post('work_title'),
				'work_description' => $this->input->post('work_description'),
				'worker_location' => $this->input->post('worker_location'),
				'work_pay' => $this->input->post('work_pay'),
				'work_language' => $this->input->post('work_language'),
				'fname' => $ei[0]['fname'],
				'mname' => $ei[0]['mname'],
				'lname' => $ei[0]['lname'],
				'email' => $ei[0]['email'],
				'contact' => $ei[0]['contact'],
				'picture' => $ei[0]['picture'],
				'username' => $lc[0]['username'],
				'password' => $lc[0]['password']
			);
			
			$data['employee'] = $infos;
			
			$this->load->view('include/header');
			$this->load->view('hiremetro/profile',$data);
			$this->load->view('include/footer', $data);
		}
		
		public function update_information(){
			$id = $this->session->userdata('id');
			$table = "login_credentials";
			
			$info = array(
			'username' => $this->input->post('username'),
			'password' => $this->input->post('password')
			);
			
			$this->hiremetrodbase->update_details($id,$table,$info);
			
			$table = "employee_information";
			
			$config['upload_path'] = './images/user_image/'; //The path where the image will be save
			$config['allowed_types'] = 'jpeg|jpg|png'; //Images extensions accepted
			$config['overwrite'] = TRUE; //If exists an image with the same name it will overwrite. Set to  false if don't want to overwrite
			$this->load->library('upload', $config); //Load the upload CI library
				
			if (!$this->upload->do_upload('userfile')){
				$uploadError = array('upload_error' => $this->upload->display_errors()); 
				$this->set_flashdata('uploadError', $uploadError, 'hiremetro/home.php'); //If for some reason the upload could not be done, returns the error in a flashdata and redirect to the page you specify in $urlYouWantToReturn
				exit;
			};
				
			$file_info = $this->upload->data('');
			$file_name = $file_info['file_name'];
				
			$config['image_library'] = 'gd2';
			$config['source_image'] = '/images/user_image/'.$file_name.'';
			$config['maintain_ratio'] = TRUE;
			$config['width'] = 164;
			$config['height'] = 163;
			
			$file_name = '/images/user_image/'.$file_name.'';
				
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
			
			$info = array(
			'fname' => $this->input->post('fname'),
			'mname' => $this->input->post('mname'),
			'lname' => $this->input->post('lname'),
			'email' => $this->input->post('email'),
			'contact' => $this->input->post('contact'),
			'picture' => $file_name
			);
			
			$this->hiremetrodbase->update_details($id,$table,$info);
			
			$table = "work_details";
			$wd = $this->hiremetrodbase->get_employee_information($id,$table);
			
			$infos = array(
				'work_title' => $wd[0]['work_title'],
				'work_description' => $wd[0]['work_description'],
				'worker_location' => $wd[0]['worker_location'],
				'work_pay' => $wd[0]['work_pay'],
				'work_language' => $wd[0]['work_language'],
				'fname' => $this->input->post('fname'),
				'mname' => $this->input->post('mname'),
				'lname' => $this->input->post('lname'),
				'email' => $this->input->post('email'),
				'contact' => $this->input->post('contact'),
				'username' => $this->input->post('username'),
				'password' => $this->input->post('password'),
				'picture' => $file_name
			);
			
			$data['employee'] = $infos;
			
			$this->load->view('include/header');
			$this->load->view('hiremetro/profile',$data);
			$this->load->view('include/footer', $data);
		}
		
		public function employee_profile(){
		$data['title'] = "Ads: Hiremetro";
		
		if( $_GET['username'])
		{
			$username = $_GET['username'];
			$result = $this->hiremetrodbase->get_id($username);
			
			$employees = null;
			
			foreach($result as $r){
				$id = $r['employee_id'];
			}
			
			foreach($result as $r){
				
				$allinfo = $this->hiremetrodbase->get_all($id);
				$alldetails = $this->hiremetrodbase->get_details($id);
				
				$info = array(
					'employee_id' => $id,
					'lname' => $allinfo[0]['lname'],
					'fname' => $allinfo[0]['fname'],
					'mname' => $allinfo[0]['mname'],
					'address' => $allinfo[0]['address'],
					'sex' => $allinfo[0]['sex'],
					'birthday' => $allinfo[0]['birthday'],
					'contact' => $allinfo[0]['contact'],
					'email' => $allinfo[0]['email'],
					'work_title' => $alldetails[0]['work_title'],
					'work_description' => $alldetails[0]['work_description'],
					'worker_location' => $alldetails[0]['worker_location'],
					'work_language' => $alldetails[0]['work_language'],
					'picture' => $allinfo[0]['picture']
						);
				$employees[] = $info;
			}
			$data['employees'] = $employees;	
		}
		
		
		$this->load->view('include/header', $data);
		$this->load->view('hiremetro/ads', $data);
		$this->load->view('include/footer', $data);
	}
	
	public function view_faqs(){
		
		$this->load->view('include/header');
		$this->load->view('hiremetro/faqs');
		$this->load->view('include/footer');
		
	}
	
	public function deactivate(){
		$id = $this->session->userdata('id');
		
		$this->hiremetrodbase->deactivate($id);
		$this->session->sess_destroy();
		$this->session->set_userdata('logged_in', FALSE);
		
		
		$this->load->view('include/header');
		$this->load->view('hiremetro/home');
		$this->load->view('include/footer');
	}
		
	public function update_status(){
		$id = $this->session->userdata('id');
		$table = "work_details";
		$status = $_POST['status'];
		
		$wd = $this->hiremetrodbase->get_employee_information($id,$table);
		
		$info = array(
			'work_title' => $wd[0]['work_title'],
			'work_description' => $wd[0]['work_description'],
			'work_pay' => $wd[0]['work_pay'],
			'worker_location' => $wd[0]['worker_location'],
			'status' => $status
		);
		
		$this->hiremetrodbase->update_details($id,$table,$info);
		
		$table = "employee_information";
			
		$ei = $this->hiremetrodbase->get_employee_information($id,$table);
			
		$table = "login_credentials";
			
		$lc = $this->hiremetrodbase->get_employee_information($id,$table);
			
		$infos = array(
			'work_title' => $wd[0]['work_title'],
			'work_description' => $wd[0]['work_description'],
			'work_pay' => $wd[0]['work_pay'],
			'worker_location' => $wd[0]['worker_location'],
			'status' => $status,
			'fname' => $ei[0]['fname'],
			'mname' => $ei[0]['mname'],
			'lname' => $ei[0]['lname'],
			'email' => $ei[0]['email'],
			'contact' => $ei[0]['contact'],
			'picture' => $ei[0]['picture'],
			'username' => $lc[0]['username'],
			'password' => $lc[0]['password']
		);
			
		$data['employee'] = $infos;
		
		$data['title'] = "Hiremetro: Profile";
		
		$this->load->view('include/header',$data);
		$this->load->view('hiremetro/profile');
	}
	
	public function admin(){
		$data['title'] = "Hiremetro";

		$category = "Bartender";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['bartender'] = $result;
		$total = $result;
		
		$category = "Carpenter";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['carpenter'] = $result;
		$total= $total+$result;
		
		$category = "Cook";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['cook'] = $result;
		$total= $total+$result;
		
		$category = "Driver";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['driver'] = $result;
		$total= $total+$result;
		
		$category = "Gardener";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['gardener'] = $result;
		$total= $total+$result;
		
		$category = "Janitor";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['janitor'] = $result;
		$total= $total+$result;
		
		$category = "Maid";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['maid'] = $result;
		$total= $total+$result;
		
		$category = "Masseuse";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['masseuse'] = $result;
		$total= $total+$result;
		
		$category = "Nanny";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['nanny'] = $result;
		$total= $total+$result;
		
		$category = "Plumber";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['plumber'] = $result;
		$total= $total+$result;
		
		$category = "Tutor";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['tutor'] = $result;
		$total= $total+$result;
		
		$category = "Waiter";
		$result = $this->hiremetrodbase->admin_dashboard($category);
		$data['waiter'] = $result;
		$total= $total+$result;
		
		$data['total'] = $total;
		$this->load->view('include/header_admin', $data);
		$this->load->view('hiremetro/admin_dashboard', $data);
	}
	
	public function admin_profiles(){
		
		$data['title'] = "Hiremetro";
		
		if(!empty($_GET['category']) && !empty($_GET['search'])){
			
			$category = $_GET['category'];
			if($category != 'All'){
				
				$search = $_GET['search'];
				
				$result_search = $this->hiremetrodbase->admin_profiles_search($search);
				$result_category = $this->hiremetrodbase->search_category($category);
				
				$profiles = null;
				
				foreach($result_search as $s){
					foreach($result_category as $c){
						if($c['employee_id'] == $s['employee_id']){
							$info = array(
								'employee_id' => $c['employee_id'],
								'name' => (' '.$s['fname'].' '.$s['mname'].' '.$s['lname']),
								'address' => $s['address'],
								'email' => $s['email'],
								'contact' => $s['contact'],
								'work_title' => $c['work_title']
							);
							$profiles[] = $info;
							break;
						}
					}
				}
				if($profiles == null){
					$data['profiles'] = 'null';
				}else{
					$data['profiles'] = $profiles;
				}
			}
			else{
				$search = $_GET['search'];
				
				$result_search =  $this->hiremetrodbase->admin_profiles_search($search);
				
				foreach($result_search as $s){
					$wd = $this->hiremetrodbase->admin_profiles_2($s['employee_id']);
					
					$info = array(
						'employee_id' => $s['employee_id'],
						'name' => (' '.$s['fname'].' '.$s['mname'].' '.$s['lname']),
						'address' => $s['address'],
						'email' => $s['email'],
						'contact' => $s['contact'],
						'work_title' => $wd[0]['work_title']
					);
					$profiles[] = $info;
				}
				
				$data['profiles'] = $profiles;
				
			}
		}
		elseif(!empty($_GET['category']) && $_GET['category'] != 'All'){
			
			$category = $_GET['category'];
				
				$result = $this->hiremetrodbase->search_category($category);
				
				if($result != 'false'){
					foreach($result as $r){
						$table = "employee_information";
						$ei = $this->hiremetrodbase->get_employee_information($r['employee_id'], $table);
						
						$info = array(
							'employee_id' => $r['employee_id'],
							'name' => (' '.$ei[0]['fname'].' '.$ei[0]['mname'].' '.$ei[0]['lname']),
							'address' => $ei[0]['address'],
							'email' => $ei[0]['email'],
							'contact' => $ei[0]['contact'],
							'work_title' => $r['work_title']
						);
						$profiles[] = $info;
					}
					
					$data['profiles'] = $profiles;
				}
				else{
					$data['profiles'] = 'null';
				};
			
		}	
		else{
			$ei = $this->hiremetrodbase->admin_profiles();
			
			foreach($ei as $e){
				$wd = $this->hiremetrodbase->admin_profiles_2($e['employee_id']);
				
				$info = array(
					'employee_id' => $e['employee_id'],
					'name' => (' '.$e['fname'].' '.$e['mname'].' '.$e['lname']),
					'address' => $e['address'],
					'email' => $e['email'],
					'contact' => $e['contact'],
					'work_title' => $wd[0]['work_title']
				);
				$profiles[] = $info;
			}
			
			$data['profiles'] = $profiles;
		};
		
		$this->load->view('include/header_admin', $data);
		$this->load->view('hiremetro/admin_profile', $data);
		
	}
	
	public function admin_reports(){
		$data['title'] = "Hiremetro";		
		
		if(isset($_GET['view'])){
			
			$id = $_GET['id'];
		
			$reports = $this->hiremetrodbase->admin_report_messages($id);
		
			foreach($reports as $r){
				
				$info = array(
					'report_id' => $r['report_id'],
					'employee_id' => $id,
					'report_subject' => $r['report_subject'],
					'report_body' => $r['report_body'],
					'report_date' => $r['report_date']
				);
				
				$report[] = $info;
			};
			
			$data['message'] = $report;
		};
		
		if(isset($_GET['delete'])){
			$id = $_GET['id'];
			
			$this->hiremetrodbase->admin_report_delete($id);
		}
		
		$report = $this->hiremetrodbase->admin_reports();
		
		$id = 0;
		
		foreach($report as $r){
				if($id != $r['employee_id']){
					$name = $this->hiremetrodbase->admin_report_name($r['employee_id']);
					$work_title = $this->hiremetrodbase->admin_report_work($r['employee_id']);
					$num_reports = $this->hiremetrodbase->admin_report_nos($r['employee_id']);
					
					$reports = array(
						'name' => $name,
						'work_title' => $work_title,
						'num_reports' => $num_reports,
						'employee_id' => $r['employee_id']
					);
					
					$reports1[] = $reports;
					$id = $r['employee_id'];
				}
		}
		
		$data['reports'] = $reports1;
		
		print_r($reports);
		
		$this->load->view('include/header_admin', $data);
		$this->load->view('hiremetro/admin_reports', $data);
	}
	
	public function admin_suggestions(){
		$data['title'] = "Hiremetro";	
		
		if(isset($_GET['delete'])){
			$id = $_GET['id'];
			$this->hiremetrodbase->admin_suggestions_delete($id);
		}
		
		$getsuggestions = $this->hiremetrodbase->get_suggestions();
		$info = array('suggestions' => $getsuggestions);
		
		$suggestions[] = $info;
		$data['suggestions'] = $suggestions;
		
		//////////////////////////////////////////////////////////////////////
		$config['base_url'] = base_url().'hiremetro/admin_suggestions';        
        $config['total_rows'] = $this->hiremetrodbase->count_all_users();        
        $config['per_page'] = 8;        
        $config['uri_segment'] = 3;        
        $config['full_tag_open'] = '<ul class="pagination pagination-lg">';        
        $config['full_tag_close'] = '</ul>';        
        $config['first_link'] = 'First';        
        $config['last_link'] = 'Last';        
        $config['first_tag_open'] = '<li>';        
        $config['first_tag_close'] = '</li>';        
        $config['prev_link'] = '&laquo';        
        $config['prev_tag_open'] = '<li class="prev">';        
        $config['prev_tag_close'] = '</li>';        
        $config['next_link'] = '&raquo';        
        $config['next_tag_open'] = '<li>';        
        $config['next_tag_close'] = '</li>';        
        $config['last_tag_open'] = '<li>';        
        $config['last_tag_close'] = '</li>';        
        $config['cur_tag_open'] = '<li class="active"><a href="#">';        
        $config['cur_tag_close'] = '</a></li>';        
        $config['num_tag_open'] = '<li>';        
        $config['num_tag_close'] = '</li>';
        
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $this->pagination->initialize($config);        
        $data['links'] = $this->pagination->create_links();        
        $data['users'] = $this->hiremetrodbase->get_users($config["per_page"], $page);    
        ////////////////////////////////////////////////////////////////////////////////////
		
		$this->load->view('include/header_admin', $data);
		$this->load->view('hiremetro/admin_suggestions', $data);
	}
	public function admin_message(){
		$data['title'] = "Hiremetro";
		
		$id = $_GET['id'];
		
		$result = $this->hiremetrodbase->admin_message($id);
		
		$info = array (
			'suggestion_id' => $result[0]['suggestion_id'],
			'subject' => $result[0]['subject'],
			'date' => $result[0]['date'],
			'suggestion' => $result[0]['suggestion'],
			'viewed' => $result[0]['viewed']
		);
		
		if($info['viewed'] != 1){
			$info['viewed'] = 1;
			$this->hiremetrodbase->admin_message_update($id, $info);
		}
		
		$data['suggestion'] = $info;
		
		$this->load->view('include/header_admin', $data);
		$this->load->view('hiremetro/admin_message', $data);
	}
	public function admin_settings(){
		$data['title'] = "Hiremetro";	
		
		$table = "login_credentials";
		$admin = $this->hiremetrodbase->admin_settings($table);
		$table = "admin";
		$accounts = $this->hiremetrodbase->admin_settings($table);
		
		$admin = array(
			'username' => $admin[0]['username'],
			'password' => $admin[0]['password'],
			'email' => $admin[0]['email'],
			'facebook' => $accounts[0]['account'],
			'twitter' => $accounts[1]['account'],
			'gmail' => $accounts[2]['account']
		);
		
		if(isset($_POST['username'])){
			$update = array(
				'employee_id' => 0,
				'username' => $_POST['username'],
				'password' => $admin['password'],
				'email' => $admin['email']
			);
			$admin['username'] = $_POST['username'];
			$this->hiremetrodbase->admin_settings_update($update);
		}
		
		if(isset($_POST['password'])){
			$update = array(
				'employee_id' => 0,
				'username' => $admin['username'],
				'password' => $_POST['password'],
				'email' => $admin['email']
			);
			$admin['password'] = $_POST['password'];
			$this->hiremetrodbase->admin_settings_update($update);
		}
		
		if(isset($_POST['email'])){
			$update = array(
				'employee_id' => 0,
				'username' => $admin['username'],
				'password' => $admin['password'],
				'email' => $_POST['email']
			);
			$admin['email'] = $_POST['email'];
			$this->hiremetrodbase->admin_settings_update($update);
		}
		if(isset($_POST['account'])){
			$update = array(
				'social_id' => 0,
				'social_media' => 'facebook',
				'account' => $_POST['facebook']
			);
			$admin['facebook'] = $_POST['facebook'];
			$this->hiremetrodbase->admin_settings_update2($update);
			
			$update = array(
				'social_id' => 1,
				'social_media' => 'twitter',
				'account' => $_POST['twitter']
			);
			$admin['twitter'] = $_POST['twitter'];
			$this->hiremetrodbase->admin_settings_update2($update);
			
			$update = array(
				'social_id' => 2,
				'social_media' => 'gmail',
				'account' => $_POST['gmail']
			);
			$admin['gmail'] = $_POST['gmail'];
			$this->hiremetrodbase->admin_settings_update2($update);
		}
		
		$data['admin'] = $admin;
		
		$this->load->view('include/header_admin', $data);
		$this->load->view('hiremetro/admin_settings', $data);
	}
	
	
}