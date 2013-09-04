<?php
class ModelBurroMailing extends Model {
	
	
	public function getPaymentReminderText($order_id, $date_order_string) { 
		//date_end like "YYYY-MM-DD"
		$text = "";
		$date_order = date_create_from_format('Y-m-d H:i:s', $date_order_string);
		$date_order->setTime(0,0,0);
		$date_now = new DateTime();
		$date_now->setTime(0,0,0);
		$interval = $date_now->diff($date_order);
		$interval_days = (int)$interval->format('%R%a');
		//$this->log->write("getPaymentReminderText() con interval_days=".$interval_days); 
		if ( $interval_days == -1 ) {
			$text = "your order (ID: ".$order_id.", date:".$date_order_string.") is awaiting for payment / il tuo ordine (ID: ".$order_id.", data:".$date_order_string.") è in attesa di pagamento";
		} elseif ( $interval_days == -7 ) {
			$text = "your order (ID: ".$order_id.", date:".$date_order_string.") is awaiting for payment since 7 days / il tuo ordine (ID: ".$order_id.", data:".$date_order_string.") è in attesa di pagamento da 7 giorni";
		} elseif ( $interval_days < -7 && abs($interval_days) % 7 == 0 ) {
			$text = "your order (ID: ".$order_id.", date:".$date_order_string.") is awaiting for payment since ".abs($interval_days / 7)." weeks / il tuo ordine (ID: ".$order_id.", data:".$date_order_string.") è in attesa di pagamento da ".abs($interval_days / 7)." settimane";
		}
		return $text;
	}
	
	public function sendMail($dest_email,$subject,$body,$attachments=array(),$dest_name="") {
		//$this->log->write("sendMail() sono in: ".getcwd());
		require_once '../system/library/PHPMailer/class.phpmailer.php';
		//$this->log->write("importato mailer!");
		$mail = new PHPMailer; 
		$mail->IsMail();                                      // Set mailer to use PHP mail()
		$mail->From = $this->config->get('config_email');
		$mail->FromName = $this->config->get('config_name');
		$mail->AddAddress($dest_email); //mando in chiaro al customer
		$mail->AddReplyTo($this->config->get('config_email'));
		$mail->AddBCC($this->config->get('config_email'));//e in copia blind anche all'admin
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		$mail->IsHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		//headers
		$message = $this->model_burro_mailing->getMailTemplate("header");
		//body
		if ( $dest_name != "" ) {
			$message .= "Dear ".$dest_name." / Gentile ".$dest_name.",<br/><br/>"; 
		}
		$message .= $body;
		//footers
		$message .= $this->model_burro_mailing->getMailTemplate("footer");
		//attachments
		if ( is_array( $attachments ) && count( $attachments ) > 0 ) {
			foreach ( $attachments as $att ) {
				$mail->AddAttachment($att["fullpath"], $att["filename"]);  
			}
		}
		//encode
		$mail->Body    = utf8_decode($message);
		$mail->AltBody =  str_replace("<br/>", "\n", $message);
		//and send
		if(!$mail->Send()) {
			$this->log->write( 'sendMail(): Message could not be sent.' );
			$this->log->write( 'sendMail(): Mailer Error: ' . $mail->ErrorInfo );
		}
	
	}
	
	
	public function getMailTemplate($template) {
		$content = "";
		switch ( $template ) {
			case "header":
				$content = "<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01//EN' 'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'><html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'><title>".$this->config->get("config_name")."</title></head><body style='font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;'><img src='http://".$_SERVER["SERVER_NAME"]."/image/data/logo.png' /><br/><br/><br/>
				";
				break;
			case "footer":
				$content = "
				<br/><br/><br/><br/>Thank you / Grazie,<br/><br/>The ".$this->config->get("config_name")." Staff</body></html>";
				break;
		}
		return $content;
	}
	
	/*
	public function getBuyableProducts() {
		$query = "SELECT ".DB_PREFIX."product_hosting.product_id, ".DB_PREFIX."product_description.name FROM ".DB_PREFIX."product_hosting INNER JOIN ".DB_PREFIX."product_description ON ".DB_PREFIX."product_hosting.product_id = ".DB_PREFIX."product_description.product_id WHERE ".DB_PREFIX."product_hosting.service = 'domain' ORDER BY name;";
		$result = $this->db->query($query);
		return $result->rows;				
	
	}
	*/
	
	public function Mailing() {
	}
	
}
?>