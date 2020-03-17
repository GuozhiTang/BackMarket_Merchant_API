<?php

include_once ('../backmarket_api/BackMarketAPI.php');
include_once ('../config/database_tables.php');
include_once ('../config/conn.php');

updateBMListingsAll();

/**
 * METHOD updateBMListingsAll
 * update data in database of all the listings
 * @return void
 * @author Guozhi Tang
 * @since 2020-03-17
 */
function updateBMListingsAll() {
  $bm = new BackMarketAPI();
  // get all listings in an array
  $res_array = $bm->getAllListings();
  // print_r($res_array);

  if ($res_array != null) {
    // clear the database and reset the auto_increamental parameter firstly
    $SQL = "SELECT no FROM ".TABLE_STORE_INV_BACKMARKET." WHERE no=1;";
    $isEmpty = mysql_query($SQL) or die('Cannot execute query! Error: '.mysql_error());
    if ($isEmpty != null) {
      $truncateSQL = "TRUNCATE TABLE ".TABLE_STORE_INV_BACKMARKET.";";
      echo $truncateSQL."\n";
      mysql_query($truncateSQL) or die('Cannot execute query! Error: '.mysql_error());
    }

    foreach ($res_array as $key => $value) {
      // get the object of each listing
      $listing_obj = $res_array[$key];
      // print_r($listing_obj);

      // insert all the listings into the database
      updateListingsInDB($listing_obj);
    }
  } else print_r("No listings existed currently!");
}

/**
 * METHOD updateListingsInDB
 * update all the data in the database of the specific listing
 * @param object $listing - the listing object containing all the information inside of this listing
 * @return void
 * @author Guozhi Tang
 * @since 2020-03-17
 */
function updateListingsInDB($listing) {
  /* ------------- deal with the data which may have symbols ------------- */
  $title = mysql_real_escape_string($listing->title);
  $comment = mysql_real_escape_string($listing->comment);

  /* ------------- deal with the data in shippings ------------- */
  if ($listing->shippings != null) {
    $shipping_price = $listing->shippings->shipping_price;
    $shipping_delay = $listing->shippings->shipping_delay;
    $shipper = $listing->shippings->shipper;
    $shipper_display = $listing->shippings->shipper_display;
    $country_code = $listing->shippings->country_code;
  }

  /* ------------- map the exact condition state of a listing accoring to the state number ------------- */
  switch ($listing->state) {
    case 0:
      $condition_state = 'Shiny (perfect state)';
    break;
    case 1:
      $condition_state = 'Gold (as good as new)';
    break;
    case 2:
      $condition_state = 'Silver (very good state)';
    break;
    case 3:
      $condition_state = 'Bronze (good state)';
    break;
    case 4:
      $condition_state = 'Stallone (acceptable)';
    break;
  }

  /* ------------- map the exact status of a listing accoring to the publication_state ------------- */
  switch ($listing->publication_state) {
    case 0:
      $status = 'Missing price or comment';
    break;
    case 1:
      $status = 'Pending validation';
    break;
    case 2:
      $status = 'Online';
    break;
    case 3:
      $status = 'Offline';
    break;
    case 4:
      $status = 'Deactivated';
    break;
  }

  /* ------------- insert all the data into the database store_inv_back_market ------------- */
  $insertSQL = "INSERT INTO ".TABLE_STORE_INV_BACKMARKET.
                " (`no`, `ListingId`, `SKU`, `Title`, `Deee`, `ConditionState`, `State`, `Price`, `PricePromo`, `Quantity`, `Status`, `PublicationState`, `Comment`, `WarrantlyDelay`, `Currency`, `BackMarketId`, `ShippingPrice`, `ShippingDelay`, `Shipper`, `ShipperDisplay`, `CountryCode`)
                VALUES (null, '$listing->listing_id', '$listing->sku', '$title', '$listing->deee', '$condition_state', '$listing->state', '$listing->price', '$listing->price_promo', '$listing->quantity', '$status', '$listing->publication_state', '$listing->comment', '$listing->warranty_delay', '$listing->currency', '$listing->backmarket_id', '$shipping_price', '$shipping_delay', '$shipper', '$shipper_display', '$country_code');";
  echo $insertSQL."\n";
  mysql_query($insertSQL) or die('Cannot execute query! Error: '.mysql_error());
}

?>