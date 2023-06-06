<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( ));
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );
function my_custom_upload_mime_types( $mimes ) {
 
    // Add new allowed MIME types here.
    $mimes['svg'] = 'image/svg+xml';
 
    // Return the array back to the function with our added MIME type.
    return $mimes;
}
add_filter( 'upload_mimes', 'my_custom_upload_mime_types' );

add_action('woocommerce_after_add_to_cart_form','one_time_button');
function one_time_button() {
	$prod_id = get_field('select_product');
	echo '<a href="'.do_shortcode('[add_to_cart_url id=' . $prod_id . ']').'" class="add-to-cart-onetime"><button class="button alt">Add to cart</button></a>';
}

/*
 * Product subscriptions
 */

function get_company_name($echo = false){

	if (function_exists('get_field'))
		$output = get_field('company', 'option');

	if (empty($output))
		$output = get_bloginfo('name');

	if ($echo) echo $output;
	else return $output;
}


function get_help_icon($content, $type = 'text', $echo = false){

	if ($type == 'image') {

		$class = 'covering-image';
		$content = "<img src='$content' alt='' />";

	} else $class = 'with-paddings';

	$output = "<span class='help-icon'>\n".
		"<span class='help-icon-inner fa fa-question-circle'></span>\n".
		($content ? "<span class='help-icon-hover $class'><span class='help-icon-hover-inner'>$content</span></span>\n" : "").
		"</span>\n";

	if ($echo) echo $output;
	else return $output;
}

/*
 * Product subscriptions: Cart
 */

// Remove filters added by "WC Subscriptions" and "WC All Products For Subscriptions"
remove_filter( 'woocommerce_cart_item_price', array( 'WCS_ATT_Display_Cart', 'show_cart_item_subscription_options' ), 1000, 3 );
remove_filter( 'woocommerce_cart_item_subtotal', array( 'WC_Subscriptions_Switcher', 'add_cart_item_switch_direction' ), 10, 3 );

/**
 * @snippet       Continue Shopping Link - WooCommerce Cart
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 3.6.2
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
 
add_filter( 'woocommerce_continue_shopping_redirect', 'bbloomer_change_continue_shopping' );
 
function bbloomer_change_continue_shopping() {
   return get_permalink( 3607);
}

add_filter( 'woocommerce_add_to_cart_fragments', 'wc_refresh_mini_cart_count');
function wc_refresh_mini_cart_count($fragments){
    ob_start();
		$items_count = WC()->cart->get_cart_contents_count(); ?>
		<div id="mini-cart-count"><?php echo $items_count ? $items_count : '&nbsp;'; ?></div> <?php
    $fragments['#mini-cart-count'] = ob_get_clean();
	
return $fragments;
}

add_filter( 'login_headerurl', 'my_custom_login_url' );
function my_custom_login_url($url) {
    return 'https://kauaihempco.com/';
}

// REMOVE 'CHOOSE OPTION' in Product's variable dropdown 
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'filter_dropdown_option_html', 12, 2 );
function filter_dropdown_option_html( $html, $args ) {
    $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' );
    $show_option_none_html = '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

    $html = str_replace($show_option_none_html, '', $html);

    return $html;
}



/*
Create Shortcode for WooCommerce Cart Menu Item
--------------------------------------------------------------------------*/
add_shortcode ('woo_cart_but', 'woo_cart_but' );
function woo_cart_but() {
	ob_start();
        $cart_count = WC()->cart->cart_contents_count; // Set variable for cart item count
        $cart_url = wc_get_cart_url();  // Set Cart URL ?>
        
        <a class="menu-item cart-contents" href="<?php echo $cart_url; ?>" title="My Basket"><?php
			if ( $cart_count > 0 ) {?>
				<span class="cart-contents-count"><?php echo $cart_count; ?></span><?php
			} ?>
		</a> <?php
    return ob_get_clean();
}

/*
Add AJAX Shortcode when cart contents update
--------------------------------------------------------------------------*/
add_filter( 'woocommerce_add_to_cart_fragments', 'woo_cart_but_count' );
function woo_cart_but_count( $fragments ) {
    ob_start();
    	$cart_count = WC()->cart->cart_contents_count;
		$cart_url = wc_get_cart_url(); ?>
		<a class="cart-contents menu-item" href="<?php echo $cart_url; ?>" title="<?php _e( 'View your shopping cart' ); ?>"><?php

			if ( $cart_count > 0 ) { ?>

				<span class="cart-contents-count"><?php echo $cart_count; ?></span>
				<?php            
			} ?>
		</a><?php
    $fragments['a.cart-contents'] = ob_get_clean();
    return $fragments;
}

add_filter( 'woocommerce_ship_to_different_address_checked', '__return_true');


//Enqueue Ajax Scripts
function enqueue_cart_qty_ajax() {

    wp_register_script( 'cart-qty-ajax-js', get_template_directory_uri() . '/js/cart-qty-ajax.js', array( 'jquery' ), '', true );
    wp_localize_script( 'cart-qty-ajax-js', 'cart_qty_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    wp_enqueue_script( 'cart-qty-ajax-js' );

}
add_action('wp_enqueue_scripts', 'enqueue_cart_qty_ajax');

function ajax_qty_cart() {

    // Set item key as the hash found in input.qty's name
    $cart_item_key = $_POST['hash'];

    // Get the array of values owned by the product we're updating
    $threeball_product_values = WC()->cart->get_cart_item( $cart_item_key );

    // Get the quantity of the item in the cart
    $threeball_product_quantity = apply_filters( 'woocommerce_stock_amount_cart_item', apply_filters( 'woocommerce_stock_amount', preg_replace( "/[^0-9\.]/", '', filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT)) ), $cart_item_key );

    // Update cart validation
    $passed_validation  = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $threeball_product_values, $threeball_product_quantity );

    // Update the quantity of the item in the cart
    if ( $passed_validation ) {
        WC()->cart->set_quantity( $cart_item_key, $threeball_product_quantity, true );
    }

    // Refresh the page
    echo do_shortcode( '[woocommerce_cart]' );

    die();

}

add_action('wp_ajax_qty_cart', 'ajax_qty_cart');
add_action('wp_ajax_nopriv_qty_cart', 'ajax_qty_cart');




function promotions_banner() {
  // LOADING TEMPLATE TO SHOW BEFORE HEADER
  get_template_part('template-part/promotion');

}
add_action('wp_body_open', 'promotions_banner');


function count_query_product() {

    ob_start();

    $currentCategory = get_the_terms( $post->ID, 'product_cat' );
    $cat = get_queried_object();
    $catID = $cat->term_id;
    
    foreach ($currentCategory as $category) {
        // matching current cat id to the query id
        if($category -> term_id == $catID) {
            $totalPost = $category -> count;
            $catName = $category -> name;
            echo $catName . ' Has Total ' . $totalPost . ' Products';
        }
    }

    return ob_get_clean();
}

add_shortcode( 'product_count', 'count_query_product');

// function csk_sticky_cart_navigations() {
//     ob_start();
//     get_template_part('template-part/cart-navigations');
//     return ob_get_clean();
// }

// add_shortcode( 'sticky_cart_navigations', 'csk_sticky_cart_navigations');

function zero_quantity_subscribe_form() {
    ob_start();

    global $product;

    $stock = $product->get_stock_quantity();
    $stockStatus = $product->get_stock_status();

    // checking the stock quantity to print the form

    if($stock > 0 || $stockStatus ==  'instock') {
        // slient is gold!
    } else {
        ?>
        <style>
            .yith-wcwl-add-button {
            display: none; }
        </style>
        <?php  
        echo do_shortcode('[forminator_form id="4977"]');
    }

    // $order = wc_get_order( $order_id );
    // $total_quantity = $order->get_item_count();


    $totalSales = $product->get_total_sales();

    if($totalSales > 0) {
        echo 'Total ' . $totalSales . ' Item Sold';
    }

    // var_dump();

   return ob_get_clean();
}

add_shortcode( 'quantity_subscribe_form', 'zero_quantity_subscribe_form');

// ADD TO CART FOR LIVE SEARCH PAGE
add_filter('asp_results', 'asp_add_to_cart_data', 1, 1);
function asp_add_to_cart_data($results) {
    $product_add_to_cart_text   = 'Add to cart';
    $variation_add_to_cart_text = 'Choose variation';   // Leave it empty to not display at all
    
    if (class_exists("WooCommerce")) {
        $_pf = new WC_Product_Factory();
        foreach ($results as &$r) {
            if (
            $r->content_type == "pagepost" &&
                in_array($r->post_type, array("product", "product_variation"))
            ) {
                $product = $_pf->get_product($r->id);
                $is_variable = $product->is_type( 'variable' ) || $r->post_type == 'product_variation';
                $link = !$is_variable ? get_permalink(wc_get_page_id('shop')) : $product->get_permalink(); 
                $ajax = !$is_variable ? ' ajax_add_to_cart' : ''; 
                $text = !$is_variable ? $product_add_to_cart_text : $variation_add_to_cart_text;
                if ( empty($text) )
                    continue;
                ob_start();
                ?>
                <div class="woocommerce">
                    <a href="<?php echo $link; ?>"
                       data-quantity="1"
                       class="button product_type_simple add_to_cart_button<?php echo $ajax; ?>"
                       data-product_id="<?php echo $r->id; ?>" data-product_sku=""
                       rel="nofollow"><?php echo $text; ?></a>
                </div>
                <?php
                $button = ob_get_clean();
                $r->content .= $button;
            }
        }
    }
    return $results;
}
add_action('wp_footer', 'asp_add_to_cart_handler');
function asp_add_to_cart_handler() {
    ?>
    <script>
    jQuery(function(t){if("undefined"==typeof wc_add_to_cart_params)return!1;var a=function(){t(".asp_r").on("click",".add_to_cart_button",this.onAddToCart).on("click",".remove_from_cart_button",this.onRemoveFromCart).on("added_to_cart",this.updateButton).on("added_to_cart",this.updateCartPage).on("added_to_cart removed_from_cart",this.updateFragments)};a.prototype.onAddToCart=function(a){var o=t(this);if(o.is(".ajax_add_to_cart")){if(!o.attr("data-product_id"))return!0;a.preventDefault(),o.removeClass("added"),o.addClass("loading");var r={};t.each(o.data(),function(t,a){r[t]=a}),t(document.body).trigger("adding_to_cart",[o,r]),t.post(wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%","add_to_cart"),r,function(a){a&&(a.error&&a.product_url?window.location=a.product_url:"yes"!==wc_add_to_cart_params.cart_redirect_after_add?t(document.body).trigger("added_to_cart",[a.fragments,a.cart_hash,o]):window.location=wc_add_to_cart_params.cart_url)})}},a.prototype.onRemoveFromCart=function(a){var o=t(this),r=o.closest(".woocommerce-mini-cart-item");a.preventDefault(),r.block({message:null,overlayCSS:{opacity:.6}}),t.post(wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%","remove_from_cart"),{cart_item_key:o.data("cart_item_key")},function(a){a&&a.fragments?t(document.body).trigger("removed_from_cart",[a.fragments,a.cart_hash,o]):window.location=o.attr("href")}).fail(function(){window.location=o.attr("href")})},a.prototype.updateButton=function(a,o,r,e){(e=void 0!==e&&e)&&(e.removeClass("loading"),e.addClass("added"),wc_add_to_cart_params.is_cart||0!==e.parent().find(".added_to_cart").length||e.after(' <a href="'+wc_add_to_cart_params.cart_url+'" class="added_to_cart wc-forward" title="'+wc_add_to_cart_params.i18n_view_cart+'">'+wc_add_to_cart_params.i18n_view_cart+"</a>"),t(document.body).trigger("wc_cart_button_updated",[e]))},a.prototype.updateCartPage=function(){var a=window.location.toString().replace("add-to-cart","added-to-cart");t(".shop_table.cart").load(a+" .shop_table.cart:eq(0) > *",function(){t(".shop_table.cart").stop(!0).css("opacity","1").unblock(),t(document.body).trigger("cart_page_refreshed")}),t(".cart_totals").load(a+" .cart_totals:eq(0) > *",function(){t(".cart_totals").stop(!0).css("opacity","1").unblock(),t(document.body).trigger("cart_totals_refreshed")})},a.prototype.updateFragments=function(a,o){o&&(t.each(o,function(a){t(a).addClass("updating").fadeTo("400","0.6").block({message:null,overlayCSS:{opacity:.6}})}),t.each(o,function(a,o){t(a).replaceWith(o),t(a).stop(!0).css("opacity","1").unblock()}),t(document.body).trigger("wc_fragments_loaded"))},new a});
    </script>
    <?php
}