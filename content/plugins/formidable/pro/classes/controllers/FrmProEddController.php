<?php
// Contains all the functions necessary to provide an update mechanism for FormidablePro!

class FrmProEddController{

    // Where all the vitals are defined for this plugin
	public $author                 = 'Strategy11';
	public $edd_plugin_name        = 'Formidable Pro';
    public $plugin_nicename        = 'formidable';
    public $plugin_name            = 'formidable/formidable.php';
	public $pro_mothership         = 'https://formidablepro.com/';
    public $pro_last_checked_store = 'frm_autoupdate';
    var $pro_check_interval     = 0; // Don't check. Pro updates will force over free updates
    var $timeout                = 10;
	var $update_to;
	var $plugin_file;
	var $version;
	var $edd;
	var $pre_check_run = false;

    var $pro_wpmu = false;
    var $pro_error_message_str;
    var $license        = '';

    var $pro_cred_store  = 'frmpro-credentials';
    var $pro_auth_store  = 'frmpro-authorized';
    var $pro_wpmu_store  = 'frmpro-wpmu-sitewide';

    function __construct(){
        $this->pro_error_message_str = __( 'Your Formidable Pro License was Invalid', 'formidable' );
		$this->plugin_file = FrmAppHelper::plugin_path() .'/formidable.php';
		$this->version = FrmAppHelper::$plug_version;

        $this->set_license();

		global $frm_vars;
		$frm_vars['pro_is_authorized'] = $this->pro_is_authorized();

		$this->edd_plugin_updater();
    }

	public function load_hooks() {
        add_filter( 'site_transient_update_plugins', array( &$this, 'queue_update' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'maybe_check_update' ), 9 );
		add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'save_update' ), 11 );
	}

	private function set_license() {
		$this->license = $this->get_license();
        if ( $this->license && is_multisite() && get_site_option( $this->pro_wpmu_store ) ) {
            $this->pro_wpmu = true;
        }
	}

	private function get_license() {
		if ( is_multisite() && get_site_option( $this->pro_wpmu_store ) ) {
			$creds = get_site_option( $this->pro_cred_store );
		} else {
			$creds = get_option( $this->pro_cred_store );
		}

		$license = '';
		if ( $creds && is_array( $creds ) && isset( $creds['license'] ) ) {
			$license = $creds['license'];
			if ( strpos( $license, '-' ) ) {
				// this is a fix for licenses saved in the past
				$license = strtoupper( $license );
			}
		}

		return $license;
	}

	public function edd_plugin_updater() {
		$license = trim( $this->license );

		if ( ! empty( $license ) ) {
			if ( ! class_exists('EDD_SL_Plugin_Updater') ) {
				include( FrmAppHelper::plugin_path() .'/classes/models/EDD_SL_Plugin_Updater.php' );
			}

			// setup the updater
			$edd = new EDD_SL_Plugin_Updater( $this->pro_mothership, $this->plugin_file, array(
				'version' 	=> $this->version,
				'license' 	=> $license,
				'item_name' => $this->edd_plugin_name,
				'author' 	=> $this->author,
			) );

			$this->edd = $edd;

			// let WordPress handle the changelog
			remove_filter( 'plugins_api', array( $edd, 'plugins_api_filter' ), 10, 3 );
		}
	}

	function pro_is_authorized() {
		if ( empty( $this->license ) ) {
			return false;
		}

        if ( is_multisite() && $this->pro_wpmu ) {
            $authorized = get_site_option( $this->pro_auth_store );
        } else {
            $authorized = get_option( $this->pro_auth_store );
        }

        return $authorized;
    }

    function pro_is_installed_and_authorized(){
        return $this->pro_is_authorized();
    }

    public function pro_cred_form(){
        global $frm_vars;
		if ( $_POST && isset( $_POST['process_cred_form'] ) && $_POST['process_cred_form'] == 'Y' ) {
            if ( ! isset($_POST['frm_cred']) || ! wp_verify_nonce( $_POST['frm_cred'], 'frm_cred_nonce' ) ) {
                $frm_settings = FrmAppHelper::get_settings();
                $response = array( 'response' => $frm_settings->admin_permission, 'auth' => false);
            }else{
                $response = $this->activate();
            }

			if ( $response['auth'] ) {
                $frm_vars['pro_is_authorized'] = true;
?>
<div id="message" class="updated"><strong><?php _e( 'Your Pro installation is now active. Enjoy!', 'formidable' ); ?></strong></div>
<?php       }else{ ?>
<div class="error">
    <strong><?php _e( 'ERROR', 'formidable' ); ?></strong>: <?php echo $response['response']; ?>
</div>
<?php
            }
        }
?>
<div id="frm_license_top">
    <?php $this->display_form();

    if ( ! $frm_vars['pro_is_authorized'] ) { ?>
    <p>Already signed up? <a href="https://formidablepro.com/account/?action=licenses" target="_blank"><?php _e( 'Click here', 'formidable' ) ?></a> to get your license number.</p>
    <?php } ?>
</div>

<?php if ( $frm_vars['pro_is_authorized'] ) { ?>
<div class="frm_pro_installed">
<div><strong class="alignleft" style="margin-right:10px;"><?php _e( 'Formidable Pro is Installed', 'formidable' ) ?></strong>
    <a href="javascript:void(0)" class="frm_show_auth_form button-secondary alignleft"><?php _e( 'Enter new license', 'formidable' ) ?></a>
    <a href="#" id="frm_deauthorize_link" class="button-secondary alignright"><?php _e( 'Deauthorize this site', 'formidable' ) ?></a>
    <div class="spinner"></div>
</div>
<div class="clear"></div>
</div>
<p class="frm_aff_link"><a href="https://formidablepro.com/account/?action=licenses" target="_blank"><?php _e( 'Account', 'formidable' ) ?></a></p>
<?php } ?>

<div class="clear"></div>

<?php
    }

    function display_form(){
        global $frm_vars;

        // this is the view for the license form
        ?>
<div id="pro_cred_form" <?php echo $frm_vars['pro_is_authorized'] ? 'class="frm_hidden"' : ''; ?>>
    <form name="cred_form" method="post" autocomplete="off">
    <input type="hidden" name="process_cred_form" value="Y" />
    <?php wp_nonce_field('frm_cred_nonce', 'frm_cred'); ?>

    <p><input type="text" name="proplug-license" value="" style="width:97%;" placeholder="<?php esc_attr_e( 'Enter your license number here', 'formidable' ) ?>"/>

    <?php if ( is_multisite() ) {
        $creds = $this->get_pro_cred_form_vals(); ?>
        <br/><label for="proplug-wpmu"><input type="checkbox" value="1" name="proplug-wpmu" id="proplug-wpmu" <?php checked($creds['wpmu'], 1) ?> />
        <?php _e( 'Use this license to enable Formidable Pro site-wide', 'formidable' ); ?></label>
    <?php } ?>
    </p>
	<input class="button-secondary" type="submit" value="<?php esc_attr_e( 'Save License', 'formidable' ); ?>" />
    <?php if ( $frm_vars['pro_is_authorized'] ) {
        _e( 'or', 'formidable' );
    ?>
        <a href="javascript:void(0)" class="frm_show_auth_form button-secondary"><?php _e( 'Cancel', 'formidable' ); ?></a>
    <?php } ?>
    </form>
</div>
<?php
    }

    private function _update_auth( $creds, $authorized = true ) {
        if (is_multisite())
            update_site_option( $this->pro_wpmu_store, $creds['wpmu'] );

        if ($creds['wpmu']){
            update_site_option( $this->pro_cred_store, $creds );
            update_site_option( $this->pro_auth_store, $authorized );
        }else{
            update_option( $this->pro_cred_store, $creds );
            update_option( $this->pro_auth_store, $authorized );
        }

        $this->license = ( isset( $creds['license'] ) && ! empty( $creds['license'] ) ) ? $creds['license'] : '';
    }

    function get_pro_cred_form_vals(){
		$license = isset( $_POST['proplug-license'] ) ? sanitize_text_field( $_POST['proplug-license'] ) : $this->license;
        $wpmu = isset($_POST['proplug-wpmu']) ? true : $this->pro_wpmu;

        return compact('license', 'wpmu');
    }

	private function activate() {
		$creds = $this->get_pro_cred_form_vals();
		$license = $creds['license'];

		$response = array( 'auth' => false, 'response' => '' );
		if ( empty( $license ) ) {
			$response['response'] = __( 'Oops! You forgot to enter your license number.', 'formidable' );
			return $response;
		}
		
		try {
			$license_data = $this->send_mothership_request( 'activate_license', $license );

			// $license_data->license will be either "valid" or "invalid"
			$is_valid = false;
			$response['response'] = __( 'That license is invalid', 'formidable' );
			if ( is_array( $license_data ) ) {
				if ( $license_data['license'] == 'valid' ) {
					$this->manually_queue_update();
					$is_valid = $license_data['license'];
					$response['response'] = __( 'Enjoy!', 'formidable' );
					$is_valid = true;
					$response['auth'] = true;
				}
			} else if ( $license_data == 'expired' ) {
				$response['response'] = __( 'That license is expired', 'formidable' );
			} else if ( $license_data == 'no_activations_left' ) {
				$response['response'] = __( 'That license has been used too many times', 'formidable' );
			} else {
				$response['response'] = FrmAppHelper::kses( $license_data, array('a') );
			}

	        $this->_update_auth( $creds, $is_valid );
		} catch ( Exception $e ) {
			$response['response'] = $e->getMessage();
		}

		return $response;
	}

	public static function deactivate() {
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$this_plugin = new FrmProEddController();
		$license = $this_plugin->get_license();
		if ( empty( $license ) ) {
			wp_die();
		}
		
		$response = array( 'success' => false, 'message' => '' );
		try {
			// $license_data->license will be either "deactivated" or "failed"
			$license_data = $this_plugin->send_mothership_request( 'deactivate_license', $license );

			if ( is_array( $license_data ) && $license_data['license'] == 'deactivated' ) {
				$response['success'] = true;
				$response['message'] = __( 'That license was removed successfully', 'helpdesk' );
			} else {
				$response['message'] = __( 'There was an error deactivating your license.', 'formidable' );
			}
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();
		}

        delete_option( $this_plugin->pro_cred_store );
        delete_option( $this_plugin->pro_auth_store );
        delete_site_option( $this_plugin->pro_cred_store );
        delete_site_option( $this_plugin->pro_auth_store );

        wp_die();
	}

    function queue_update( $transient ) {
        if ( ! is_object($transient) || ! $this->pro_is_authorized() ) {
            return $transient;
        }

		if ( $this->is_current_version( $transient ) ) {
			//make sure it doesn't show there is an update if plugin is up-to-date

			if ( isset( $transient->response[ $this->plugin_name ] ) ) {
				unset( $transient->response[ $this->plugin_name ] );
			}

		} else if ( isset( $transient->response ) && isset( $transient->response[ $this->plugin_name ] ) && ! empty( $transient->response[ $this->plugin_name ] ) ) {

			if ( $this->is_updating_to_free( $transient->response[ $this->plugin_name ]->url ) ) {

				$version_info = $this->get_plugin_info_transient();

				if ( ! empty( $version_info ) && ! empty( $version_info->url ) ) {
					$transient->response[ $this->plugin_name ] = $version_info;
				} else if ( is_multisite() && ! empty( $version_info->license_check ) ) {
					add_filter( 'in_plugin_update_message-' . $this->plugin_name, array( &$this, 'no_permission_msg' ) );
					$transient->response[ $this->plugin_name ] = (object) array_merge( (array) $transient->response[ $this->plugin_name ], (array) $version_info );
					unset( $transient->response[$this->plugin_name]->package );
				} else {
					// the pro checking failed, but we still don't want to update to the free version
					if ( is_multisite() && ! empty( $transient->response[$this->plugin_name]->url ) ) {
						unset( $transient->response[$this->plugin_name] );
					} else {
						unset( $transient->response[$this->plugin_name]->package );
					}

					if ( $this->pre_check_run ) {
						add_filter( 'in_plugin_update_message-' . $this->plugin_name, array( &$this, 'pro_check_failed_msg' ) );
					} else {
						$this->manually_queue_update();
					}
				}
			} else {
				$this->save_plugin_info_transient( $transient->response[ $this->plugin_name ] );
			}
		} else if ( $this->pro_update_disallowed( $transient ) ) {
            add_filter( 'in_plugin_update_message-' . $this->plugin_name, array( &$this, 'no_permission_msg' ) );
        }

        return $transient;
    }

	function maybe_check_update( $transient ) {
		$this->pre_check_run = true;
		$updating_to_free = ( ! empty( $transient->response ) && isset( $transient->response[ $this->plugin_name ] ) && $this->is_updating_to_free( $transient->response[ $this->plugin_name ]->url ) );
		if ( ! $updating_to_free ) {
			// there's no update, so don't check
			remove_filter( 'pre_set_site_transient_update_plugins', array( $this->edd, 'check_update' ), 10 );
		} else {
			unset( $transient->response[ $this->plugin_name ] );
			remove_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'maybe_check_update' ), 9 );
			add_filter( 'pre_set_site_transient_update_plugins', array( $this->edd, 'check_update' ), 10 );
		}

		return $transient;
	}

	function save_update( $transient ) {
		if ( ! empty( $transient->response ) && isset( $transient->response[ $this->plugin_name ] ) ) {
			$this->save_plugin_info_transient( $transient->response[ $this->plugin_name ] );
		}

		return $transient;
	}

	function save_plugin_info_transient( $info ) {
		if ( is_object( $info ) && ! empty( $info->url ) && ! $this->is_updating_to_free( $info->url ) ) {
			$cache_key = 'edd_plugin_' .  md5( sanitize_key( $this->plugin_name . FrmAppHelper::plugin_version() ) . '_version_info' );
			set_transient( $cache_key, $info, 60 * 60 * 4 );
		}
	}

	function get_plugin_info_transient() {
		$cache_key = 'edd_plugin_' . md5( sanitize_key( $this->plugin_name . FrmAppHelper::plugin_version() ) . '_version_info' );
		return get_transient( $cache_key );
	}

	/**
	 * Check if the transient says there is an update when the plugin has already been updated
	 *
	 * @return boolean - true if the plugin is up to date
	 * @since 2.0.5
	 */
	function is_current_version( $transient ) {
		if ( empty( $transient->checked ) || ! isset( $transient->checked[ $this->plugin_name ] ) ) {
			return false;
		}

		$response = ! isset( $transient->response ) || empty( $transient->response );
		if ( $response ) {
			return true;
		}

		return isset( $transient->response ) && isset( $transient->response[ $this->plugin_name ] ) && $transient->checked[ $this->plugin_name ] == $transient->response[ $this->plugin_name ]->new_version;
	}

	function is_updating_to_free( $url ) {
		return strpos( $url, 'wordpress.org' ) !== false;
	}

	/**
	 * If the license is not active on this site, the update will not be allowed.
	 * If there is an upgrade notice, that tells us the user is not allowed.
	 *
	 * @return boolean - true if user is not allowed to update to pro
	 * @since 2.0.5
	 */
	function pro_update_disallowed( $transient ) {
		return (
			isset( $transient->response ) && isset( $transient->response[ $this->plugin_name ] ) &&
			isset( $transient->response[ $this->plugin_name ]->upgrade_notice ) &&
			! empty( $transient->response[ $this->plugin_name ]->upgrade_notice )
		);
	}

    function manually_queue_update(){
        set_site_transient('update_plugins', null);
    }

	function send_mothership_request( $action, $license ) {
		$uri = $this->pro_mothership;

		$api_params = array(
			'edd_action' => $action,
			'license'    => $license,
			'item_name'  => urlencode( $this->edd_plugin_name ),
			'url'        => home_url(),
		);

		$arg_array = array(
			'body'      => $api_params,
			'timeout'   => $this->timeout,
			'sslverify' => false,
			'user-agent' => 'Formidable/' . FrmAppHelper::plugin_version() . '; ' . get_bloginfo( 'url' ),
		);

		$resp = wp_remote_post( $this->pro_mothership, $arg_array );
		$body = wp_remote_retrieve_body( $resp );

		$message = __( 'Your License Key was invalid', 'formidable' );
		if(is_wp_error($resp)){
			$message = sprintf(__( 'You had an error communicating with Strategy11\'s API. %1$sClick here%2$s for more information.', 'formidable' ), '<a href="http://formidablepro.com/knowledgebase/why-cant-i-activate-formidable-pro/" target="_blank">', '</a>');
			if ( is_wp_error( $resp ) ) {
				$message .= ' '. $resp->get_error_message();
			}
			return $message;
		} else if ( $body == 'error' || is_wp_error( $body ) ) {
			$message = __( 'You had an HTTP error connecting to Strategy11\'s API', 'formidable' );
		} else {
			$json_res = json_decode( $body, true );
			if ( null !== $json_res ) {
				if ( is_array($json_res) && isset($json_res['error']) ) {
					$message = $json_res['error'];
				} else {
					$message = $json_res;
				}
			} else if ( isset( $resp['response'] ) && isset( $resp['response']['code'] ) ) {
				$message = sprintf( __( 'There was a %1$s error: %2$s', 'formidable' ), $resp['response']['code'], $resp['response']['message'] .' '. $resp['body'] );
			}
		}

		return $message;
	}

	function pro_check_failed_msg() {
		echo ' <em>' . __( 'There was a problem getting the pro update.', 'formidable' ) . '</em>';
	}

	function no_permission_msg() {
		echo ' <em>' . __( 'Your license is invalid or expired.', 'formidable' ) . '</em>';
	}
}


