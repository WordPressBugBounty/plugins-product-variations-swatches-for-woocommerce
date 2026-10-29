<?php
/**
 * Function include all files in folder
 *
 * @param $path   Directory address
 * @param $ext    array file extension what will include
 * @param $prefix string Class prefix
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'vi_include_folder' ) ) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	function vi_include_folder( $path, $prefix = '', $ext = array( 'php' ) ) {

		/*Include all files in payment folder*/
		if ( ! is_array( $ext ) ) {
			$ext = explode( ',', $ext );
			$ext = array_map( 'trim', $ext );
		}
		if ( ! is_dir( $path ) ) {
			return;
		}
		$sfiles = scandir( $path );
		if ( ! is_array( $sfiles ) ) {
			return;
		}
		foreach ( $sfiles as $sfile ) {
			if ( $sfile != '.' && $sfile != '..' ) {
				if ( is_file( $path . "/" . $sfile ) ) {
					$ext_file  = pathinfo( $path . "/" . $sfile );
					$file_name = $ext_file['filename'];
					if ( ! empty( $ext_file['extension'] ) ) {
						if ( in_array( $ext_file['extension'], $ext, true ) ) {
							$class = preg_replace( '/\W/i', '_', $prefix . ucfirst( $file_name ) );

							if ( ! class_exists( $class ) ) {
								require_once $path . $sfile;
								if ( class_exists( $class ) ) {
									new $class;
								}
							}
						}
					}
				}
			}
		}
	}
}
if ( ! function_exists( 'woocommerce_version_check' ) ) {
	function woocommerce_version_check( $version = '3.0' ) {
		global $woocommerce;

		if ( ! $woocommerce || empty( $woocommerce->version ) ) {
			return false;
		}

		if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
			return true;
		}

		return false;
	}
}

/**
 * Resolve WooCommerce script handle across WC 6.0–latest (WC 10.3+ uses wc-prefixed handles).
 *
 * @param string $new_handle    Prefixed handle (e.g. wc-select2).
 * @param string $legacy_handle Legacy handle (e.g. select2).
 *
 * @return string
 */
if ( ! function_exists( 'vi_wpvs_wc_script_handle' ) ) {
	function vi_wpvs_wc_script_handle( $new_handle, $legacy_handle ) {
		if ( wp_script_is( $new_handle, 'registered' ) ) {
			return $new_handle;
		}

		return $legacy_handle;
	}
}
if ( ! function_exists( 'vi_wpvs_sanitize_fields' ) ) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	function vi_wpvs_sanitize_fields( $data ) {
		if ( ! $data ) {
			return false;
		} elseif ( is_array( $data ) ) {
			return array_map( 'vi_wpvs_sanitize_fields', $data );
		} else {
			return sanitize_text_field( wp_unslash( $data ) );
		}
	}
}
if ( ! function_exists( 'vi_wpvs_sanitize_css_value' ) ) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	function vi_wpvs_sanitize_css_value( $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';

		return str_replace( array( "\n", "\r", '{', '}', '<', '>', '"', "'" ), '', $value );
	}
}
if ( ! function_exists( 'vi_wpvs_sanitize_css_color' ) ) {
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
	/**
	 * Sanitize a CSS color or simple gradient background value.
	 *
	 * @param mixed $value Raw color / gradient string.
	 *
	 * @return string
	 */
	function vi_wpvs_sanitize_css_color( $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';
		if ( '' === $value ) {
			return '';
		}
		if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value ) ) {
			return $value;
		}
		if ( preg_match( '/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+(?:\s*,\s*[\d.]+)?\s*\)$/', $value ) ) {
			return $value;
		}
		if ( preg_match( '/^[a-zA-Z]+$/', $value ) ) {
			return strtolower( $value );
		}
		if ( 0 === stripos( $value, 'linear-gradient(' ) ) {
			return vi_wpvs_sanitize_css_value( $value );
		}

		return vi_wpvs_sanitize_css_value( $value );
	}
}

