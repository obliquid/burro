<?php
class ModelBurroHosting extends Model {
	
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
		
	public function getHostings($states=array(''),$services=array(''),$order_id=0,$customer_id=0) {
		$query = "SELECT * FROM " . DB_PREFIX . "order_hosting INNER JOIN " . DB_PREFIX . "order ON " . DB_PREFIX . "order_hosting.order_id = " . DB_PREFIX . "order.order_id LEFT JOIN " . DB_PREFIX . "product_hosting ON " . DB_PREFIX . "order_hosting.order_product_id = " . DB_PREFIX . "product_hosting.product_id ";
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
		if ( $order_id > 0 ) $query .= " AND " . DB_PREFIX . "order.order_id = '" . (int)$order_id . "' ";
		if ( $customer_id > 0 ) $query .= " AND " . DB_PREFIX . "order.customer_id = '" . (int)$customer_id . "' ";
		
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
		
	public function getMailboxes($customer_id=0) {
		//QUI!!!
		return array( array("email"=>"john@pippo.com"),array("email"=>"bubu@pippo.com") );
	
	
	
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
		
}
?>