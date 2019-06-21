<?php
/**
 * @ description : Code Library
 * @ author : prog106 <prog106@haomun.com>
 */
class Redis_lib {
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
      $result = $this->redis->zrevrange('BTC_sell', 0, 10, 'withscores');
      foreach($result as $id=>$score){
        $this->redis->zrem('BTC_sell', $id); //delete
      }

      $result = $this->redis->zrevrange('BTC_buy', 0, 10, 'withscores');
      foreach($result as $id=>$score){
        $this->redis->zrem('BTC_buy', $id); //delete
      }

      $result = $this->redis->zrange('order_BTC_sell', 0, -1, 'withscores');
      foreach($result as $id=>$score){
        $this->redis->zrem('order_BTC_sell', $id); //delete
      }

      $result = $this->redis->zrange('order_BTC_buy', 0, -1, 'withscores');
      foreach($result as $id=>$score){
        $this->redis->zrem('order_BTC_buy', $id); //delete
      }

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
            $ord_price = ($i*100000) + 9000000;
            // $sql = "INSERT INTO order_BTC
            //           (`user_seq`, `coin_code`, `base_code`, `trade_code`, `od_status`, `ord_price`, `ord_qty`, `unexe_qty`)
            //         values
            //           (1, '$coin_code', '$base_code','sell','01', $ord_price, 0.001, 0.001)";
            // $CI->db->query($sql);
            //
            // $this->redis->zincrby($coin_code.'_sell', 0.001*100000000, $ord_price);

            $this->order($coin_code, $base_code, 'sell', $ord_price, 0.001*100000000);
          }

          // 매수 주문
          for($i=1; $i<=10; $i++){
            $ord_price = ($i*100000) + 7900000;
            // $sql = "INSERT INTO order_BTC
            //           (`user_seq`, `coin_code`, `base_code`, `trade_code`, `od_status`, `ord_price`, `ord_qty`, `unexe_qty`)
            //         VALUES
            //           (1, '$coin_code', '$base_code','buy','01', $ord_price, 0.001, 0.001)";
            // $CI->db->query($sql);
            // $this->redis->zincrby($coin_code.'_buy', 0.001*100000000, $ord_price);

            $this->order($coin_code, $base_code, 'buy', $ord_price, 0.001*100000000);
          }

          // order book
          // $this->redis->zincrby($coin_code.'_sell', 0.001*100000000, 9000000);
          // $this->redis->zincrby($coin_code.'_sell', 0.001*100000000, 9100000);
          // $this->redis->zincrby($coin_code.'_sell', 0.001*100000000, 9200000);
          // $this->redis->zincrby($coin_code.'_sell', 0.001*100000000, 9300000);
          // $this->redis->zincrby($coin_code.'_sell', 0.001*100000000, 9400000);
          // $this->redis->zincrby($coin_code.'_sell', 0.001*100000000, 9500000);
          //
          // $this->redis->zincrby($coin_code.'_buy', 0.001*100000000, 8900000);
          // $this->redis->zincrby($coin_code.'_buy', 0.001*100000000, 8800000);
          // $this->redis->zincrby($coin_code.'_buy', 0.001*100000000, 8700000);
          // $this->redis->zincrby($coin_code.'_buy', 0.001*100000000, 8600000);
          // $this->redis->zincrby($coin_code.'_buy', 0.001*100000000, 8500000);
          // $this->redis->zincrby($coin_code.'_buy', 0.001*100000000, 8400000);

          $CI->db->trans_commit();


      } catch(Exception $e) {
          if(!empty($e->getCode())){
            $CI->db->trans_rollback();
            $this->reset_orders();
          }
      }

    }

    public function order($coin_code, $base_code, $trade_code, $ord_price, $ord_qty){
      $CI =& get_instance();

      $user_seq = $CI->session->userdata('user_seq');
      $ord_qty_db = $ord_qty / 100000000;

      try {
          $CI->db->trans_begin();

          // $sql = "INSERT INTO order_$coin_code
          // (`user_seq`, `coin_code`, `base_code`, `trade_code`, `od_status`, `ord_price`, `ord_qty`, `unexe_qty`)
          // VALUES
          // ($user_seq, '$coin_code', '$base_code','$trade_code','01', $ord_price, $ord_qty_db, $ord_qty_db)";
          // $CI->db->query($sql);

          $data = array(
                  'user_seq' => $user_seq,
                  'coin_code' => $coin_code,
                  'base_code' => $base_code,
                  'trade_code' => $trade_code,
                  'od_status' => '01',
                  'ord_price' => $ord_price,
                  'ord_qty' => $ord_qty_db,
                  'unexe_qty' => $ord_qty_db,
          );

          $CI->db->insert('order_'.$coin_code, $data);
          $od_seq = $CI->db->insert_id();

          $this->redis->zAdd('order_'.$coin_code.'_'.$trade_code, $ord_price, $od_seq);

          //$this->redis->hset('order_'.$coin_code.'_'.$trade_code, $ord_qty, $ord_price);
          //여러개 HASH 데이터 한번에 입력
          //$this->redis->hMset('order_'.$coin_code.'_'.$trade_code, array('user_seq' => $user_seq, 'od_seq' =>$od_seq));

          //여러개 HASH 데이터를 한번에 가져옴
          //var_dump($redis->hMGet('user_list', array('uid4', 'uid5')));
          //var_dump($redis->hGetAll('user_list'));


          $this->redis->zincrby($coin_code.'_'.$trade_code, $ord_qty, $ord_price);

          $CI->db->trans_commit();
      } catch(Exception $e) {
          if(!empty($e->getCode())){
            $CI->db->trans_rollback();
            $this->reset_orders();
          }
      }

    }

    public function trade($coin_code, $base_code, $trade_code, $ord_price, $ord_qty){

      $CI =& get_instance();

      $user_seq = $CI->session->userdata('user_seq');
      $ord_qty_db = $ord_qty / 100000000;

      try {

          //$this->redis->zrangebyscore($key, $ord_price, $ord_price, array('withscores'=>true)));
          $od_list = $this->redis->zrangebyscore('order_'.$coin_code.'_'.$trade_code, $ord_price, $ord_price, array('withscores'=>true));

          $od_seq_list = array();
          foreach($od_list as $id=>$score){
            array_push($od_seq_list, $id);
          }

          //$sql_od_seq = implode( ',', $od_seq_list);

          $CI->db->where_in('od_seq', $od_seq_list);
          $CI->db->order_by('od_seq', 'asc');
          $query = $CI->db->get('order_'.$coin_code);
          $result = $query->result_array();

          foreach ($result as $key => $value) {
            //$value['od_price']
            //$value['od_qty']

            //if($value['unexec_qty'] <= $ord_qty_db){
              $temp_ord_qty = $ord_qty_db;
              $ord_qty_db = $ord_qty_db - $value['unexec_qty'];

              if($ord_qty_db >= 0){
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
          //         'od_status' => '01',
          //         'ord_price' => $ord_price,
          //         'ord_qty' => $ord_qty_db,
          //         'unexe_qty' => $ord_qty_db,
          // );
          //
          // $CI->db->insert('order_'.$coin_code, $data);
          // $td_seq = $CI->db->insert_id();

          //$redis->zAdd('order_'.$coin_code.'_'.$trade_code, $ord_price, $od_seq);
          //$this->redis->zincrby($coin_code.'_'.$trade_code, $ord_qty, $ord_price);


          $CI->db->trans_commit();
      } catch(Exception $e) {
          if(!empty($e->getCode())){
            $CI->db->trans_rollback();
            $this->reset_orders();
          }
      }
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

              $od_list = $this->redis->zrangebyscore('order_'.$coin_code.'_buy', $price_buy, $price_buy, array('withscores'=>true));

              $od_seq_list = array();
              foreach($od_list as $id=>$score){
                // 매도 주문건 삭제
                $this->redis->zrem('order_'.$coin_code.'_buy', $id); //delete
                array_push($od_seq_list, $id);
              }

              $datetime = date("Y-m-d H:i:s");
              $CI =& get_instance();
              $user_seq = $CI->session->userdata('user_seq');

              // 체결 내역 등록
              $CI->db->where_in('od_seq', $od_seq_list);
              $CI->db->order_by('od_seq', 'asc');
              $query = $CI->db->get('order_'.$coin_code);
              $result = $query->result_array();

              $insert_data = array();
              foreach ($result as $key => $value) {
                $insert_row = array();
                $insert_row['od_seq'] = $value['od_seq'];
                $insert_row['trade_code'] = 'sell';
                $insert_row['fuser_seq'] = $value['user_seq'];
                $insert_row['tuser_seq'] = $user_seq;
                $insert_row['coin_code'] = $value['coin_code'];
                $insert_row['base_code'] = $value['base_code'];
                $insert_row['price'] = $value['ord_price'];
                $insert_row['qty'] = $value['unexe_qty'];
                $insert_row['reg_date'] = $datetime;

                array_push($insert_data, $insert_row);
              }

              $CI->db->insert_batch('trade_'.$coin_code, $insert_data);

              //-------------------------------------------------

              // DB 매도 주문건 조회 unexe_qty 전부 0으로 업데이트
              $data = array(
                'od_status' => '03', // 전체 체결
                'unexe_qty' => 0,
                'update_date' => $datetime
              );

              $CI->db->where_in('od_seq', $od_seq_list);
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


              $od_list = $this->redis->zrangebyscore('order_'.$coin_code.'_buy', $price_buy, $price_buy, array('withscores'=>true));

              $od_seq_list = array();
              foreach($od_list as $id=>$score){
                array_push($od_seq_list, $id);
              }

              $datetime = date("Y-m-d H:i:s");
              $CI =& get_instance();
              $user_seq = $CI->session->userdata('user_seq');

              // 매도 주문 금액에 해당 하는 매수 주문 목록 조회(먼저 주문한 순서)
              $CI->db->where_in('od_seq', $od_seq_list);
              $CI->db->order_by('ord_price', 'desc');
              $CI->db->order_by('od_seq', 'asc');
              $query = $CI->db->get('order_'.$coin_code);
              $result = $query->result_array();


              $insert_data = array();
              foreach ($result as $key => $value) {

                if($trade_qty >= ($value['unexe_qty'] * 100000000)){
                  // 매도 주문 수량이 남은 수량 보다 크거나 같으면 전체 체결 : 여러건 일 수 있음.

                  $insert_row = array();
                  $insert_row['od_seq'] = $value['od_seq'];
                  $insert_row['trade_code'] = 'sell';
                  $insert_row['fuser_seq'] = $value['user_seq'];
                  $insert_row['tuser_seq'] = $user_seq;
                  $insert_row['coin_code'] = $value['coin_code'];
                  $insert_row['base_code'] = $value['base_code'];
                  $insert_row['price'] = $value['ord_price'];
                  $insert_row['qty'] = $value['unexe_qty'];
                  $insert_row['reg_date'] = $datetime;

                  array_push($insert_data, $insert_row);

                  // DB 매도 주문건 조회 unexe_qty 전부 0으로 업데이트
                  $data = array(
                    'od_status' => '03', // 전체 체결
                    'unexe_qty' => 0,
                    'update_date' => $datetime
                  );

                  $CI->db->where('od_seq', $value['od_seq']);
                  $CI->db->update('order_'.$coin_code, $data);

                  // 매도 수량 만큼 매수 주문건 삭제 --------------- 이건 db 먼저 조회해서 주문 수량을 확인 해야 할듯 함.
                  $this->redis->zrem('order_'.$coin_code.'_buy', $value['od_seq']); //delete

                  $trade_qty = $trade_qty - ($value['unexe_qty'] * 100000000);
                }else{
                  // 남은 주문 수량 부분 체결 : 부분 체결건은 1건(must)
                  $insert_row = array();
                  $insert_row['od_seq'] = $value['od_seq'];
                  $insert_row['trade_code'] = 'sell';
                  $insert_row['fuser_seq'] = $value['user_seq'];
                  $insert_row['tuser_seq'] = $user_seq;
                  $insert_row['coin_code'] = $value['coin_code'];
                  $insert_row['base_code'] = $value['base_code'];
                  $insert_row['price'] = $value['ord_price'];
                  $insert_row['qty'] = $trade_qty / 100000000;
                  $insert_row['reg_date'] = $datetime;

                  array_push($insert_data, $insert_row);

                  // DB 매도 주문건 조회 unexe_qty 차감 후 남은 수량으로 업데이트
                  $data = array(
                    'od_status' => '03', // 전체 체결
                    'unexe_qty' => (($value['unexe_qty'] * 100000000) - $trade_qty) / 100000000,
                    'update_date' => $datetime
                  );

                  $CI->db->where('od_seq', $value['od_seq']);
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
              //   'od_status' => '03', // 전체 체결
              //   'unexe_qty' => 0,
              //   'update_date' => $datetime
              // );
              //
              // $CI->db->where_in('od_seq', $od_seq_list);
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

    public function trade_list($coin_code){
      $result = array();
      //$result['sell'] = array();
      //$result['buy'] = array();

      $od_seq_list = array();

      $order_sell = $this->redis->zrange('order_'.$coin_code.'_sell', 0, -1, 'withscores');
      foreach($order_sell as $id=>$score){
         //array_push($result['sell'], array('od_seq'=>$id, 'price'=>$score));
         array_push($od_seq_list, $id);
      }

      $order_buy = $this->redis->zrange('order_'.$coin_code.'_buy', 0, -1, 'withscores');
      foreach($order_buy as $id=>$score){
        //array_push($result['buy'], array('od_seq'=>$id, 'price'=>$score));
        array_push($od_seq_list, $id);
      }

      if(count($od_seq_list) > 0){
        $CI =& get_instance();
        $CI->db->where_in('od_seq', $od_seq_list);
        $CI->db->order_by('od_seq', 'asc');
        $query = $CI->db->get('order_'.$coin_code);
        $result = $query->result_array();


        foreach ($result as $key => $value) {
          //array_push($result['sell'], array('od_seq'=>$value['od_seq'], 'price'=>$value['od_price'], 'qty'=>$value['unexec_qty'], 'user_seq'=>$value['user_seq']));
          //array_push($result['sell'], $key);
        }
      }

      return $result;
    }

}
