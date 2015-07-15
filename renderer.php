<?php
/**
 * Moodec Renderer
 *
 * @package    local_moodec
 * @copyright  2015 Thomas Threadgold <tj.threadgold@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Moodec Renderer
 *
 * @copyright  2015 Thomas Threadgold <tj.threadgold@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_moodec_renderer extends plugin_renderer_base {


	/**
	 * Outputs the information for the single product page
	 * @param  product 	$product  	object containing all the product information
	 * @return string          		the html output
	 */
	function single_product($product) {
		global $CFG, $DB;

		// Require Moodec lib
		require_once $CFG->dirroot . '/local/moodec/lib.php';

		$cart = local_moodec_get_cart();

		// Product single wrapper
		$html = '<div class="product-single">';

			// Product title
			$html .= sprintf(
				'<h1 class="product__title">%s</h1>',
				get_string('product_title', 'local_moodec', array('coursename' => $product->fullname))
			);

			// Product/course image
			if (!!get_config('local_moodec', 'page_product_show_image')) {
				$html .= $this->product_image($product);
			}

			// Product details wrapper
			$html .= '<div class="product-details">';

				// Product description
				if (!!get_config('local_moodec', 'page_product_show_description')) {

					$html .= sprintf(
						'<div class="product-description">%s</div>',
						local_moodec_format_course_summary($product->courseid)
					);

				}

				// Product additional description
				if (!!get_config('local_moodec', 'page_product_show_additional_description')) {

					$html .= sprintf(
						'<div class="additional-info">%s</div>',
						$product->additional_info
					);

				}

				// Additional product details wrapper
				$html .= '<div class="product-details__additional">';

					// Product duration
					$html .= '<div class="product-duration__wrapper">';

						$html .= sprintf(
							'<span class="product-duration__label">%s</span>',
							get_string('enrolment_duration_label', 'local_moodec')
						);

						if( $product->pricing_model === 'simple') {

							$html .= sprintf(
								'<span class="product-duration">%s</span>',
								local_moodec_format_enrolment_duration($product->enrolment_duration)
							);
						} else {
							$attr = '';

							foreach ($product->variations as $v) {
								$attr .= sprintf('data-tier-%d="%s" ',
									$v->variation_id,
									local_moodec_format_enrolment_duration($v->enrolment_duration)
								);
							}

							$firstVariation = reset($product->variations);

							$html .= sprintf(
								'<span class="product-duration" %s>%s</span>',
								$attr,
								local_moodec_format_enrolment_duration($firstVariation->enrolment_duration)
							);
						}

					$html .= '</div>';


					// Product category
					if (!!get_config('local_moodec', 'page_catalogue_show_category')) {

						// Get the category the product belongs to
						$category = $DB->get_record(
							'course_categories',
							array(
								'id' => $product->category
							)
						);

						// Get the url to link to the category page with the filter active
						$categoryURL = new moodle_url(
							$CFG->wwwroot . '/local/moodec/pages/catalogue.php',
							array(
								'category' => $product->category
							)
						);

						// Category wrapper
						$html .= '<div class="product-category__wrapper">';

							// Category label
							$html .= sprintf(
								'<span class="product-category__label">%s</span> ',
								get_string('course_list_category_label', 'local_moodec')
							);

							// Category link
							$html .= sprintf(
								'<a class="product-category__link" href="%s">%s</a>',
								$categoryURL,
								$category->name
							);

						$html .= '</div>';

					}

					// Product Price
					if($product->pricing_model === 'simple') {

						$html .= '<div class="product-price">';

							// Price label
							$html .= sprintf(
								'<span class="product-price__label">%s</span>',
								get_string('price_label', 'local_moodec')
							);

							// Price
							$html .= sprintf(
								'<span class="product-price__value">%s<span class="amount">%.2f</span></span>',
								local_moodec_get_currency_symbol(get_config('local_moodec', 'currency')),
								$product->price
							);

						$html .= '</div>';

					} else {

						$attr = '';

						foreach ($product->variations as $v) {
							$attr .= sprintf('data-tier-%d="%.2f" ', $v->variation_id, $v->price);
						}

						$firstVariation = reset($product->variations);

						$html .= sprintf(
							'<div class="product-price" %s><span class="product-price__label">%s</span> <span class="product-price__value">%s<span class="amount">%.2f</span></span></div>',
							$attr,
							get_string('price_label', 'local_moodec'),
							local_moodec_get_currency_symbol(get_config('local_moodec', 'currency')),
							$firstVariation->price
						);
					}

					// Add to cart button states
					if (isloggedin() && is_enrolled(context_course::instance($product->courseid, MUST_EXIST))) {

						// Display 'enrolled' button
						$html .= sprintf(
							'<div class="product-single__form">
								<button class="product-form__add button--enrolled" disabled="disabled">%s</button>
							</div>',
							get_string('button_enrolled_label', 'local_moodec')
						);

					} else if (is_array($cart['courses']) && array_key_exists($product->courseid, $cart['courses'])) {

						// Display 'in cart' button
						$html .= sprintf(
							'<div class="product-single__form">
								<button class="product-form__add button--cart" disabled="disabled">%s</button>
							</div>',
							get_string('button_in_cart_label', 'local_moodec')
						);

					} else {

						// Check whether this is a simple or variable product
						if($product->pricing_model === 'simple') {

							// Display simple product 'add to cart' form
							$html .= sprintf(
								'<form action="%s" method="POST" class="product-single__form">
									<input type="hidden" name="action" value="addToCart">
									<input type="hidden" name="id" value="%d">
									<input type="submit" class="product-form__add" value="%s">
								</form>',
								new moodle_url('/local/moodec/pages/cart.php'),
								$product->courseid,
								get_string('button_add_label', 'local_moodec')
							);

						} else {

							// Variable product selection 'add to cart' form
							$html .= sprintf(
								'<form action="%s" method="POST" class="product-single__form">
									<input type="hidden" name="action" value="addVariationToCart">
									<select class="product-tier" name="variation">',
								new moodle_url('/local/moodec/pages/cart.php')
							);

							// output variations
							foreach($product->variations as $variation) {

								$html .= sprintf(
									'<option value="%d">%s</option>',
									$variation->variation_id,
									$variation->name
								);

							}

							// output rest of the form
							$html .= sprintf(
								'	</select>
									<input type="hidden" name="id" value="%d">
									<input type="submit" class="product-form__add" value="%s">
								</form>',
								$product->courseid,
								get_string('button_add_label', 'local_moodec')
							);

						}
					}


				// close additional product details wrapper
				$html .= '</div>';

			// close product details wrapper
			$html .= '</div>';

		// close product single wrapper
		$html .= '</div>';

		return $html;
	}


	/**
	 * Outputs the HTML to display related products given a product
	 * @param  product 	$product  	the product for which to find the related ones
	 * @return string           	the HTML output
	 */
	function related_products($product) {
		global $CFG;

		// Require Moodec lib
		require_once $CFG->dirroot . '/local/moodec/lib.php';

		$html = '';
		$iterator = 0;

		// Get products related to the product passed to us
		$products = local_moodec_get_related_products($product->courseid, $product->category);

		// We only output anything if there ARE related products
		if (is_array($products) && 0 < count($products)) {

			// Output section wrapper
			$html .= '<div class="related-products">';

				// Show the section title
				$html .= sprintf(
					'<h2 class="related-products__title">%s</h2>',
					get_string('product_related_label', 'local_moodec')
				);

				// Output container to hold product items
				$html .= '<ul class="grid-container">';

				foreach ($products as $product) {

					$html .= '<li class="grid-item">';

						// Product image
						$html .= $this->product_image($product);

						// Product title
						$html .= sprintf(
							'<h5>%s</h5>',
							$product->fullname
						);

						// Product link
						$html .= sprintf(
							'<a href="%s" class="product-view btn">%s</a>',
							new moodle_url('/local/moodec/pages/product.php', array('id' => $product->courseid)),
							get_string('product_related_button_label', 'local_moodec')
						);

					$html .= '</li>';

					// Iterator limits only 3 products to be shown
					$iterator++;
					if ($iterator > 2) {
						break;
					}
				}

				// Close item container
				$html .= '</ul>';

			// Close section wrapper
			$html .= '</div>';
		}

		return $html;
	}


	/**
	 * Returns the HTML for the product image
	 * @param  product 	$product 	the product for the image to be retrieved
	 * @return string          		the HTML output
	 */
	function product_image($product) {
		global $CFG;

		// Require Moodec lib
		require_once $CFG->dirroot . '/local/moodec/lib.php';

		$html = '';
		$imageURL = local_moodec_get_course_image_url($product->courseid);

		if ( !!$imageURL ) {
			$html = sprintf(
				'<img src="%s" alt="%s" class="product-image">',
				$imageURL,
				$product->fullname
			);
		}

		return $html;
	}

	/**
	* Returns the HTML for the Moodec cart
	* @param 	array 		cart
	* @param 	bool 		is it the checkout page
	* @return 	string 		the HTML output
	*/
	function moodec_cart($cart, $checkout = false, $removedProducts = array()) {
		global $CFG, $USER;

		// Require Moodec lib
		require_once $CFG->dirroot . '/local/moodec/lib.php';

		// Initialise vars
		$html = '';
		$ipnData = sprintf('U:%d', $USER->id);
		$itemCount = 1;

		$html .= '<div class="cart-overview">';
		
		// Render only on checkout page
		if( !!$checkout ) {

			// Output cart review message
			$html .= sprintf(
				'<p class="cart-review__message">%s</p>',
				get_string('checkout_message', 'local_moodec')
			);

			if (!!$removedProducts && is_array($removedProducts)) {
				
				$html .= sprintf(
					'<p class="cart-review__message--removed">%s</p>', 
					get_string('checkout_removed_courses_label', 'local_moodec')
				);

				$html .= '<ul>';

				foreach ($removedProducts as $product) {
					$thisCourse = get_course($product);
					
					$html .= sprintf(
						'<li class="cart-review__item--removed">%s</li>', 
						$thisCourse->fullname
					);
				}

				$html .= '</ul>';
			}

			$html .= sprintf(
				'<form class="cart-review" action="%s" method="post">',
				// TODO: make this a sandbox setting?
				'https://www.paypal.com/cgi-bin/webscr' 
			);
		}

		if (is_array($cart['courses']) && 0 < count($cart['courses'])) {

			// Output required paypal fields
			if( !!$checkout ) {
				$html .= $this->paypal_fields();
			}

			$html .= '<ul class="products">';

			// Go through each product in the cart
			foreach ($cart['courses'] as $courseid => $variation) {

				$product = local_moodec_get_product($courseid);

				$html .= '<li class="product-item">';

					// Product title and variation
					$html .= sprintf(
						'<h4 class="product-title"><a href="%s">%s</a></h4>',
						new moodle_url('/local/moodec/pages/product.php', array('id'=>$courseid)),
						$variation === 0 ? $product->fullname : $product->fullname . ' - ' . $product->variations[$variation]->name
					);

					// Product price
					$html .= sprintf(
						'<div class="product-price">%s%.02f</div>',
						local_moodec_get_currency_symbol(get_config('local_moodec', 'currency')),
						$variation === 0 ? $product->price : $product->variations[$variation]->price
					);

					if( !!$checkout ) {

						// Output the hidden paypal fields for this product info
						$html .= $this->paypal_product_info($product, $variation, $itemCount);

					} else {

						// 'Remove' from cart button
						$html .= sprintf(
							'<form class="product__form" action="" method="POST">
								<input type="hidden" name="id" value="%d">
								<input type="hidden" name="action" value="removeFromCart">
								<input class="form__submit" type="submit" value="%s">
							</form>',
							$courseid,
							get_string('button_remove_label', 'local_moodec')
						);

					}

				$html .= '</li>';

				$ipnData .= sprintf('|C:%d,V:%d', $courseid, $variation);
				$itemCount++;
			}

			$html .= '</ul>';

			// Output cart summary section
			$html .= '<div class="cart-summary">';

				// Cart total price
				$html .= sprintf(
					'<h3 class="cart-total__label">%s</h3><h3 class="cart-total">%s%0.2f</h3>',
					get_string('cart_total', 'local_moodec'),
					local_moodec_get_currency_symbol(get_config('local_moodec', 'currency')),
					local_moodec_cart_get_total()
				);

			$html .= '</div>';

			if( !!$checkout ) {
				// Get the checkout action HTML
				$html .= $this->checkout_actions($ipnData);

				// Close the checkout form so we can open another below for 
				// the return to store button
				$html .= '</form>';

				// Return to store button
				$html .= $this->return_to_store_action();

			} else {
				// Get the cart action HTML
				$html .= $this->cart_actions();
			}

		} else {

			// Empty cart message
			$html .= sprintf(
				'<p class="cart-mesage--empty">%s</p>',
				get_string('cart_empty_message', 'local_moodec')
			);

			// Return to store button
			$html .= $this->return_to_store_action();

		}

		$html .= '</div>';

		return $html;
	}	


	/**
	 * Returns the HTML output for the standard cart actions
	 * @return string  	HTML
	 */	
	function cart_actions() {
		$html = '<div class="cart-actions">';

			// Return to store button
			$html .= $this->return_to_store_action();

			// Proceed to checkout button
			$html .= sprintf(
				'<form action="%s" method="GET">
					<input type="submit" value="%s">
				</form>',
				new moodle_url('/local/moodec/pages/checkout.php'),
				get_string('button_checkout_label', 'local_moodec')
			);

		$html .= '</div>';

		return $html;
	}

	/**
	 * Returns the HTML output for the checkout actions
	 * @param  string  $ipnData  The IPN data required for Paypal
	 * @return string        	 HTML
	 */
	function checkout_actions($ipnData){
		$html = '<div class="cart-actions">';

			// Output proceed to paypal button
			$html .= sprintf(
				'<input type="hidden" name="custom" value="%s">
				<input type="submit" name="submit"  value="%s">',
				$ipnData,
				get_string('button_paypal_label', 'local_moodec')
			);

		$html .= '</div>';

		return $html;
	}


	/**
	 * Returns the HTML output for the return to store button
	 * @return string 	HTML
	 */	
	function return_to_store_action() {
		// Return to store button
		return sprintf(
			'<form action="%s" method="GET" class="back-to-shop">
				<input type="submit" value="%s">
			</form>',
			new moodle_url('/local/moodec/pages/catalogue.php'),
			get_string('button_return_store_label', 'local_moodec')
		);
	}

	/**
	 * Returns the Paypal input fields required
	 * @return string  HTML
	 */
	function paypal_fields(){
		return sprintf(
			'<input type="hidden" name="cmd" value="_cart">
			<input type="hidden" name="charset" value="utf-8">
			<input type="hidden" name="upload" value="1">
			<input type="hidden" name="business" value="%s">
			<input type="hidden" name="currency_code" value="%s">
			<input type="hidden" name="for_auction" value="false">
			<input type="hidden" name="no_note" value="1">
			<input type="hidden" name="no_shipping" value="1">
			<input type="hidden" name="notify_url" value="%s">
			<input type="hidden" name="return" value="%s">
			<input type="hidden" name="cancel_return" value="%s">',
			get_config('local_moodec', 'paypalbusiness'),
			get_config('local_moodec', 'currency'),
			new moodle_url('/local/moodec/ipn.php'),
			new moodle_url('/local/moodec/pages/catalogue.php'),
			new moodle_url('/local/moodec/pages/cart.php')
		);
	}


	/**
	 * Returns the Paypal item fields
	 * @param  product  $p  the product
	 * @param  int 		$v  the product variation id
	 * @param  int 		$i  the iterator value 
	 * @return string    	HTML
	 */
	function paypal_product_info($p, $v, $i) {

		// Paypal item name field
		$html = sprintf(
			'<input type="hidden" name="%s" value="%s">',
			'item_name_' . $i,
			$v === 0 ? $p->fullname : $p->fullname . ' - ' . $p->variations[$v]->name
		);

		// Paypal item amount field
		$html .= sprintf(
			'<input type="hidden" name="%s" value="%s">',
			'amount_' . $i,
			$v === 0 ? $p->price : $p->variations[$v]->price
		);

		return $html;
	}
}