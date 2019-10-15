<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trade extends CI_Controller {

	public function __construct() {
			parent::__construct();

			if(!$this->session->userdata('user_seq')){
				$url = 'http://stock.egkang.pe.kr';
	    	redirect($url, 'refresh');
			}

			// load form_validation library
			//$this->load->library('form_validation');
			$this->load->library('Redis_lib');
	} // End constructor


	public function index()
	{
		$data = array();
		load_view('trade', $data);
	}

	public function reset_orders()
	{
		$this->redis_lib->reset_orders();
		echo 0;
	}

	public function set_orders()
	{
		$this->redis_lib->set_orders();
		echo 0;
	}


	public function order_book()
	{
		$coin_code = $this->input->post('coin_code', true);

		$result = array();
		$result = $this->redis_lib->order_book($coin_code);

		echo json_encode($result);
	}

	public function sell()
	{
		$coin_code = $this->input->post('coin_code', true);
		$price = $this->input->post('price', true);
		$qty = $this->input->post('qty', true);
		$user_srl = $this->input->post('user_srl', true);

		echo $this->redis_lib->sell($coin_code, $price, $qty, $user_srl);
	}

	public function buy()
	{
		$coin_code = $this->input->post('coin_code', true);
		$price = $this->input->post('price', true);
		$qty = $this->input->post('qty', true);
		$user_srl = $this->input->post('user_srl', true);

		echo $this->redis_lib->buy($coin_code, $price, $qty, $user_srl);
	}

	public function trade_list(){
		$coin_code = $this->input->post('coin_code', true);

		$result = array();
		$result = $this->redis_lib->trade_list($coin_code);

		echo json_encode($result);
	}

	public function test(){
		$this->server['address'] = "127.0.0.1";//"192.168.0.11";
		$this->server['port'] = "6379";
		$r = new Redis();
		try {
			$r->connect($this->server['address'],$this->server['port'], 2.5, NULL, 150);

			$key = "zadd_test";

			//$r->zREM($key,'a');

			//$scores = array(1,2,3,4);
			//$values = array('a','b','c','d');

			//$values = array(9000000,9000000,9100000);
			//$scores = array(11,12,13);

			$scores = array(9000000,9000000,9100000);
			$values = array(11,12,13);

			$args = array();
			$length = count($scores);
			$args[] = $key;
			for ($i = 0; $i < $length; $i++) {
				$args[] = $scores[$i];
				$args[] = $values[$i];
			}

			call_user_func_array(array($r,'zAdd'),$args);

			//echo json_encode($r->zrange($key, 0, -1, 'withscores'));

			//$r->zAdd($key, 9000000, 14);

			echo json_encode($r->zrangebyscore($key, 9000000, 9000000, array('withscores'=>true)));

			//echo json_encode($r->zrange($key, 0, -1));



		} catch(RedisException $e) {
			$this->redis = null;
		}
	}

}
