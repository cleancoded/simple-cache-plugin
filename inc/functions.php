<?php
/**
 * Utility functions for plugin
 *
 * @package  cleancoded-simple-cache
 */

/**
 * Clear the cache
 *
 * @param  bool $network_wide Flush all site caches
 * @since  1.4
 */
function Cleancoded_cache_flush( $network_wide = false ) {
	$paths = array();

	if ( $network_wide && Cleancoded_IS_NETWORK ) {
		$sites = get_sites();

		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );

			$url_parts = wp_parse_url( home_url() );

			$path = Cleancoded_get_cache_dir() . '/' . untrailingslashit( $url_parts['host'] ) . '/';

			if ( ! empty( $url_parts['path'] ) && '/' !== $url_parts['path'] ) {
				$path .= trim( $url_parts['path'], '/' );
			}

			$paths[] = $path;

			restore_current_blog();
		}
	} else {
		$url_parts = wp_parse_url( home_url() );

		$path = Cleancoded_get_cache_dir() . '/' . untrailingslashit( $url_parts['host'] ) . '/';

		if ( ! empty( $url_parts['path'] ) && '/' !== $url_parts['path'] ) {
			$path .= trim( $url_parts['path'], '/' );
		}

		$paths[] = $path;
	}

	foreach ( $paths as $rm_path ) {
		Cleancoded_rrmdir( $rm_path );
	}

	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
}

/**
 * Verify we can write to the file system
 *
 * @since  1.7
 * @return array|boolean
 */
function Cleancoded_verify_file_access() {
	if ( function_exists( 'clearstatcache' ) ) {
		@clearstatcache();
	}

	$errors = array();

	if ( ! apply_filters( 'Cleancoded_disable_auto_edits', false ) ) {
		// First check wp-config.php.
		if ( ! @is_writable( ABSPATH . 'wp-config.php' ) && ! @is_writable( ABSPATH . '../wp-config.php' ) ) {
			$errors[] = 'wp-config';
		}

		// Now check wp-content
		if ( ! @is_writable( untrailingslashit( WP_CONTENT_DIR ) ) ) {
			$errors[] = 'wp-content';
		}

		// Make sure config directory or parent is writeable
		if ( file_exists( Cleancoded_get_config_dir() ) ) {
			if ( ! @is_writable( Cleancoded_get_config_dir() ) ) {
				$errors[] = 'config';
			}
		} else {
			if ( file_exists( dirname( Cleancoded_get_config_dir() ) ) ) {
				if ( ! @is_writable( dirname( Cleancoded_get_config_dir() ) ) ) {
					$errors[] = 'config';
				}
			} else {
				$errors[] = 'config';
			}
		}
	}

	// Make sure cache directory or parent is writeable
	if ( file_exists( Cleancoded_get_cache_dir() ) ) {
		if ( ! @is_writable( Cleancoded_get_cache_dir() ) ) {
			$errors[] = 'cache';
		}
	} else {
		if ( file_exists( dirname( Cleancoded_get_cache_dir() ) ) ) {
			if ( ! @is_writable( dirname( Cleancoded_get_cache_dir() ) ) ) {
				$errors[] = 'cache';
			}
		} else {
			if ( file_exists( dirname( dirname( Cleancoded_get_cache_dir() ) ) ) ) {
				if ( ! @is_writable( dirname( dirname( Cleancoded_get_cache_dir() ) ) ) ) {
					$errors[] = 'cache';
				}
			} else {
				$errors[] = 'cache';
			}
		}
	}

	if ( ! empty( $errors ) ) {
		return $errors;
	}

	return true;
}

/**
 * Remove directory and all it's contents
 *
 * @param  string $dir Directory
 * @since  1.7
 */
function Cleancoded_rrmdir( $dir ) {
	if ( is_dir( $dir ) ) {
		$objects = scandir( $dir );

		foreach ( $objects as $object ) {
			if ( '.' !== $object && '..' !== $object ) {
				if ( is_dir( $dir . '/' . $object ) ) {
					Cleancoded_rrmdir( $dir . '/' . $object );
				} else {
					unlink( $dir . '/' . $object );
				}
			}
		}

		rmdir( $dir );
	}
}
