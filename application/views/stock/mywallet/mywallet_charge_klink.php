<style>
    .amount_klink::after {
        display: block;
        clear: both;
        content: '';
    }

    .checkbox_klink input[type=checkbox] {
        display: none;
    }

    .checkbox_klink input[type=checkbox] + label {
        position: relative;
        /*left: 295px;*/
        /*top: 70px;*/
        cursor: pointer;
    }

    .checkbox_klink input[type=checkbox] + label::before {
        content: '';
        position: absolute;
        display: inline-block;
        top: 50%;
        left: -25px;
        transform: translateY(-50%);
        width: 17px;
        height: 17px;
        background: #ffffff;
        cursor: pointer;
        border: 1px solid #666666;
    }

    .checkbox_klink input[type=checkbox]:checked + label::before {
        background: url(/resources/new/img/check_active.png) no-repeat center/17px 17px;
        border: 1px solid #6834be;
    }
</style>
<? $this->load->view('stock/top'); ?>
<div class="wrap" style="background: #f5f5f5;">
    <? $this->load->view('stock/mywallet/menu') ?>
    <div class="mywallet_wrap" style="min-height: 620px;">
        <form name="frm_double_link_c">
            <input type="hidden" name="send_data" value="<?=$send_data?>">
        </form>

        <div class="mywallet_title" style="font-weight: 400; font-size: 26px; color: #333333;">KLIN 충전</div>

        <div class="mywallet_hr"></div>

        <div class="mywallet_main">
            <div class="amount_klink" style="background: #f9f9f9; padding: 20px;">
                <p style="color: #999999; font-size: 14px; float: left;">현재 보유 KLIN</p>
                <p style="font-size: 16px; color: #000; float: right;"><?=$comma_total?> <span style="color: #999999; font-size: 12px;">KLIN</span></p>
            </div>

<!--            <div style="margin-top: 30px;">-->
<!--                <p style="color: #333333; font-size: 18px; font-weight: 500; margin-bottom: 15px;">입금계좌 안내</p>-->
<!--                <table style="width: 100%; border-top: 3px solid #e6e6e6; border-bottom: 1px solid #e6e6e6;">-->
<!--                    <tr>-->
<!--                        <th style="background: #f9f9f9; width: 17%; padding: 20px 0; color: #666666; font-size: 14px; padding-left: 30px; border-right: 1px solid #e6e6e6;">은행명</th>-->
<!--                        <td style="width: 33%; padding: 20px 0; color: #333333; font-size: 14px; padding-left: 30px;">기업은행</td>-->
<!--                        <th style="background: #f9f9f9; width: 17%; padding: 20px 0; font-size: 14px; color: #666666; padding-left: 30px; border-right: 1px solid #e6e6e6;">예금주</th>-->
<!--                        <td style="width: 33%; padding: 20px 0; color: #333333; font-size: 14px; padding-left: 30px;">(주)더블링크</td>-->
<!--                    </tr>-->
<!--                </table>-->
<!--                <table style="width: 100%;">-->
<!--                    <tr style="border-bottom: 1px solid #e6e6e6;">-->
<!--                        <th style="background: #f9f9f9; width: 17%; padding: 20px 0; font-size: 14px; color: #666666; padding-left: 30px; border-right: 1px solid #e6e6e6;">계좌번호</th>-->
<!--                        <td style="width: 83%; padding: 20px 0; font-size: 14px; color: #333333; padding-left: 30px;">123-456789-01-234</td>-->
<!--                    </tr>-->
<!--                </table>-->
<!--            </div>-->

<!--            <div style="margin-top: 30px;">-->
<!--                <p style="color: #333333; font-size: 18px; font-weight: 500; margin-bottom: 15px;">입금 정보</p>-->
<!--                <table style="width: 100%; border-bottom: 1px solid #e6e6e6; border-top: 3px solid #e6e6e6;">-->
<!--                    <tr>-->
<!--                        <th style="background: #f9f9f9; width: 17%; padding: 20px 0; color: #666666; padding-left: 30px; border-right: 1px solid #e6e6e6; font-size: 14px;">입금 고객명</th>-->
<!--                        <td style="width: 83%; padding: 20px 0; color: #333333; padding-left: 30px; font-size: 14px;">-->
<!--                            --><?//if( !empty($charge_list['data'][0]) ){?>
<!--                                <p id="charger_name" style="color: #333333;">--><?//=$user_name?><!----><?//=$charge_list['data'][0]['charge_unique_num']?><!--</p>-->
<!--                            --><?//} else {?>
<!--                                <p id="charger_name" style="color: #999999;">충전신청 완료 시 자동 기입됩니다.</p>-->
<!--                            --><?// } ?>
<!--                        </td>-->
<!--                    </tr>-->
<!--                </table>-->
<!--            </div>-->

            <div style="border: 1px solid #e6e6e6; padding: 30px; margin-top: 30px;">
                <p style="color: #333333; font-size: 16px; font-weight: 500; margin-bottom: 15px;">충전 전 유의사항 안내</p>
                <p style="font-size: 14px; color: #333333; font-weight: 400; margin-bottom: 10px;">1. 반드시 인증하신 입금계좌번호로 신청한 충전 금액과 동일한 금액을 10분 이내에 송금하여야 합니다.</p>
                <p style="font-size: 14px; color: #333333; font-weight: 400; margin-bottom: 10px;">2. 보이스 피싱 등의 금융사고 예방을 위해 최초 충전 시, 72시간 동안 원화 및 암호화폐 출금이 제한됩니다.</p>
                <p style="font-size: 14px; color: #333333; font-weight: 400; margin-bottom: 10px;">3. 입금된 금액을 확인 후 충전 처리가 진행됩니다.</p>
                <p style="font-size: 14px; color: #333333; font-weight: 400; margin-bottom: 10px;">4. KLIN 충전은 제휴사인 (주)더블링크를 통해 진행됩니다.</p>
                <p style="font-size: 14px; color: #333333; font-weight: 400;">5. (주)더블링크의 KLIN 서비스 이용을 위해 보라빛 회원의 개인정보(이름, 생년월일, 휴대전화번호 등)이 (주)더블링크에 제공됩니다.</p>
            </div>

            <div class="checkbox_klink" style="text-align: center; margin-top: 40px;">
                <input type="checkbox" id="check_charge_klink">
                <label for="check_charge_klink"></label>
                <span style="color: #666666; font-size: 14px;">(주)더블링크의 <a onclick="openTerms('http://www.doublelink.co.kr/api/popterm/term_basic', 'doublelink_term_01')" style="font-weight: 400; text-decoration: underline;">KLIN 서비스 이용</a>과 <a onclick="openTerms('http://www.doublelink.co.kr/api/popterm/term_personal_info', 'doublelink_term_02')" style="font-weight: 400; text-decoration: underline;">개인정보 제공</a>, <a onclick="openTerms('http://www.doublelink.co.kr/api/popterm/term_klink_blockchain', 'doublelink_term_03')" style="font-weight: 400; text-decoration: underline;">KLIN 상품권 이용</a>에 동의합니다.</span>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <button id="charge_klink_submit" type="button" style="border: none; background: #541bb6; font-size: 16px; color: #ffffff; padding: 15px 0; width: 200px; margin: 0 auto;">KLIN 충전신청</button>
            </div>

            <div style="margin-top: 50px;">
                <p style="color: #333333; font-size: 18px; font-weight: 500; margin-bottom: 15px;">충전내역</p>
                <table style="width: 100%;">
                    <thead>
                        <tr style="background: #f9f9f9; color: #666666; font-size: 14px; border-top: 1px solid #e6e6e6; border-bottom: 1px solid #e6e6e6;">
                            <th style="width: 33%; text-align: center; padding: 20px 0;">충전시각</th>
                            <th style="width: 34%; text-align: center; padding: 20px 0;">신청금액</th>
                            <th style="width: 33%; text-align: center; padding: 20px 0;">상태</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?if( !empty($charge_list['data']) ){?>
                        <?foreach($charge_list['data'] as $value){?>
                            <?php
                            switch($value['apply_state']){
                                case 'REQUEST':
                                    $apply_status = '접수';
                                    break;
                                case 'WAITING':
                                    $apply_status = '상태확인중';
                                    break;
                                case 'PENDING':
                                    $apply_status = '입금지연중';
                                    break;
                                case 'COMPLETE':
                                    $apply_status = '입금완료';
                                    break;
                                case 'CANCEL':
                                    $apply_status = '입금취소';
                                    break;
                            }
                            $value['apply_amount'] = number_format($value['apply_amount'], 0);
                            ?>
                            <tr>
                                <td style="width: 33%; text-align: center; padding: 20px 0; font-size: 12px; color: #666666;">
                                    <p><?=$value['apply_time']?></p>
                                </td>
                                <td style="width: 34%; text-align: center; padding: 20px 0; font-size: 12px; color: #666666;">
                                    <p><?=$value['apply_amount']?> <span style="color: #999999; font-size: 11px;">원</span></p>
                                </td>
                                <td style="width: 33%; text-align: center; padding: 20px 0; font-size: 12px; color: #666666;">
                                    <p><?=$apply_status?></p>
                                </td>
                            </tr>
                        <? } ?>
                    <?} else {?>
                        <tr>
                            <td style="text-align: center; padding: 50px 0; font-size: 14px; color: #666666;" colspan="3">
                                <p>충전 신청 내역이 없습니다.</p>
                            </td>
                        </tr>
                    <? } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
<script type="text/javascript" src="/resources/js/jquery.qrcode.js"></script>
<script type="text/javascript" src="/resources/js/qrcode.js"></script>

<script>
    $('body').css('background', '#f5f5f5');

    function openTerms(url, name){
        var width  = 470;
        var height = 800;

        var leftpos = screen.width  / 2 - ( width  / 2 );
        var toppos  = screen.height / 2 - ( height / 2 );

        var winopts  = "width=" + width   + ", height=" + height + ", toolbar=no,status=no,statusbar=no,menubar=no,scrollbars=no,resizable=no";
        var position = ",left=" + leftpos + ", top="    + toppos;
        window.open(url, name, winopts + position);
    }

    $(document).ready(function() {
        var chargeList = <?=json_encode($charge_list['data'])?>;
        var chargeBreak = false;

        // 버튼 비활성화 조건 ( 신청 내역 중 상태값이 리퀘스트, 웨이팅, 펜딩이 있을 경우 비활성화 )
        $.each(chargeList, function(index, item){
            if( item.apply_state === 'REQUEST' || item.apply_state === 'WAITING' || item.apply_state === 'PENDING' ){
                chargeBreak = true;
            }
        });

        if( chargeBreak === true ){
            $('#charge_klink_submit').css('opacity', '0.5');
        } else {
            $('#charge_klink_submit').css('opacity', '1');
        }

        // 충전신청 버튼
        function chargeSubmit(){
            var domain = '';

            switch('<?=$_SERVER['HTTP_HOST']?>'){
                case 'www.borabit.me':
                case 'www.borabit.xyz':
                    domain = 'http://dev.doublelink.co.kr/api/depositaccount';
                    break;
                case 'www.borabit.com':
                    domain = 'http://www.doublelink.co.kr/api/depositaccount';
                    break;
            }

            var frm_double_link = document.frm_double_link_c;
            var url = domain;

            // doublelink 팝업 열기
            var width  = 470;
            var height = 800;

            var leftpos = screen.width  / 2 - ( width  / 2 );
            var toppos  = screen.height / 2 - ( height / 2 );

            var winopts  = "width=" + width   + ", height=" + height + ", toolbar=no,status=no,statusbar=no,menubar=no,scrollbars=no,resizable=no";
            var position = ",left=" + leftpos + ", top="    + toppos;
            window.open("", "doublelink", winopts + position);

            frm_double_link.method = "post";
            frm_double_link.action = url;
            frm_double_link.target = "doublelink";
            frm_double_link.submit();
        }

        // 액션
        $('#charge_klink_submit').click(function(){
            jsnA('noti', '서비스 점검중입니다.');
            return false;

            if( chargeBreak === true ){
                jsnA('noti', '이미 처리중인 내역이 있습니다.<br>처리 완료 후 다시 신청해주시기 바랍니다.<br><br><b style="display: inline-block; margin-bottom:10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[입금정보 확인]</b><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;예금주: (주)더블링크<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;은행명: 국민은행<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;계좌번호: 074301-04-108724');
                return false;
            } else {
                if( $('#check_charge_klink').is(':checked') === true ){
                    chargeSubmit();
                } else {
                    jsnA('noti', '이용약관에 동의 후 이용가능합니다.');
                    return false;
                }
            }
        })

    });
</script>
