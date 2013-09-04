<?
	
class ControllerBurroHosting extends Controller{ 
	public function index(){
		// VARS
		$template="burro/hosting.tpl"; // .tpl location and file
		$this->language->load('burro/hosting');
		$this->load->model('burro/hosting');
		$this->document->setTitle($this->language->get('heading_title'));
		
		//get all available customers
		$hostingCustomers = $this->model_burro_hosting->getHostingCustomers();
		
		//get hostings for selected customer (or for all customers if noone is selected)
		if ( isset( $this->request->get['customer_id'] ) ) {
			$customer_id = $this->request->get['customer_id'];
		} else {
			$customer_id = 0;
		}
		$hostingsView = $this->model_burro_hosting->drawHostings(true, $customer_id);
		
		
		
		//vars for template
		$this->data['hostingCustomers'] = $hostingCustomers; 
		$this->data['hostingsView'] = $hostingsView;  
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
       		'text'      => 'Hostings : Services',
			'href'      => $this->url->link('burro/hosting', 'token=' . $this->session->data['token'], 'SSL'),
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
	
	//this method is an ajax-callable wrapper for model method "update"
	public function update() {
	
		$jsonResult = array();

		if (isset($this->request->post['order_hosting_id'])) {
			$order_hosting_id = $this->request->post['order_hosting_id'];
		} else {
			$order_hosting_id = 0;
		}
		if (isset($this->request->post['state'])) {
			$state = $this->request->post['state'];
		} else {
			$state = "";
		}
		if (isset($this->request->post['date_start'])) {
			$date_start = str_replace ( "‑", "-", $this->request->post['date_start'] );
		} else {
			$date_start = "";
		}
		if (isset($this->request->post['date_end'])) {
			$date_end = str_replace ( "‑", "-", $this->request->post['date_end'] );
		} else {
			$date_end = "";
		}
		if (isset($this->request->post['server'])) {
			$server = $this->request->post['server'];
		} else {
			$server = "";
		}
		if (isset($this->request->post['domain'])) {
			$domain = $this->request->post['domain'];
		} else {
			$domain = "";
		}
		if (isset($this->request->post['email'])) {
			$email = $this->request->post['email'];
		} else {
			$email = "";
		}
		
		//call model method 
		$this->load->model('burro/hosting');
		$updateResult = $this->model_burro_hosting->orderHostingUpdate( $order_hosting_id, $state, $date_start, $date_end, $server, $domain, $email ); 
		
		if ( $updateResult ) {
			$jsonResult['success'] = "OK!";
		} else {
			$jsonResult['error'] = "error during update query in admin/model/burro/hosting/orderHostingUpdate";
		}
		$this->response->setOutput(json_encode($jsonResult));
		
	}	
	
	
	
	
	
	
}
?>