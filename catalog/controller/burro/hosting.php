<?php 

include('catalog/controller/burro/phpwhois/whois.main.php');

class ControllerBurroHosting extends Controller {
	private $error = array();
	
	public function domainAvail() { 
	
		$result = array();
	
		if (isset($this->request->post['domain'])) {
			$domain_name = $this->request->post['domain'];
		} else {
			$domain_name = '';
		}
		if (isset($this->request->post['domain_extension'])) {
			$domain_extension = $this->request->post['domain_extension'];
		} else {
			$domain_extension = '';
		}
		
		if ( $domain_name != '' && $domain_extension != '' ) {
			$domain = $domain_name.".".$domain_extension;
			
			//this version uses whois shell command
			$whoisOutput = shell_exec('whois '.$domain);
			//$this->log->writeVar($whoisOutput);
			
			if
			(
				( 
					(strpos($whoisOutput,'AVAILABLE') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'NOT FOUND') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'No match for domain') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'No Found') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'No match!!') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'No match for') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'Status: free') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'No entries found in the AFNIC Database') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'no matching record') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'This domain name has not been registered') !== false)
				)  
				|| 
				( 
					(strpos($whoisOutput,'The domain has not been registered') !== false)
				)  
			)
			{ 
				//domain is available!
				$result = array( true, $whoisOutput );
			} else {
				//domain is registered!
				$result = array( false, $whoisOutput );
			}
			
		} 

		$this->response->setOutput(json_encode($result));

	}
	
	public function add() {
	
		$jsonHosting = array();
		
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}
		if (isset($this->request->post['is_hosting'])) {
			$is_hosting = $this->request->post['is_hosting'];
		} else {
			$is_hosting = 'no';
		}
		if (isset($this->request->post['hosting_renew_order_hosting_id']) && (int)$this->request->post['hosting_renew_order_hosting_id'] > 0 ) {
			//sto aggiungendo un rinnovo di hosting esistente
			$hosting_renew_order_hosting_id = (int)$this->request->post['hosting_renew_order_hosting_id'];
			//la data di inizio del nuovo servizio deve essere uguale alla data di scadenza dell'hosting che sto rinnovando, nella forma "2012-07-22 00:00:00"
			$hosting_renew_date_start = $this->request->post['hosting_renew_date_end']; 
			//calcolo la nuova data di scadenza
			$old_date_start = date_create_from_format('Y-m-d H:i:s', $this->request->post['hosting_renew_date_start']);
			$old_date_start->setTime(0,0,0);
			$old_date_end = date_create_from_format('Y-m-d H:i:s', $this->request->post['hosting_renew_date_end']);
			$old_date_end->setTime(0,0,0);
			//$this->log->writeVar($old_date_end);
			//$this->log->writeVar($old_date_start);
			//calcolo la durata (interval_days) del vecchio (e quindi anche del nuovo) servizio
			$interval = $old_date_start->diff($old_date_end);
			$interval_days = (int)$interval->format('%R%a');
			//la nuova data di scadenza sarà data dalla scadenza del vecchio servizio più la durata
			date_modify($old_date_end, '+'.$interval_days.' days');
			$hosting_renew_date_end = $old_date_end->format('Y-m-d H:i:s');
			//salvo anche il vecchio server
			$hosting_renew_server = $this->request->post['hosting_renew_server'];
		} else {
			//sto aggiungengo un nuovo hosting
			$hosting_renew_order_hosting_id = 0;
			$hosting_renew_date_start = "";
			$hosting_renew_date_end = "";
			$hosting_renew_server = "";
		}
		if (isset($this->request->post['hosting_domain'])) { 
			$hosting_domain = $this->request->post['hosting_domain'];
		} else {
			$hosting_domain = '';
		}
		/*
		if (isset($this->request->post['hosting_duration'])) {
			$hosting_duration = (int)$this->request->post['hosting_duration'];
		} else {
			$hosting_duration = 0;
		}
		if (isset($this->request->post['hosting_size'])) {
			$hosting_size = (int)$this->request->post['hosting_size'];
		} else {
			$hosting_size = 0;
		}
		if (isset($this->request->post['hosting_quantity'])) {
			$hosting_quantity = (int)$this->request->post['hosting_quantity'];
		} else {
			$hosting_quantity = 0;
		}
		*/
		
		if (isset($this->request->post['hosting_domain_extension'])) {
			$hosting_domain_extension = $this->request->post['hosting_domain_extension'];
		} else {
			$hosting_domain_extension = '';
		}
		if (isset($this->request->post['hosting_domain_selected'])) {
			$hosting_domain_selected = $this->request->post['hosting_domain_selected'];
		} else {
			if (isset($this->request->post['hosting_domain']) && isset($this->request->post['hosting_domain_extension']) ) {
				$hosting_domain_selected = $this->request->post['hosting_domain'].'.'.$this->request->post['hosting_domain_extension'];
			} else {
				$hosting_domain_selected = '';
			}
		}
		if (isset($this->request->post['hosting_mailbox_selected'])) {
			$hosting_mailbox_selected = $this->request->post['hosting_mailbox_selected'];
		} else {
			$hosting_mailbox_selected = '';
		}
		
		//registrant
		if (isset($this->request->post['hosting_registrant_type'])) {
			$hosting_registrant_type = $this->request->post['hosting_registrant_type'];
		} else {
			$hosting_registrant_type = '';
		}
		if (isset($this->request->post['hosting_registrant_company'])) {
			//$hosting_registrant_company = $this->request->post['hosting_registrant_company'];
			parse_str( html_entity_decode( $this->request->post['hosting_registrant_company'] ), $hosting_registrant_company);
		} else {
			$hosting_registrant_company = array();
		}
		if (isset($this->request->post['hosting_registrant_person'])) {
			//$hosting_registrant_person = $this->request->post['hosting_registrant_person'];
			parse_str( html_entity_decode( $this->request->post['hosting_registrant_person'] ), $hosting_registrant_person);
		} else {
			$hosting_registrant_person = array();
		}
		if (isset($this->request->post['hosting_registrant'])) {
			$hosting_registrant = $this->request->post['hosting_registrant'];
		} else {
			$hosting_registrant = array();
		}
		
		if ( $is_hosting == 'yes' ) {
			/*
			$this->log->write( "ciao, sono catalog/controller/burro/hosting/add(), e mi arriva in post:" );
			$this->log->writeVar($this->request->post);
			*/
			
			//must populate with hostings saved in oc_product_hostings for my product_id
			$this->load->model('burro/hosting');
			$hostings = $this->model_burro_hosting->getProductHostings($product_id);
			
			//get longest hosting associated to product, and have duration for oc_order_hostings
			$hosting_duration = 0;
			foreach ( $hostings as $my_hosting ) {
				if ( isset( $my_hosting['duration'] ) &&  (int)$my_hosting['duration'] > $hosting_duration ) {
					$hosting_duration = (int)$my_hosting['duration'];
				}
			}
			
			//if sessions for hostings are not created yet, init it!
			if ( !isset( $this->session->data['hostings'] ) ) $this->session->data['hostings'] = array();
			
			//store in sessions
			array_push($this->session->data['hostings'], 
				array( 
					'product_id' => $product_id,
					'hosting_domain' => $hosting_domain,
					'hosting_domain_extension' => $hosting_domain_extension,
					'hosting_domain_selected' => $hosting_domain_selected,
					'hosting_mailbox_selected' => $hosting_mailbox_selected,
					'hosting_duration' => $hosting_duration,
					//'hosting_size' => $hosting_size,
					//'hosting_quantity' => $hosting_quantity,
					'hostings' => $hostings,
					'hosting_renew_order_hosting_id' => $hosting_renew_order_hosting_id,
					'hosting_registrant_type' => $hosting_registrant_type,
					'hosting_registrant_company' => $hosting_registrant_company,
					'hosting_registrant_person' => $hosting_registrant_person,
					'hosting_registrant' => $hosting_registrant,
					'hosting_renew_date_start' => $hosting_renew_date_start,
					'hosting_renew_date_end' => $hosting_renew_date_end,
					'hosting_renew_server' => $hosting_renew_server
				) 
			);
			
			//$this->log->write('dopo che ho aggiunto le session valgono: ');
			//$this->log->writeVar($this->session->data['hostings']);
			
		}
		
		$jsonHosting['success'] = "OK!";
		$this->response->setOutput(json_encode($jsonHosting));
		
	}
	
}
?>
