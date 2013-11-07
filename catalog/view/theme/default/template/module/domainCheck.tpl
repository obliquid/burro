<div id="domainCheck<?php echo $module; ?>" class="domainCheck box">
	<div class="box-heading">Domain Check</div>
	<table class="domainCheckBody" > 
		<tr>
			<td valign="middle" style=""><h3>Check if your domain is available!</h3></td>
			<td valign="middle" style="">
				<h3>www&nbsp;.</h3>
			</td>
			<td valign="middle" style="width:260px;">
				<input type="text" name="check_domain" >
				<span class="hosting-error" id="check_domain_error"></span>
				<!-- <span class="help" style="width: 230px;">check if your domain is free!</span> -->
				<script>
				$(document).ready(function() {
				
					function checkMinLength(quiet) {
						if ( !quiet ) quiet = false;
						if ( $('input[name="check_domain"]').val().length < 3 ) {
							$('#check_domain_error').html("Domain must be at least 3 chars long");
							$('#check_domain_error').show();
							$('#button-domainCheck').hide();
						} else {
							$('#check_domain_error').hide();
							$('#button-domainCheck').show();
						}
						if ( quiet ) {
							$('#check_domain_error').hide();
						}
					}
				
				
					var whoisResult = '';
					var alternativeDomainPageNum = 0;
					var alternativeDomainPerPage = 10;
					function checkIt(pageNum) {
						if ( pageNum && typeof pageNum == "number" ) {
							alternativeDomainPageNum = pageNum;
						} else {
							alternativeDomainPageNum = 0;
						}
						
						//reset previous alternative domains
						$('#button-moreAlternatives').remove();
						if ( alternativeDomainPageNum == 0 ) {
							$('.alternativeDomain').remove();
						}
						
						//reset buttons
						$('#boxWhoisResult').hide();
						$('#button-domainCheck').hide();
						$('#busy-domainCheck').show();
						$('#altRow').show();
						$('input[name="check_domain"]').prop('disabled', true);
						$('select[name="check_domain_extension"]').prop('disabled', true);
						
						//init some vars
						$('input[name="check_domain"]').val( sanitizeDomain( $('input[name="check_domain"]').val(), true) );
						var domainRaw = $('input[name="check_domain"]').val();
						var domainExtAndProdId = $('select[name="check_domain_extension"]').val().split(',');
						var domainExt = domainExtAndProdId[0];
						var domainProdId = domainExtAndProdId[1];
						//first ajax call
						$.ajax({
							url: 'index.php?route=burro/hosting/domainAvail',
							type: 'post',
							data: { 
								'domain' : domainRaw,
								'domain_extension' : domainExt 
							},
							dataType: 'json',
							success: function(json) {
								//console.log("success!");
								//console.log(json);
								$('#button-domainCheck').show();
								$('#busy-domainCheck').hide();
								$('input[name="check_domain"]').prop('disabled', false);
								$('select[name="check_domain_extension"]').prop('disabled', false);
								
								var mainFullDomain = domainRaw+'.'+domainExt;
								$('#button-viewSite').attr('href', 'http://'+mainFullDomain );
								$('#button-domainAdd').attr('href', 'index.php?route=product/product&product_id='+domainProdId+'&preselected_domain='+domainRaw+'&preselected_extension='+domainExt );
								$('.rowDomain').html(domainRaw+'.'+domainExt);
								
								whoisResult = json[1];
								if (json[0] ) {
									//alert("libero!: \n\n"+json[1]);
									$('#successRow').show();
									$('#failRow').hide();
								} else {
									//alert("occupato!: \n\n"+json[1]);
									$('#successRow').hide();
									$('#failRow').show();
								}
								//check for domain alternatives
								var x = 0;
								$(".allowed_domain_option").each(function() {
									var extension = $(this).val().split(',')[0]; 
									if ( 
										extension != domainExt
										&&
										x >= alternativeDomainPageNum*alternativeDomainPerPage 
										&& 
										x < (alternativeDomainPageNum+1)*alternativeDomainPerPage
									) {
										//first add child to view
										var altDomainProdId = $(this).val().split(',')[1]; 
										var fullDomain = domainRaw+'.'+extension;
										var domId = 'alternativeNum'+String(x);
										$('.boxDomainAlternatives').append('<div class="'+domId+' alternativeDomain attention" style="position:relative;padding-top:4px;padding-bottom:3px;">'+fullDomain+'<img src="catalog/view/theme/default/image/busy.svg" id="busy-domainCheck'+domId+'" style="position:absolute;top:2px;right:2px;width:20px;height:20px;"/></div>');
										//then launch ajax call
										$.ajax({
											url: 'index.php?route=burro/hosting/domainAvail',
											type: 'post',
											data: { 
												'domain' : domainRaw,
												'domain_extension' : extension 
											},
											dataType: 'json',
											success: function(jsonAlt) {
												//console.log("### passo 00: success on alternative domain!");
												//console.log(jsonAlt);
												$('.'+domId).removeClass('attention');
												$('#busy-domainCheck'+domId).remove();
												if (jsonAlt[0] ) {
													//alert("libero!: \n\n"+jsonAlt[1]);
													//console.log("### passo 01");
													$('.'+domId).addClass('success');
													//console.log("### passo 02");
													$('.'+domId).append(' - available');
													//console.log("### passo 03");
													$('.'+domId).append('<a class="button" id="button-domainAdd'+domId+'" style="position:absolute;top:0px;right:-54px;">Buy</a>');
													//console.log("### passo 04 con "+$('#button-domainAdd'+domId).attr('href'));
													$('#button-domainAdd'+domId).attr('href', 'index.php?route=product/product&product_id='+altDomainProdId+'&preselected_domain='+domainRaw+'&preselected_extension='+extension );
													//console.log("### passo 05 con "+$('#button-domainAdd'+domId).attr('href'));
												} else {
													//alert("occupato!: \n\n"+jsonAlt[1]);
													$('.'+domId).addClass('warning');
													$('.'+domId).append(' - registered <a href="http://'+fullDomain+'" target="_blank">(view)</a>');
												}
											},
											error: function(jsonAlt) {
												console.log("sorry, there was an error retrieving whois information for domain "+fullDomain+", please try again!\n(error calling index.php?route=burro/hosting/domainAvail)");
												console.log(jsonAlt);
											}
										});										
									}
									x++;
								});
								//add "more" button
								if ( alternativeDomainPageNum < Math.floor( x / alternativeDomainPerPage ) ) {
									$('.boxDomainAlternatives').append('<a class="button" id="button-moreAlternatives" style="">More</a>');
									$('#button-moreAlternatives').click(function() {
										checkIt(alternativeDomainPageNum+1);
									});
								} else {
									$('.boxDomainAlternatives').append('<a href="index.php?route=information/contact" ><div class="successDomain" id="button-moreAlternatives" style="">Almost all TLDs are available for registration, even if not listed here! Contact us for quotations.<br/><i>Praticamente tutti i TLD sono disponibili per la registrazione, anche se non listati qui! Contattateci per quotazioni.</i></div></a>');
								}
							},
							error: function(json) {
								console.log("error calling index.php?route=burro/hosting/domainAvail");
								console.log(json);
								alert("error calling index.php?route=burro/hosting/domainAvail");
							}
						});				
					
					}					
					$('#button-domainCheck').click( checkIt );
					$('#button-viewWhoisResult').click(function() {
						if ( $('#boxWhoisResult').is(":visible") ) {
							$('#boxWhoisResult').hide();
						} else {
							$('#boxWhoisResult').html( whoisResult );
							$('#boxWhoisResult').show();
						}
					});
					
					
					//also add some input validation and some automation
					$('input[name="check_domain"]').keyup(function(event) {
						//first sanitize
						var sanitizedDomain = sanitizeDomain( $('input[name="check_domain"]').val() );
						if ( sanitizedDomain != $('input[name="check_domain"]').val() ) $('input[name="check_domain"]').val( sanitizedDomain );
						checkMinLength();
						//then activate check on enter
						if ( event.which == 13 && $('input[name="check_domain"]').val().length > 2 ) {
							event.preventDefault();
							checkIt();
						}
					});  
					$('select[name="check_domain_extension"]').change(function(event) {
						if ( $('input[name="check_domain"]').val().length > 2 ) {
							checkIt();
						}
					});  
					
					//check min length on ready
					checkMinLength(true);
					
					
					
				});
				
				</script>
			</td>
			<td valign="middle" style="width:10px;">
				<h3>&nbsp;.&nbsp;</h3>
			</td>
			<td valign="middle" style="width:60px;">
				<select name="check_domain_extension">
<?php 				foreach ( $allowed_domains as $allowed_domain ) { ?>
						<option class="allowed_domain_option" value="<?php echo $allowed_domain['ext'].",".$allowed_domain['product_id']; ?>" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:bold;font-size:14px;"><?php echo $allowed_domain['ext']; ?></option> 
<?php 				} ?>
				</select>
			</td>
			<td valign="middle" style="width:80px;">
				<a class="button" id="button-domainCheck" >Check</a>
				<img src="catalog/view/theme/default/image/busy.svg" id="busy-domainCheck" style="display:none;"/>
			</td>
		</tr>
		<tr id="successRow" style="display:none;">
			<td valign="top" style=""></td>
			<td valign="top" style="" colspan="4">
				<div class="success">Domain <span class="rowDomain" style='font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;font-size: 14px;font-weight: bold;'></span> is available! Buy it now!</div>
			</td>
			<td valign="top" style="width:80px;">
				<a id="button-domainAdd" class="button" >Buy</a>
			</td>
		</tr>
		<tr id="failRow" style="display:none;">
			<td valign="top" style=""></td>
			<td valign="top" style="width:430px;" colspan="4">
				<div class="warning">
					Domain <span class="rowDomain" style='font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;font-size: 14px;font-weight: bold;'></span> is registered! <a id="button-viewSite" target="_blank">(view)</a>&nbsp;<a id="button-viewWhoisResult">(view actual owner)</a>
				</div>
				<textarea id="boxWhoisResult" style="display:none;width:420px;height:200px;font-size:9px;"></textarea>
			</td>
			<td valign="top" style="width:80px;">
			</td>
		</tr>
		<tr id="altRow" style="display:none;">
			<td valign="top" style=""></td>
			<td valign="top" colspan="4">
				<div class="boxDomainAlternatives">
					<h4>Alternative extensions:</h4>
				</div>
			</td>
			<td valign="top">
			</td>
		</tr>
	</table>
</div>
<script type="text/javascript"><!--

$(document).ready(function() {
	//$('#domainCheck<?php echo $module; ?> div:first-child').css('display', 'block');
});


//--></script> 