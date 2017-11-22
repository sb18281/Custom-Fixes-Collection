<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $product, $simon, $pagename;
?>

<p><?php
	/* translators: 1: user display name 2: logout url */
	printf(
		__( 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'woocommerce' ),
		'<strong>' . esc_html( $current_user->display_name ) . '</strong>',
		esc_url( wc_logout_url( wc_get_page_permalink( 'myaccount' ) ) )
	);
?></p>


<p><?php
	printf(
		__( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a> and <a href="%3$s">edit your password and account details</a>.', 'woocommerce' ),
		esc_url( wc_get_endpoint_url( 'orders' ) ),
		esc_url( wc_get_endpoint_url( 'edit-address' ) ),
		esc_url( wc_get_endpoint_url( 'edit-account' ) )
	);
?></p>
<h2><center>Zum Nachbestellen: Deine letzten Bestellungen bei uns:</center></h2>
<ul class="products">
	</ul>
    <?php
		/*
			Custom Dashboard Demo
			Written for Matthias & Jan at Fire Drinks DE
			By Simon Barnes
		  (simon@madeandbound.co)
			
		*/
		// Get all of the customer's Previous orders
				$customer_orders = get_posts( array(
					'numberposts' => -1,
					'meta_key'    => '_customer_user',
					'meta_value'  => get_current_user_id(),
					'post_type'   => wc_get_order_types(),
					'post_status' => array_keys( wc_get_order_statuses() ),
					) );
					$customer = wp_get_current_user();

    // Get the products from the orders
		$orders = Array();
    foreach ($customer_orders as $order_id) {
			$products = Array();
	    $order = new WC_Order( $order_id );
	    $order_items = $order->get_items();

	    foreach ( $order_items as $order_item) {
					array_push($products, $order_item);
	    }
			array_push($orders, $products);
    };

		// Last Ordered product
		/*
			If there is more than one product in the last order only show the first, then show the rest underneath
		*/
		echo '<h4>'.__('Last order', 'woocommerce').'</h4></br>';
		if (sizeof($orders[0]) > 1){
			$last_order = array_shift($orders[0]);
		}else {
			$last_order = array_shift($orders)[0];

		}
		echo '</br>';
		// Get all of the variation data and the flasche image
		$variation_data = $last_order->get_meta_data();
		$flasche_image_html = get_flasche_image($last_order->get_meta('pa_flasche'));



		$product = new WC_Product_Variable($last_order['product_id']);
		$etikett_url= $last_order->get_meta('Etikett hochladen');



		/*
		Used for file upload


		$url = 'http://www.fire-drinks.de/wp-content/uploads/2017/10/mblogo-3.png';
		echo get_attachment_id($url).'</br> -> URL';
		*/

		add_filter('wccpf/upload/type=file', function($file){

			if ( !function_exists( 'wp_handle_upload' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
			}
			$movefile = wp_handle_upload( $uploadedfile, array( 'test_form' => false ) );
			return $movefile;
		}, 9);



		?>
	 <!-- Product Container Begin -->
	 <div>
		 <!-- Uploaded Image and Product Name -->
		<div>
			<!-- This image is the user's uploaded image if it exists -->
			<!-- TODO: Add an if statement that replaces it with the product image -->
			<img src="<?php echo $etikett_url; ?>" width="150" />
			<!-- The product name e.g Gin -->
			<p>
				<?php echo $product->get_name(); ?>
			</p>
		</div>
		<!-- Bottle Type & Image -->
		<div>
			<?php
				echo $flasche_image_html;
				// The Name is included in the flasche_image_html
			?>
		</div>
		<!-- The Add to Cart Button & Quantity, contains custom code to make sure that the file upload box is hidden and the form can post the right data -->
		<!-- TODO: Find a way to send the file url  -->
		<form method="post" data-product_id="<?php echo absint( $product->get_id() ); ?>" enctype='multipart/form-data' >
				<?php
				// This automatically adds the file upload box, and removes the up/down arrows for the quantity
				// File upload box is hidden later in the JavaScript
				 do_action( 'woocommerce_before_add_to_cart_button' ); ?>
				<div class="single_variation_wrap">
					<?php
						woocommerce_single_variation_add_to_cart_button();
						// Edit Inital Variation Id
						//<input type="hidden" name="variation_id" class="variation_id" value="0">
						// Script will on page load, add in the correct data and remove the upload field
						echo "
						<script>

							document.addEventListener('DOMContentLoaded', function(){

								var el = document.querySelectorAll('input[name=\"variation_id\"]');
								el[0].value =		'".$last_order['variation_id'].
								"';
								var al =  document.querySelectorAll('input[name=\"quantity\"]');
								al[0].value =		'".$last_order['quantity'].
								"';
								document.getElementsByClassName('wccpf-fields-group-1')[0].style.display = 'none';

							});

						</script>";
						// This will add in the rest of the correct data
						foreach($variation_data as $variation){
							if (substr($variation->key, 0, 2) == 'pa'){
								echo '<input type="hidden" name="attribute_'.$variation->key.'" value="'.$variation->value.'">';
							}
						}
						?>
					</div>

								<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

							<!-- <?php //do_action( 'woocommerce_after_variations_form' ); ?> -->
						</form>

	 </div>
	 <!-- Product Container End -->

						<?php
						// do_action( 'woocommerce_after_add_to_cart_form' );








		// Show the other products
		echo '<h4>'.__('Other', 'woocommerce').'</h4>';

		foreach ($orders as $order){
			// Used as a temporary separator
			echo 'Order : </br>';
			foreach($order as $order_product){
				// Get all of the variation data and the flasche image
				$variation_data = $order_product->get_meta_data();
				$flasche_image_html = get_flasche_image($order_product->get_meta('pa_flasche'));



				$product = new WC_Product_Variable($order_product['product_id']);
				$etikett_url= $order_product->get_meta('Etikett hochladen');
				$etikett_image_html = '<img src="'.$etikett_url.'" width="150" />';

				?>
				<!-- Product Container Begin -->
		 	 <div>
		 		 <!-- Uploaded Image and Product Name -->
		 		<div>
		 			<!-- This image is the user's uploaded image if it exists
					Otherwise use the default product image
				 -->
					<?php
						if(empty($etikett_url)){
							echo $product->get_image();
						}else {
							echo $etikett_image_html;
						}
					?>
		 			<!-- The product name e.g Gin -->
		 			<p>
		 				<?php echo $product->get_name(); ?>
		 			</p>
		 		</div>
		 		<!-- Bottle Type & Image -->
		 		<div>
		 			<?php
		 				echo $flasche_image_html;
		 				// The Name is included in the flasche_image_html
		 			?>
		 		</div>
		 		<!-- The Add to Cart Button & Quantity, contains custom code to make sure that the file upload box is hidden and the form can post the right data -->
		 		<!-- TODO: Find a way to send the file url  -->
				<div>
		 			<form method="post" data-product_id="<?php echo absint( $product->get_id() ); ?>" enctype='multipart/form-data' >
			 				<?php
			 				// This automatically adds the file upload box, and removes the up/down arrows for the quantity
			 				// File upload box is hidden later in the JavaScript
		 				 	do_action( 'woocommerce_before_add_to_cart_button' ); ?>
			 				<div class="single_variation_wrap">
			 					<?php
			 						woocommerce_single_variation_add_to_cart_button();
			 						// Edit Inital Variation Id
			 						//<input type="hidden" name="variation_id" class="variation_id" value="0">
			 						// Script will on page load, add in the correct data and remove the upload field
			 						echo "
			 						<script>

			 							document.addEventListener('DOMContentLoaded', function(){

			 								var el = document.querySelectorAll('input[name=\"variation_id\"]');
			 								el[0].value =		'".$last_order['variation_id'].
			 								"';
			 								var al =  document.querySelectorAll('input[name=\"quantity\"]');
			 								al[0].value =		'".$last_order['quantity'].
			 								"';
			 								var hide = document.querySelectorAll('.wccpf-fields-group-1').forEach(function(x){x.style.display = 'none';});



			 							});

			 						</script>";
			 						// This will add in the rest of the correct data
			 						foreach($variation_data as $variation){
			 							if (substr($variation->key, 0, 2) == 'pa'){
			 								echo '<input type="hidden" name="attribute_'.$variation->key.'" value="'.$variation->value.'">';
			 							}
			 						}
			 						?>
		 						</div>

		 								<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

		 							<!-- <?php //do_action( 'woocommerce_after_variations_form' ); ?> -->
		 					</form>
			 			</div>
		 	 </div>
			 <!-- Product Container End -->
			 <?php

			}
		}




        wp_reset_postdata();

    ?>


<?php
	/**
	 * My Account dashboard.
	 *
	 * @since 2.6.0
	 */
	do_action( 'woocommerce_account_dashboard' );

	/**
	 * Deprecated woocommerce_before_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_before_my_account' );

	/**
	 * Deprecated woocommerce_after_my_account action.
	 *
	 * @deprecated 2.6.0
	 */
	do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
function get_image_id( $url ) {

	// Split the $url into two parts with the wp-content directory as the separator
	$parsed_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );
	// Get the host of the current site and the host of the $url, ignoring www
	$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
	$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

	// Return nothing if there aren't any $url parts or if the current host and $url host do not match
	if ( ! isset( $parsed_url[1] ) || empty( $parsed_url[1] ) || ( $this_host != $file_host ) ) {
		return;
	}

	global $wpdb;
	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parsed_url[1] ) );
	// Returns null if no attachment is found
	return $attachment[0];

}

function get_flasche_image($flasche_name){
	$term = get_term_by(
		'name',
		$flasche_name,
		'pa_flasche'
	);

	$stage_image = $image_html = '';
	$image_url = get_option( 'mspc_variation_image_'. $term->term_id );
	if( $image_url !== false && !empty($image_url) ) {
			$image_id = get_image_id( $image_url );
			if( !is_null($image_id) ) {
				$stage_image = wp_get_attachment_image_src($image_id, 'shop_single' );
				$stage_image = $stage_image[0];

			}
			else {
				$stage_image = $image_url;
			}

			$image_thumb =  $stage_image;
			$image_html = '<div>

			<img src="'.$image_thumb.'" alt="'.$term->name.'" class="mspc-attribute-image rounded ui image" style="width: 15%;"/> <p>
			'.$term->name.'
			</p></div>';
	} else{
		$image_html ='<div>
		<p>
		'.$term->name.'(no image available)
		</p>
		</div>';
	}
	return $image_html;
}


    /**
     * Get the Attachment ID for a given image URL.
     *
     * @link   http://wordpress.stackexchange.com/a/7094
     *
     * @param  string $url
     *
     * @return boolean|integer
     */
		 /*
    function get_attachment_id( $url ) {

        $dir = wp_upload_dir();

        // baseurl never has a trailing slash
        if ( false === strpos( $url, $dir['baseurl'] . '/' ) ) {
            // URL points to a place outside of upload directory

            return false;
        }

        $file  = basename( $url );
        $query = array(
            'post_type'  => 'attachment',
            'fields'     => 'ids',
            'meta_query' => array(
                array(
                    'key'     => '_wp_attached_file',
                    'value'   => $file,
                    'compare' => 'LIKE',
                ),
            )
        );

        // query attachments
        $ids = get_posts( $query );

        if ( ! empty( $ids ) ) {

            foreach ( $ids as $id ) {

                // first entry of returned array is the URL
                if ( $url === array_shift( wp_get_attachment_image_src( $id, 'full' ) ) )
                    return $id;
            }
        }

        $query['meta_query'][0]['key'] = '_wp_attachment_metadata';

        // query attachments again
        $ids = get_posts( $query );

        if ( empty( $ids) )
            return false;

        foreach ( $ids as $id ) {

            $meta = wp_get_attachment_metadata( $id );

            foreach ( $meta['sizes'] as $size => $values ) {

                if ( $values['file'] === $file && $url === array_shift( wp_get_attachment_image_src( $id, $size ) ) )
                    return $id;
            }
        }

        return false;
    }
*/
