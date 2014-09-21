<?php
/**
 * Template Name: Blockgram Redirect Page Template
 *
 * Description: Description: Blockgram Redirect Page es una plantilla personalizada
 * 		que se encarga de guardar el la tabla wp-options, el
 * 		código de acceso del usuario y después hace una redirección
 * 		a la página de destino.
 *
 */
require_once ( dirname(__FILE__).'/../blockgram.php' );

function getReturnURI() {
    if(isset($_GET['return_uri'])){
	
	return $_GET['return_uri'];
    }
    		
    return null;
}

function getAccessCode() {
    
    $blockgram = new BlockgramPlugin;
    $adminURL = $blockgram->getOAuthRedirectUrl();
    
    //Comprobamos si es el Administrador del sitio o si es un visitante
    if( strcmp( $adminURL, getReturnURI() ) == 0 ){
	
	if(isset($_GET['code'])){
	    
	    $options = $blockgram->getBgramOptions();
	    $options[ 'app_current_code' ] = $_GET['code'];
	    update_option($blockgram->dbOptionKey, $options);
	}
    }else{
	update_option( 'BlockgramPlugin_Follower_Options', array());
	if(isset($_GET['code'])){
	
		$followerOption = array( 'app_follower_code' => $_GET['code'] );
		update_option( $blockgram->dbFrontEndOptionKey, $followerOption);
	}
    }
    
    /*##############################################*/
    return array( $adminURL, getReturnURI());
}

$links = getAccessCode();
header( 'Location: '.getReturnURI() );
exit();

?>
<h1>Optigram Redirect Page</h1>
<h2><?php echo 'Admin URL_____'.$links[0]; ?></h2>
<h2><?php echo 'Visitor URL_____'.$links[1]; ?></h2>
<h2><?php $returnURI = getReturnURI(); echo 'Return URI_____'.$returnURI; ?></h2>
<h2><?php echo 'Current URL_____'.get_permalink(); ?></h2>