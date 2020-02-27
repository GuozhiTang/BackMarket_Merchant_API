<?php

include_once ('../backmarket_api/BackMarketAPI.php');


// get_backmarket_order_all();
get_backmarket_order_new();

function get_backmarket_order_all() {

  $bm = new BackMarketAPI();
  $res_array = $bm->get_all_orders();
  // print_r($res_array);

  foreach ($res_array as $key => $value) {
    // get the object of each order
    $order_obj = $res_array[$key];
  }

  // @TODO update the data in $result to the database
}

function get_backmarket_order_new() {
  $bm = new BackMarketAPI();

  $res_array = $bm->get_new_orders();
  print_r($res_array);

  // @TODO insert result0 and result1 to database
}
?>