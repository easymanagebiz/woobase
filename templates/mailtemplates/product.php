<?php

defined( 'ABSPATH' ) || exit;

?>

<div class="easymanage-email-product-container">
  <div id="product-<?php echo $product->get_id() ?>" <?php wc_product_class( '', $product ); ?>>
    <h3 class="easymanage-email-product-name">
      <a href="<?php echo get_permalink($product->get_id()) ?>"><?php echo $product->get_name(); ?></a>
    </h3>

    <?php
      $image = $product->get_image('woocommerce_thumbnail');

      if ($image) {
        echo '<div class="easymanage-email-product-image">';
      ?>
        <a href="<?php echo get_permalink($product->get_id()) ?>">
          <?php
            echo $image;
          ?>
        </a>  
      <?php
        echo '</div>';
      }
      ?>

      <div class="easymanage-email-price"><?php echo $product->get_price_html(); ?></div>
      <div class="easymanage-email-product-to-cart">
        <a href="<?php echo get_permalink($product->get_id()) ?>"><?php echo __('View', 'easymanage') ?></a>
      </div>
  <div>
</div>
