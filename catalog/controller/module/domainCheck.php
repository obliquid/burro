<?php  
class ControllerModuleDomainCheck extends Controller {
	protected function index($setting) {
		static $module = 0;
		
		
		//$this->log->write("uèuèuèuèèuèuèuèuèuèuèuè: ");
		//$this->log->writeVar($setting);
		
		//////$this->data['allowed_domains'] = explode( ",", $setting['domain_check'] );
		//////$this->data['buyable_product_id'] = $setting['product_id'];
		
		$this->load->model('burro/hosting');
		$this->data['allowed_domains'] = $this->model_burro_hosting->getAvailableDomainExt();
		
		
		
		$this->data['module'] = $module++;
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/domainCheck.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/domainCheck.tpl';
		} else {
			$this->template = 'default/template/module/domainCheck.tpl';
		}
		
		$this->render();
		/*
		$this->data['banners'] = array();
		
		$results = $this->model_design_banner->getBanner($setting['banner_id']);
		  
		foreach ($results as $result) {
			if (file_exists(DIR_IMAGE . $result['image'])) {
				$this->data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}
		
		$this->data['module'] = $module++;
				
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/domainCheck.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/domainCheck.tpl';
		} else {
			$this->template = 'default/template/module/domainCheck.tpl';
		}
		
		$this->render();
		
		*/
	}
	
	public function checkDomain() {
		
	}
}
?> 