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
      <h1><img src="view/image/mailing.png" alt="" /> <?php echo $heading_title; ?></h1>
      
      <div class="buttons"></div>
    </div>
    <div class="content">
      <p><i><?php echo $heading_subtitle; ?></i></p>
	
	<script>
		function sendSuspendReminders() {
			blockUI(); 
			$.ajax({ 
				url: 'index.php?route=burro/mailing/sendSuspendReminders&token=<?php echo $this->session->data['token'];  ?>',
				type: 'post',
				data: { 
					'nullaperora' : 'appunto'
				}, 
				dataType: 'json',
				success: function(sent_reminders) {
					$('#send_suspend_button').removeAttr("onclick");
					$('#send_suspend_button').html("done");
					unblockUI();
					if ( sent_reminders !== null && sent_reminders.length > 0 ) {
						for ( var x=0; x < sent_reminders.length; x++ ) {
							$('#suspend_results').append("To <strong>"+sent_reminders[x]['customer']+"</strong>: <i>"+sent_reminders[x]['text']+"</i><br/>"); 
						}
						$('#suspend_results').addClass("success");
						$('#suspend_results').fadeIn();
					} else {
						console.log("no results");
						$('#suspend_results').addClass("attention");
						$('#suspend_results').html("Today there are no services that need reminders about suspensions. For a service near its expiration date not every day is sent a reminder. See timing below to know when reminders are sent.");
						$('#suspend_results').fadeIn();
					}
				}
			});
		}
		function sendPaymentReminders() {
			blockUI(); 
			$.ajax({ 
				url: 'index.php?route=burro/mailing/sendPaymentReminders&token=<?php echo $this->session->data['token'];  ?>',
				type: 'post',
				data: { 
					'nullaperora' : 'appunto'
				}, 
				dataType: 'json',
				success: function(sent_reminders) {
					$('#send_payment_button').removeAttr("onclick");
					$('#send_payment_button').html("done");
					unblockUI();
					if ( sent_reminders !== null && sent_reminders.length > 0 ) {
						for ( var x=0; x < sent_reminders.length; x++ ) {
							$('#payment_results').append("<a href='index.php?route=sale/order/info&token=<?php echo $this->session->data['token'];  ?>&order_id="+sent_reminders[x]['order_id']+"' target='_blank'><img src='view/image/order.png' style='cursor:pointer;' title='View order' /></a> To <strong>"+sent_reminders[x]['customer']+"</strong>: <i>"+sent_reminders[x]['text']+"</i><br/>"); 
						}
						$('#payment_results').addClass("success");
						$('#payment_results').fadeIn();
					} else {
						console.log("no results");
						$('#payment_results').addClass("attention");
						$('#payment_results').html("Today there are no orders that need reminders about payments. For an order in pending state (not paid) not every day is sent a reminder. See timing below to know when reminders are sent.");
						$('#payment_results').fadeIn();
					}
				}
			});
		}
		function blockUI() {
			$('body').prepend('<div id="fuckin_blocking_wall" style="position:fixed;z-index:10000;background-image:url(view/image/nero50perc.png);width:100%;height:100%;"></div>');
		}
		function unblockUI() {
			$('#fuckin_blocking_wall').remove();
		}
	
	</script>
	<table cellpadding="10">
		<tr>
			<td>
				<h3>
					<img src="view/image/mailing_suspend.png" alt="" />&nbsp;&nbsp;Suspend reminder emails
				</h3>
			</td>
			<td>
				<h3>
					<img src="view/image/mailing_payment.png" alt="" />&nbsp;&nbsp;Payment reminder emails
				</h3>
			</td>
		</tr>
		<tr>
			<td>
				<a id="send_suspend_button" onclick="sendSuspendReminders();" class="button">Send now!</a>
			</td>
			<td>
				<a id="send_payment_button" onclick="sendPaymentReminders();" class="button">Send now!</a>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					By clicking this button you will send a reminder email for each <strong>service near or beyond its expiration date</strong>. 
				</p>
			</td>
			<td>
				<p>
					By clicking this button you will send a reminder email for each <strong>pending order (orders not yet paid)</strong>.  
				</p>
			</td>
		</tr>
		<tr>
			<td>
				<div id="suspend_results" style="display:none;">
					<h3>These emails have been sent:</h3>
				</div>
			</td>
			<td>
				<div id="payment_results" style="display:none;">
					<h3>These emails have been sent:</h3>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<h4 class='warning'>
					Note that each time these buttons are clicked, emails are sent to multiple customers: there is no control to mark a message as already sent and prevent erroneous multiple send. So don't use repeatedly!!! 
				</h4>
			</td>
		</tr>
		<tr>
			<td>
				<p>
					If you want to change suspend timings, edit the function <code>getSuspendReminderLabelAndPriority()</code> that is present in 2 files: <code>admin/model/burro/hosting.php</code> and <code>catalog/model/burro/hosting.php</code>
				</p>
			</td>
			<td>
				<p>
					If you want to change payment timings, edit the function <code>getPaymentReminderText()</code> in the file: <code>admin/model/burro/mailing.php</code>
				</p>
			</td>
		</tr>
	</table>
	
	  
	  
	  
	  
	  
    </div>
  </div>
</div>
<?php echo $footer; ?>