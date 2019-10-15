
	<!-- <h1>Welcome to STOCK!</h1> -->

	<div id="body">

		<div class="row">
		  <div class="col-sm-6">
		    <div class="card">
		      <div class="card-body">
						<div class="row">
							<div class="col-sm-6">
						    가격 : <input type=text id='price' value='9000000'>
						    <br>
						    수량 : <input type=text id='qty' value='0.001'>
							</div>

					    <div class="col-sm-6">
								<button id='reset_orders'>초기화</button>
								<button id='set_orders'>호가 셋팅</button>
								<br>
						    <button id='order_sell'>매도</button>
						    <button id='order_buy'>매수</button>
							</div>
						</div>

		      </div>
		    </div>
		  </div>
		  <div class="col-sm-6">
		    <div class="card">
					<button id='get_wallet'>자산 가져오기</button>
		      <div class="card-body" id="my_wallet">
		        	자산
		      </div>
		    </div>
		  </div>
		</div>

		<hr>

		<div class="row">
		  <div class="col-sm-6">
		    <div class="card">
		      <div class="card-body">
						<button id='get_order_book'>호가 가져오기</button>
						<div class='order_book' id='order_book'>
							호가 리스트
						</div>
		      </div>
		    </div>
		  </div>
		  <div class="col-sm-6">
		    <div class="card">
		      <div class="card-body">
						<button id='get_trade_list'>미체결 가져오기</button>
						<div class='trade_list' id='trade_list'>
							체결 / 미체결
						</div>
		      </div>
		    </div>
		  </div>
		</div>

	</div>

    <script>
    function draw_order_book(obj){
    	var html = "<table>";
    	html += "<tr><td style='width:120px;'> 매도잔량 </td>";
    	html += "<td style='width:120px;'> 호가 </td>";
    	html += "<td style='width:120px;'> 매수잔량 </td>";
    	html += "</tr>";
    	html += "<br>";

    	var sell = obj['sell'];
    	var buy = obj['buy'];

    	for(key in sell) {
    		html += "<tr style='color:blue;'><td style='width:120px;'>" + sell[key]['qty'] + "</td>";
    		html += "<td style='width:120px;'>" + sell[key]['price'] + "</td>";
    		html += "<tr><td style='width:120px;'> </td>";
    		html += "</tr>";
    	}

    	for(key in buy) {
    		html += "<tr style='color:red;'><td style='width:120px;'> </td>";
    		html += "<td style='width:120px;'>" + buy[key]['price'] + "</td>";
    		html += "<td style='width:120px;'>" + buy[key]['qty'] + "</td>";
    		html += "</tr>";
    	}

    	html += "</table>";
    	$('#order_book').html(html);
    }

    function get_order_book(){
    	$.ajax({
    		url: 'stock/order_book', // 요청 할 주소
    		//async: true, // false 일 경우 동기 요청으로 변경
    		type: 'POST', // GET, PUT
    		data: {
    			coin_code: 'BTC'
    		}, // 전송할 데이터
    		dataType: 'json', // xml, json, script, html
    		beforeSend: function(jqXHR) {}, // 서버 요청 전 호출 되는 함수 return false; 일 경우 요청 중단
    		success: function(jqXHR) {
    			//console.log('order_book', jqXHR);
    			draw_order_book(jqXHR);

    		}, // 요청 완료 시
    		error: function(jqXHR) {}, // 요청 실패.
    		complete: function(jqXHR) {} // 요청의 실패, 성공과 상관 없이 완료 될 경우 호출
    	});
    }



    $('#get_order_book').click(function(){
    	get_order_book();
    });

		// 미체결 / 체결 가져오기
		function get_trade_list(){
			$.ajax({
    		url: 'stock/trade_list',
    		type: 'POST',
    		data: {
    			coin_code: 'BTC'
    		},
    		dataType: 'json',
    		beforeSend: function(jqXHR) {}, // 서버 요청 전 호출 되는 함수 return false; 일 경우 요청 중단
    		success: function(obj) {
// console.log(obj);
					var html = "<table border=1>";
		    	html += "<tr><td style='width:120px;'> 주문종류 </td>";
		    	html += "<td style='width:120px;'> 주문번호 </td>";
		    	html += "<td style='width:120px;'> 주문가격 </td>";
					html += "<td style='width:120px;'> 원<br>주문수량 </td>";
					html += "<td style='width:120px;'> 미체결<br>주문수량 </td>";
					html += "<td style='width:120px;'> 주문<br>회원번호 </td>";
		    	html += "</tr>";
		    	html += "<br>";

		    	var sell = obj['sell'];
		    	var buy = obj['buy'];

		    	for(key in obj) {
						if(obj[key]['trade_code'] == 'sell'){
							html += "<tr style='color:blue;'><td style='width:120px;'>매도(SELL)</td>";
						}else{
							html += "<tr style='color:red;'><td style='width:120px;'>매수(BUY)</td>";
						}

						html += "<td style='width:120px;'>" + obj[key]['order_seq'] + "</td>";
		    		html += "<td style='width:120px;'>" + obj[key]['order_price'] + "</td>";
						html += "<td style='width:120px;'>" + obj[key]['order_qty'] + "</td>";
						html += "<td style='width:120px;'>" + obj[key]['unexe_qty'] + "</td>";
						html += "<td style='width:120px;'>" + obj[key]['user_seq'] + "</td>";
		    		html += "</tr>";

						// base_code: "KRW"
						// coin_code: "BTC"
						// order_status: "01"
		    	}

		    	// for(key in buy) {
		    	// 	html += "<tr style='color:blue;'><td style='width:120px;'>BUY</td>";
		    	// 	html += "<td style='width:120px;'>" + buy[key]['od_seq'] + "</td>";
		    	// 	html += "<td style='width:120px;'>" + buy[key]['price'] + "</td>";
		    	// 	html += "</tr>";
		    	// }

		    	html += "</table>";
		    	$('#trade_list').html(html);

    		},
    		error: function(jqXHR) {}, // 요청 실패.
    		complete: function(jqXHR) {} // 요청의 실패, 성공과 상관 없이 완료 될 경우 호출
    	});
		}

		$('#get_trade_list').click(function(){
			get_trade_list();
    });

    $('#order_sell').click(function(){
    	order('sell');
    });

    $('#order_buy').click(function(){
    	order('buy');
    });

    $('#reset_orders').click(function(){
    	$.ajax({
    		url: 'stock/reset_orders', // 요청 할 주소
    		async: true, // false 일 경우 동기 요청으로 변경
    		type: 'POST', // GET, PUT
    		data: {
    			type : 'reset_orders',
    			coin_code: 'BTC'
    		},
    		dataType: 'json', // xml, json, script, html
    		beforeSend: function(jqXHR) {}, // 서버 요청 전 호출 되는 함수 return false; 일 경우 요청 중단
    		success: function(jqXHR) {
    			get_order_book();
					get_trade_list();
    		}, // 요청 완료 시
    		error: function(jqXHR) {}, // 요청 실패.
    		complete: function(jqXHR) {} // 요청의 실패, 성공과 상관 없이 완료 될 경우 호출
    	});
    });

    $('#set_orders').click(function(){
    	$.ajax({
    		url: 'stock/set_orders', // 요청 할 주소
    		async: true, // false 일 경우 동기 요청으로 변경
    		type: 'POST', // GET, PUT
    		data: {
    			type : 'set_orders',
    			coin_code: 'BTC'
    		},
    		dataType: 'json', // xml, json, script, html
    		beforeSend: function(jqXHR) {}, // 서버 요청 전 호출 되는 함수 return false; 일 경우 요청 중단
    		success: function(jqXHR) {
    			get_order_book();
					get_trade_list();
    		}, // 요청 완료 시
    		error: function(jqXHR) {}, // 요청 실패.
    		complete: function(jqXHR) {} // 요청의 실패, 성공과 상관 없이 완료 될 경우 호출
    	});
    });


    function order(type){
    	var price = $('#price').val();
    	var qty = $('#qty').val();
    	if(!price || price < 0){
    		alert('가격을 입력하세요');
    		return;
    	}
    	if(!qty || qty < 0){
    		alert('수량을 입력하세요');
    		return;
    	}

    	$.ajax({
    		url: 'stock/order', // 요청 할 주소
    		async: true, // false 일 경우 동기 요청으로 변경
    		type: 'POST', // GET, PUT
    		data: {
					trade_type : type,
    			coin_code: 'BTC',
    			order_type: type,
    			price : price,
    			qty : qty
    		},
    		dataType: 'json', // xml, json, script, html
    		beforeSend: function(jqXHR) {}, // 서버 요청 전 호출 되는 함수 return false; 일 경우 요청 중단
    		success: function(jqXHR) {
					// console.log('order : ' + type);
    			get_order_book();
					get_trade_list();
    		}, // 요청 완료 시
    		error: function(jqXHR) {}, // 요청 실패.
    		complete: function(jqXHR) {} // 요청의 실패, 성공과 상관 없이 완료 될 경우 호출
    	});
    }


		$('#get_wallet').click(function(){
    	$.ajax({
    		url: 'stock/get_wallet', // 요청 할 주소
    		async: true, // false 일 경우 동기 요청으로 변경
    		type: 'POST', // GET, PUT
    		data: {
    			coin_code: 'BTC'
    		},
    		dataType: 'json', // xml, json, script, html
    		beforeSend: function(jqXHR) {}, // 서버 요청 전 호출 되는 함수 return false; 일 경우 요청 중단
    		success: function(obj) {
					var html = "";

		    	for(key in obj) {
		    		html += "<div> base : " + obj[key]['base'] + "</div>";
						html += "<div> trade_base : " + obj[key]['trade_base'] + "</div>";
		    	}

    			$('#my_wallet').html(html);
    		}, // 요청 완료 시
    		error: function(jqXHR) {}, // 요청 실패.
    		complete: function(jqXHR) {} // 요청의 실패, 성공과 상관 없이 완료 될 경우 호출
    	});
    });



    $(document).ready(function() {
    	get_order_book();
			get_trade_list();
    });

    </script>
