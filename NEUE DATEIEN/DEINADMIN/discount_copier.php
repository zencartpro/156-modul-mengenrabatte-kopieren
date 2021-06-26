<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart-pro.at/license/3_0.txt GNU General Public License V3.0
 * @version $Id: discount_copier.php 2019-09-06 08:19:17Z webchills $
 */

  require('includes/application_top.php');
//zen_get_categories_products_list
?>

<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>

    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
    
  </head>
<body onLoad="init()" >
      <!-- header //-->
      <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
      <!-- header_eof //-->
      <div class="container-fluid">
        <!-- body //-->
<table border="0" width="100%" cellspacing="5" cellpadding="5">
  <tr>
    <td width="100%" valign="top" class="pageHeading">RABATT-KOPIERER<br />&nbsp;</td>
  </tr>
  <tr>
    <td><b><font color="#FF0000">WICHTIG: SICHERN SIE ZUERST IHRE DATENBANK</b></font>.  Es ist sehr einfach, den falschen Knopf zu drücken und alle Ihre Rabatte zu überschreiben.  Die Schaltfläche "Rabatte aktualisieren" macht genau das.  Sie löscht die bestehenden Rabatte und ersetzt sie durch den neuen Rabatt. Die Schaltfläche 'Aktualisieren ohne Überschreiben' ersetzt nicht einen bestehenden Rabatt, aber die Schaltfläche 'Rabatte aktualisieren' überschreibt bestehende Rabatte.<br />&nbsp;</td>
  </tr>
  <tr>
<!-- body_text //-->
    <td>

<?php 
//
// Build array of  all products
//
  $dc_products_all = $db->Execute("select products_id from ".TABLE_PRODUCTS);
    while (!$dc_products_all->EOF) { 
      $dc_products_all_array[]=$dc_products_all->fields['products_id'];
      $dc_products_all->MoveNext();}

?>


<?php
//
// Update Discounts in Database
//
global $db;

if(!isset($_POST['dc_get_discounts'])){
$changed=array();
}

if(isset($_POST['dc_delete_single'])){
    zen_delete_discounts_from_product($_POST['from_product']);
    $changed[]=$_POST['from_product'];
}


//single update only if product to and from are different and if product_from is set
if((isset($_POST['dc_update_single'])||isset($_POST['dc_update_single_no']))&&
  ($_POST['to_product'] != $_POST['from_product']) && 
  isset($_POST['from_product']))
{
  if(zen_has_product_discounts($_POST['to_product'])=='false'||isset($_POST['dc_update_single'])) {
      zen_delete_discounts_from_product($_POST['to_product']);
      zen_copy_discounts_to_product($_POST['from_product'], $_POST['to_product']);
      $changed[]=$_POST['to_product'];
  }
}
//EOF single update

//category update only if  product_from is set
if(((isset($_POST['dc_update_category']))||(isset($_POST['dc_update_category_no'])))&&
  isset($_POST['from_product'])){
    $dc_categories_products = $db->Execute("select products_id from ".TABLE_PRODUCTS." where (master_categories_id=".$_POST['to_category'].")");
      while (!$dc_categories_products->EOF) { 
        $dc_category_product_array[]=$dc_categories_products->fields['products_id'];
        $dc_categories_products->MoveNext();
      }
      for($i=0;$i<=(count($dc_category_product_array)-1);$i++){
        if($dc_category_product_array[$i] != $_POST['from_product']){
          if(zen_has_product_discounts($dc_category_product_array[$i])=='false'||isset($_POST['dc_update_category'])){
            zen_delete_discounts_from_product($dc_category_product_array[$i]);
            zen_copy_discounts_to_product($_POST['from_product'], $dc_category_product_array[$i]);
            $changed[]=$dc_category_product_array[$i];
          }
        }
      }
}
//EOF category update

//whole shop update only if  product_from is set
if((isset($_POST['dc_update_shop'])||isset($_POST['dc_update_shop_no']))&&
  isset($_POST['from_product'])){
      for($i=0;$i<=(count($dc_products_all_array)-1);$i++){
        if($dc_products_all_array[$i] != $_POST['from_product']){
          if(zen_has_product_discounts($dc_products_all_array[$i])=='false'||isset($_POST['dc_update_shop'] )){
          zen_delete_discounts_from_product($dc_products_all_array[$i]);
          zen_copy_discounts_to_product($_POST['from_product'], $dc_products_all_array[$i]);
          $changed[]=$dc_products_all_array[$i];
          }
        }
      }
}
//EOF whole shop
?>


<?php
//
// Build arrays of products with dicounts, products without dicounts
//
  $dc_products_with = $db->Execute("select products_id from ".TABLE_PRODUCTS." where (products_discount_type != '0')");
    while (!$dc_products_with->EOF) { 
      $dc_products_with_array[]=$dc_products_with->fields['products_id'];
      $dc_products_with->MoveNext();}
  $dc_products_without = $db->Execute("select products_id from ".TABLE_PRODUCTS." where (products_discount_type = '0')");
    while (!$dc_products_without->EOF) { 
      $dc_products_without_array[]=$dc_products_without->fields['products_id'];
      $dc_products_without->MoveNext();}
?>


<form  action="discount_copier.php" method="post">
<table border="1" style="margin:0 auto;" cellpadding="5">

  <tr>
  <td colspan="4">

<?php
      //display discounts
      if(isset($_POST['from_product'])){
        $fromproduct=(int)$_POST['from_product'];
        $dc_discounts = $db->Execute("select  pd.discount_id,
                                            pd.discount_qty,
                                            pd.discount_price,
                                            p.products_discount_type,
                                            p.products_discount_type_from 
                                            from (".TABLE_PRODUCTS_DISCOUNT_QUANTITY." pd, ".TABLE_PRODUCTS." p)
                                            where (pd.products_id = '$fromproduct' and p.products_id = '$fromproduct')");

          switch ($dc_discounts->fields['products_discount_type']) {
              case 0: $dc_discounttype ='Kein Rabatt'; break;
              case 1: $dc_discounttype ='Prozentsatz'; break;
              case 2: $dc_discounttype ='Aktueller Preis'; break;
              case 3: $dc_discounttype ='Ausgeschlossener Betrag'; break;
          }
          echo 'Werfen Sie einen Blick auf die Rabatte für die Produkt-ID='.(int)$_POST['from_product'].'<br/>Typ: '.$dc_discounttype.'&nbsp;';
          $discountfrom=($dc_discounts->fields['products_discount_type_from']=='1')?'Special':'Preis';
          echo ' abgezogen von: '.$discountfrom.'<br/>';
          echo '<table border="1" cellpadding="10" style="margin:10px auto"><tr>';
          while (!$dc_discounts->EOF) {
            echo '<td>';
            echo 'ID = '.$dc_discounts->fields['discount_id'].'<br/>';
            echo 'MENGE= '.$dc_discounts->fields['discount_qty'].'<br/>';
            echo 'PREIS = '.$dc_discounts->fields['discount_price'].'<br/></td>';
            $dc_discounts->MoveNext();
            };
          echo '</tr></table>';
        }else{
         echo 'Werfen Sie einen Blick auf das Display<br/>Es wurden keine Rabatte ausgewählt. Wählen Sie ein Produkt in der linken Spalte aus und klicken Sie auf die Schaltfläche Ansehen.';
        }
?>
  </td>
  </tr>

  <tr>
  <td><h1 style="text-align:center;">Wählen Sie einen Rabatt, um ihn zu nutzen</h1></td>
  <td><h1 style="text-align:center;">Auf das Produkt anwenden</h1></td>
  <td><h1 style="text-align:center;">Auf Kategorie anwenden</h1></td>
  <td><h1 style="text-align:center;">Auf den gesamten Shop anwenden</h1></td>
  </tr>

  <tr>
  <td>
<!--Choose Discount to apply-->
  
    <?php 


      echo '<select name = "from_product" style="width:200px">';
      for($i=0;$i<=(count($dc_products_all_array)-1);$i++){
        $dc_selected = ((int)$_POST['from_product']==$dc_products_all_array[$i]) ? ' selected ' : '';
        echo '<option value="'.$dc_products_all_array[$i].'"'.$dc_selected.'>ID='.$dc_products_all_array[$i].'&nbsp;'.zen_get_products_name($dc_products_all_array[$i]);
      }
      echo '</select><br/>';
      //get discounts button
      echo '<br/><input type="submit" name="dc_get_discounts" value="Ansehen" style="width:200px;color:#ffffff;background-color:#4EBA51"/><br/>';
      echo '<br/><input type="submit" name="dc_delete_single" value="Diesen Rabatt löschen" style="width:200px;color:#ffffff;background-color:#E71426"/><br/><br/>';


    ?>
  
  </td>
<!--Apply to one other product...............................Apply to one other product-->

  <td>
  <?php
      echo '<select name = "to_product" style="width:200px">';
      for($i=0;$i<=(count($dc_products_all_array)-1);$i++){
        $dc_selected = ((int)$_POST['to_product']==$dc_products_all_array[$i]) ? ' selected ' : '';
        echo '<option value="'.$dc_products_all_array[$i].'"'.$dc_selected.'>ID='.$dc_products_all_array[$i].'&nbsp;'.zen_get_products_name($dc_products_all_array[$i]);
      }
      echo '</select><br/>';
      //get discounts button
      echo '<br/><input type="submit" name="dc_update_single" value="Rabatte aktualisieren" style="width:220px;color:#ffffff;background-color:#E71426"/><br/>';
      echo '<br/><input type="submit" name="dc_update_single_no" value="Aktualisieren ohne Überschreiben" style="width:220px;color:#ffffff;background-color:#4EBA51"/><br/><br/>';
  ?>
  </td>
  <td>

<!--Apply to a category.............................................Apply to a category-->
    <?php
    $dc_category_array = zen_get_categories();
    $i=0;
    while ($i<=(count($dc_category_array)-1)) {$category_array[$dc_category_array[$i]['id']] = $dc_category_array[$i]['text'];$i++;}
    $used=array();
    $unused_children_of_current=array();
    $current_cat_id = '0';
    $parent_cat_id = '0';

    while(count($used)<=(count($dc_category_array)-1)){
      unset($children_of_current_array);
      //Build array (children of current array) of all the children of the current category
      $children_of_current = $db->Execute("select categories_id from ".TABLE_CATEGORIES." where(parent_id='$current_cat_id')");
      $children_of_current_array=array();
        while (!$children_of_current->EOF) { 
          $children_of_current_array[]=$children_of_current->fields['categories_id'];
          $children_of_current->MoveNext();}

//Build array (unused children of current array) of all the children of the current category that have not been used

      $unused_children_of_current = array_diff($children_of_current_array,$used);

//logic to build categories by category/sub-category order

      if (count($unused_children_of_current)>='1'){
        $a=$unused_children_of_current[key($unused_children_of_current)];
        $used[]=$a;
        $parent_cat_id = $current_cat_id;
        $current_cat_id=$a;
      }
      else
      {
        $current_cat_id = $parent_cat_id;
        $previous = $db->Execute("select parent_id from ".TABLE_CATEGORIES." where (categories_id='$parent_cat_id')");
        $parent_cat_id=$previous->fields['parent_id'];
      }
    
    }

//Output categories dropdown

      echo '<select name = "to_category" style="width:200px">';
      for($i=0;$i<=(count($used)-1);$i++){
        $dc_selected = ((int)$_POST['to_category']==(int)$used[$i]) ? ' selected ' : '';
        echo '<option value="'.(int)$used[$i].'" '.$dc_selected.'>CAT='.(int)$used[$i].'&nbsp;'.$category_array[(int)$used[$i]];
      }
      echo '</select><br/>';
    
    //by category button
    echo '<br/><input type="submit" name="dc_update_category" value="Rabatte aktualisieren" style="width:220px;color:#ffffff;background-color:#E71426"/><br/>';
    echo '<br/><input type="submit" name="dc_update_category_no" value="Aktualisieren ohne Überschreiben" style="width:220px;color:#ffffff;background-color:#4EBA51"/><br/><br/>';
    ?>

  </td>

<!--Apply to whole shop.............................................Apply to whole shop-->
  <td valign="bottom">
  <?php
  echo '<br/><input type="submit" name="dc_update_shop" value="Rabatte aktualisieren" style="width:220px;color:#ffffff;background-color:#E71426"/><br/>';
  echo '<br/><input type="submit" name="dc_update_shop_no" value="Aktualisieren ohne Überschreiben" style="width:220px;color:#ffffff;background-color:#4EBA51"/><br/><br/>';
  ?>
  </td>
  </tr>

<!--BOF Graphical Representation..............................BOF Graphical Representation-->
  <tr>
    <td colspan="4">
      <div>
      <h1 style="text-align:center;">Grafische Darstellung</h1>
      <p>Produkte mit schwarzen Rändern haben KEINE Rabatte. Produkte mit rosa Hervorhebung wurden gerade geändert.</p>

      <?php
      foreach($dc_products_all_array as $b){
      $b2=($b-1);     
      

      if($dc_products_with_array && count($dc_products_with_array)>0){
       $color=(in_array($b,$dc_products_with_array))?'red':'black';
      }else{
        $color='black';
      }
      if($changed && count($changed)>0){
        $bcolor=(in_array($b,$changed))?'#FFB8B6':'white';
      }
      else
      {
        $bcolor='white';
      }
      echo '<div style="width:30px;height:12px;line-height:12px;text-align:center;border:1px solid '.$color.';background-color:'.$bcolor.';float:left;margin:2px;">'.$b.'</div>';
      }
      ?><br style="clear:both"/>
      </div>
    </td>
  </tr>
<!--EOF Graphical Representation..............................EOF Graphical Representation-->
</table>
</form>


    </td>
<!-- body_text_eof //-->
  </tr>
  


  
</table>



<!-- body_text_eof //-->
      </div>
      <!-- body_eof //-->
      <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
      <!-- footer_eof //-->
    </body>
  </html>