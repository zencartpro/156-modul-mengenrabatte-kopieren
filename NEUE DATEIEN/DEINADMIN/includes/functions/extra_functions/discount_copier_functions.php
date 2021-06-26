<?php
////
//delete discounts from a product
  function zen_delete_discounts_from_product($product_to_delete_from) {
    global $db;
    $sql_delete_discounts = "delete from ".TABLE_PRODUCTS_DISCOUNT_QUANTITY." where (products_id=".(int)$product_to_delete_from." )";
    $db->execute($sql_delete_discounts);
    $db->Execute("update " . TABLE_PRODUCTS . " set products_discount_type='0', products_discount_type_from='0' where products_id='" . $product_to_delete_from . "'");
  }
