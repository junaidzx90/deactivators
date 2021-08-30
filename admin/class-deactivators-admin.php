<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.example.com/anonymous
 * @since      1.0.0
 *
 * @package    Deactivators
 * @subpackage Deactivators/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Deactivators
 * @subpackage Deactivators/admin
 * @author     Anonymous <admin@example.com>
 */
class Deactivators_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/deactivators-admin.css', array(), '1.0', 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/deactivators-admin.js', array( 'jquery' ), '1.0', true );
		wp_localize_script($this->plugin_name, "deactivators", array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ajax-nonce'),
		));
	}
	
	public function deactivators_action_menu(){
		global $wp_admin_bar;

		$menus[] = array(
			'id' => 'deactivators',
			'title' => 'Deactivators',
			'href' => '#',
			'meta' => array(
				'target' => 'blank',
				'html' => $this->deactivators_panel_html()
			)
		);

   		foreach ( $menus as $menu ){
			$wp_admin_bar->add_menu( $menu );
		}	
	}

	function deactivators_panel_html(){
		wp_enqueue_style( $this->plugin_name );
		wp_enqueue_script( $this->plugin_name );
		// Get all plugins
		include_once( ABSPATH. 'wp-admin/includes/plugin.php' );
		$all_plugins = get_plugins();

		if(is_multisite(  )){
			$active_plugins = get_site_option('active_sitewide_plugins');
		}else{
			$active_plugins = get_option('active_plugins');
		}
		
		foreach ( $all_plugins as $key => $value ) {
			if(is_multisite(  )){
				$is_active = ( array_key_exists( $key, $active_plugins ) ) ? true : false;
			}else{
				$is_active = ( in_array( $key, $active_plugins ) ) ? true : false;
			}
			$plugins[ $key ] = array(
				'name'    => $value['Name'],
				'version' => $value['Version'],
				'active'  => $is_active,
			);
		}
		
		$diactivatedPlugins = '';
		$no_deactivate = 'true';
		$activeplugins = '';
		$ind = 0;
		$dind = 1;
		foreach($plugins as $key => $plugin){
			if($plugin['active']){
				if($plugin['name'] !== 'Deactivators'){
					$activeplugins .= '<li data-baseurl="'.$key.'">
					<button id="pl-'.$ind.'" class="deactivators-checkbox"></button>
					<label for="pl-'.$ind.'" class="deactivators-plname">'.$plugin['name'].'</label>
					</li>';
				}
			}else{
				$no_deactivate = 'false';
				if($plugin['name'] !== 'Deactivators'){
					$diactivatedPlugins .= '<li data-baseurl="'.$key.'">'.$dind.'. '.$plugin['name'].'</li>';
					$dind++;
				}
			}

			$ind++;
		}

		$output = '<div id="deactivators_panel">
		<div class="selectallbox">
			<button id="selall" class="selectall"></button>
			<label for="selall" class="selall">Select all</label>
		</div>
		<div class="allplugins">
			<div class="pluginsv activated_plugins">
				<ul>
				'.$activeplugins.'
				</u>
			</div>
			<div class="pluginsv deactivated_plugins">
				<ul>
				'.$diactivatedPlugins.'
				</ul>
			</div>
		</div>
		<div class="deact-buttons">
			<input type="hidden" class="nodata" value="'.$no_deactivate.'">
			<button disabled class="button deactivators-start-btn">Start</button>
			<button disabled class="button deactivators-next-btn">Next</button>
		</div>
		<span class="sidebtn">⟩</span>
		</div>';
		
		return $output;
	}

	public function deactivators_scripts(){
		?>
		<script type="text/javascript">
			let nodata = document.querySelector('.nodata').value
			if (nodata == 'true') {
				localStorage.removeItem('deactivated_plugins');
			}else{
				if(document.querySelector('.deactivated_plugins ul li')){
					let urls = []
					document.querySelectorAll('.deactivated_plugins ul li').forEach(element => {
						let elem = element.getAttribute('data-baseurl')
						urls.push(elem)
					});

					if(localStorage.getItem('deactivated_plugins') ===  'undefined'){
						localStorage.setItem('deactivated_plugins', urls)
					}
					
				}
			}

			let selected_plugins = localStorage.getItem('selected_plugins')
			let deactivated_plugins = localStorage.getItem('deactivated_plugins')

			if(selected_plugins || deactivated_plugins){
				let panel = document.getElementById('deactivators_panel');
				panel.style.display = 'block';
			}

			if(selected_plugins){
				selected_plugins = selected_plugins.split(',')
				selected_plugins.forEach(plugin => {
					let elem = document.querySelector('[data-baseurl="'+plugin+'"]');
					if(elem)
						elem.firstElementChild.classList.add('pl-active')
				});
			}

			if(deactivated_plugins){
				let nextButton = document.querySelector('.deactivators-next-btn')
				nextButton.removeAttribute('disabled')
			}
		</script>
		<?php
	}

	private function deactivate_pugin($plugins){
		if(is_multisite(  )){
			deactivate_plugins( $plugins, false, true  );
		}else{
			deactivate_plugins( $plugins );
		}
	}

	public function deactivators_deactivate(){
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
			die ( 'Hey! What are you doing?');
		}

		if(isset($_POST['plugin'])){
			$plugins = $_POST['plugin'];
			$this->deactivate_pugin($plugins);
			
			echo wp_json_encode( array('deactivated' => [$plugins]) );
			die;
		}else{
			echo wp_json_encode( array('error' => ' ') );
			die;
		}
		die;
	}

	public function deactivators_reactivate(){
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
			die ( 'Hey! What are you doing?');
		}

		if(isset($_POST['reactivatable']) || isset($_POST['selected_plugins'])){
			$reactivatable = $_POST['reactivatable'];
			$reactivatable = array_unique($reactivatable);
			if(is_multisite(  )){
				activate_plugins($reactivatable, '', true, true);
			}else{
				activate_plugins($reactivatable);
			}

			if(isset($_POST['selected_plugins'])){
				$deactivatable = $_POST['selected_plugins'];
				$this->deactivate_pugin($deactivatable);
			}

			echo wp_json_encode( array('reactivated' => $reactivatable ) );
			die;
		}else{
			echo wp_json_encode( array('error' => ' ') );
			die;
		}
		die;
	}
}
