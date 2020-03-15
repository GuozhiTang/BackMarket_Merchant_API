<?php

include_once ('../includes/backmarket_api/BackMarketAPI.php');
include_once ('../includes/database_tables.php');
include_once ('../includes/conn.php');

getBMOrdersNew();
updateBMOrdersAll();

/**
 * METHOD getBMOrdersNew
 * Insert data into database of all the new order in state 0 and 1
 * @return void
 * @author Guozhi Tang
 * @since 2020-02-28
 */
function getBMOrdersNew() {
  $bm = new BackMarketAPI();

  $res_array = $bm->getNewOrders();
  // $res_array = $bm->getAllOrders();

  // print_r($res_array);

  // if there are new orders
  if ($res_array != null) {
    foreach ($res_array as $key1 => $value1) {
      // get the object of each new order
      $order_obj = $res_array[$key1];

      // check whether this order exists in database
      // if not, insert this new order, if exists, do nothing
      if (checkExistance($order_obj->order_id, TABLE_BACKMARKET_ORDERS) == 0) {
        // insert the data into database of each new order
        updateOrderInDB($order_obj, true);
      }

      // get the new orderlines array
      $items_array = $order_obj->orderlines;
      foreach ($items_array as $key2 => $value2) {
        // get the object of each new orderline/item
        $item_obj = $items_array[$key2];

        // check whether this orderline exists in database
        // if not, insert this new orderline, if exists, do nothing
        if (checkExistance($item_obj->id, TABLE_BACKMARKET_ORDER_ITEMS) == 0) {
          // insert the data into database of each new item/orderline
          updateItemInDB($item_obj, $order_obj->order_id, true);
        }

        // validate the state of new orderline
        $bm->validateOrderlines($order_obj->order_id, $item_obj->listing);
      }
    }
  } else print_r('No new orders (in state 0 or 1) exists!'."\n\n");
}

/**
 * METHOD updateBMOrdersAll
 * Update data in database of all the orders which have been modified in last 60 days.
 * @return void
 * @author Guozhi Tang
 * @since 2020-02-28
 */
function updateBMOrdersAll() {
  $bm = new BackMarketAPI();
  // get all orders in an array
  $res_array = $bm->getAllOrders();
  // print_r($res_array);

  if ($res_array != null) {
    foreach ($res_array as $key1 => $value1) {
      // get the object of each order
      $order_obj = $res_array[$key1];

      // check whether this order exists in database
      // if not, insert this order, if exists, update the order
      if (checkExistance($order_obj->order_id, TABLE_BACKMARKET_ORDERS) == 0) {
        // insert the data into database of each new order
        updateOrderInDB($order_obj, true);
      } else {
        // update the data in database of each order
        updateOrderInDB($order_obj);
      }

      // get the orderlines array
      $items_array = $order_obj->orderlines;
      foreach ($items_array as $key2 => $value2) {
        // get the object of each orderline/item
        $item_obj = $items_array[$key2];
        // print_r($item_obj);

        // check whether this orderline exists in database
        // if not, insert this orderline, if exists, update the orderline
        if (checkExistance($item_obj->id, TABLE_BACKMARKET_ORDER_ITEMS) == 0) {
          // insert the data into database of each new item/orderline
          updateItemInDB($item_obj, $order_obj->order_id, true);
        } else {
          // update the data in database of each item/orderline
          updateItemInDB($item_obj, $order_obj->order_id);
        }
      }
    }
  } else print_r('No orders have been modified in 3 months!');
}

/**
 * METHOD updateOrderInDB
 * Update all the data in the database of the specific order.
 * @param object $order - the order object containing all the information inside of this order.
 * @param boolean $insert - whether it needs insertion update: true, normally update: false
 * @return void
 * @author Guozhi Tang
 * @since 2020-02-28
 */
function updateOrderInDB($order, $insert = false) {
  /* ------------- modify the format from 'date" in PHP to 'datetime' in MySQL ------------- */
  if ($order->date_creation != null) $dateCreation = str_replace('T', ' ', substr($order->date_creation, 0, -6));
  else $dateCreation = '0001-01-01 00:00:00';

  if ($order->date_modification != null) $dateModification = str_replace('T', ' ', substr($order->date_modification, 0, -6));
  else $dateModification = '0001-01-01 00:00:00';

  if ($order->date_shipping != null) $dateShipping = str_replace('T', ' ', substr($order->date_shipping, 0, -6));
  else $dateShipping = '0001-01-01 00:00:00';

  if ($order->date_payment != null) $datePayment = str_replace('T', ' ', substr($order->date_payment, 0, -6));
  else $datePayment = '0001-01-01 00:00:00';

  /* ------------- deal with the data in shipping_address and billing_address ------------- */
  if ($order->shipping_address != null) {
    $shipGender = $order->shipping_address->gender;
    $shipPostal = $order->shipping_address->postal_code;
    $shipCountry = $order->shipping_address->country;
    $shipState = $order->shipping_address->state_or_province;
    $shipCity = $order->shipping_address->city;
    $shipPhone = $order->shipping_address->phone;
    $shipEmail = $order->shipping_address->email;

    $shipFirstName = mysql_real_escape_string($order->shipping_address->first_name);
    $shipLastName = mysql_real_escape_string($order->shipping_address->last_name);
    $shipCompany = mysql_real_escape_string($order->shipping_address->company);
    $shipSt = mysql_real_escape_string($order->shipping_address->street);
    $shipSt2 = mysql_real_escape_string($order->shipping_address->street2);
  }

  if ($order->billing_address != null) {
    $billGender = $order->billing_address->gender;
    $billPostal = $order->billing_address->postal_code;
    $billCountry = $order->billing_address->country;
    $billState = $order->billing_address->state_or_province;
    $billCity = $order->billing_address->city;
    $billPhone = $order->billing_address->phone;
    $billEmail = $order->billing_address->email;

    $billFirstName = mysql_real_escape_string($order->billing_address->first_name);
    $billLastName = mysql_real_escape_string($order->billing_address->last_name);
    $billCompany = mysql_real_escape_string($order->billing_address->company);
    $billSt = mysql_real_escape_string($order->billing_address->street);
    $billSt2 = mysql_real_escape_string($order->billing_address->street2);
  }

  /* ------------- deal with the boolean data of installment_payment ------------- */
  if ($order->installment_payment == '' || $order->installment_payment == false) $installPay = 0;
  else $installPay = 1;

  /* ------------- get the title with the maximum price in all orderlines in this order ------------- */
  $item_array = $order->orderlines;
  $title = $item_array[0]->product;
  for ($i = 1; $i < count($item_array); $i++) {
    if (($item_array[$i]->price) >= ($item_array[$i-1]->price)) $title = ($item_array[$i]->product);
    else $title = ($item_array[$i-1]->product); 
  } 

  /* ------------- map the exact status of an order accoring to states of order and its orderlines ------------- */
  $status = mapStateToStatus($order);

  /* ------------- Get the sum of orderlines quantities of an exact order ------------- */
  $qty = orderQuantity($order);

  /* ------------- Get the sum of orderlines quantities of an exact order ------------- */
  $delay = shipByCalculate($order);

  // if only need update, no insertion
  if (!$insert) {
    /* ------------- update the orders which has been modified in 60 days database order_back_market ------------- */
    $updateSQL = "UPDATE ".TABLE_BACKMARKET_ORDERS.
              " SET Status='$status', State='$order->state', Title='$title', Quantity='$qty', DateCreation='$dateCreation', DateModification='$dateModification', DateShipping='$dateShipping', DatePayment='$datePayment', ShipBy=DATE_ADD('$dateCreation', INTERVAL $delay HOUR), OrderPrice='$order->price', ShippingPrice='$order->shipping_price', Currency='$order->currency', ShipAddrCompany='$shipCompany', ShipAddrFirstN='$shipFirstName', ShipAddrLastN='$shipLastName', ShipAddrGender='$shipGender', ShipAddrSt='$shipSt', ShipAddrSt2='$shipSt2', ShipAddrPostal='$shipPostal', ShipAddrCity='$shipCity', ShipAddrState='$shipState', ShipAddrCountry='$shipCountry', ShipAddrPhone='$shipPhone', ShipAddrEmail='$shipEmail', BillAddrCompany='$billCompany', BillAddrFirstN='$billFirstName', BillAddrLastN='$billLastName', BillAddrGender='$billGender', BillAddrSt='$billSt', BillAddrSt2='$billSt2', BillAddrPostal='$billPostal', BillAddrCity='$billCity', BillAddrState='$billState', BillAddrCountry='$billCountry', BillAddrPhone='$billPhone', BillAddrEmail='$billEmail', DeliveryNote='$order->delivery_note', TrackingNum='$order->tracking_number', TrackingUrl='$order->tracking_url', Shipper='$order->shipper', CountryCode='$order->country_code', PaypalRef='$order->paypal_reference', InstallPayment='$installPay', PaymentMethod='$order->payment_method', SaleTaxes='$order->sales_taxes', IsUpgrade='false' 
              WHERE BMOrderId='$order->order_id'";
    echo $updateSQL."\n";
    mysql_query($updateSQL) or die('Cannot execute query! Error: '.mysql_error());
  } else { // if needs insertion
    /* ------------- insert all the data into the database order_back_market ------------- */
    $insertSQL = "INSERT INTO ".TABLE_BACKMARKET_ORDERS.
              " (`no`, `BMOrderId`, `Status`, `State`, `Title`, `Quantity`, `DateCreation`, `DateModification`, `DateShipping`, `DatePayment`, `ShipBy`, `OrderPrice`, `ShippingPrice`, `Currency`, `ShipAddrCompany`, `ShipAddrFirstN`, `ShipAddrLastN`, `ShipAddrGender`, `ShipAddrSt`, `ShipAddrSt2`, `ShipAddrPostal`, `ShipAddrCity`, `ShipAddrState`, `ShipAddrCountry`, `ShipAddrPhone`, `ShipAddrEmail`, `BillAddrCompany`, `BillAddrFirstN`, `BillAddrLastN`, `BillAddrGender`, `BillAddrSt`, `BillAddrSt2`, `BillAddrPostal`, `BillAddrCity`, `BillAddrState`, `BillAddrCountry`, `BillAddrPhone`, `BillAddrEmail`, `DeliveryNote`, `TrackingNum`, `TrackingUrl`, `Shipper`, `CountryCode`, `PaypalRef`, `InstallPayment`, `PaymentMethod`, `SaleTaxes`, `IsUpgrade`)
              VALUES (null, '$order->order_id', '$status', '$order->state', '$title', '$qty', '$dateCreation', '$dateModification', '$dateShipping', '$datePayment', DATE_ADD('$dateCreation', INTERVAL $delay HOUR), '$order->price', '$order->shipping_price', '$order->currency', '$shipCompany', '$shipFirstName', '$shipLastName', '$shipGender', '$shipSt', '$shipSt2', '$shipPostal', '$shipCity', '$shipState', '$shipCountry', '$shipPhone', '$shipEmail', '$billCompany', '$billFirstName', '$billLastName', '$billGender', '$billSt', '$billSt2', '$billPostal', '$billCity', '$billState', '$billCountry', '$billPhone', '$billEmail', '$order->delivery_note', '$order->tracking_number', '$order->tracking_url', '$order->shipper', '$order->country_code', '$order->paypal_reference', '$installPay', '$order->payment_method', '$order->sales_taxes', 'false')";
    echo $insertSQL."\n";
    mysql_query($insertSQL) or die('Cannot execute query! Error: '.mysql_error());
  }
}

/**
 * METHOD updateItemInDB
 * Update all the data in the database of the specific item/orderline.
 * @param object $item - the item object which is each orderline in an order
 * @param string $order_id - order_id of the exact order
 * @param boolean $insert - whether it needs insertion update: true, normally update: false
 * @return void
 * @author Guozhi Tang
 * @since 2020-02-28
 */
function updateItemInDB($item, $order_id, $insert = false) {
  /* ------------- modify the format from 'date" in PHP to 'datetime' in MySQL ------------- */
  if ($item->date_creation != null) $dateCreation = str_replace('T', ' ', substr($item->date_creation, 0, -6));
  else $dateCreation = '0001-01-01 00:00:00';

  /* ------------- deal with the boolean data of backcare ------------- */
  // if ($item->imei_numbers != null) $IMEI;
  $IMEI = implode(",", $item->imei_numbers);

  /* ------------- deal with the boolean data of backcare ------------- */
  if ($item->backcare == '' || $item->backcare == false) $Backcare = 0;
  else $Backcare = 1;

  // if only need update, no insertion
  if (!$insert) {
    /* ------------- update the items which has been modified in 60 days database order_items_back_market ------------- */
    $updateSQL = "UPDATE ".TABLE_BACKMARKET_ORDER_ITEMS.
                  " SET BMOrderId='$order_id', OrderItemId='$item->product_id', State='$item->state', ShipDelay='$item->shipping_delay', DateCreation='$dateCreation', ListingPrice='$item->price', ShippingPrice='$item->shipping_price', Currency='$item->currency', ListingSKU='$item->listing', ProductTitle='$item->product', Quantity='$item->quantity', IMEINum='$IMEI', Brand='$item->brand', Backcare='$Backcare', BackcarePrice='$item->backcare_price', ReturnReason='$item->return_reason', ReturnMessage='$item->return_message'
                  WHERE OrderlineId='$item->id'";
    echo $updateSQL."\n";
    mysql_query($updateSQL) or die('Cannot execute query! Error: '.mysql_error());
  } else { // if needs insertion
    /* ------------- first time to insert all the data into the database order_items_back_market ------------- */
    $insertSQL = "INSERT INTO ".TABLE_BACKMARKET_ORDER_ITEMS.
                  " (`no`, `BMOrderId`, `OrderlineId`, `OrderItemId`, `State`, `ShipDelay`, `DateCreation`, `ListingPrice`, `ShippingPrice`, `Currency`, `ListingSKU`, `ProductTitle`, `Quantity`, `IMEINum`, `Brand`, `Backcare`, `BackcarePrice`, `ReturnReason`, `ReturnMessage`)
                  VALUES (null, '$order_id', '$item->id', '$item->product_id', '$item->state', '$item->shipping_delay', '$dateCreation', '$item->price', '$item->shipping_price', '$item->currency', '$item->listing', '$item->product', '$item->quantity', '$IMEI', '$item->brand', '$Backcare', '$item->backcare_price', '$item->return_reason', '$item->return_message')";
    echo $insertSQL."\n";
    mysql_query($insertSQL) or die('Cannot execute query! Error: '.mysql_error());
  }
}

/**
 * METHOD mapStateToStatus
 * Map the exact status of the order according to specific order and its orderlines
 * @param object $order - the order object containing all the information inside of this order.
 * @return String - the exact status of an order in String type
 * @author Guozhi Tang
 * @since 2020-03-05
 */
function mapStateToStatus($order) {
  $orderlines = $order->orderlines;

  // if the state of order or is 0 or 1, then the order status is 'Created'
  if ($order->state == 0 || $order->state == 1) return 'Created';

  if ($order->state == 3) {
    foreach($orderlines as $key => $value) {
      // in case there are some of the orderlines not being validated, then the status is still 'Created'
      if ($orderlines[$key] == 0 || $orderlines[$key] == 1) return 'Created';
      else continue;
    }
    // if all the states of orderlines are 2, the order status should be 'Validated'
    return 'Validated';
  }

  if ($order->state == 8) return 'Cancelled';

  if ($order->state == 9) {
    // if any one of the states of orderlines is 6, the order status should be 'Returned'
    foreach($orderlines as $key => $value) {
      if ($orderlines[$key]->state == 6) return 'Returned';
    }

    // if any one of the states of orderlines is 4 or 5
    foreach($orderlines as $key => $value) {
      if ($orderlines[$key]->state == 4 || $orderlines[$key]->state == 5) return 'Cancelled';
    }

    // if any one of the states of orderlines is 3, the order status should be 'Shipped'
    foreach($orderlines as $key => $value) {
      if ($orderlines[$key]->state == 3) return 'Shipped';
    }
  }
}

/**
 * METHOD orderQuantity
 * Get the sum of orderlines quantities of an exact order
 * @param object $order - the order object containing all the information inside of this order.
 * @return int - the total quantities of an exact order
 * @author Guozhi Tang
 * @since 2020-03-06
 */
function orderQuantity($order) {
  $orderlines = $order->orderlines;

  foreach($orderlines as $key => $value) {
    $sum += $orderlines[$key]->quantity;
  }
  return $sum;
}

/**
 * METHOD shipByCalculate
 * Get the Ship By date according to the Creation Date
 * @param object $order - the order object containing all the information inside of this order.
 * @return datetime - the exact Ship By date according to the Creation Date
 * @author Guozhi Tang
 * @since 2020-03-06
 */
function shipByCalculate($order) {
  $orderlines = $order->orderlines;
  $delay = $orderlines[0]->shipping_delay;
  for ($i = 1; $i < count($orderlines); $i++) {
    if ($orderlines[$i]->shipping_delay < $orderlines[$i-1]) $delay = $orderlines[$i]->shipping_delay;
  }
  return $delay;
}

/**
 * METHOD checkExistance
 * check whether the data with the exact parameter exists in the speicific table
 * @param string $parameter - the exact parameter of the data, could be the unique id.
 * @param string $table - the specific table which should be checked.
 * @return int - 1 means the data exists already, 0 means the data does not exist in the table.
 * @author Guozhi Tang
 * @since 2020-03-13
 */
function checkExistance($parameter, $table) {
  // check the data in orders table
  if ($table == TABLE_BACKMARKET_ORDERS) {
    $SQL = "SELECT * FROM ".TABLE_BACKMARKET_ORDERS." WHERE BMOrderId='$parameter' LIMIT 1";
  } else {
    // check the data in orderlines table
    $SQL = "SELECT * FROM ".TABLE_BACKMARKET_ORDER_ITEMS." WHERE OrderlineId='$parameter' LIMIT 1";
  }

  $result = mysql_query($SQL) or die("Couldn't execute query.".mysql_error());
  
  if (mysql_num_rows($result)) $check = 1;
  else $check = 0;

  return $check;
}
?>