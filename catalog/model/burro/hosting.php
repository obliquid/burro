<?php
class ModelBurroHosting extends Model {
	
	
	/*
	####################################################################################################################################################
	####################################################################################################################################################
	NOTE!!!! cause to opencart architecture (admin separated from catalog), this method is replicated 100% equal in the admin/model/burro/hosting.php file
	####################################################################################################################################################
	####################################################################################################################################################
	*/
	public function getHostings($states=array(),$services=array(),$order_id=0,$customer_id=0,$product_id=0) {
		$query = "SELECT *, " . DB_PREFIX . "order_hosting.email AS hosting_email FROM " . DB_PREFIX . "order_hosting INNER JOIN " . DB_PREFIX . "order ON " . DB_PREFIX . "order_hosting.order_id = " . DB_PREFIX . "order.order_id LEFT JOIN " . DB_PREFIX . "product_hosting ON " . DB_PREFIX . "order_hosting.order_product_id = " . DB_PREFIX . "product_hosting.product_id ";
		$query .= " WHERE 1 = 1 ";
		if ( count($states)>0 ) {
			$query .= " AND ( ";
			foreach ( $states as $state ) {
				$query .= DB_PREFIX . "order_hosting.state = '" . $state . "' OR ";
			}
			$query .= " 1=0 ) ";
		}
		if ( count($services)>0 ) {
			$query .= " AND ( ";
			foreach ( $services as $service ) {
				$query .= DB_PREFIX . "product_hosting.service = '" . $service . "' OR ";
			}
			$query .= " 1=0 ) ";
		}
		if ( (int)$order_id > 0 ) $query .= " AND " . DB_PREFIX . "order.order_id = '" . (int)$order_id . "' ";
		if ( (int)$customer_id > 0 ) $query .= " AND " . DB_PREFIX . "order.customer_id = '" . (int)$customer_id . "' ";
		if ( (int)$product_id > 0 ) $query .= " AND " . DB_PREFIX . "order_hosting.order_product_id = '" . (int)$product_id . "' ";
		
		$query .= " ORDER BY " . DB_PREFIX . "order_hosting.date_start DESC";
		$result = $this->db->query($query);

		return $result->rows;		
		/*
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");

		return $query->rows;		
		*/
	}
		
	public function getHostingsFromSessions($product_id=0) {
		$hostings = array();
		foreach ( $this->session->data['hostings'] as $hosting ) {
			if ( $product_id == 0 || ( $product_id > 0 && $product_id == $hosting['product_id'] ) ) {
				array_push( $hostings, $hosting );
			}
		}
		return $hostings;
	}
		
	public function removeHostingFromSessions($product_id=0) {
		foreach($this->session->data['hostings'] as $k => $v) {
			$hosting = $v;
			if ( (int)$hosting['product_id'] == $product_id ) {
				unset($this->session->data['hostings'][$k]);
			}
		}		
	}
		
	public function saveHostings($order_id) { 
		//$this->log->write("############# saveHostings su order_id:".$order_id);
		//$this->log->writeVar($this->session->data['hostings']);
		foreach ( $this->session->data['hostings'] as $hosting ) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "order_hosting` SET order_id = '" . $order_id . "', order_product_id = '" . $hosting["product_id"] . "', date_end = DATE_ADD(NOW(), INTERVAL ".$hosting["hosting_duration"]." MONTH), state = 'new', renew_order_hosting_id = 0, domain = '".$hosting["hosting_domain_selected"]."', email = '".$hosting["hosting_mailbox_selected"]."', registrant = '".$this->db->escape( serialize( array($hosting["hosting_registrant_type"],$hosting["hosting_registrant_company"],$hosting["hosting_registrant_person"]) ) )."' ");
		}
		//after saving on db, reset sessions
		$this->session->data['hostings'] = array();
		
	}
	
	public function getProductHostings($product_id) {
		/*
		if ($this->customer->isLogged())  {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
		*/		
		$query = "SELECT * FROM " . DB_PREFIX . "product_hosting WHERE product_id = '" . (int)$product_id . "' ORDER BY service ASC";
		$result = $this->db->query($query);

		return $result->rows;		
	}
		
	public function productHostingsContainDomain($product_hostings) {
		$hasDomain = false;
		foreach ( $product_hostings as $hosting ) {
			if ( $hosting['service']=="domain" ) {
				$hasDomain = true;
			}
		}
		return $hasDomain;
	}

	public function getMailboxes($customer_id=0) {
		//QUI!!!
		return array( array("email"=>"john@pippo.com"),array("email"=>"bubu@pippo.com") );
	}
	
	public function getAvailableDomainExt() {
		$availExts = array();
		
		$query = "SELECT " . DB_PREFIX . "product_hosting.extensions, " . DB_PREFIX . "product.product_id FROM " . DB_PREFIX . "product_hosting INNER JOIN " . DB_PREFIX . "product ON " . DB_PREFIX . "product_hosting.product_id = " . DB_PREFIX . "product.product_id WHERE " . DB_PREFIX . "product_hosting.service = 'domain' AND " . DB_PREFIX . "product.status = 1;";
		$result = $this->db->query($query);
		foreach ( $result->rows as $row ) {
			$serviceAvailExt = explode("," , $row["extensions"]);
			foreach ( $serviceAvailExt as $ext ) {
				//search if already have this
				$alreadyHave = false;
				foreach ( $availExts as $availExt ) {
					if ( $availExt["ext"] == $ext ) {
						$alreadyHave = true;
						break;
					}
				}
				if ( !$alreadyHave ) array_push($availExts, array("ext"=>$ext,"product_id"=>$row["product_id"] ) );
			}
		}
		
		return $availExts;
	
	}
	
	
	

	
}
?> 