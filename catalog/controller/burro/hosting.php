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
			//$this->log->write("##############################################");
			//$this->log->write("##############################################");
			//$this->log->write("##############################################");
			//$this->log->write("##############################################");
			//$this->log->write("##############################################");
			//$this->log->write("##############################################");
			//$this->log->write("##############################################");
			//$this->log->write("##############################################");
			//$this->log->write("##############################################");
			//$this->log->write("##############################################");
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

			
			
			/* this version uses phpwhois library
			$whois = new Whois();
			$whois->deep_whois = false;
			$result = $whois->Lookup($domain,false);
			$this->log->write("domainAvail() per ".$domain." ottengo:");
			$this->log->writeVar($result);
			
			if ( isset($result["rawdata"]) && count($result["rawdata"]) > 0 ) {
				$rawdata = implode("",$result["rawdata"]);
			} else {
				$rawdata = "";
			}
			
			if ( 
				( 
					isset($result["regrinfo"]["registered"]) 
					&& 
					$result["regrinfo"]["registered"] == "no" 
				) 
				||
				( 
					isset($result["regrinfo"]["domain"]["status"]) 
					&& 
					$result["regrinfo"]["domain"]["status"] == "AVAILABLE" 
				) 
				|| 
				( 
					isset($result["rawdata"][0]) 
					&& 
					$result["rawdata"][0] == "NOT FOUND" 
				) 
				|| 
				( 
					(strpos($rawdata,'No match for domain') !== false)
				) 
				|| 
				( 
					(strpos($rawdata,'This domain name has not been registered') !== false)
				)  
				|| 
				( 
					(strpos($rawdata,'The domain has not been registered') !== false)
				)  
			) { 
				//domain is available!
				$result = array( true, implode("\n",$result["rawdata"]) );
			} else {
				//domain is registered!
				$result = array( false, implode("\n",$result["rawdata"]) );
			}
			*/
			
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
		if (isset($this->request->post['hosting_domain'])) {
			$hosting_domain = $this->request->post['hosting_domain'];
		} else {
			$hosting_domain = '';
		}
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
		/*
		$this->log->write("hosting.php add(): hosting_registrant_type:");
		$this->log->writeVar($hosting_registrant_type);
		$this->log->write("hosting.php add(): hosting_registrant_company:");
		$this->log->writeVar($hosting_registrant_company);
		$this->log->write("hosting.php add(): hosting_registrant_person:");
		$this->log->writeVar($hosting_registrant_person);
		*/
		
		
		
		
		
		
		if ( $is_hosting == 'yes' ) {
			/*
			$this->log->write( "ciao, sono catalog/controller/burro/hosting/add(), e mi arriva in post:" );
			$this->log->writeVar($this->request->post);
			$this->log->write( "da cui estraggo product_id = ".$product_id );
			$this->log->write( "da cui estraggo hosting_duration = ".$hosting_duration );
			$this->log->write( "da cui estraggo hosting_size = ".$hosting_size );
			$this->log->write( "da cui estraggo hosting_quantity = ".$hosting_quantity );
			$this->log->write( "da cui estraggo hosting_domain = ".$hosting_domain );
			$this->log->write( "da cui estraggo hosting_domain_extension = ".$hosting_domain_extension );
			$this->log->write( "da cui estraggo hosting_domain_selected = ".$hosting_domain_selected );
			$this->log->write( "da cui estraggo hosting_mailbox_selected = ".$hosting_mailbox_selected );
			*/
			
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
					'hosting_size' => $hosting_size,
					'hosting_quantity' => $hosting_quantity,
					'hosting_renew_order_hosting_id' => 0, //QUI!!! DA FARE
					'hosting_registrant_type' => $hosting_registrant_type,
					'hosting_registrant_company' => $hosting_registrant_company,
					'hosting_registrant_person' => $hosting_registrant_person
				) 
			);
			
			//$this->log->write('dopo che ho aggiunto le session valgono: ');
			//$this->log->writeVar($this->session->data['hostings']);
			
		}
		
		$jsonHosting['success'] = "OK!";
		$this->response->setOutput(json_encode($jsonHosting));
		
		
		/*
		$this->language->load('checkout/cart');
		
		
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}
		
		$this->load->model('catalog/product');
						
		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		if ($product_info) {			
			if (isset($this->request->post['quantity'])) {
				$quantity = $this->request->post['quantity'];
			} else {
				$quantity = 1;
			}
														
			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();	
			}
			
			$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);
			
			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}
			
			if (!$json) {
				$this->cart->add($this->request->post['product_id'], $quantity, $option);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));
				
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
				
				// Totals
				$this->load->model('setting/extension');
				
				$total_data = array();					
				$total = 0;
				$taxes = $this->cart->getTaxes();
				
				// Display prices
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$sort_order = array(); 
					
					$results = $this->model_setting_extension->getExtensions('total');
					
					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
					}
					
					array_multisort($sort_order, SORT_ASC, $results);
					
					foreach ($results as $result) {
						if ($this->config->get($result['code'] . '_status')) {
							$this->load->model('total/' . $result['code']);
				
							$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
						}
						
						$sort_order = array(); 
					  
						foreach ($total_data as $key => $value) {
							$sort_order[$key] = $value['sort_order'];
						}
			
						array_multisort($sort_order, SORT_ASC, $total_data);			
					}
				}
				
				$json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total));
			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			}
		}
		
		*/
	}
	
}
?>
