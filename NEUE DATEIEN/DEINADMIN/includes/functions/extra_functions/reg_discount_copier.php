<?php
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if (function_exists('zen_register_admin_page')) {
    if (!zen_page_key_exists('discountCopier')) {
        // Add discount copier to webshop menu
        zen_register_admin_page('discountCopier', 'BOX_CATALOG_DISCOUNT_COPIER','FILENAME_DISCOUNT_COPIER', '', 'catalog', 'Y', 230);
    }
}