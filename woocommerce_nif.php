<?php
/**
 * Plugin Name: NIF (Num. de Contribuinte Português) for WooCommerce
 * Plugin URI: https://www.webdados.pt/produtos-e-servicos/internet/desenvolvimento-wordpress/nif-de-contribuinte-portugues-woocommerce-wordpress/
 * Description: This plugin adds the Portuguese VAT identification number (NIF/NIPC) as a new field to WooCommerce checkout and order details, if the billing address is from Portugal.
 * Version: 4.2
 * Author: Webdados
 * Author URI: https://www.webdados.pt
 * Text Domain: nif-num-de-contribuinte-portugues-for-woocommerce
 * Domain Path: /lang
 * WC tested up to: 3.5.5
**/

/* WooCommerce CRUD ready */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 **/
// Get active network plugins - "Stolen" from Novalnet Payment Gateway
function nif_active_nw_plugins() {
	if ( !is_multisite() )
		return false;
	$nif_activePlugins = ( get_site_option( 'active_sitewide_plugins' ) ) ? array_keys( get_site_option( 'active_sitewide_plugins' ) ) : array();
	return $nif_activePlugins;
}
if ( in_array( 'woocommerce/woocommerce.php', (array) get_option( 'active_plugins ') ) || in_array( 'woocommerce/woocommerce.php', (array) nif_active_nw_plugins() ) ) {

	//Languages
	add_action( 'plugins_loaded', 'woocommerce_nif_init' );
	function woocommerce_nif_init() {
		//load_plugin_textdomain('nif-num-de-contribuinte-portugues-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/lang/');
		load_plugin_textdomain( 'nif-num-de-contribuinte-portugues-for-woocommerce' );
		add_action( 'wp_enqueue_scripts', 'woocommerce_nif_billing_fields_enqueue_scripts' );
	}

	//Javascript
	function woocommerce_nif_billing_fields_enqueue_scripts() {		
		//if ( is_checkout() && apply_filters( 'woocommerce_nif_use_javascript', false ) ) { //Default = NO Javascript (3.3)
		if ( is_checkout() && apply_filters( 'woocommerce_nif_use_javascript', true ) ) { //Default = USE Javascript (4.0)
			if ( !apply_filters( 'woocommerce_nif_show_all_countries', false ) ) { //If we want it on all countries, we shouldn't load the javascript
				wp_enqueue_script( 'woocommerce-nif', plugins_url( 'js/functions.js', __FILE__ ), array( 'jquery' ) );
			}
		}
	}
	
	//Add field to billing address fields - Globally
	add_filter( 'woocommerce_billing_fields', 'woocommerce_nif_billing_fields', 10, 2 );
	function woocommerce_nif_billing_fields( $fields, $country ) {
		//if( $country == 'PT' || apply_filters( 'woocommerce_nif_use_javascript', false ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) { //Default = NO Javascript (3.3)
		//if( $country == 'PT' || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) { //Default = USE Javascript (4.0)
			$fields['billing_nif'] = array(
				'type'			=>	'text',
				'label'			=> apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'nif-num-de-contribuinte-portugues-for-woocommerce' ) ),
				'placeholder'	=> apply_filters( 'woocommerce_nif_field_placeholder', __( 'Portuguese VAT identification number', 'nif-num-de-contribuinte-portugues-for-woocommerce' ) ),
				'class'			=> apply_filters( 'woocommerce_nif_field_class', array( 'form-row-first' ) ), //Should be an option (?)
				'required'		=> (
										$country == 'EC' || apply_filters( 'woocommerce_nif_show_all_countries', false )
										?
										apply_filters( 'woocommerce_nif_field_required', false ) //Should be an option (?)
										:
										false
									),
				'clear'			=> apply_filters( 'woocommerce_nif_field_clear', true ), //Should be an option (?)
				'autocomplete'	=> apply_filters( 'woocommerce_nif_field_autocomplete', 'on' ),
				'priority'		=> apply_filters( 'woocommerce_nif_field_priority', 120 ), //WooCommerce should order by this parameter but it doesn't seem to be doing so
				'maxlength'		=> apply_filters( 'woocommerce_nif_field_maxlength', 13 ),
				'validate'		=> (
										$country == 'EC'
										?
										(
											apply_filters( 'woocommerce_nif_field_validate', true )
											?
											array( 'nif_ec' ) //Does nothing, actually - Validation is down there on the 'woocommerce_checkout_process' action
											:
											array()
										)
										:
										false
									),
			);
		//}
		return $fields;
	}

	//Add field to order admin panel
	add_filter( 'woocommerce_admin_billing_fields', 'woocommerce_nif_admin_billing_fields' );
	function woocommerce_nif_admin_billing_fields( $billing_fields ) {
		global $post;
		if ( $post->post_type == 'shop_order' ) {
			$order = new WC_Order( $post->ID );
			$countries = new WC_Countries();
			$billing_country = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_billing_country() : $order->billing_country;
			//Customer is portuguese or it's a new order ?
			if ( $billing_country == 'EC' || ( $billing_country == '' && $countries->get_base_country() == 'EC' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
				$billing_fields['nif'] = array(
					'label' => apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'nif-num-de-contribuinte-portugues-for-woocommerce' ) ),
				);
			}
		}
		return $billing_fields;
	}
	//Add field to ajax billing get_customer_details - See https://github.com/woothemes/woocommerce/commit/5c43b340027fc9dea78e15825f12191768f7d2ed
	add_action( 'admin_init', 'woocommerce_nif_admin_init_found_customer_details' );
	function woocommerce_nif_admin_init_found_customer_details() {
		if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
			add_filter( 'woocommerce_ajax_get_customer_details', 'woocommerce_nif_ajax_get_customer_details', 10, 3 );
		} else {
			add_filter( 'woocommerce_found_customer_details', 'woocommerce_nif_found_customer_details_old', 10, 3 );
		}
	}
	//Pre 3.0
	function woocommerce_nif_found_customer_details_old( $customer_data, $user_id, $type_to_load ) {
		if ( $type_to_load == 'billing' ) {
			if ( ( isset( $customer_data['billing_country'] ) && $customer_data['billing_country'] == 'EC' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
				$customer_data['billing_nif'] = get_user_meta( $user_id, $type_to_load . '_nif', true );
			}
		}
		return $customer_data;
	}
	//3.0 and above - See https://github.com/woocommerce/woocommerce/issues/12654
	function woocommerce_nif_ajax_get_customer_details( $customer_data, $customer, $user_id ) {
		if ( ( isset( $customer_data['billing']['country']) && $customer_data['billing']['country'] == 'EC' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
			$customer_data['billing']['nif'] = $customer->get_meta( 'billing_nif' );
		}
		return $customer_data;
	}

	//Add field to the admin user edit screen
	add_action( 'woocommerce_customer_meta_fields', 'woocommerce_nif_customer_meta_fields' );
	function woocommerce_nif_customer_meta_fields( $show_fields ) {
		if ( isset( $show_fields['billing'] ) && is_array( $show_fields['billing']['fields'] ) ) {
			$show_fields['billing']['fields']['billing_nif'] = array(
				'label' => apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'nif-num-de-contribuinte-portugues-for-woocommerce' ) ),
				'description' => apply_filters( 'woocommerce_nif_field_placeholder', __( 'Portuguese VAT identification number', 'nif-num-de-contribuinte-portugues-for-woocommerce' ) ),
			);
		}
		return $show_fields;
	}

	//Add field to customer details on the Thank You page
	add_action( 'woocommerce_order_details_after_customer_details', 'woocommerce_nif_order_details_after_customer_details' );
	function woocommerce_nif_order_details_after_customer_details( $order ) {
		$billing_country = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_billing_country() : $order->billing_country;
		$billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_meta( '_billing_nif' ) : $order->billing_nif;
		if ( ( $billing_country == 'EC' || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) && $billing_nif ) {
			?>
			<tr>
				<th><?php echo apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'nif-num-de-contribuinte-portugues-for-woocommerce' ) ); ?>:</th>
				<td><?php echo esc_html( $billing_nif ); ?></td>
			</tr>
			<?php
		}
	}

	//Add field to customer details on Emails
	add_filter( 'woocommerce_email_customer_details_fields', 'woocommerce_nif_email_customer_details_fields', 10, 3 );
	function woocommerce_nif_email_customer_details_fields( $fields, $sent_to_admin, $order ) {
		$billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_meta( '_billing_nif' ) : $order->billing_nif;
		if ( $billing_nif ) {
			$fields['billing_nif'] = array(
				'label' => apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'nif-num-de-contribuinte-portugues-for-woocommerce' ) ),
				'value' => wptexturize( $billing_nif )
			);
		}
		return $fields;
	}

	//Add field to the REST API
	add_filter( 'woocommerce_api_order_response', 'woocommerce_nif_woocommerce_api_order_response', 11, 2 ); //After WooCommerce own add_customer_data
	function woocommerce_nif_woocommerce_api_order_response( $order_data, $order ) {
		//Order
		if ( isset( $order_data['billing_address'] ) ) {
			$billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $order->get_meta( '_billing_nif' ) : $order->billing_nif;
			$order_data['billing_address']['nif'] = $billing_nif;
		}
		return $order_data;
	}
	add_filter( 'woocommerce_api_customer_response', 'woocommerce_nif_woocommerce_api_customer_response', 10, 2 );
	function woocommerce_nif_woocommerce_api_customer_response( $customer_data, $customer ) {
		//Customer
		if ( isset( $customer_data['billing_address'] ) ) {
			$billing_nif = version_compare( WC_VERSION, '3.0', '>=' ) ? $customer->get_meta( 'billing_nif' ) : get_user_meta( $customer->get_id(), 'billing_nif', true );
			$customer_data['billing_address']['nif'] = $billing_nif;
		}
		return $customer_data;
	}

	//Validation - Checkout
	add_action( 'woocommerce_checkout_process', 'woocommerce_nif_checkout_process' );
	function woocommerce_nif_checkout_process() {
		if ( apply_filters( 'woocommerce_nif_field_validate', false ) ) {
			$customer_country = version_compare( WC_VERSION, '3.0', '>=' ) ? WC()->customer->get_billing_country() : WC()->customer->get_country();
			$countries = new WC_Countries();
			if ( $customer_country == 'EC' || ( $customer_country == '' && $countries->get_base_country() == 'EC' ) ) {
				$billing_nif = wc_clean( isset( $_POST['billing_nif'] ) ? $_POST['billing_nif'] : '' );
				if ( woocommerce_valida_nif( $billing_nif, true ) || ( trim( $billing_nif ) == '' &&  !apply_filters( 'woocommerce_nif_field_required', false ) ) ) { //If the field is NOT required and it's empty, we shouldn't validate it
					//OK
				} else {
					wc_add_notice(
						sprintf( __( 'You have entered an invalid %s for Portugal.', 'nif-num-de-contribuinte-portugues-for-woocommerce' ), '<strong>'.apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'nif-num-de-contribuinte-portugues-for-woocommerce' ) ).'</strong>' ),
						'error'
					);
				}
			} else {
				//Not Portugal
			}
		} else {
			//All good - No validation required
		}
	}

	//Validation - Save address
	add_action( 'woocommerce_after_save_address_validation', 'woocommerce_nif_after_save_address_validation', 10, 3 );
	function woocommerce_nif_after_save_address_validation( $user_id, $load_address, $address ) {
		if ( $load_address == 'billing' ) {
			if ( apply_filters( 'woocommerce_nif_field_validate', false ) ) {
				$country = wc_clean( isset( $_POST['billing_country'] ) ? $_POST['billing_country'] : '' );
				if ( $country == 'EC' ) {
					$billing_nif = wc_clean( isset( $_POST['billing_nif'] ) ? $_POST['billing_nif'] : '' );
					if ( woocommerce_valida_nif( $billing_nif, true ) || ( trim( $billing_nif ) == '' &&  !apply_filters( 'woocommerce_nif_field_required', false ) ) ) { //If the field is NOT required and it's empty, we shouldn't validate it
					//OK
					} else {
						wc_add_notice(
							sprintf( __( 'You have entered an invalid %s for Portugal.', 'nif-num-de-contribuinte-portugues-for-woocommerce' ), '<strong>'.apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'nif-num-de-contribuinte-portugues-for-woocommerce' ) ).'</strong>' ),
							'error'
						);
					}
				}
			}
		}
	}

	//NIF Validation
	function woocommerce_valida_nif( $nif, $ignoreFirst = true ) {
		//Limpamos eventuais espaços a mais
		$nif = trim( $nif );
		//Verificamos se é numérico e tem comprimento 9
		if ( !is_numeric( $nif ) || strlen( $nif ) != 9 ) {
			return false;
		} else {
			$nifSplit = str_split( $nif );
			//O primeiro digíto tem de ser 1, 2, 5, 6, 8 ou 9
			//Ou não, se optarmos por ignorar esta "regra"
			if (
				in_array( $nifSplit[0], array( 1, 2, 5, 6, 8, 9 ) )
				||
				$ignoreFirst
			) {
				//Calculamos o dígito de controlo
				$checkDigit=0;
				for( $i=0; $i<8; $i++ ) {
					$checkDigit += $nifSplit[$i] * ( 10-$i-1 );
				}
				$checkDigit = 11 - ( $checkDigit % 11 );
				//Se der 10 então o dígito de controlo tem de ser 0
				if( $checkDigit >= 10 ) $checkDigit = 0;
				//Comparamos com o último dígito
				if ( $checkDigit == $nifSplit[8] ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}

	if ( ( ! defined( 'WEBDADOS_INVOICEXPRESS_NAG' ) ) && empty( get_transient( 'webdados_invoicexpress_nag' ) ) ) {
		define( 'WEBDADOS_INVOICEXPRESS_NAG', true );
		require_once( 'webdados_invoicexpress_nag.php' );
	}

	/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
	
} 