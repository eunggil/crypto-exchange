<<<<<<< HEAD
<?php
/**
 * @ description : Code Library
 * @ author : prog106 <prog106@haomun.com>
 */
class Stock_lib {
    public $server; // redis server info
    public $redis;

    // Create constructor function to connect to Redis
    public function __construct() {
        $this->server['address'] = "127.0.0.1";//"192.168.0.11";
        $this->server['port'] = "6379";

        // Connect to Redis
        $this->redis = new Redis();
        try {
          $this->redis->connect($this->server['address'],$this->server['port'], 2.5, NULL, 150);
        } catch(RedisException $e) {
          $this->redis = null;
        }
    } // End constructor

    // Create destructor function to disconnect from Redis
    public function __destruct () {
      // Disconnect from Redis
      if (isset($this->redis)) {
        $this->redis->close();
      }
    } // End destructor

    public function reset_orders(){
      $this->redis->FLUSHDB();

      $CI =& get_instance();
      $CI->db->truncate('order_BTC');
      $CI->db->truncate('trade_BTC');
    }

    public function set_orders(){

      $CI =& get_instance();

      try {
          $CI->db->trans_begin();

          $coin_code = 'BTC';
          $base_code = 'KRW';

          // 매도 주문
          for($i=1; $i<=10; $i++){
            $order_price = ($i*100000) + 9000000;
            $this->order($coin_code, $base_code, 'sell', $order_price, 0.001*100000000);
          }

          // 매수 주문
          for($i=1; $i<=10; $i++){
            $order_price = ($i*100000) + 7900000;
            $this->order($coin_code, $base_code, 'buy', $order_price, 0.001*100000000);
          }

          $CI->db->trans_commit();
      } catch(Exception $e) {
          if(!empty($e->getCode())){
            $CI->db->trans_rollback();
            $this->reset_orders();
          }
      }

    }

    public function order($coin_code, $base_code, $trade_code, $order_price, $order_qty){
      $datetime = date("Y-m-d H:i:s");

      $CI =& get_instance();

      $user_seq = $CI->session->userdata('user_seq');
      $order_qty_db = $order_qty / 100000000;

      $trade_all_list = array();
      $trade_part_list = array();

      try {

          if($trade_code == 'sell'){
            // sell(매도) 주문일 때, 매수 미체결 조회 후 주문금액 보다 큰 매수금액 부터 주문금액까지 체결
            // 매수 미체결 조회 ( 매수금액 >= $order_price )
            $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_buy', $order_price, '+inf', array('withscores'=>true));

            // 매수 미체결 건 있으면 ...
            // 조회 결과 내역이 있으면 체결 ()
            if($order_list && count($order_list) > 0){

              // 매수 미체결 주문 정보 조회
              $return_data = array();
              foreach ($order_list as $key => $value) {
                $order_data = $this->redis->hgetall('order_list_'.$key);
                $order_data['order_seq'] = $key;
                // $order_data['order_price']
                // $order_data['order_qty']
                // $order_data['order_status']
                // $order_data['unexe_qty']
                array_push($return_data, $order_data);
              }

              // 주문 금액 / 주문 순서 정렬
              $return_data = array_msort($return_data, array('order_price'=>SORT_DESC, 'order_seq'=>SORT_ASC));

              foreach ($return_data as $key => $value) {
                // $order_qty 만큼 채결
                if($order_qty_db <= 0) continue;
                if($order_qty_db >= $value['unexe_qty']){
                  // 전체 체결
                  $order_qty_db = $order_qty_db - ($value['unexe_qty'] * 1);
                  $value['exe_qty'] = $value['unexe_qty'] * 1;
                  $value['unexe_qty'] = 0;
                  array_push($trade_all_list, $value);
                }else{
                  // 부분 체결
                  $value['exe_qty'] = $value['unexe_qty'] * 1;
                  $value['unexe_qty'] = ($value['unexe_qty'] * 1) - $order_qty_db;
                  $order_qty_db = 0;
                  array_push($trade_part_list, $value);
                }

              }

            } // 매수 체결 처리 끝

          } // sell(매도) 주문 처리 끝

          if($trade_code == 'buy'){
            // buy(매수) 주문일 때, 매도 미체결 조회 후 주문금액 보다 작은 매도금액 부터 주문금액까지 체결
            // 매도 미체결 조회 ( 매도금액 <= $order_price )
            $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_sell', '-inf', $order_price, array('withscores'=>true));

            // 매도 미체결 건 있으면 ...
            // 조회 결과 내역이 있으면 체결 ()
            if($order_list && count($order_list) > 0){

                $return_data = array();
                foreach ($order_list as $key => $value) {
                  // 조회 결과 내역이 있으면 체결 ()
                  $order_data = $this->redis->hgetall('order_list_'.$key);
                  $order_data['order_seq'] = $key;
                  array_push($return_data, $order_data);
                }

                // 주문 금액 / 주문 순서 정렬
                $return_data = array_msort($return_data, array('order_price'=>SORT_ASC, 'order_seq'=>SORT_ASC));

                foreach ($return_data as $key => $value) {
                  // $order_qty 만큼 채결
                  if($order_qty_db <= 0) continue;
                  if($order_qty_db >= $value['unexe_qty']){
                    // 전체 체결
                    $order_qty_db = $order_qty_db - ($value['unexe_qty'] * 1);
                    $value['exe_qty'] = $value['unexe_qty'] * 1;
                    $value['unexe_qty'] = 0;

                    array_push($trade_all_list, $value);

                  }else{
                    // 부분 체결
                    $value['exe_qty'] = $value['unexe_qty'] * 1;
                    $value['unexe_qty'] = ($value['unexe_qty'] * 1) - $order_qty_db;
                    $order_qty_db = 0;
                    array_push($trade_part_list, $value);
                  }

                }

              } // 매도 체결 끝
          } // 매수 주문 처리 끝

          ////////////////////////////////////////
          // 남은거 미체결건 처리
          ////////////////////////////////////////
          if($order_qty_db > 0){
            // 체결 후 남은 수량 : 남은 건 미체결 주문으로 처리
            $CI->db->trans_begin();
            $data = array(
              'user_seq' => $user_seq,
              'coin_code' => $coin_code,
              'base_code' => $base_code,
              'trade_code' => $trade_code,
              'order_status' => '01',
              'order_price' => $order_price,
              'order_qty' => ($order_qty / 100000000),
              'unexe_qty' => $order_qty_db,
            );

            $CI->db->insert('order_'.$coin_code, $data);
            $order_seq = $CI->db->insert_id();

            $this->redis->zAdd('order_'.$coin_code.'_'.$trade_code, $order_price, $order_seq);

            $this->redis->hmset('order_list_'.$order_seq, $data);
            $this->redis->zincrby($coin_code.'_'.$trade_code, ($order_qty_db * 100000000), $order_price);

            $CI->db->trans_commit();
          }

          ////////////////////////////////////////
          // 체결 내역 처리
          ////////////////////////////////////////
          
          // 전체 체결 내역 DB처리
          if(count($trade_all_list) > 0){
            // 체결 내역 DB 처리
            $trade_all_list_seq = array();
            $insert_data = array();
            foreach ($trade_all_list as $key => $value) {
              $insert_row = array();
              $insert_row['order_seq'] = $value['order_seq'];
              $insert_row['trade_code'] = $trade_code;
              $insert_row['fuser_seq'] = $value['user_seq'];
              $insert_row['tuser_seq'] = $user_seq;
              $insert_row['coin_code'] = $value['coin_code'];
              $insert_row['base_code'] = $value['base_code'];
              $insert_row['price'] = $value['order_price'];
              $insert_row['qty'] = $value['exe_qty'];
              $insert_row['reg_date'] = $datetime;

              array_push($insert_data, $insert_row);
              array_push($trade_all_list_seq, $value['order_seq']);
            }

            $CI->db->insert_batch('trade_'.$coin_code, $insert_data);

            $data = array(
              'order_status' => '03', // 전체 체결
              'unexe_qty' => 0,
              'update_date' => $datetime
            );

            $CI->db->where_in('order_seq', $trade_all_list_seq);
            $CI->db->update('order_'.$coin_code, $data);

          }

          // 부분 체결 내역 DB 처리
          if(count($trade_part_list) > 0){
            $value = $trade_part_list[0];

            $insert_data = array();
            $insert_row = array();
            $insert_row['order_seq'] = $value['order_seq'];
            $insert_row['trade_code'] = $trade_code;
            $insert_row['fuser_seq'] = $value['user_seq'];
            $insert_row['tuser_seq'] = $user_seq;
            $insert_row['coin_code'] = $value['coin_code'];
            $insert_row['base_code'] = $value['base_code'];
            $insert_row['price'] = $value['order_price'];
            $insert_row['qty'] = $value['exe_qty'];
            $insert_row['reg_date'] = $datetime;

            array_push($insert_data, $insert_row);
            $CI->db->insert_batch('trade_'.$coin_code, $insert_data);

            $data = array(
              'order_status' => '02', // 부분 체결
              'unexe_qty' => $value['unexe_qty'],
              'update_date' => $datetime
            );

            $CI->db->where('order_seq', $value['order_seq']);
            $CI->db->update('order_'.$coin_code, $data);
          }

      } catch(Exception $e) {
          if(!empty($e->getCode())){
            $CI->db->trans_rollback();
            $this->reset_orders();
          }
      }

      return $data;
    }


    public function order_book($coin_code='BTC'){
      $result = array();
      $result['sell'] = array();
      $result['buy'] = array();

      // 매도 호가
      $order_sell = $this->redis->zrange($coin_code.'_sell', 0, -1, 'withscores');
      krsort($order_sell);

      foreach($order_sell as $id=>$score){
         array_push($result['sell'], array('price'=>$id, 'qty'=>($score / 100000000)));
      }

      // 매수 호가
      $order_buy = $this->redis->zrevrange($coin_code.'_buy', 0, -1, 'withscores');
      krsort($order_buy);

      foreach($order_buy as $id=>$score){
        array_push($result['buy'], array('price'=>$id, 'qty'=>($score / 100000000)));
      }

      return $result;
    }

    public function trade_list($coin_code){
      $result = array();

      $params = array(
        '#',
        'order_list_*->user_seq',
        'order_list_*->coin_code',
        'order_list_*->base_code',
        'order_list_*->trade_code',
        'order_list_*->order_status',
        'order_list_*->order_price',
        'order_list_*->order_qty',
        'order_list_*->unexe_qty',
      );
      $options = array(
        'get' => $params,
      );

      $order_list = $this->redis->sort('order_'.$coin_code.'_sell', $options);

      $loop_count = count($order_list) / 9;
      for($i=0; $i<$loop_count; $i++){
          $data = array(
            'order_seq'=>$order_list[0+($i*9)],
            'user_seq'=>$order_list[1+($i*9)],
            'coin_code'=>$order_list[2+($i*9)],
            'base_code'=>$order_list[3+($i*9)],
            'trade_code'=>$order_list[4+($i*9)],
            'order_status'=>$order_list[5+($i*9)],
            'order_price'=>$order_list[6+($i*9)],
            'order_qty'=>$order_list[7+($i*9)],
            'unexe_qty'=>$order_list[8+($i*9)],
          );
          array_push($result, $data);
      }

      $order_list = $this->redis->sort('order_'.$coin_code.'_buy', $options);
      $loop_count = count($order_list) / 9;
      for($i=0; $i<$loop_count; $i++){
          $data = array(
            'order_seq'=>$order_list[0+($i*9)],
            'user_seq'=>$order_list[1+($i*9)],
            'coin_code'=>$order_list[2+($i*9)],
            'base_code'=>$order_list[3+($i*9)],
            'trade_code'=>$order_list[4+($i*9)],
            'order_status'=>$order_list[5+($i*9)],
            'order_price'=>$order_list[6+($i*9)],
            'order_qty'=>$order_list[7+($i*9)],
            'unexe_qty'=>$order_list[8+($i*9)],
          );
          array_push($result, $data);
      }

      return $result;
    }

    ////////////////////////////////////////////////////////
    //
    ////////////////////////////////////////////////////////


    public function trade($coin_code, $base_code, $trade_code, $order_price, $order_qty){

      // $trade_code : sell, buy
      // sell(매도) 주문일 때, 매수 미체결 조회 후 주문금액 보다 큰 매수금액 부터 주문금액까지 체결
      if($trade_code == 'sell'){
          // 매수 미체결 조회 ( 매수금액 >= $order_price)
          // 조회 결과 내역이 있으면 체결 ()
      }


      // buy(매수) 주문일 때, 매도 미체결 조회 후 주문금액 보다 작은 매도금액 부터 주문금액까지 체결

      $CI =& get_instance();

      $user_seq = $CI->session->userdata('user_seq');
      $order_qty_db = $order_qty / 100000000;

      try {

          //$this->redis->zrangebyscore($key, $order_price, $order_price, array('withscores'=>true)));
          $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_'.$trade_code, $order_price, $order_price, array('withscores'=>true));

          $order_seq_list = array();
          foreach($order_list as $id=>$score){
            array_push($order_seq_list, $id);
          }

          //$sql_order_seq = implode( ',', $order_seq_list);

          $CI->db->where_in('order_seq', $order_seq_list);
          $CI->db->order_by('order_seq', 'asc');
          $query = $CI->db->get('order_'.$coin_code);
          $result = $query->result_array();

          foreach ($result as $key => $value) {
            //$value['order_price']
            //$value['order_qty']

            //if($value['unexec_qty'] <= $order_qty_db){
              $temp_order_qty = $order_qty_db;
              $order_qty_db = $order_qty_db - $value['unexec_qty'];

              if($order_qty_db >= 0){
                // delete? update order_coin db

                // delete order_coin_type redis
                //$this->redis->zrem($coin_code.'_'.$trade_code, $price_buy);
              }else {
                $this->redis->zincrby($coin_code.'_'.$trade_code, ($trade_qty * -1), $price_buy);
              }
            //}


          }


          $CI->db->trans_begin();

          // $data = array(
          //         'user_seq' => $user_seq,
          //         'coin_code' => $coin_code,
          //         'base_code' => $base_code,
          //         'trade_code' => $trade_code,
          //         'order_status' => '01',
          //         'order_price' => $order_price,
          //         'order_qty' => $order_qty_db,
          //         'unexe_qty' => $order_qty_db,
          // );
          //
          // $CI->db->insert('order_'.$coin_code, $data);
          // $td_seq = $CI->db->insert_id();

          //$redis->zAdd('order_'.$coin_code.'_'.$trade_code, $order_price, $order_seq);
          //$this->redis->zincrby($coin_code.'_'.$trade_code, $order_qty, $order_price);


          $CI->db->trans_commit();
      } catch(Exception $e) {
          if(!empty($e->getCode())){
            $CI->db->trans_rollback();
            $this->reset_orders();
          }
      }
    }

    public function sell($coin_code='BTC', $price, $qty, $user_srl=1){
      $trade_qty = $qty * 100000000;

      $order_buy = $this->redis->zrevrange($coin_code.'_buy', 0, -1, 'withscores');
      krsort($order_buy);

      foreach($order_buy as $price_buy=>$qty_buy){

        if($price <= $price_buy){
          //trade
          if($trade_qty > 0){
            if($trade_qty >= $qty_buy){
              // 매수 전체 체결 건
              $trade_qty = $trade_qty - $qty_buy;

              //$this->trade($coin_code, $base_code, 'buy', $price_buy, $qty_buy);


              //====================================================================
              // 전체 체결 프로세스
              //====================================================================

              // 해당 호가 금액 매수 수량 삭제
              $this->redis->zrem($coin_code.'_buy', $price_buy);

              $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_buy', $price_buy, $price_buy, array('withscores'=>true));

              $order_seq_list = array();
              foreach($order_list as $id=>$score){
                // 매도 주문건 삭제
                $this->redis->zrem('order_'.$coin_code.'_buy', $id); //delete
                array_push($order_seq_list, $id);
              }

              $datetime = date("Y-m-d H:i:s");
              $CI =& get_instance();
              $user_seq = $CI->session->userdata('user_seq');

              // 체결 내역 등록
              $CI->db->where_in('order_seq', $order_seq_list);
              $CI->db->order_by('order_seq', 'asc');
              $query = $CI->db->get('order_'.$coin_code);
              $result = $query->result_array();

              $insert_data = array();
              foreach ($result as $key => $value) {
                $insert_row = array();
                $insert_row['order_seq'] = $value['order_seq'];
                $insert_row['trade_code'] = 'sell';
                $insert_row['fuser_seq'] = $value['user_seq'];
                $insert_row['tuser_seq'] = $user_seq;
                $insert_row['coin_code'] = $value['coin_code'];
                $insert_row['base_code'] = $value['base_code'];
                $insert_row['price'] = $value['order_price'];
                $insert_row['qty'] = $value['unexe_qty'];
                $insert_row['reg_date'] = $datetime;

                array_push($insert_data, $insert_row);
              }

              $CI->db->insert_batch('trade_'.$coin_code, $insert_data);

              //-------------------------------------------------

              // DB 매도 주문건 조회 unexe_qty 전부 0으로 업데이트
              $data = array(
                'order_status' => '03', // 전체 체결
                'unexe_qty' => 0,
                'update_date' => $datetime
              );

              $CI->db->where_in('order_seq', $order_seq_list);
              $CI->db->update('order_'.$coin_code, $data);
              //====================================================================

            }else{
              // 매수 부분 체결 건
              //$this->trade($coin_code, $base_code, 'buy', $price_buy, $trade_qty);

              //====================================================================
              // 부분 체결 프로세스
              //====================================================================

              // 해당 호가 금액에서 매도 수량 만큼 매수(buy) 수량 차감
              $this->redis->zincrby($coin_code.'_buy', ($trade_qty * -1), $price_buy);


              $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_buy', $price_buy, $price_buy, array('withscores'=>true));

              $order_seq_list = array();
              foreach($order_list as $id=>$score){
                array_push($order_seq_list, $id);
              }

              $datetime = date("Y-m-d H:i:s");
              $CI =& get_instance();
              $user_seq = $CI->session->userdata('user_seq');

              // 매도 주문 금액에 해당 하는 매수 주문 목록 조회(먼저 주문한 순서)
              $CI->db->where_in('order_seq', $order_seq_list);
              $CI->db->order_by('order_price', 'desc');
              $CI->db->order_by('order_seq', 'asc');
              $query = $CI->db->get('order_'.$coin_code);
              $result = $query->result_array();


              $insert_data = array();
              foreach ($result as $key => $value) {

                if($trade_qty >= ($value['unexe_qty'] * 100000000)){
                  // 매도 주문 수량이 남은 수량 보다 크거나 같으면 전체 체결 : 여러건 일 수 있음.

                  $insert_row = array();
                  $insert_row['order_seq'] = $value['order_seq'];
                  $insert_row['trade_code'] = 'sell';
                  $insert_row['fuser_seq'] = $value['user_seq'];
                  $insert_row['tuser_seq'] = $user_seq;
                  $insert_row['coin_code'] = $value['coin_code'];
                  $insert_row['base_code'] = $value['base_code'];
                  $insert_row['price'] = $value['order_price'];
                  $insert_row['qty'] = $value['unexe_qty'];
                  $insert_row['reg_date'] = $datetime;

                  array_push($insert_data, $insert_row);

                  // DB 매도 주문건 조회 unexe_qty 전부 0으로 업데이트
                  $data = array(
                    'order_status' => '03', // 전체 체결
                    'unexe_qty' => 0,
                    'update_date' => $datetime
                  );

                  $CI->db->where('order_seq', $value['order_seq']);
                  $CI->db->update('order_'.$coin_code, $data);

                  // 매도 수량 만큼 매수 주문건 삭제 --------------- 이건 db 먼저 조회해서 주문 수량을 확인 해야 할듯 함.
                  $this->redis->zrem('order_'.$coin_code.'_buy', $value['order_seq']); //delete

                  $trade_qty = $trade_qty - ($value['unexe_qty'] * 100000000);
                }else{
                  // 남은 주문 수량 부분 체결 : 부분 체결건은 1건(must)
                  $insert_row = array();
                  $insert_row['order_seq'] = $value['order_seq'];
                  $insert_row['trade_code'] = 'sell';
                  $insert_row['fuser_seq'] = $value['user_seq'];
                  $insert_row['tuser_seq'] = $user_seq;
                  $insert_row['coin_code'] = $value['coin_code'];
                  $insert_row['base_code'] = $value['base_code'];
                  $insert_row['price'] = $value['order_price'];
                  $insert_row['qty'] = $trade_qty / 100000000;
                  $insert_row['reg_date'] = $datetime;

                  array_push($insert_data, $insert_row);

                  // DB 매도 주문건 조회 unexe_qty 차감 후 남은 수량으로 업데이트
                  $data = array(
                    'order_status' => '03', // 전체 체결
                    'unexe_qty' => (($value['unexe_qty'] * 100000000) - $trade_qty) / 100000000,
                    'update_date' => $datetime
                  );

                  $CI->db->where('order_seq', $value['order_seq']);
                  $CI->db->update('order_'.$coin_code, $data);

                  $trade_qty = 0;
                }

              }

              // 체결 내역 등록
              $CI->db->insert_batch('trade_'.$coin_code, $insert_data);

              //-------------------------------------------------

              // // 매도 수량 만큼 매수 주문건 삭제 --------------- 이건 db 먼저 조회해서 주문 수량을 확인 해야 할듯 함.
              // $this->redis->zrem('order_'.$coin_code.'_buy', $id); //delete
              //
              // // DB 매도 주문건 조회 unexe_qty 전부 0으로 업데이트
              // $data = array(
              //   'order_status' => '03', // 전체 체결
              //   'unexe_qty' => 0,
              //   'update_date' => $datetime
              // );
              //
              // $CI->db->where_in('order_seq', $order_seq_list);
              // $CI->db->update('order_'.$coin_code, $data);
              // //====================================================================
              //
              // $trade_qty = 0;
            } //체결 프로세스 끝
          }
        }else{
        }
      }

      if($trade_qty > 0){
        //order
        $base_code = 'KRW';
        $this->order($coin_code, $base_code, 'sell', $price, $trade_qty);

        //$this->redis->zincrby($coin_code.'_sell', $trade_qty, $price);
      }

      return $trade_qty;
    }

    public function buy($coin_code='BTC', $price, $qty, $user_srl=1){
      $trade_qty = $qty * 100000000;

      $order_sell = $this->redis->zrange($coin_code.'_sell', 0, -1, 'withscores');
      ksort($order_sell);

      foreach($order_sell as $price_sell=>$qty_sell){

        if($price >= $price_sell){
          //trade
          if($trade_qty > 0){
            if($trade_qty >= $qty_sell){
              // 매도 전체 체결 건
              $trade_qty = $trade_qty - $qty_sell;
              $this->redis->zrem($coin_code.'_sell', $price_sell);
            }else{
              // 매도 부분 체결 건
              $this->redis->zincrby($coin_code.'_sell', ($trade_qty * -1), $price_sell);
              $trade_qty = 0;
            }
          }
        }else{
        }

      }

      if($trade_qty > 0){
        //order
        $base_code = 'KRW';
        $this->order($coin_code, $base_code, 'buy', $price, $trade_qty);

        //$this->redis->zincrby($coin_code.'_buy', $trade_qty, $price);
      }

      return $trade_qty;

    }



}
=======
<?php
/**
 * @ description : Code Library
 * @ author : prog106 <prog106@haomun.com>
 */
class Stock_lib {
    public $server; // redis server info
    public $redis;

    // Create constructor function to connect to Redis
    public function __construct() {
        $this->server['address'] = "127.0.0.1";//"192.168.0.11";
        $this->server['port'] = "6379";

        // Connect to Redis
        $this->redis = new Redis();
        try {
          $this->redis->connect($this->server['address'],$this->server['port'], 2.5, NULL, 150);
        } catch(RedisException $e) {
          $this->redis = null;
        }
    } // End constructor

    // Create destructor function to disconnect from Redis
    public function __destruct () {
      // Disconnect from Redis
      if (isset($this->redis)) {
        $this->redis->close();
      }
    } // End destructor

    public function reset_orders(){
      $this->redis->FLUSHDB();

      $CI =& get_instance();
      $CI->db->truncate('order_BTC');
      $CI->db->truncate('trade_BTC');
    }

    public function set_orders(){

      $CI =& get_instance();

      try {
          $CI->db->trans_begin();

          $coin_code = 'BTC';
          $base_code = 'KRW';

          // 매도 주문
          for($i=1; $i<=10; $i++){
            $order_price = ($i*100000) + 9000000;
            $this->order($coin_code, $base_code, 'sell', $order_price, 0.001*100000000);
          }

          // 매수 주문
          for($i=1; $i<=10; $i++){
            $order_price = ($i*100000) + 7900000;
            $this->order($coin_code, $base_code, 'buy', $order_price, 0.001*100000000);
          }

          $CI->db->trans_commit();
      } catch(Exception $e) {
          if(!empty($e->getCode())){
            $CI->db->trans_rollback();
            $this->reset_orders();
          }
      }

    }

    public function order($coin_code, $base_code, $trade_code, $order_price, $order_qty){
      $datetime = date("Y-m-d H:i:s");

      $CI =& get_instance();

      $user_seq = $CI->session->userdata('user_seq');
      $order_qty_db = $order_qty / 100000000;

      $trade_all_list = array();
      $trade_part_list = array();

      try {

          if($trade_code == 'sell'){
            // sell(매도) 주문일 때, 매수 미체결 조회 후 주문금액 보다 큰 매수금액 부터 주문금액까지 체결
            // 매수 미체결 조회 ( 매수금액 >= $order_price )
            $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_buy', $order_price, '+inf', array('withscores'=>true));

            // 매수 미체결 건 있으면 ...
            // 조회 결과 내역이 있으면 체결 ()
            if($order_list && count($order_list) > 0){

              // 매수 미체결 주문 정보 조회
              $return_data = array();
              foreach ($order_list as $key => $value) {
                $order_data = $this->redis->hgetall('order_list_'.$key);
                $order_data['order_seq'] = $key;
                // $order_data['order_price']
                // $order_data['order_qty']
                // $order_data['order_status']
                // $order_data['unexe_qty']
                array_push($return_data, $order_data);
              }

              // 주문 금액 / 주문 순서 정렬
              $return_data = array_msort($return_data, array('order_price'=>SORT_DESC, 'order_seq'=>SORT_ASC));

              foreach ($return_data as $key => $value) {
                // $order_qty 만큼 채결
                if($order_qty_db <= 0) continue;
                if($order_qty_db >= $value['unexe_qty']){
                  // 전체 체결
                  $order_qty_db = $order_qty_db - ($value['unexe_qty'] * 1);
                  $value['exe_qty'] = $value['unexe_qty'] * 1;
                  $value['unexe_qty'] = 0;
                  array_push($trade_all_list, $value);
                }else{
                  // 부분 체결
                  $value['exe_qty'] = $value['unexe_qty'] * 1;
                  $value['unexe_qty'] = ($value['unexe_qty'] * 1) - $order_qty_db;
                  $order_qty_db = 0;
                  array_push($trade_part_list, $value);
                }

              }

            } // 매수 체결 처리 끝

          } // sell(매도) 주문 처리 끝

          if($trade_code == 'buy'){
            // buy(매수) 주문일 때, 매도 미체결 조회 후 주문금액 보다 작은 매도금액 부터 주문금액까지 체결
            // 매도 미체결 조회 ( 매도금액 <= $order_price )
            $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_sell', '-inf', $order_price, array('withscores'=>true));

            // 매도 미체결 건 있으면 ...
            // 조회 결과 내역이 있으면 체결 ()
            if($order_list && count($order_list) > 0){

                $return_data = array();
                foreach ($order_list as $key => $value) {
                  // 조회 결과 내역이 있으면 체결 ()
                  $order_data = $this->redis->hgetall('order_list_'.$key);
                  $order_data['order_seq'] = $key;
                  array_push($return_data, $order_data);
                }

                // 주문 금액 / 주문 순서 정렬
                $return_data = array_msort($return_data, array('order_price'=>SORT_ASC, 'order_seq'=>SORT_ASC));

                foreach ($return_data as $key => $value) {
                  // $order_qty 만큼 채결
                  if($order_qty_db <= 0) continue;
                  if($order_qty_db >= $value['unexe_qty']){
                    // 전체 체결
                    $order_qty_db = $order_qty_db - ($value['unexe_qty'] * 1);
                    $value['exe_qty'] = $value['unexe_qty'] * 1;
                    $value['unexe_qty'] = 0;

                    array_push($trade_all_list, $value);

                  }else{
                    // 부분 체결
                    $value['exe_qty'] = $value['unexe_qty'] * 1;
                    $value['unexe_qty'] = ($value['unexe_qty'] * 1) - $order_qty_db;
                    $order_qty_db = 0;
                    array_push($trade_part_list, $value);
                  }

                }

              } // 매도 체결 끝
          } // 매수 주문 처리 끝

          ////////////////////////////////////////
          // 남은거 미체결건 처리
          ////////////////////////////////////////
          if($order_qty_db > 0){
            // 체결 후 남은 수량 : 남은 건 미체결 주문으로 처리
            $CI->db->trans_begin();
            $data = array(
              'user_seq' => $user_seq,
              'coin_code' => $coin_code,
              'base_code' => $base_code,
              'trade_code' => $trade_code,
              'order_status' => '01',
              'order_price' => $order_price,
              'order_qty' => ($order_qty / 100000000),
              'unexe_qty' => $order_qty_db,
            );

            $CI->db->insert('order_'.$coin_code, $data);
            $order_seq = $CI->db->insert_id();

            $this->redis->zAdd('order_'.$coin_code.'_'.$trade_code, $order_price, $order_seq);

            $this->redis->hmset('order_list_'.$order_seq, $data);
            $this->redis->zincrby($coin_code.'_'.$trade_code, ($order_qty_db * 100000000), $order_price);

            $CI->db->trans_commit();
          }

          ////////////////////////////////////////
          // 체결 내역 처리
          ////////////////////////////////////////
          
          // 전체 체결 내역 DB처리
          if(count($trade_all_list) > 0){
            // 체결 내역 DB 처리
            $trade_all_list_seq = array();
            $insert_data = array();
            foreach ($trade_all_list as $key => $value) {
              $insert_row = array();
              $insert_row['order_seq'] = $value['order_seq'];
              $insert_row['trade_code'] = $trade_code;
              $insert_row['fuser_seq'] = $value['user_seq'];
              $insert_row['tuser_seq'] = $user_seq;
              $insert_row['coin_code'] = $value['coin_code'];
              $insert_row['base_code'] = $value['base_code'];
              $insert_row['price'] = $value['order_price'];
              $insert_row['qty'] = $value['exe_qty'];
              $insert_row['reg_date'] = $datetime;

              array_push($insert_data, $insert_row);
              array_push($trade_all_list_seq, $value['order_seq']);
            }

            $CI->db->insert_batch('trade_'.$coin_code, $insert_data);

            $data = array(
              'order_status' => '03', // 전체 체결
              'unexe_qty' => 0,
              'update_date' => $datetime
            );

            $CI->db->where_in('order_seq', $trade_all_list_seq);
            $CI->db->update('order_'.$coin_code, $data);

          }

          // 부분 체결 내역 DB 처리
          if(count($trade_part_list) > 0){
            $value = $trade_part_list[0];

            $insert_data = array();
            $insert_row = array();
            $insert_row['order_seq'] = $value['order_seq'];
            $insert_row['trade_code'] = $trade_code;
            $insert_row['fuser_seq'] = $value['user_seq'];
            $insert_row['tuser_seq'] = $user_seq;
            $insert_row['coin_code'] = $value['coin_code'];
            $insert_row['base_code'] = $value['base_code'];
            $insert_row['price'] = $value['order_price'];
            $insert_row['qty'] = $value['exe_qty'];
            $insert_row['reg_date'] = $datetime;

            array_push($insert_data, $insert_row);
            $CI->db->insert_batch('trade_'.$coin_code, $insert_data);

            $data = array(
              'order_status' => '02', // 부분 체결
              'unexe_qty' => $value['unexe_qty'],
              'update_date' => $datetime
            );

            $CI->db->where('order_seq', $value['order_seq']);
            $CI->db->update('order_'.$coin_code, $data);
          }

      } catch(Exception $e) {
          if(!empty($e->getCode())){
            $CI->db->trans_rollback();
            $this->reset_orders();
          }
      }

      return $data;
    }


    public function order_book($coin_code='BTC'){
      $result = array();
      $result['sell'] = array();
      $result['buy'] = array();

      // 매도 호가
      $order_sell = $this->redis->zrange($coin_code.'_sell', 0, -1, 'withscores');
      krsort($order_sell);

      foreach($order_sell as $id=>$score){
         array_push($result['sell'], array('price'=>$id, 'qty'=>($score / 100000000)));
      }

      // 매수 호가
      $order_buy = $this->redis->zrevrange($coin_code.'_buy', 0, -1, 'withscores');
      krsort($order_buy);

      foreach($order_buy as $id=>$score){
        array_push($result['buy'], array('price'=>$id, 'qty'=>($score / 100000000)));
      }

      return $result;
    }

    public function trade_list($coin_code){
      $result = array();

      $params = array(
        '#',
        'order_list_*->user_seq',
        'order_list_*->coin_code',
        'order_list_*->base_code',
        'order_list_*->trade_code',
        'order_list_*->order_status',
        'order_list_*->order_price',
        'order_list_*->order_qty',
        'order_list_*->unexe_qty',
      );
      $options = array(
        'get' => $params,
      );

      $order_list = $this->redis->sort('order_'.$coin_code.'_sell', $options);

      $loop_count = count($order_list) / 9;
      for($i=0; $i<$loop_count; $i++){
          $data = array(
            'order_seq'=>$order_list[0+($i*9)],
            'user_seq'=>$order_list[1+($i*9)],
            'coin_code'=>$order_list[2+($i*9)],
            'base_code'=>$order_list[3+($i*9)],
            'trade_code'=>$order_list[4+($i*9)],
            'order_status'=>$order_list[5+($i*9)],
            'order_price'=>$order_list[6+($i*9)],
            'order_qty'=>$order_list[7+($i*9)],
            'unexe_qty'=>$order_list[8+($i*9)],
          );
          array_push($result, $data);
      }

      $order_list = $this->redis->sort('order_'.$coin_code.'_buy', $options);
      $loop_count = count($order_list) / 9;
      for($i=0; $i<$loop_count; $i++){
          $data = array(
            'order_seq'=>$order_list[0+($i*9)],
            'user_seq'=>$order_list[1+($i*9)],
            'coin_code'=>$order_list[2+($i*9)],
            'base_code'=>$order_list[3+($i*9)],
            'trade_code'=>$order_list[4+($i*9)],
            'order_status'=>$order_list[5+($i*9)],
            'order_price'=>$order_list[6+($i*9)],
            'order_qty'=>$order_list[7+($i*9)],
            'unexe_qty'=>$order_list[8+($i*9)],
          );
          array_push($result, $data);
      }

      return $result;
    }

    ////////////////////////////////////////////////////////
    //
    ////////////////////////////////////////////////////////


    public function trade($coin_code, $base_code, $trade_code, $order_price, $order_qty){

      // $trade_code : sell, buy
      // sell(매도) 주문일 때, 매수 미체결 조회 후 주문금액 보다 큰 매수금액 부터 주문금액까지 체결
      if($trade_code == 'sell'){
          // 매수 미체결 조회 ( 매수금액 >= $order_price)
          // 조회 결과 내역이 있으면 체결 ()
      }


      // buy(매수) 주문일 때, 매도 미체결 조회 후 주문금액 보다 작은 매도금액 부터 주문금액까지 체결

      $CI =& get_instance();

      $user_seq = $CI->session->userdata('user_seq');
      $order_qty_db = $order_qty / 100000000;

      try {

          //$this->redis->zrangebyscore($key, $order_price, $order_price, array('withscores'=>true)));
          $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_'.$trade_code, $order_price, $order_price, array('withscores'=>true));

          $order_seq_list = array();
          foreach($order_list as $id=>$score){
            array_push($order_seq_list, $id);
          }

          //$sql_order_seq = implode( ',', $order_seq_list);

          $CI->db->where_in('order_seq', $order_seq_list);
          $CI->db->order_by('order_seq', 'asc');
          $query = $CI->db->get('order_'.$coin_code);
          $result = $query->result_array();

          foreach ($result as $key => $value) {
            //$value['order_price']
            //$value['order_qty']

            //if($value['unexec_qty'] <= $order_qty_db){
              $temp_order_qty = $order_qty_db;
              $order_qty_db = $order_qty_db - $value['unexec_qty'];

              if($order_qty_db >= 0){
                // delete? update order_coin db

                // delete order_coin_type redis
                //$this->redis->zrem($coin_code.'_'.$trade_code, $price_buy);
              }else {
                $this->redis->zincrby($coin_code.'_'.$trade_code, ($trade_qty * -1), $price_buy);
              }
            //}


          }


          $CI->db->trans_begin();

          // $data = array(
          //         'user_seq' => $user_seq,
          //         'coin_code' => $coin_code,
          //         'base_code' => $base_code,
          //         'trade_code' => $trade_code,
          //         'order_status' => '01',
          //         'order_price' => $order_price,
          //         'order_qty' => $order_qty_db,
          //         'unexe_qty' => $order_qty_db,
          // );
          //
          // $CI->db->insert('order_'.$coin_code, $data);
          // $td_seq = $CI->db->insert_id();

          //$redis->zAdd('order_'.$coin_code.'_'.$trade_code, $order_price, $order_seq);
          //$this->redis->zincrby($coin_code.'_'.$trade_code, $order_qty, $order_price);


          $CI->db->trans_commit();
      } catch(Exception $e) {
          if(!empty($e->getCode())){
            $CI->db->trans_rollback();
            $this->reset_orders();
          }
      }
    }

    public function sell($coin_code='BTC', $price, $qty, $user_srl=1){
      $trade_qty = $qty * 100000000;

      $order_buy = $this->redis->zrevrange($coin_code.'_buy', 0, -1, 'withscores');
      krsort($order_buy);

      foreach($order_buy as $price_buy=>$qty_buy){

        if($price <= $price_buy){
          //trade
          if($trade_qty > 0){
            if($trade_qty >= $qty_buy){
              // 매수 전체 체결 건
              $trade_qty = $trade_qty - $qty_buy;

              //$this->trade($coin_code, $base_code, 'buy', $price_buy, $qty_buy);


              //====================================================================
              // 전체 체결 프로세스
              //====================================================================

              // 해당 호가 금액 매수 수량 삭제
              $this->redis->zrem($coin_code.'_buy', $price_buy);

              $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_buy', $price_buy, $price_buy, array('withscores'=>true));

              $order_seq_list = array();
              foreach($order_list as $id=>$score){
                // 매도 주문건 삭제
                $this->redis->zrem('order_'.$coin_code.'_buy', $id); //delete
                array_push($order_seq_list, $id);
              }

              $datetime = date("Y-m-d H:i:s");
              $CI =& get_instance();
              $user_seq = $CI->session->userdata('user_seq');

              // 체결 내역 등록
              $CI->db->where_in('order_seq', $order_seq_list);
              $CI->db->order_by('order_seq', 'asc');
              $query = $CI->db->get('order_'.$coin_code);
              $result = $query->result_array();

              $insert_data = array();
              foreach ($result as $key => $value) {
                $insert_row = array();
                $insert_row['order_seq'] = $value['order_seq'];
                $insert_row['trade_code'] = 'sell';
                $insert_row['fuser_seq'] = $value['user_seq'];
                $insert_row['tuser_seq'] = $user_seq;
                $insert_row['coin_code'] = $value['coin_code'];
                $insert_row['base_code'] = $value['base_code'];
                $insert_row['price'] = $value['order_price'];
                $insert_row['qty'] = $value['unexe_qty'];
                $insert_row['reg_date'] = $datetime;

                array_push($insert_data, $insert_row);
              }

              $CI->db->insert_batch('trade_'.$coin_code, $insert_data);

              //-------------------------------------------------

              // DB 매도 주문건 조회 unexe_qty 전부 0으로 업데이트
              $data = array(
                'order_status' => '03', // 전체 체결
                'unexe_qty' => 0,
                'update_date' => $datetime
              );

              $CI->db->where_in('order_seq', $order_seq_list);
              $CI->db->update('order_'.$coin_code, $data);
              //====================================================================

            }else{
              // 매수 부분 체결 건
              //$this->trade($coin_code, $base_code, 'buy', $price_buy, $trade_qty);

              //====================================================================
              // 부분 체결 프로세스
              //====================================================================

              // 해당 호가 금액에서 매도 수량 만큼 매수(buy) 수량 차감
              $this->redis->zincrby($coin_code.'_buy', ($trade_qty * -1), $price_buy);


              $order_list = $this->redis->zrangebyscore('order_'.$coin_code.'_buy', $price_buy, $price_buy, array('withscores'=>true));

              $order_seq_list = array();
              foreach($order_list as $id=>$score){
                array_push($order_seq_list, $id);
              }

              $datetime = date("Y-m-d H:i:s");
              $CI =& get_instance();
              $user_seq = $CI->session->userdata('user_seq');

              // 매도 주문 금액에 해당 하는 매수 주문 목록 조회(먼저 주문한 순서)
              $CI->db->where_in('order_seq', $order_seq_list);
              $CI->db->order_by('order_price', 'desc');
              $CI->db->order_by('order_seq', 'asc');
              $query = $CI->db->get('order_'.$coin_code);
              $result = $query->result_array();


              $insert_data = array();
              foreach ($result as $key => $value) {

                if($trade_qty >= ($value['unexe_qty'] * 100000000)){
                  // 매도 주문 수량이 남은 수량 보다 크거나 같으면 전체 체결 : 여러건 일 수 있음.

                  $insert_row = array();
                  $insert_row['order_seq'] = $value['order_seq'];
                  $insert_row['trade_code'] = 'sell';
                  $insert_row['fuser_seq'] = $value['user_seq'];
                  $insert_row['tuser_seq'] = $user_seq;
                  $insert_row['coin_code'] = $value['coin_code'];
                  $insert_row['base_code'] = $value['base_code'];
                  $insert_row['price'] = $value['order_price'];
                  $insert_row['qty'] = $value['unexe_qty'];
                  $insert_row['reg_date'] = $datetime;

                  array_push($insert_data, $insert_row);

                  // DB 매도 주문건 조회 unexe_qty 전부 0으로 업데이트
                  $data = array(
                    'order_status' => '03', // 전체 체결
                    'unexe_qty' => 0,
                    'update_date' => $datetime
                  );

                  $CI->db->where('order_seq', $value['order_seq']);
                  $CI->db->update('order_'.$coin_code, $data);

                  // 매도 수량 만큼 매수 주문건 삭제 --------------- 이건 db 먼저 조회해서 주문 수량을 확인 해야 할듯 함.
                  $this->redis->zrem('order_'.$coin_code.'_buy', $value['order_seq']); //delete

                  $trade_qty = $trade_qty - ($value['unexe_qty'] * 100000000);
                }else{
                  // 남은 주문 수량 부분 체결 : 부분 체결건은 1건(must)
                  $insert_row = array();
                  $insert_row['order_seq'] = $value['order_seq'];
                  $insert_row['trade_code'] = 'sell';
                  $insert_row['fuser_seq'] = $value['user_seq'];
                  $insert_row['tuser_seq'] = $user_seq;
                  $insert_row['coin_code'] = $value['coin_code'];
                  $insert_row['base_code'] = $value['base_code'];
                  $insert_row['price'] = $value['order_price'];
                  $insert_row['qty'] = $trade_qty / 100000000;
                  $insert_row['reg_date'] = $datetime;

                  array_push($insert_data, $insert_row);

                  // DB 매도 주문건 조회 unexe_qty 차감 후 남은 수량으로 업데이트
                  $data = array(
                    'order_status' => '03', // 전체 체결
                    'unexe_qty' => (($value['unexe_qty'] * 100000000) - $trade_qty) / 100000000,
                    'update_date' => $datetime
                  );

                  $CI->db->where('order_seq', $value['order_seq']);
                  $CI->db->update('order_'.$coin_code, $data);

                  $trade_qty = 0;
                }

              }

              // 체결 내역 등록
              $CI->db->insert_batch('trade_'.$coin_code, $insert_data);

              //-------------------------------------------------

              // // 매도 수량 만큼 매수 주문건 삭제 --------------- 이건 db 먼저 조회해서 주문 수량을 확인 해야 할듯 함.
              // $this->redis->zrem('order_'.$coin_code.'_buy', $id); //delete
              //
              // // DB 매도 주문건 조회 unexe_qty 전부 0으로 업데이트
              // $data = array(
              //   'order_status' => '03', // 전체 체결
              //   'unexe_qty' => 0,
              //   'update_date' => $datetime
              // );
              //
              // $CI->db->where_in('order_seq', $order_seq_list);
              // $CI->db->update('order_'.$coin_code, $data);
              // //====================================================================
              //
              // $trade_qty = 0;
            } //체결 프로세스 끝
          }
        }else{
        }
      }

      if($trade_qty > 0){
        //order
        $base_code = 'KRW';
        $this->order($coin_code, $base_code, 'sell', $price, $trade_qty);

        //$this->redis->zincrby($coin_code.'_sell', $trade_qty, $price);
      }

      return $trade_qty;
    }

    public function buy($coin_code='BTC', $price, $qty, $user_srl=1){
      $trade_qty = $qty * 100000000;

      $order_sell = $this->redis->zrange($coin_code.'_sell', 0, -1, 'withscores');
      ksort($order_sell);

      foreach($order_sell as $price_sell=>$qty_sell){

        if($price >= $price_sell){
          //trade
          if($trade_qty > 0){
            if($trade_qty >= $qty_sell){
              // 매도 전체 체결 건
              $trade_qty = $trade_qty - $qty_sell;
              $this->redis->zrem($coin_code.'_sell', $price_sell);
            }else{
              // 매도 부분 체결 건
              $this->redis->zincrby($coin_code.'_sell', ($trade_qty * -1), $price_sell);
              $trade_qty = 0;
            }
          }
        }else{
        }

      }

      if($trade_qty > 0){
        //order
        $base_code = 'KRW';
        $this->order($coin_code, $base_code, 'buy', $price, $trade_qty);

        //$this->redis->zincrby($coin_code.'_buy', $trade_qty, $price);
      }

      return $trade_qty;

    }



}
>>>>>>> c68f20c0e0533ea2501940c63bf466b9eb49c2f2
