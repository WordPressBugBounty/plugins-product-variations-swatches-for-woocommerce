<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VI_WOO_PRODUCT_VARIATIONS_SWATCHES_Compatible_Dokan {
	protected static $cache=[];
	public function __construct() {
		add_action( 'dokan_product_edit_after_title', array( 'VI_WOO_PRODUCT_VARIATIONS_SWATCHES_Admin_Custom_Attribute', 'enqueue_scripts' ) );
		add_filter( 'wp_ajax_dokan_load_variations', array( $this, 'swatches_settings' ),9 );
		add_action( 'wp_ajax_dokan_save_attributes', [ $this, 'save_swatches_settings' ],9 );
	}
    public function save_swatches_settings(){
	    $post_id = isset( $_POST['post_id'] ) ? (int) sanitize_text_field(wp_unslash($_POST['post_id'])) :0;//phpcs:ignore WordPress.Security.NonceVerification.Missing
        parse_str($_POST['data']??'', $data );
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $data['viwpvs_nonce'] ) ), 'viwpvs-dokan-save-attributes' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( function_exists( 'dokan_get_vendor_by_product' ) ) {
            $vendor_id = dokan_get_vendor_by_product( $post_id, true );
            if ($vendor_id && (int) $vendor_id !== get_current_user_id() && ! current_user_can( 'manage_woocommerce' )) {
                return;
            }
        }
	    if ( !$post_id || empty($data['viwpvs_save_attribute']) || !is_array($data['viwpvs_save_attribute'])) {
		    return;
	    }
	    if ( empty( $data['attribute_names'] ) || empty( $data['attribute_values'] ) ) {
		    delete_post_meta($post_id,'_vi_woo_product_variation_swatches_product_attribute');
		    return;
	    }
        $viwpvs_save_attribute= $data['viwpvs_save_attribute'];
	    $vi_attribute_settings                                                 = get_post_meta( $post_id, '_vi_woo_product_variation_swatches_product_attribute', true );
	    $vi_attribute_settings                                                 = $vi_attribute_settings ? json_decode( $vi_attribute_settings, true ) : array();
        foreach ($viwpvs_save_attribute as $i => $v){
            if (empty($v)){
                continue;
            }
	        $attribute_name               = wc_clean( esc_html( $data['attribute_names'][ $i ]??'' ) );
            if (!$attribute_name){
                continue;
            }
	        $vi_attribute_type            = isset( $data['vi_attribute_type'][ $i ] ) ? wc_clean( $data['vi_attribute_type'][ $i ] ) : '';
	        $vi_attribute_profile         = isset( $data['vi_attribute_profile'][ $i ] ) ? wc_clean( $data['vi_attribute_profile'][ $i ] ) : '';
	        $vi_attribute_color_separator = isset( $data['vi_attribute_color_separator'][ $i ] ) ? wc_clean( $data['vi_attribute_color_separator'][ $i ] ) : '';
	        $vi_attribute_colors          = isset( $data['vi_attribute_colors'][ $i ] ) ? wc_clean( $data['vi_attribute_colors'][ $i ] ) : '';
	        $vi_attribute_images          = isset( $data['vi_attribute_images'][ $i ] ) ? wc_clean( $data['vi_attribute_images'][ $i ] ) : '';
	        $vi_attribute_display_type    = isset( $data['vi_attribute_display_type'][ $i ] ) ? wc_clean( $data['vi_attribute_display_type'][ $i ] ) : '';
	        if ( 'pa_' !== substr( $attribute_name, 0, 3 ) ) {
		        $attribute_name = html_entity_decode( $attribute_name, ENT_NOQUOTES, 'UTF-8' );
		        if (!empty($vi_attribute_colors) && is_array($vi_attribute_colors)){
			        $vi_attribute_colors = array_values($vi_attribute_colors);
		        }
	        }
	        $vi_attribute_settings['attribute_type'][ $attribute_name ]            = $vi_attribute_type ??'';
	        $vi_attribute_settings['attribute_profile'][ $attribute_name ]         = $vi_attribute_profile??'';
	        $vi_attribute_settings['attribute_color_separator'][ $attribute_name ] = $vi_attribute_color_separator??'';
	        $vi_attribute_settings['attribute_colors'][ $attribute_name ]          = $vi_attribute_colors??'';
	        $vi_attribute_settings['attribute_img_ids'][ $attribute_name ]         = $vi_attribute_images??'';
	        $vi_attribute_settings['attribute_display_type'][ $attribute_name ]    = $vi_attribute_display_type??'';
        }
	    $vi_attribute_settings                                                 = wp_json_encode( $vi_attribute_settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	    update_post_meta( $post_id, '_vi_woo_product_variation_swatches_product_attribute', $vi_attribute_settings );
    }
	public function swatches_settings(){
        check_ajax_referer( 'load-variations', 'security' );
        if ( ! current_user_can( 'dokandar' ) || empty( $_POST['product_id'] ) || empty( $_POST['attributes'] ) ) {
            die( -1 );
        }
		if (empty( $_POST['product_id'] ) || !empty(self::$cache['swatches_settings'])){
			return;
		}
		self::$cache['swatches_settings'] = true;
        $product_id = wc_clean(wp_unslash($_POST['product_id']));
		$product = wc_get_product($product_id);
        $attributes = $product->get_attributes();
		global $thepostid;
		$tmp_thepostid = $thepostid;
        $thepostid = $product_id;
        if (is_array($attributes) && !empty($attributes)){
            $i=0;
            foreach ($attributes as $attribute){
	            $attr_name ="attribute_values[{$i}][]";
	            ?>
                <div class="viwpvs-dokan-setting-wrap dokan-hide" data-name="<?php echo esc_attr($attr_name) ;?>">
                    <div class="dokan-clearfix"></div>
                    <div class="dokan-form-group">
			            <?php
			            VI_WOO_PRODUCT_VARIATIONS_SWATCHES_Admin_Custom_Attribute::after_product_attribute_settings($attribute, $i);
			            ?>
                    </div>
                </div>
	            <?php
                $i++;
            }
        }
		$thepostid = $tmp_thepostid;
	}
}