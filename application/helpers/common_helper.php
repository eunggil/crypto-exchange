<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


if(!function_exists('load_view')) {
    function load_view($view_file, $data) {
      $CI =& get_instance();
      $CI->load->view('common/head.php');
      $CI->load->view($view_file, $data);
      $CI->load->view('common/footer.php');
    }
}

?>
