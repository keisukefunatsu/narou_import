<?php

require_once NAROU_DIR . '/importer/importer.php';
$narou_admin = new NarouAdmin();

/**
 *
 */
class NarouAdmin {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'narou_integration' ] );
	}


	public function add_menu() {
		add_options_page( __( 'なろうまとめ', 'narou-integrator' ), __( 'なろうまとめ', 'narou-integrator' ), 'administrator', 'narou-setting', [ $this, 'narou_setting' ] );
	}

	public function narou_setting() {
		$html  = '';
		$html .= '<h2>取り込み</h2>';
		$html .= '<div class="narou-integration">';
		$html .= '<form action="" method="post" enctype="multipart/form-data">';
		$html .= '<p><input type="file" name="book_list" class="" /></p>';
		$html .= '<p><input type="submit" value="同期" class="button button-secondary" /></p>';
		$html .= wp_nonce_field( 'narou-integrator-nonce', 'narou_integration-nonce' );
		$html .= '<input type="hidden" name="narou_sync" value="narou_sync" />';
		$html .= '</div>';
		$html .= '</form>';

		echo $html;
	}

	public function narou_integration() {
		// pp($_POST);
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			if ( isset( $_POST['narou_sync'] ) && $_POST['narou_sync'] == 'narou_sync' ) {
				if ( ( check_admin_referer( 'narou-integrator-nonce', 'narou_integration-nonce' ) ) ) {
					$importer = new NarouImporter();
					$response = $importer->import_all();
					pp( $response );
				}
			}
		}
	}
}
