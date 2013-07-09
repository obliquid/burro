<?php
class ModelBurroHosting extends Model {
	public function Hosting() {
		/*
		$sql = "SELECT x FROM `" . DB_PREFIX . "country`)"; 
		$implode = array();
		$query = $this->db->query($sql);
		return $query->row['total'];	
		*/
	}
	
	/*
	####################################################################################################################################################
	####################################################################################################################################################
	NOTE!!!! cause to opencart architecture (admin separated from catalog), this method is replicated 100% equal in the catalog/model/burro/hosting.php file
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
	
	/* not used, but ok
	public function getBuyableProducts() {
		$query = "SELECT " . DB_PREFIX . "product_hosting.product_id, " . DB_PREFIX . "product_description.name FROM " . DB_PREFIX . "product_hosting INNER JOIN " . DB_PREFIX . "product_description ON " . DB_PREFIX . "product_hosting.product_id = " . DB_PREFIX . "product_description.product_id WHERE " . DB_PREFIX . "product_hosting.service = 'domain' ORDER BY name;";
		$result = $this->db->query($query);
		return $result->rows;				
	
	}
	*/
	
	
}
?>