<?
	
class ControllerBurroMailing extends Controller{ 
	public function index(){
		// VARS
		$template="burro/mailing.tpl"; // .tpl location and file
		$this->language->load('burro/mailing');
		$this->load->model('burro/mailing');
		$this->document->setTitle($this->language->get('heading_title'));
		
		
		
		
		//vars for template
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
       		'text'      => 'Hostings : Mailing',
			'href'      => $this->url->link('burro/mailing', 'token=' . $this->session->data['token'], 'SSL'),
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

	public function sendPaymentReminders() {   
		//$this->log->write("sendPaymentReminders()");  
		$sent_reminders = array();
		$this->load->model('sale/order');
		$this->load->model('burro/mailing');
		$this->load->model('burro/task');
		$data = array(
			"filter_order_status_id"=>1,
			"sort"=>"o.date_added",
			"start"=>"0",
			"limit"=>"10000"
		);
		$orders = $this->model_sale_order->getOrders($data);
		//$this->log->write("sendPaymentReminders chiamato, trova orders: ");
		//$this->log->writeVar($orders);
		
		foreach ( $orders as $order ) {
			//check if there is a mail to be sent for this order
			//$this->log->write("sendPaymentReminders ciclo su order: ".$order["order_id"]);
			$text = $this->model_burro_mailing->getPaymentReminderText($order["order_id"], $order["date_added"]);
			//$this->log->write("sendPaymentReminders ottengo text: ".$text);
			if ( $text != "" ) {
				//$this->log->write("quindi mando email...");
				//must send a mail
				//get order detail
				$orderDetail = $this->model_sale_order->getOrder($order["order_id"]);

				//get settings
				$this->load->model('setting/setting');
				//$this->log->write("caricato il model...");
				$settings = $this->model_setting_setting->getSetting('bank_transfer');
				//$this->log->write("ottenuto settings:");
				//$this->log->writeVar($settings);
				//$bank_transfer_instructions = $settings["bank_transfer_bank_1"];
				$bank_transfer_instructions = str_replace("\n", "<br/>", $settings["bank_transfer_bank_1"]);
				//$this->log->write("da cui bank_transfer_instructions:");
				//$this->log->writeVar($bank_transfer_instructions);
				
				
				//send email to customer with reminder
				$order_total = (round($order["total"]*100)/100).$order["currency_code"];
				$subject = $this->config->get("config_name")." - payment reminder / sollecito di pagamento (ord.ID ".$order["order_id"].", date ".$order["date_added"].", tot ".$order_total.")";
				//body
				$message = "";
				$message .= "<h4>".$text."</h4>";
				$message .= "<p>we kindly request to proceed as soon as possible with payment of <a href='http://".$_SERVER["SERVER_NAME"]."/index.php?route=account/order/info&order_id=".$order["order_id"]."'>this order (tot. ".$order_total.")</a>.</p>";
				$message .= "<p>preferred payment method for pending orders is a Bank Tranfer to:</p>";
				$message .= "<p>".$bank_transfer_instructions."</p>";
				$message .= "<p>if you already paid this order, please reply to this email sending us a receipt of your payment.</p>";
				$message .= "<hr/>";
				$message .= "<p>vi chiediamo cortesemente di provvedere al più presto al pagamento di <a href='http://".$_SERVER["SERVER_NAME"]."/index.php?route=account/order/info&order_id=".$order["order_id"]."'>questo ordine (tot. ".$order_total.")</a>.</p>";
				$message .= "<p>il metodo preferenziale per il pagamento di ordini in sospeso è tramite Bonifico Bancario a:</p>";
				$message .= "<p>".$bank_transfer_instructions."</p>";
				$message .= "<p>se avete già pagato questo ordine, vi preghiamo di rispondere a questa email inviandoci una ricevuta del pagamento.</p>";
				//send
				$this->model_burro_mailing->sendMail($orderDetail["email"],$subject,$message);
				//and add to results
				array_push($sent_reminders,array(
					"customer"=>$order["customer"],
					"text"=>$text,
					"order_id"=>$order["order_id"]
				));
			}
		}
		//$this->log->write("sendPaymentReminders ooèoèoè io ritornerei: ");
		//$this->log->writeVar($sent_reminders);
		$this->response->setOutput(json_encode($sent_reminders));
		//return $sent_reminders; 
	}
	
	public function sendSuspendReminders() {  
		//$this->log->write("sendSuspendReminders()");  
		$sent_reminders = array();
		$this->load->model('burro/hosting');
		//$this->load->model('sale/order');
		$this->load->model('burro/mailing');
		//$this->load->model('burro/task');
		
		//get all active hostings
		$hostings = $this->model_burro_hosting->getHostings(array("active"),array(),0,0,0,array(),false);
		//$this->log->write("sendSuspendReminders chiamato, trova hostings: ");
		//$this->log->writeVar($hostings);
		foreach ( $hostings as $hosting ) {
			//check if there is a mail to be sent for this hosting
			//$this->log->write("sendSuspendReminders ciclo su hosting: ".$hosting["order_hosting_id"]);
			$suspendReminderLabelAndPriority = $this->model_burro_hosting->getSuspendReminderLabelAndPriority($hosting["date_end"]);
			$text = $suspendReminderLabelAndPriority[0];
			//$priority = $suspendReminderLabelAndPriority[1];
			$interval_days = $suspendReminderLabelAndPriority[2];
			//$this->log->write("sendSuspendReminders ottengo text: ".$text);
			if ( $text != "" ) {
				//$this->log->write("quindi mando email...");
				//must send a mail

				//get hosting detail (with product hostings and product details)
				$hostingsDetail = $this->model_burro_hosting->getHostings(array(),array(),0,0,0,array(),true,$hosting["order_hosting_id"]);
				$hostingDetail = array_pop($hostingsDetail);
				//$this->log->write("sendSuspendReminders devo mandare mail per hostingDetail:");
				//$this->log->writeVar($hostingDetail);
				$hosting_complex_name = $hostingDetail["product_details"]["name"];
				if ( $hosting["domain"] != "" ) $hosting_complex_name .= " on ".$hosting["domain"];
				if ( $hosting["hosting_email"] != "" ) $hosting_complex_name .= " for ".$hosting["hosting_email"];
				$hosting_complex_name .= " (exp. date ".substr($hosting["date_end"],0,10).")";
				
				
				
				//send email to customer with reminder
				$subject = $this->config->get("config_name")." - expiration reminder in ".$interval_days." days / avviso di scadenza tra ".$interval_days." giorni : ".$hosting_complex_name;
				//body
				$message = "
				<h4>Your service / Il tuo servizio <i>".$hosting_complex_name."</i> ".$text."</h4>";
				$message .= "
				<p>Expiring service includes / Il servizio in scadenza comprende:</p><small><ul>";
				if ( $hosting["domain"] != "" ) {
					$message .= "<li>Selected Domain: ".$hosting["domain"]."</li>";
				}
				if ( $hosting["hosting_email"] != "" ) {
					$message .= "<li>Selected Mailbox: ".$hosting["hosting_email"]."</li>";
				}
				if ( isset($hostingDetail["product_hostings"]) && count($hostingDetail["product_hostings"]) > 0 ) {
					foreach ( $hostingDetail["product_hostings"] as $my_product_hosting ) {
						$message .= "<li>
						";
						if ( isset( $my_product_hosting["quantity"] ) && (int)$my_product_hosting["quantity"] > 0 ) {
							$product_hosting_qty = (int)$my_product_hosting["quantity"];
						} else {
							$product_hosting_qty = 1;
						}
						$message .= "<strong>".$product_hosting_qty."</strong> x <strong>".$my_product_hosting["service"]."</strong>";
						if ( isset( $my_product_hosting["size"] ) && (int)$my_product_hosting["size"] > 0 ) {
							$message .= ", <strong>".$my_product_hosting["size"]."</strong> GB";
						}
						if ( isset( $my_product_hosting["duration"] ) && (int)$my_product_hosting["duration"] > 0 ) {
							$message .= ", <strong>".$my_product_hosting["duration"]."</strong> months";
						}
						$message .= "</li>";
					}
				}
				$message .= "</ul></small>";
				$message .= "
				<p>To renew the service go to <a href='http://".$_SERVER["SERVER_NAME"]."/index.php?route=account/login'>".$this->config->get("config_name")." website</a>, authenticate, then go to <a href='http://".$_SERVER["SERVER_NAME"]."/index.php?route=account/hosting'>your services page</a> and add to cart services you want to renew using the cart button for each.<br/>Finally checkout and pay your order.</p>";
				$message .= "<hr/>";
				$message .= "
				<p>Per rinnovare il servizio vai sul <a href='http://".$_SERVER["SERVER_NAME"]."/index.php?route=account/login'>sito ".$this->config->get("config_name")."</a>, accedi con le tue credenziali, quindi vai alla pagina <a href='http://".$_SERVER["SERVER_NAME"]."/index.php?route=account/hosting'>i tuoi servizi</a> e aggiungi al carrello i servizi che vuoi rinnovare usando il relativo bottone di ciascuno.<br/>Infine vai alla cassa e acquista il tuo carrello effettuando il pagamento secondo le modalità previste.</p>";
				//send email
				$this->model_burro_mailing->sendMail($hosting["email"],$subject,$message); 
				//and add to results 
				array_push($sent_reminders,array(
					"customer"=>$hosting["firstname"]." ".$hosting["lastname"],
					"text"=>"Your service / Il tuo servizio : ".$hosting_complex_name." ".$text
				));
			}
		}
		//$this->log->write("sendSuspendReminders ooèoèoè io ritornerei: ");
		//$this->log->writeVar($sent_reminders);
		$this->response->setOutput(json_encode($sent_reminders));
		//return $sent_reminders; 
	}
	
	

}
?>