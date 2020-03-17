<?php

include_once ('../backmarket_api/BackMarketAPI.php');
include_once ('../backmarket_api/Config.php');


$bm = new BackMarketAPI();
/**
 * Get orders directly through apiGet() method.
 */
// echo $bm->apiGet('orders');

/**
 * Get listings directly through apiGet()cd method
 */
// echo $bm->apiGet('listings');

/**
 * Edit specific listing directly through apiPost()
 */
/*
$request = array ('quantity' => 15, 'price' => 499.99);
$request_JSON = json_encode($request);
echo $bm->apiPost('listings/67026', $request_JSON);
*/

/**
 * Get orders through getAllOrders() method
 */
// $date_creation = date("Y-m-d+H:i:s", time() - 90 * 60 * 60 * 24);
// $state = 9;
// echo $bm->getAllOrders();

/**
 * Get one specific order information through getOneOrder() method
 */
/*
$order_id = 209810;
echo $bm->getOneOrder($order_id);
*/

/**
 * Get new orders through getNewOrders() method
 */
// echo $bm->getNewOrders();

/**
 * Test for validate and shipping functions with validateOrderlines() and shippingOrderlines()
 */
// $bm->validateOrderlines('324272', '889842392111_bkmt');
// $bm->shippingOrderlines(true, '324272', '1Z85329E4231218965', 'UPS');

/**
 * Get all listings through getAllListings() method
 */
// $bm->getAllListings();

/**
 * Get one listing with specific listing id through getOneListing() method
 */
$bm->getOneListing(241147);
?>