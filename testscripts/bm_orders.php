<?php

include_once ('../backmarket_api/BackMarketAPI.php');


getBMOrderAll();
// getBMOrderNew();

function getBMOrderAll() {

  $bm = new BackMarketAPI();
  $res_array = $bm->getAllOrders();
  print_r($res_array);

  foreach ($res_array as $key => $value) {
    // get the object of each order
    $order_obj = $res_array[$key];
  }

  // @TODO update the data in $result to the database
}

function getBMOrderNew() {
  $bm = new BackMarketAPI();

  $res_array = $bm->getNewOrders();
  print_r($res_array);

  // @TODO insert result0 and result1 to database
}
?>