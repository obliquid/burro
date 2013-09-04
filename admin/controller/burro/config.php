<?
	
class ControllerBurroConfig extends Controller{ 
	public function index(){
		// VARS
		$template="burro/config.tpl"; // .tpl location and file		
		$this->language->load('burro/config');
		$this->load->model('burro/config');
		$this->document->setTitle($this->language->get('heading_title'));
		global $log;
		
		//load burro config
		require("config_burro.php");
		
		//add burro tables to db, if they do not exist already
		$msg_db = $this->model_burro_config->updateDbForBurro();
		
		//check for each server in config_burro.php if ispconnection works
		$msg_ispc = "";
		foreach ( $BURRO_SERVERS as $server) {
			//$this->log->writeVar($server);
			
			
			//faccio i controlli della connessione in base al tipo di server software
			if ( isset( $server["software"] ) ) switch ( $server["software"] ) {
				case "ispconfig":
					//controllo connessioni per server ispconfig
					
					// Parametri connessione SOAP
					$username = $server["ispconfig_username"];
					$password = $server["ispconfig_password"];
					$soap_location = $server["ispconfig_soap_location"];
					$soap_uri = $server["ispconfig_soap_uri"];

					// Inizializzazione del client SOAP
					$client = new SoapClient(null, array(
						'location' => $soap_location, 
						'uri' => $soap_uri
					));
					
					try {
						// Autenticazione del client SOAP	
						if($session_id = $client->login($username,$password)) {
							// Conferma collegamento con ID di sessione
							$msg_ispc .= "Connection to ISPConfig server <b>".$soap_uri."</b> is working correctly.<br/>";
						} else {
							$msg_ispc .= "<b>ERROR</b> Connection to ISPConfig server <b>".$soap_uri."</b> FAILED<br/>";
						}
					} catch (SoapFault $e) {
							//die('SOAP Error: '.$e->getMessage());
							$msg_ispc .= "<b>ERROR</b> Connection to ISPConfig server <b>".$soap_uri."</b> FAILED (".$e->getMessage().")<br/>";
							$this->log->write( "Connessione SOAP fallita." );
					}
					
					//control direct mysql connection
					if ( $mysqli = new mysqli($server["ispconfig_db_host"], $server["ispconfig_db_username"], $server["ispconfig_db_pw"], $server["ispconfig_db_name"]) ) {
						/* check connection */
						if ($mysqli->connect_errno) {
							$msg_ispc .= "<b>ERROR</b> Connection to mysql server <b>".$server["ispconfig_db_host"]."</b> FAILED (".$mysqli->connect_error.")<br/>";
							$this->log->write( "Connessione mysql fallita." );
						} else {
							$msg_ispc .= "Connection to mysql server <b>".$server["ispconfig_db_host"]."</b> is working correctly.<br/>";
						}
						$mysqli->close();		
					} else {
					
					}
					
					break;
			}
			
			
		}
		
		
		
		
		
		
		//vars for template
		$this->data['database_message'] = $msg_db;		
		$this->data['ispconfig_message'] = $msg_ispc;		
		$this->data['heading_title'] = $this->language->get('heading_title');		
		$this->data['heading_subtitle'] = $this->language->get('heading_subtitle');		
		$this->data['body01'] = $this->language->get('body01');		
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => 'Hostings : Configuration',
			'href'      => $this->url->link('burro/config', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		$this->data['error_warning'] = "";
		$this->data['success'] = "";
		
		$this->template = ''.$template.'';
		$this->children = array(
			'common/header',
			'common/footer'
		);	  
		$this->response->setOutput($this->render());
	}
}
?>