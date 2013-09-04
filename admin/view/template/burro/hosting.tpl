<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/hosting.png" alt="" /> <?php echo $heading_title; ?></h1>
      
      <div class="buttons"></div>
    </div>
    <div class="content">
      <p><i><?php echo $heading_subtitle; ?></i></p>
		Filter by customer:&nbsp;<select name="hostingCustomer">
			<option value="0">All</option>
			<?php foreach ($hostingCustomers as $customer) { ?> 
				<option value="<?php echo $customer['customer_id']; ?>"       <?php if ( isset( $_GET['customer_id'] ) && $_GET['customer_id'] == $customer['customer_id'] ) echo " selected='selected' " ?>       ><?php echo $customer['firstname']." ".$customer['lastname']." (".$customer['email'].")"; ?></option>
			<?php } ?>
		</select>
		<script>
			$(document).ready(function() { 
				$('select[name="hostingCustomer"]').change( function() {
					var customer_id = $('select[name="hostingCustomer"]').val();
					var url = window.location.href;    
					if (url.indexOf('customer_id') > -1){
						var reExp = /customer_id=\d+/;
						var newUrl = url.replace(reExp, "customer_id=" + customer_id);
					} else {
					   var newUrl = url + '&customer_id=' + customer_id;
					}
					window.location.href = newUrl;
				});
			});
		</script>
		<br/>
		<br/>
		<?php echo $hostingsView; ?>
		
	  
	  
	  
	  
	  
	  
    </div>
  </div>
</div>
<?php echo $footer; ?>