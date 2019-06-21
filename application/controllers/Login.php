<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function index()
	{
		$data = array();

    $email = $this->input->post('email');
    $password = $this->input->post('password');

    $sql = "SELECT user_seq
          FROM
            user
          WHERE
            user_email = '$email'
            AND user_password= '$password'
      ";
    $result = $this->db->query($sql)->result_array();

    if(!empty($result[0])){
      $this->session->set_userdata('user_seq', $result[0]['user_seq']);
      $url = 'http://stock.egkang.pe.kr/trade';
    }else{
      $url = 'http://stock.egkang.pe.kr';
    }

    redirect($url, 'refresh');
	}
}
