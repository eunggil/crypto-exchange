<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


if(!function_exists('load_view')) {
    function load_view($view_file, $data) {
      $CI =& get_instance();
      $CI->load->view('common/head.php');
      $CI->load->view($view_file, $data);
      $CI->load->view('common/footer.php');
    }
}


if(!function_exists('array_msort')) {
  function array_msort($array, $cols){ // 배열 재정열 함수
    $colarr = array();
    foreach ($cols as $col => $order) {
        $colarr[$col] = array();
        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
    }
    $eval = substr($eval,0,-1).');';
    eval($eval);
    $ret = array();
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k,1);
            if (!isset($ret[$k])) $ret[$k] = $array[$k];
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return array_values($ret);
  }
}
?>
