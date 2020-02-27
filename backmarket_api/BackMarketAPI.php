<?php

include_once ('Config.php');

/**
 * CLASS BackMarketAPI
 * Contain all the variables and methods to communicate with Back Market
 * @link https://doc.backmarket.fr/
 * @author Guozhi Tang
 * @since 2020-02-25
 */
class BackMarketAPI {

  // US base - change .fr to .com
  // Test environment - change www to preprod
  protected static $base_url = 'https://www.backmarket.com/ws/';
  protected static $COUNTRY_CODE = 'en-us';
  protected static $YOUR_ACCESS_TOKEN;
  protected static $YOUR_USER_AGENT;

  /**
   * CONSTRUCTOR __construct
   * Initialize the variables in another class containing token information
   * @author Guozhi Tang
   * @since 2020-02-27
   */
  function __construct() {
    self::$YOUR_ACCESS_TOKEN = Config::getToken();
    self::$YOUR_USER_AGENT = Config::getUserAgent();
  }

  /**
   * METHOD api_get
   * Send GET request to Back Market with specific endpoint url
   * @param string $end_point - part of the endpoint url for this GET request
   * @return void
   * @link GET https://www.backmarket.com/ws/$end_point
   * @author Guozhi Tang
   * @since 2020-02-25
   */
  public function apiGet($end_point) {
    // Limit the format of $end_point
    if(substr($end_point, 0, 1) === '/') {
      $end_point = substr($end_point, 1);
    }

    // The parameters in HTTP Header
    $api_call_data['Content-Type'] = 'application/json';
    $api_call_data['Accept'] = 'application/json';
    $api_call_data['Accept-Language'] = self::$COUNTRY_CODE;
    $api_call_data['Authorization'] = 'Basic '.self::$YOUR_ACCESS_TOKEN;
    $api_call_data['User-Agent'] = self::$YOUR_USER_AGENT;

    $headers = array();
    foreach($api_call_data as $key => $value) {
      array_push($headers, "$key:$value");
    }

    $target_url = self::$base_url.$end_point;

    // Send the GET request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TINEOUT, '60');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $get_result = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return json_decode($get_result);
  }

  /**
   * METHOD api_post
   * Send POST request to Back Market with specific url and request body
   * @param string $end_point - part of the endpoint url for this POST requet
   * @param string $request - the request body of this POST request
   * @param string $content_type - the content type in the Header of POST request
   * @return void
   * @link POST https://www.backmarket.com/ws/$end_point
   * @author Guozhi Tang
   * @since 2020-02-25
   */
  public function apiPost($end_point, $request = '', $content_type='application/json') {
    // Limit the format of $end_point
    if(substr($end_point, 0, 1) === '/') {
      $end_point = substr($end_point, 1);
    }

    // The parameters in HTTP Header
    $api_call_data['Content-Type'] = $content_type;
    $api_call_data['Accept'] = $content_type;
    $api_call_data['Accept-Language'] = self::$COUNTRY_CODE;
    $api_call_data['Authorization'] = 'Basic '.self::$YOUR_ACCESS_TOKEN;
    $api_call_data['User-Agent'] = self::$YOUR_USER_AGENT;

    $headers = array();
    foreach($api_call_data as $key => $value) {
      array_push($headers, "$key:$value");
    }

    $target_url = self::$base_url.$end_point;

    // Send the POST request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TINEOUT, '60');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
    curl_setopt($ch, CURLOPT_POST, true);

    if ($request) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    }

    $post_result = curl_exec($ch);

    $error = (curl_error($ch));
    echo $error;
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo $post_result;
    return $post_result;
  }

  /**
   * METHOD getAllOrders
   * Get data of all orders based on exact date and limitations, defalutly grab the data in 60 days.
   * @param string $date_creation - get the orders from this timestamp till now
   * @param array $param - contain all of other filter parameters
   * @return void
   * @link GET https://www.backmarket.com/ws/$end_point
   * @author Guozhi Tang
   * @since 2020-02-25
   */
  function getAllOrders($date_creation = false, $param = array()) {
    $end_point = 'orders';

    // if there is no date_creation, default could be one day.
    if (!$date_creation) {
      $date_creation = date("Y-m-d+H:i:s", time() - 60 * 24 * 60 * 60);
    }

    $end_point .= "?date_creation=$date_creation";

    if(count($param) > 0) {
      $end_point .= '&'.http_build_query($param);
    }

    // result of the first page
    $result = $this->apiGet($end_point);
    // print_r($result);

    // array results of the first page
    $result_array = $result->results;

    $result_next = $result;

    $page = 1;
    // judge whether there exists the next page
    while (($result_next->next) != null) {
      $page++;
      // get the new end point
      $end_point_next_tail = '&page='."$page";
      $end_point_next = $end_point.$end_point_next_tail;
      // print_r($end_point_next);
      // the new page object
      $result_next = $this->apiGet($end_point_next);
      // the new page array
      $result_next_array = $result_next->results;
      // add all orders in current page to the $result_array
      foreach ($result_next_array as $key => $value) {
        array_push($result_array, $result_next_array[$key]);
      }
    }
    // print_r($result_array);

    return $result_array;
  }

  /**
   * METHOD getOneOrder
   * Get one order's data according to a specific $order_id
   * @param string $order_id - specific order id
   * @return void
   * @link GET https://www.backmarket.com/ws/$end_point
   * @author Guozhi Tang
   * @since 2020-02-25
   */
  function getOneOrder($order_id) {
    $end_point = 'orders/'.$order_id;
    $result = $this->apiGet($end_point);
    return $result;
  }

  /**
   * METHOD getNewOrders
   * Get data of new orders whose states are 0 or 1.
   * @param array $param - some filter parameters
   * @return void
   * @link GET https://www.backmarket.com/ws/$end_point
   * @author Guozhi Tang
   * @since 2020-02-26
   */
  function getNewOrders($param = array()) {
    $end_point_0 = 'orders?state=9';
    $end_point_1 = 'orders?state=1';

    if (count($param) > 0) {
      $end_point_0 .= '&'.http_build_query($param);
      $end_point_1 .= '&'.http_build_query($param);
    }

    // result of the first page
    $result0 = $this->apiGet($end_point_0);
    $result1 = $this->apiGet($end_point_1);
    // print_r($result0);
    // print_r($result1);

    // array results of the first page
    $res0_array = $result0->results;
    $res1_array = $result1->results;
    // print_r($res0_array);

    $result0_next = $result0;
    $result1_next = $result1;

    $page0 = 1;
    // judge whether there exists the next page
    while (($result0_next->next) != null) {
      $page0++;
      // get the new end point
      $end_point_next0_tail = '&page='."$page0";
      $end_point_next0 = $end_point_0.$end_point_next0_tail;
      // print_r($end_point_next0);
      // the new page object
      $result0_next = $this->apiGet($end_point_next0);
      // the new page array
      $result_next0_array = $result0_next->results;
      // add all orders in current page to the $res0_array
      foreach ($result_next0_array as $key => $value) {
        array_push($res0_array, $result_next0_array[$key]);
      }
    }
    // print_r($res0_array);

    $page1 = 1;
    // judge whether there exists the next page
    while (($result1_next->next) != null) {
      $page1++;
      // get the new end point
      $end_point_next1_tail = '&page='."$page1";
      $end_point_next1 = $end_point_1.$end_point_next1_tail;
      // the new page object
      $result1_next = $this->apiGet($end_point_next1);
      // the new page array
      $result_next1_array = $result1_next->results;
      // add all orders in current page to the $res1_array
      foreach ($result_next1_array as $key => $value) {
        array_push($res1_array, $result_next1_array[$key]);
      }
    }
    // print_r($res1_array);

    // combine orders together for state are 0 and 1
    foreach ($res1_array as $key => $value) {
      array_push($res0_array, $res1_array[$key]);
    }

    // The new array containing all orders in status 0 and 1
    // print_r($res0_array);
    return $res0_array;
  }

  /**
   * METHOD validateOrderlines
   * Update the state of orderlines when state is 1: 1 -> 2 or 1 -> 4
   * @param string $order_id - specific order id
   * @param string $sku - specific sku of the listing
   * @param boolean $validated - whether it should be validated or cancelled
   * @return void
   * @link POST https://www.backmarket.com/ws/$end_point
   * @author Guozhi Tang
   * @since 2020-02-26
   */
  function validateOrderlines($order_id, $sku, $validated = true) {

    $end_point = 'orders/'.$order_id;
    // judge whether this order is validated or cancelled
    if ($validated) $new_state = 2;
    else $new_state = 4;

    // construct the request body
    $request = array('order_id' => $order_id, 'new_state' => $new_state, 'sku' => $sku);
    $request_JSON = json_encode($request);

    $result = $this->apiPost($end_point, $request_JSON);

    return $result;
  }

  /**
   * METHOD shippingOrderlines
   * Updaet the state of orderlines when state is 2: 2 -> 3 or 2 -> 5
   * @param boolean $shipping
   * @param string $order_id - speicific order id
   * @param string $tracking_num - specific tracking number
   * @param string $tracking_url - the corresponding url for the tracking
   * @param string $date_shipping - the timestamp for the shipping date and time
   * @param string $shipper - the company or person who handles this order
   * @param string $sku - specific sku of the listing
   * @return void
   * @author Guozhi Tang
   * @since 2020-02-27
   */
  function shippingOrderlines($shipping = true, $order_id, $tracking_num, $tracking_url, $date_shipping, $shipper, $sku = null) {
    // judge whether this order is in shipping process or cancelled
    if ($shipping) {
      $new_state = 3;
      // construct the request body when state == 3
      $request_shipping = array('order_id' => $order_id, 'new_state' => $new_state, 'tracking_number' => $tracking_num);
      if ($tracking_url != null) $request_shipping['tracking_url'] = $tracking_url;
      if ($date_shipping != null) $request_shipping['date_shipping'] = $date_shipping;
      if ($shipper != null) $request_shipping['shipper'] = $shipper;

      $request_JSON = json_encode($request_shipping);
    } else {
      $new_state = 5;
      // construct the request body when state == 5
      $request_cancelled = array('order_id' => $order_id, 'new_state' => $new_state, 'sku' => $sku);
      
      $request_JSON = json_encode($request_cancelled);
    } 

    $result = $this->apiPost($end_point, $request_JSON);

    return $result;
  }

  function create_items() {}

  // inventory, price
  function xxx_update() {}

  // acknowledge, cancel, refund, shipping
  function xxx_orders() {}

}
?>