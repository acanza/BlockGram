<?php
/*
Plugin Name: BlockGram
Plugin URI: http://www.todoinstagram.com
Description: BlockGram is a Wordpress plugin witch allow you increase your Instagram followers.
Version: 1.0.0
Author: Grafite
Author URI: http://www.twitter.com/canterozamorano
Text Domain: blockgram
License: A "Slug" license name e.g. GPL2
*/


require_once 'instagram-php-api/Instagram.php';

// Derive the current path and load up Sanity
$plugin_path = dirname(__FILE__).'/';
if(class_exists('SanityPluginFramework') != true)
	require_once($plugin_path.'framework/sanity.php');


/*
 *		Define your plugin class which extends the SanityPluginFramework
*		Make sure you skip down to the end of this file, as there are a few
*		lines of code that are very important.
*/
class BlockGramPlugin extends SanityPluginFramework {

	/*
	 *	Some required plugin information
	*/
	var $version = '1.0';
	
	/*
	 *  Loads /plugin/css/bgram-frontend-style.css
	 */
	var $plugin_css = array('bgram-frontend-style');
	
	/*
	 *  Loads /plugin/css/bgram-admin-style.css
	*/
	var $admin_css = array('bgram-admin-style');
	
	/*
	 * Key name to save admin parameters in wp-option table
	 */
	var $dbOptionKey = 'BlockGramPlugin_Options';
	
	/*
	 * Key name to save front-end parameters in wp-option table
	 */
	var $dbFrontEndOptionKey = 'BlockGramPlugin_Follower_Options';
	
	/*
	 * Cookie Key name to set all followers
	*/
	var $bgramCookei = 'bgram-follower';

	/*
	 * Author Plugin ID
	*/
	var $authorPluginID = '231712215';

	protected static $instance;
	
	/*
	 *		Required __construct() function that initalizes the BlockGramPlugin
	*/
	function __construct() {
		parent::__construct(__FILE__);

		$options = $this->getBgramOptions();
		$this->bgramSetVisitorCookie();

		//Register plugin text domain for translations files
		load_plugin_textdomain( 'blockgram', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		//Add plugin menu
		add_action( 'admin_menu', array(&$this , 'bgramMenu') );
		
		//Shortcode register
		add_shortcode('blockgram', array(&$this, 'bgramShortcode'));
	}

    public static function init()
    {
        is_null( self::$instance ) AND self::$instance = new self;
        return self::$instance;
    }

	/*
	 *		Run during the activation of the plugin
	*/
	public static function activate() {
	
		if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "activate-plugin_{$plugin}" );

        //Create redirect URI page
		self::bgramCreateRedirectPage();
	}

	/*
	 *		Run during the deactivation of the plugin
	*/
	public static function deactivate() {
	
		if ( ! current_user_can( 'activate_plugins' ) )
            return;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );	
	}

	/*
	 *		Run during the unistalling of the plugin
	*/
	public static function uninstall() {
	
		if ( ! current_user_can( 'activate_plugins' ) )
            return;
        check_admin_referer( 'bulk-plugins' );

        // Important: Check if the file is the one
        // that was registered during the uninstall hook.
        if ( __FILE__ != WP_UNINSTALL_PLUGIN )
            return;

        //Remove redirect URI page
        wp_delete_post( get_page_by_title( 'Blockgram Redirect Page' ) );

        //Delete plugin options from wp-options table
        delete_option( $this->dbOptionKey );
        delete_option( $this->dbFrontEndOptionKey );
	}

	/*
			Create plugin menu	
	 */
	function bgramMenu() {
		
		add_options_page( 'Blockgram', 'Blockgram', 'manage_options', 'blockgram-setting', array(&$this, 'bgramHandleOptions'));
	}
	
	/**
	* @return array con los par‡metros de configuraci—n de la API
	*/
	function getBgramConfiguration()
	{
		$options = $this->getBgramOptions();
		
		return array(
				'site_url' 	=> 'https://api.instagram.com/oauth/access_token',
				'client_id' 	=> $options['app_client_id'],
				'client_secret' => $options['app_client_secret'],
				'grant_type' 	=> 'authorization_code',
				'redirect_uri'	=> $this->getBgramOAuthRedirectUrl(),
				'return_uri'    => $this->getOAuthRedirectUrl(),
				'scope'		=> 'relationships',
				'code'		=> null,
				'is_admin'  => false
		);
	}
	
	/**
	* @return Instacian configurada de la API Instagram
	*/
	function getBgramAPIInstance($config)
	{
		
		// Inicializamos la API
		$instagram = new Instagram($config);
		
		if( $config['is_admin'] ){

			$instagram->setAccessToken(BlockGramPlugin::getBgramAccessToken());
		}
			
		return $instagram;
	}

	/**
	* @return string Access-Token der in der Datenbank gespeichert ist bzw. null
	*/
	function getBgramAccessToken()
	{
		
		$options = $this->getBgramOptions();
			
		return $options['app_access_token'];
	}
	
	/**
	* @return string con la URL de redirecci—n
	*/
	function getBgramOAuthRedirectUrl()
	{
		return get_permalink( get_page_by_title( 'Blockgram Redirect Page' ) );
	}
	
	/**
	* Consulta si hay nuevos par‡metros de configuraci—n
	* 
	* @return array con los œltimos par‡metros de configuraci—n
	*/
	function getBgramOptions()
	{
		// Por defecto
		$options = array
		(
				'app_client_id' => '',
				'app_client_secret' => '',
				'app_access_token' => '',
				'app_current_user' => '',
				'app_message_block' => '',
				'app_current_code' => '',
				'app_follow_todoinstagram' => '',
				'app_followers_count' => 0
		);
			
		// Par‡metros almacenados
		$saved = get_option($this->dbOptionKey);
			
		if(!empty($saved))
		{
			
			foreach($saved as  $key => $option)
			{
				$options[$key] = $option;
			}
		}
		
		if($saved != $options)
			update_option($this->dbOptionKey, $options);
			
		return $options;
	}
	
	/*
	* @return cadena URI que se ha devuelto despuŽs de la autorizaci—n.
	*/
	function getOAuthRedirectUrl()
	{
		return get_admin_url().'options-general.php?page=blockgram-setting.php';
	}
	
	function bgramHandleOptions(){
		
		$this->data['error'] = 'Instagram API reported the following error';
		$options = $this->getBgramOptions();

		if(isset($_POST['blockgram-reset-auth-settings']))
		{
		
			delete_option($this->dbOptionKey);
		}else{

			if(isset($_POST['blockgram-update-settings']))
			{
			
				if (isset($_POST['blockgram-message-block-content']) && ( 0 !== strcmp($_POST['blockgram-message-block-content'], $options['app_message_block']))) {
			
					$options['app_message_block'] = $_POST['blockgram-message-block-content'];
					update_option($this->dbOptionKey, $options);
				}

				if (0 !== strcmp( $options['app_follow_todoinstagram'], $_POST['blockgram-follow-todoinstagram'])){

					$options['app_follow_todoinstagram'] = $_POST['blockgram-follow-todoinstagram'];
					update_option($this->dbOptionKey, $options);

					//Modify the status of relationship
					$response = $this->bgramRelationshipPluginAuthor();
					$arrayResponse = json_decode( $response, true );

					if ( !empty($response) && (200 !== $arrayResponse['meta']['code']) ) {

						$this->data['response_message'] = $response;
					}
				}
			}
			
			if(!$options['app_access_token'])
			{

				if (isset($_POST['bgram-client-id']) 
					&& isset($_POST['bgram-client-secret'])){

					$options['app_client_id'] = trim($_POST['bgram-client-id']);
					$options['app_client_secret'] = trim($_POST['bgram-client-secret']);

					update_option($this->dbOptionKey, $options);
				}

				$config = $this->getBgramConfiguration();
				$config['is_admin'] = true;
				
				if ( !empty( $options[ 'app_current_code' ] ) ) {
					
					$config[ 'code' ] = $options[ 'app_current_code' ];
					$instagram = $this->getBgramAPIInstance( $config );
					
					$errorMessage = "";
					
					$token = $instagram->getAccessToken( $errorMessage );
					$currentUser = $instagram->getCurrentUser();
					
					if($token)
					{
			
						$options['app_access_token'] = $token;
						$options['app_current_user'] = $currentUser;
						
						update_option($this->dbOptionKey, $options);
						
						$this->data['saved'] = 'Settings saved.';
					}
					else if($errorMessage)
					{

						$this->data['errorMessage'] = $errorMessage;
					}
					
				}else{
					
					$this->data['authInstagramURL'] = $this->getBgramAuthUrl( $config );
				}

				$this->data['activation_message'] = true;

			}else{

				$this->data['activation_message'] = false;
			}
			
			$this->data['dataClientID'] = $options['app_client_id'];
			$this->data['dataClientSecret'] = $options['app_client_secret'];
			$this->data['app_access_token'] = $options['app_access_token'];
			$this->data['profile_picture'] = $this->getBgramProfileInfo( $options['app_current_user'], 'profile_picture' );
			$this->data['profile_username'] = $this->getBgramProfileInfo( $options['app_current_user'], 'username' );
			$this->data['message-block-content'] = $options['app_message_block'];
			$this->data['app_follow_todoinstagram'] = $options['app_follow_todoinstagram'];
			$this->data['followers_count'] = $options['app_followers_count'];
		}

		echo $this->render('blockgram-setting-pannel');
	}
	
	
	/*
	 * "blockgram" shortcode displaying
	 */
	function bgramShortcode( $atts, $content = null ){
		
		$options = $this->getBgramOptions();
		$config = $this->getBgramConfiguration();
		
		//Solicitamos el c—digo del usuario, para acceder a la plataforma
		$frontEndOptions = get_option( $this->dbFrontEndOptionKey );
		$code = $frontEndOptions['app_follower_code'];
		
		$config[ 'code' ] = $code;
		$config['return_uri'] = get_permalink();
		
		$instagram = $this->getBgramAPIInstance( $config );
		
		if( !empty( $code ) && !$this->bgramIsFollower($instagram) ){
			
			$myselfUser = $instagram->getCurrentUser();
			$myselfID = $this->getBgramProfileInfo( $myselfUser, 'id' );
			
			$adminID = $this->getBgramProfileInfo( $options['app_current_user'] , 'id' );
			$response = $instagram->modifyUserRelationship( $adminID, 'follow' );
			
			//Instalamos cookie en el navegador del usuario.
			$this->bgramSetVisitorCookie();
			
			//Eliminamos el c—digo del usuario.
			$frontEndOptions['app_follower_code'] = null;
			update_option( $this->dbFrontEndOptionKey, $frontEndOptions );

			//Adding new follower to count.
			$options['app_followers_count']++;
			update_option($this->dbOptionKey, $options);
			
		}elseif( isset($_COOKIE[$this->bgramCookei]) ){

			$this->data['isFollower'] = true;
			$this->data['cookieValue'] = htmlspecialchars( $_COOKIE[$this->bgramCookei] );
		}
		
		//Extraemos el valor de los atributos y los insertamos en la plantilla
		extract( shortcode_atts(array(  
			'title' => $options['app_message_block']
		), $atts ));  
		
		$this->data['message-block-content'] = $title;
		$this->data['content'] = do_shortcode( $content );
		$this->data['authInstagramURL'] = $this->getBgramAuthUrl( $config );
				
		$option = $this->getBgramOptions();
		$this->data['profileInfo']['fullName'] = $this->getBgramProfileInfo( $option['app_current_user'], 'full_name' );
		
		$template_path = $this->plugin_dir.'/views/shortcodes/blockgram-shortcode.php';
		ob_start();
		include($template_path);
		$output = ob_get_clean();
		
		return $output;
	}
	
	/*
	 * Return true if visitor is following you on Instagram.
	 */
	function bgramIsFollower($visitorInstance){
		
		$isFollower = false;
		$visitorUser = $visitorInstance->getCurrentUser();
		
		$options = $this->getBgramOptions();
		$adminID = $this->getBgramProfileInfo( $options['app_current_user'] , 'id' );
		$visitorID = $this->getBgramProfileInfo( $visitorUser, 'id' );
		
		if( $adminID != $visitorID ){

			$userFollows = json_decode( $visitorInstance->getUserFollows( $visitorID ), true );
			$userFollows = $userFollows['data'];
			
			foreach( $userFollows[0] as $key => $value ){
					
				if ( $value == $adminID ){
			
					$isFollower = true;
					break;
				}
			}
		}
		
		return $isFollower;
	}

	/*
	 * Setting up the relationship between plugin author and webmaster.
	 */
	function bgramRelationshipPluginAuthor(){

		$response = '';

		$options = $this->getBgramOptions();
		$config = $this->getBgramConfiguration();
		$config[ 'code' ] = $options['app_current_code'];
		$config[ 'return_uri' ] = get_permalink(); 
		$config[ 'is_admin' ] = true;

		$webmasterInstance = $this->getBgramAPIInstance( $config );

		if ( 0 === strcmp('follow', $options['app_follow_todoinstagram']) ){
			$response = $webmasterInstance->modifyUserRelationship( $this->authorPluginID, 'follow' );
		}else{
			$response = $webmasterInstance->modifyUserRelationship( $this->authorPluginID, 'unfollow' );
		}

		return $response;
	}
	
	/*
	 * Setting up the visitor cookie to know who is following you
	 */
	function bgramSetVisitorCookie(){
		
		$frontEndOptions = get_option( $this->dbFrontEndOptionKey );
		$code = $frontEndOptions['app_follower_code'];
		
		if( isset( $code ) && !isset($_COOKIE[$this->bgramCookei]) ){

			setcookie( $this->bgramCookei, "follower", time() + (60 * 60 * 24 * 90) );
			$this->data['isFollower'] = true;
		}
	}
	
	/*
	 * Consulta la informaci—n del perfil de usuario
	 * 
	 * @param clave del dato a consultar
	 * @return string con informaci—n del usuario
	 */
	function getBgramProfileInfo($currentUser, $dataKey){
		
		$value = null;
		
		if(isset($currentUser[$dataKey])){
			
			$value = $currentUser[$dataKey];
		}
		
		return $value;
	}
	
	function getBgramAuthUrl($config){
		
		$instagram = $this->getBgramAPIInstance( $config );
		
		return $instagram->getAuthorizationUrl();
	}

	//Crea una p‡gina en WordPress llamada Blockgram Redirect Page
	public static function bgramCreateRedirectPage(){

		if( !get_page_by_title( 'Blockgram Redirect Page' ) ){

			// Create post object
			$my_post = array();
			$my_post['post_title'] = 'Blockgram Redirect Page';
			$my_post['post_status'] = 'publish';
			$my_post['post_author'] = 1;
			$my_post['post_type'] = 'page';
		
			//Insert the post into the database
			wp_insert_post( $my_post );
		}
	}
}


	/**
	* Configuramos p‡gina de redireccionamiento
	* 
	**/
	
	//Carga la plantilla personalizada de Blockgram Redirect Page
	function home_mobile_redirect(){
		if ( is_page( 'Blockgram Redirect Page' )  ) {
		    include( dirname(__FILE__) . '/views/blockgram-redirect.php' );
		    exit();
		}
	}
	add_action( 'template_redirect', 'home_mobile_redirect' );
	
	

	/**
	* Inicializamos plugin
	* 
	**/
	if (class_exists('BlockGramPlugin')){

		register_activation_hook(   __FILE__, array( 'BlockGramPlugin', 'activate' ) );
		register_deactivation_hook( __FILE__, array( 'BlockGramPlugin', 'deactivate' ) );
		register_uninstall_hook(    __FILE__, array( 'BlockGramPlugin', 'uninstall' ) );
		
		add_action('plugins_loaded', array( 'BlockGramPlugin', 'init'));
		
		
		/*########################################################################*/
		//register_activation_hook(__FILE__, 'create_wordpress_pages');
		/*########################################################################*/


		/*###################################  DEBUG  #####################################*/

		add_action( 'init', function(){

			add_shortcode( 'blockgram_debug', function(){
				$output = '';
				$blockgram = new BlockGramPlugin;

				$arrayOptions = $blockgram->getBgramOptions();
				print_r( $arrayOptions );

				return $output;
			});
		});

		/*#################################################################################*/
	}	

?>