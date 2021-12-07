<?php
/*
Plugin Name: TDH Export
Plugin URI: https://wordpress.org/plugins/tdh-export/
Description:  Export TDH (Title-tag, meta Description, H-tag) with a single click.
Version: 1.0.4
Author: HORISAWA Kotaro
Author URI: https://github.com/horisawak/tdh-export/
License: GPL2
Text Domain: tdh-export
Domain Path: /languages
*/

/*
Copyright 2021 HORISAWA Kotaro (email : horisaworks@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// CSV File Name
define( 'TDH_EXPORT_CSV_FILE_NAME', 'tdh_export_result.csv' );
// CSV File Path
define( 'TDH_EXPORT_CSV_FILE_PATH', dirname( __FILE__ ) . DIRECTORY_SEPARATOR . TDH_EXPORT_CSV_FILE_NAME );
// CSV File Download URL
define( 'TDH_EXPORT_CSV_FILE_DOWNLOAD_URL', plugins_url( '', __FILE__ ) . DIRECTORY_SEPARATOR . TDH_EXPORT_CSV_FILE_NAME );

// Create an instance
$tdh_export_main = new TDH_Export_Main();

/*****************************
* TDH Export main class
*****************************/
class TDH_Export_Main {
	/*****************************
	 * constructor
	 *****************************/
	function __construct()
	{
		// Add TDH Plugin item to WP admin menu
		add_action( 'admin_menu', [$this, 'plugin_menu'] );
		// Load Multilingual
		add_action( 'plugins_loaded', [$this, 'plugin_textdomain'] );
	}

	/*****************************
	 * Multilingual
	 *****************************/
	public function plugin_textdomain() {
		load_plugin_textdomain( 'tdh-export', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/*****************************
	 * Add TDH Plugin item to WP admin menu
	 *****************************/
	function plugin_menu() {
		// Add TDH Plugin item to menu
		add_menu_page(
			'TDH Export',            // Page title
			'TDH Export',            // Menue title
			'administrator',         // Authority
			__FILE__,                // Slug
			[$this, 'plugin_view'],  // Callback function of view
			'dashicons-search'       // Icon
		);
	}

	/*****************************
	 * Display the management view
	 *****************************/
	function plugin_view() {
		// Load CSS
		wp_enqueue_style(
			'tdh-style',
			plugins_url( 'css/tdh-style.css', __FILE__ )
		);
		// Load Script
		wp_enqueue_script(
			'tdh-script',
			plugins_url( 'js/tdh-script.js', __FILE__ ),
			array( 'jquery' ),
			filemtime( plugin_dir_path( __FILE__ ) . 'js/tdh-script.js' ),
			true
		);
		?>
		<div class="wrap">
			<h1>TDH Export</h1>
			<ol>
				<li><?php _e('Check [Post Type] for which you want to export TDH.', 'tdh-export'); ?></li>
				<li><?php _e('Select [Export method].', 'tdh-export'); ?></li>
				<li><?php _e('Click [Export TDH].', 'tdh-export'); ?></li>
			</ol>
			<p>*<?php _e('If you are using authentication functions on your site, you may not be able to export TDH. In that case, please log in to the site once and try again without closing your browser.', 'tdh-export'); ?></p>
			<hr />
			<form name="tdh_form" action="" method="post" autocomplete="off">
				<?php wp_nonce_field('csv_export');?>
				<table class="form-table">
					<tbody>
						<tr>
							<th><?php _e('Post Type', 'tdh-export'); ?></th>
							<td>

							<?php
								// Remain flag
								$remain_post_type_flg = false;
								$remain_page_flg = false;
								$remain_post_flg = false;
								// sanitized post type (initial val: blank array)
								$sanitized_post_type_array = array();

								// Check if the "Post Type" that the user last operated remains
								if ( isset( $_POST['post_type'] ) && is_array( $_POST['post_type'] ) ) {
									// "Post type" remains
									$remain_post_type_flg = true;
									// sanitize the post type
									foreach ( $_POST['post_type'] as $post_type ) {
										$sanitized_post_type = sanitize_key( $post_type );
										array_push( $sanitized_post_type_array, $sanitized_post_type );
									}

									// Search if the "Page" was checked
									$key = array_search( 'page', $sanitized_post_type_array );
									// Check if the "Page" was checked
									if ( false !== $key ) { // Checked
										$remain_page_flg = true;
									} else { // Unchecked
										$remain_page_flg = false;
									}

									// Search if the "Post" was checked
									$key = array_search( 'post', $sanitized_post_type_array );
									// Check if the "Post" was checked
									if ( false !== $key ) { // Checked
										$remain_post_flg = true;
									} else { // Unchecked
										$remain_post_flg = false;
									}

								} else { // First time: Set 'checked'
									$remain_post_type_flg = false;
									$remain_page_flg = false;
									$remain_post_flg = false;
								}
							?>

								<li>
									<input type="checkbox" name="post_type[]" id="id_page" value="page" 
										<?php
											// Check if the "Post Type" that the user last operated remains
											if ( $remain_post_type_flg ) {
												// Check if the "Page" was checked
												if ( $remain_page_flg ) { // Checked: Set 'checked'
													echo 'checked';
												} else { // Unchecked: Do Nothing
													;
												}
											} else { // First time: Set 'checked'
												echo 'checked';
											}
										?>
									>
									<label for="id_page"><?php _e('Pages', 'tdh-export'); ?></label>
								</li>
								<li>
									<input type="checkbox" name="post_type[]" id="id_post" value="post" 
										<?php
											// Check if the "Post Type" that the user last operated remains
											if ( $remain_post_type_flg ) {
												// Check if the "Post" was checked
												if ( $remain_post_flg ) { // Checked: Set 'checked'
													echo 'checked';
												} else { // Unchecked: Do Nothing
													;
												}
											} else { // First time: Set 'checked'
												echo 'checked';
											}
										?>
									>
									<label for="id_post"><?php _e('Posts', 'tdh-export'); ?></label>
								</li>
								<?php /* Additional display, assuming there is a custom post type */ ?>
								<?php foreach ( get_post_types( array ( '_builtin' => false, 'can_export' => true ), 'objects', 'and' ) as $additional_post_type ): ?>
								<li>
								<input type="checkbox" name="post_type[]" id="<?php echo esc_attr( $additional_post_type->name ); ?>" value="<?php echo esc_attr( $additional_post_type->name ); ?>" 
									<?php
										// Check if the "Post Type" that the user last operated remains
										if ( $remain_post_type_flg ) {
											// Search if the "Additional Post Type" was checked
											$key = array_search( $additional_post_type->name, $sanitized_post_type_array );
											if ( false !== $key ) { // Checked: Set 'checked'
												echo 'checked';
											} else { // Unchecked: Do Nothing
												;
											}
										} else { // First time: Do Nothing
											;
										}
									?>
								>
								<label for="<?php echo esc_attr( $additional_post_type->name ); ?>"><?php echo esc_attr( $additional_post_type->label ); ?></label>
								</li>
								<?php endforeach; ?>
							</td>
						</tr>
						<tr>
							<th><?php _e('Export method', 'tdh-export'); ?></th>
							<td>
								<li>
									<label>
										<input type="radio" name="charset" value="not_output" required 
											<?php
												// Check if the "charset" that the user last operated remains
												if ( isset( $_POST['charset'] ) ) {
													if ( 'not_output' === $_POST['charset'] ) { // Checked 'not_output': Set 'checked'
														echo 'checked';
													} else { // Unchecked: Do Nothing
														;
													}
												} else { // First time: Default is 'not_output'
													echo 'checked';
												}
											?>
										>
										<?php _e('Display only', 'tdh-export'); ?>
									</label>
								</li>
								<li>
									<label>
										<input type="radio" name="charset" value="UTF-8"
											<?php
												// Check if the "charset" that the user last operated remains
												if ( isset( $_POST['charset'] ) ) {
													if ( 'UTF-8' === $_POST['charset'] ) { // Checked 'UTF-8': Set 'checked'
														echo 'checked';
													} else { // Unchecked: Do Nothing
														; // [NOP]
													}
												} else { // First time: Default is 'not_output'
													;
												}
											?>
										>
										<?php _e('Display & CSV file (UTF-8)', 'tdh-export'); ?>
									</label>
								</li>
								<li>
									<label>
										<input type="radio" name="charset" value="SJIS-win"
											<?php
												// Check if the "charset" that the user last operated remains
												if ( isset( $_POST['charset'] ) ) {
													if ( 'SJIS-win' === $_POST['charset'] ) { // Checked 'SJIS-win': Set 'checked'
														echo 'checked';
													} else { // Unchecked: Do Nothing
														; // [NOP]
													}
												} else { // First time: Default is 'not_output'
													;
												}
											?>
										>
										<?php _e('Display & CSV file (SJIS)', 'tdh-export'); ?>
									</label>
								</li>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="tdh-flex">
					<input type="submit" class="btn button-primary" value="<?php _e('Export TDH', 'tdh-export'); ?>" id="id_submit">
					<div class="tdh-spinner" id="id_spinner"></div>
				</div>
			</form>
		</div>
		<?php $this->run_tdh(); ?>
		<?php
	}

	/*****************************
	 * Run TDH
	 *****************************/
	public function run_tdh() {
		// --- Local variables ---
		$sanitized_post_type_array = array(); // "Post Type" array (initial val: blank array)
		$http_auth_array = array(); // HTTP Auth array (initial val: blank array)

		// Check if the form is POSTed
		if ( count( $_POST ) > 0 ) { // [OK] form is POSTed
			try {
				// Check for "Post type"
				if ( isset ( $_POST['post_type'] ) && is_array( $_POST['post_type'] ) ) { // [OK] "Post type" exists
					// Sanitize "Post type"
					foreach ( $_POST['post_type'] as $post_type ) {
						$sanitized_post_type = sanitize_key( $post_type );
						array_push( $sanitized_post_type_array, $sanitized_post_type );
					}

					// Check for "charset"
					if ( isset ( $_POST['charset'] ) ) { // [OK] "charset" exists
						// Sanitize "charset"
						$sanitized_charset = sanitize_key( $_POST['charset'] ); 

						// Get auth info if using HTTP auth
						if ( isset( $_SERVER['AUTH_TYPE'], $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) ) {
							// Sanitize auth info
							$http_auth_array['type'] = strtolower( sanitize_key( $_SERVER['AUTH_TYPE'] ) );
							$http_auth_array['user'] = sanitize_user( $_SERVER['PHP_AUTH_USER'] );
							$http_auth_array['password'] = sanitize_text_field( $_SERVER['PHP_AUTH_PW'] );

							// Branch by auth type
							switch ( $http_auth_array['type'] ) {
								case 'basic': // Basic Auth
									break;

								case 'digest': // Digest Auth (Not supported)
									throw new Exception( __('Digest authentication is used on this site. It is not supported by this plugin.', 'tdh-export') );
									break;

								default: // other (invalid)
									throw new Exception( __('Unknown authentication is used on this site. It is not supported by this plugin.', 'tdh-export') );
									break;
							}

						} else {
							$http_auth_array = array(); // blank array
						}

						// horizontal rule
						echo '<hr />';

						// Analyze TDH
						$tdh_array = $this->analyze_tdh( $sanitized_post_type_array, $http_auth_array );

						// Display TDH
						$result_display = $this->display_tdh( $tdh_array );
						// Check the result of display processing
						if ( true === $result_display ) { // [OK] success
							; // [NOP]
						} else { // [ERR] failure
							throw new Exception( __('Failed to display the results. Please try again.', 'tdh-export') );
						}

						// Export TDH to CSV file
						$result_export = $this->export_tdh( $sanitized_charset, $tdh_array );
						// Check the result of export
						if ( true === $result_export ) { // [OK] success
							; // [NOP]
						} else { // [ERR] failure
							// Exception error
							throw new Exception( __('Failed to create CSV file. Please try again.', 'tdh-export') );
						}

					} else { // [ERR] "charset" does not exist
						// Exception error
						throw new Exception( __('Select [Export Method].', 'tdh-export') );
					}

				} else { // [ERR] "Post type" does not exist
					// Exception error
					throw new Exception( __('Select [Post Type].', 'tdh-export') );
				}

			} catch (Exception $ex) { // [ERR]
				// Exception error
				$error_string = $ex->getMessage();
				echo '<div class="wrap"><div class="error inline"><p>' . esc_html__( $error_string ) . '</p></div></div>';
			}

		} else { // [OK] form is not POSTed
			; // [NOP]
		}

	} // end of function run_tdh

	/*****************************
	 * Analyze TDH
	 *
	 * @param   array   $input_post_types   Post type.
	 * @param   array   $http_auth_array    HTTP Auth data.
	 * @return  array                       TDH data.
	 *****************************/
	public function analyze_tdh( array $input_post_types, array $http_auth_array ) {
		// --- Local variables ---
		$tdh_array = array();   // TDH data array (initial val: blank array)
		$get_url = '';          // HTTP GET method URL (initial val: blank string)
		$get_args = array();    // HTTP GET method option (initial val: blank array)
		$sanitized_cookie = ''; // sanitized cookie (initial val: blank string)
		$sanitized_key = '';    // sanitized login key (initial val: blank string)
		$cookie_pattern = '@^wordpress_logged_in_@'; // pattern of WP login cookie (initial val: 'wordpress_logged_in_')

		// Loop search for WP login cookie from cookie array
		foreach ( $_COOKIE as $key => $value ) {
			// sanitize key
			$sanitized_key = sanitize_text_field( $key );
			// Search for WP login cookie
			$res_match = preg_match( $cookie_pattern, $sanitized_key );
			if ( $res_match ) { // [OK] Hit WP login cookie
				// sanitize cookie
				$sanitized_cookie = sanitize_text_field( $value );
				break;
			} else { // Not hit or ERR
				; // [NOP] Next loop
			}
		}
		// Check if cookie remains on the server
		if ( '' === $sanitized_cookie ) { // [ERR] Not Exist WP Login Cookie
			// Exception error
			throw new Exception( __('Failed to authenticate the WP administrator.', 'tdh-export') );

		} else { // [OK] Exist WP Login Cookie
			// Page acquisition cookie info (initial val: blank array)
			$cookies_array = array();
			// WP login cookie info
			$cookie1 = new WP_Http_Cookie(
				array(
					'name' => $sanitized_key,
					'value' => $sanitized_cookie,
				)
			);
			// WP test cookie info
			$cookie2 = new WP_Http_Cookie(
				array(
					'name' => 'wordpress_test_cookie',
					'value' => 'WP+Cookie+check',
				)
			);
			// Aggregate cookie info
			$cookies_array[] = $cookie1;
			$cookies_array[] = $cookie2;
			// Add cookie info to parameters for HTTP GET
			$get_args += array(
				'cookies' => $cookies_array,
			);

			// Branch by HTTP Auth type
			switch ( $http_auth_array['type'] ) {
				case 'basic': // Basic Auth
					// Check arguments
					if ( isset( $http_auth_array['user'] ) && isset( $http_auth_array['password'] ) ) { // [OK] Exist auth data
						// Basic auth param
						$user_and_password = $http_auth_array['user'] . ':' . $http_auth_array['password'];
						// Add auth info to parameters for HTTP GET 
						$get_args += array(
							'headers' => array(
								'Authorization' => 'Basic ' . base64_encode( $user_and_password )
							)
						);
					} else { // [ERR] Not exist
						// Exception error
						throw new Exception( __('Failed to authenticate.', 'tdh-export') );
					}
					break;

				default: // No HTTP auth or Unsupported auth
					// [NOP]
					break;
			}

			// Page get loop for "Post type"
			foreach ( $input_post_types as $post_type ) {
				// Get Post type object
				$post_obj = get_post_type_object( $post_type );
				// Check for response of get object
				if ( is_object( $post_obj ) ) { // [OK] Success
					// Param to get the page
					$args = array(
						'post_type'       => $post_obj->name,   // Post Type ('page','post')
						'orderby'         => 'parent',          // Sort by parents ID
						'order'           => 'ASC',             // ASC
						'posts_per_page'  => -1,                // Get All Pages
					);
					// Get pages according to post type
					$posts = get_posts( $args );

					// Check the get result of pages
					if ( !empty( $posts ) ) { // [OK] There is a public page for the post type
						// Loop by the num of public pages
						foreach ( $posts as $post ) {
							// TDH temp array (initial val: blank array)
							$tdh_tmp_array = array();
							// Get the URL from the post ID
							$get_url = get_permalink( $post->ID );
							// Get the page from the URL
							$result_remote_get = wp_remote_get( $get_url, $get_args );
							// Get error
							$err_flg = is_wp_error( $result_remote_get );
							// Check error
							if ( $err_flg ) { // [ERR] Failed to get page
								// Get error message
								$error_string = $result_remote_get->get_error_message();
								// Exception error
								throw new Exception( $error_string );

							} else { // [OK] Success to get page
								// Get status code
								$status_code = wp_remote_retrieve_response_code( $result_remote_get );
								// Branch by status code
								switch ( $status_code ) {
									case 200: // normal
										// Get body
										$result_remote_body = wp_remote_retrieve_body( $result_remote_get );
										// Check error
										if ( '' === $result_remote_body ) { // [ERR] Failed to get body
											// Display error messages
											$error_string = __('Could not get the html source code.', 'tdh-export');
											echo '<div class="wrap"><div id="message" class="error"><p>' . esc_html__( $error_string ) . '</p></div></div>';

										} else { // [OK] Success to get body
											// --- Set post type ---
											$tdh_tmp_array['post_type'] = $post_obj->label;

											// --- Get title tag ---
											$matches = '';
											$pattern = '@<title>(.*?)</title>@is'; // title tag pattarn
											$res_match = preg_match( $pattern, $result_remote_body, $matches );
											if ( $res_match ) { // [OK] Success to get title tag
												// Take out "title text" and remove the white space
												$tdh_tmp_array['title'] = trim( $matches[1] );
											} else { // [ERR] failed to get title tag
												// Set text 'no data'
												$tdh_tmp_array['title'] = '(no data)';
											}

											// --- Get meta description ---
											$matches = '';
											$pattern = '@<meta name="description" content="([^"]*?)"@is'; // meta description tag pattarn
											$res_match = preg_match( $pattern, $result_remote_body, $matches );
											if ( $res_match ) { // [OK] Success to get meta description
												// Take out "description text" and remove the white space
												$tdh_tmp_array['description'] = trim( $matches[1] );
											} else { // [ERR] failed to get meta description
												// Set text 'no data'
												$tdh_tmp_array['description'] = '(no data)';
											}

											// --- Get H tags ---
											$matches = '';
											$pattern = '@<h[1-6].*?>(.*?)</h[1-6]>@is'; // H tag pattarn
											$res_match = preg_match_all( $pattern, $result_remote_body, $matches );
											if ( $res_match ) { // [OK] Success to get H tags
												// all H tag strings
												$str_all_h = '';
												// loop for extract H tags
												foreach ( $matches[0] as $str_match ) {
													// get 'H tag number'
													$str_h_number = mb_substr( $str_match, 0, 3 ) . "> ";
													// get 'H tag text'
													$str_h = strip_tags( $str_match );
													// remove white space from 'H tag text'
													$str_h_trim = trim( $str_h );
													// Combine the strings 'H tag number', 'H tag text', 'NL code'
													$str_all_h .= $str_h_number . $str_h_trim . "\n";
												}
												// store the 'H tag strings'
												$tdh_tmp_array['h'] = $str_all_h;
											} else { // [ERR] failed to get H tags
												// Set text 'no data'
												$tdh_tmp_array['h'] = '(no data)';
											}
										} // end of if (Check for to get body)
										break;

									case 401: // Auth required
										// Exception error
										throw new Exception( __('Failed to authenticate.', 'tdh-export') );
										break;

									default: // [ERR] unexpected
										// Exception error
										throw new Exception( __('Failed to retrieve the page.', 'tdh-export') );
										break;
								} // end of switch
							} // end of if (check for get the page)

							// push to TDH array
							array_push( $tdh_array, $tdh_tmp_array );
						} // end of foreach (public pages)

					} else { // [ERR] There are no public pages for the specified post type
						// Display error messages
						$error_string = $post_obj->label.' '.__('does not have a public page.', 'tdh-export');
						echo '<div class="wrap"><div id="message" class="notice notice-warning inline"><p>' . esc_html__( $error_string ) . '</p></div></div>';
					} // end of if (check for post type)

				} else { // [ERR] No specified post type
					// Exception error
					throw new Exception( __('The selected post type is invalid.', 'tdh-export') );
				} // end of if (check to get post type obj)

			} // end of foreach (post type)

		} // end of if (WP Login cookie)

		// Return TDH data
		return $tdh_array;
	} // end of function

	/*****************************
	 * Display TDH List
	 *
	 * @param   array   $tdh_array  TDH data.
	 * @return  bool                Success or failure of processing (true / false)
	 *****************************/
	public function display_tdh( $tdh_array ) {
		// return value
		$ret_val = false;
		// check argument
		if ( !empty( $tdh_array ) && is_array( $tdh_array ) ) { // [OK]
			if ( array_key_exists( 'post_type', $tdh_array[0] )
				&& array_key_exists( 'title', $tdh_array[0] )
				&& array_key_exists( 'description', $tdh_array[0] )
				&& array_key_exists( 'h', $tdh_array[0] ) ) { // [OK] TDH data is valid

				// Display TDH data in table format
				?>
				<div class="wrap">
					<table class="tdh-table" id="modal-content-v2"><tr></tr>
						<thead>
							<tr>
								<th style="white-space: nowrap;"><?php _e('Post Type', 'tdh-export'); ?></th>
								<th style="white-space: nowrap;"><?php _e('Title tag', 'tdh-export'); ?></th>
								<th style="white-space: nowrap;"><?php _e('Meta Description', 'tdh-export'); ?></th>
								<th style="white-space: nowrap;"><?php _e('H tag', 'tdh-export'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $tdh_array as $page ): ?>
							<tr>
								<td style="white-space: nowrap;"><?php echo esc_html( $page['post_type'] ); ?></td>
								<td style="white-space: nowrap;"><?php echo esc_html( $page['title'] ); ?></td>
								<td><?php echo nl2br( esc_html( $page['description'] ) ); ?></td>
								<td style="white-space: nowrap;"><?php echo nl2br( esc_html( $page['h'] ) ); ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php

				$ret_val = true;

			} else { // [ERR] Invalid argument
				// return false
				$ret_val = false;
			} // end of if (check array_key_exists)

		} else { // [ERR] Invalid argument
			// return false
			$ret_val = false;
		} // end of if (check argument)

		// return success or failure
		return $ret_val;
	} // end of function

	/*****************************
	 * Export TDH
	 *
	 * @param   string  $input_charset  Charset fot CSV file.
	 * @param   array   $tdh_array      TDH data.
	 * @return  bool                    Success or failure of processing (true / false)
	 *****************************/
	public function export_tdh( string $input_charset, array $tdh_array ) {
		// --- Local variables ---
		$ret_val = false; // return val (initial val: false)
		$err_flg = false; // err flag (initial val: false)

		// Check the arguments
		if ( is_array( $tdh_array )
			&& !empty( $tdh_array ) ) { // [OK] TDH array is clear
			// Branch by CSV file export type
			switch ( $input_charset ) {
				case 'UTF-8':
				case 'SJIS-win':
				case 'utf-8':
				case 'sjis-win':
					// --- Export a CSV file with the specified charset ---
					// Get the internal encoding
					$internal_enc = mb_internal_encoding();
					// Get the external encoding
					$external_enc = $input_charset;
					// Get file pointer
					$fp = fopen( TDH_EXPORT_CSV_FILE_PATH, 'w' );
					// Check the get result of the file pointer
					if ( false !== $fp ) { // [OK] success
						// File write loop for each array
						foreach ( $tdh_array as $fields ) {
							// Convert the charset of the array
							$result_convert = mb_convert_variables( $external_enc, $internal_enc, $fields );
							// Check the convert result
							if ( $result_convert === $internal_enc ) { // [OK] conversion success
								// Write the array to a file in CSV format
								$result_fput = fputcsv( $fp, $fields );
								// Check the write result
								if ( is_int( $result_fput ) && ( 0 !== $result_fput ) ) { // [OK] writing success
									; // Continue loop processing
								} else { // [ERR] writing failure
									// Set an error flag, break loop processing
									$err_flg = true;
									break;
								}
							} else { // [ERR] conversion failure
								// Set an error flag, break loop processing
								$err_flg = true;
								break;
							}
						}

						// Release the file pointer
						fclose( $fp );

						// Check for file export errors
						if ( false === $err_flg ) { // [OK] No error
							// Show download link button
							$link_text = __('Download CSV file', 'tdh-export');
							echo '<p><a href="' . esc_attr( TDH_EXPORT_CSV_FILE_DOWNLOAD_URL ) . '" target="_blank" download class="button button-primary">' . esc_html( $link_text ) . '</a></p>';
							// Set return value to true
							$ret_val = true;
						} else { // [ERR] error exist
							// Set return value to false
							$ret_val = false;
						} // end of if (Check for file export errors)

					} else { // [ERR] File pointer acquisition failed
						// Set return value to false
						$ret_val = false;
					} // end of if (Check the get result of the file pointer)

					break;

				case 'not_output': // No need to export CSV file
					// Set return value to true
					$ret_val = true;
					break;

				default: // Invalid case
					// Set return value to false
					$ret_val = false;
					break;
			}

		} else { // [ERR] TDH array is invalid
			// Set return value to false
			$ret_val = false;
		}

		// Return a return value
		return $ret_val;
	} // end of function

} // end of class

?>
