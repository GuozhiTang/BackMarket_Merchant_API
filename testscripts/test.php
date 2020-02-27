<?php

include_once ('../backmarket_api/BackMarketAPI.php');
include_once ('../backmarket_api/Config.php');


$bm = new BackMarketAPI();
/**
 * Get orders directly through api_get() method.
 */
// echo $bm->apiGet('orders');

/**
 * Get listings directly through api_get() method
 */
// echo $bm->api_get('listings');

/**
 * Edit specific listing directly through api_post()
 */
/*
$request = array ('quantity' => 15, 'price' => 499.99);
$request_JSON = json_encode($request);
echo $bm->api_post('listings/67026', $request_JSON);
*/

/**
 * Get orders through get_all_orders() method
 */
// $date_creation = date("Y-m-d+H:i:s", time() - 90 * 60 * 60 * 24);
// $state = 9;
// echo $bm->get_all_orders();

/**
 * Get one specific order information through get_one_order() method
 */
/*
$order_id = 209810;
echo $bm->get_one_order($order_id);
*/

/**
 * Get new orders through get_new_orders() method
 */
// echo $bm->get_new_orders();
echo $bm->test();
?>