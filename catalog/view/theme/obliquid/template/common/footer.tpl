<div id="obli_footer">
		<img src='catalog/view/theme/obliquid/image/info.png'/ > All prices VAT excluded / <i>Tutti i prezzi IVA esclusa</i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src='catalog/view/theme/obliquid/image/info.png'/ > Only registered users can checkout / <i>Per poter acquistare i prodotti bisogna essere registrati al sito</i><br/><br/><img src='catalog/view/theme/obliquid/image/no_windows_logo.jpg'/ > 100% MS Windows free! Only linux servers available / <i>100% MS Windows free! Disponibili solo server Linux</i>
</div>
<div id="footer">
  <?php if ($informations) { ?>
  <div class="column">
    <h3><?php echo $text_information; ?></h3>
    <ul>
      <?php foreach ($informations as $information) { ?>
      <li><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></li>
      <?php } ?>
      <li><a href="<?php echo $sitemap; ?>"><?php echo $text_sitemap; ?></a></li>
      <li><a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a></li>
    </ul>
  </div>
  <?php } ?>
  <div class="column">
    <h3><?php echo $text_extra; ?></h3>
    <ul>
      <!-- <li><a href="<?php echo $return; ?>"><?php echo $text_return; ?></a></li> -->
      <!-- <li><a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a></li> -->
      <li><a href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a></li>
      <!-- <li><a href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a></li> -->
      <li><a href="<?php echo $special; ?>"><?php echo $text_special; ?></a></li>
    </ul>
  </div>
  <div class="column">
    <h3><?php echo $text_account; ?></h3>
    <ul>
      <li><a href="<?php echo $account; ?>"><?php echo $text_account; ?></a></li>
      <li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
      <li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li>
      <li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li>
    </ul>
  </div>
  <div class="column">
    <h3>Obliquid</h3>
    <ul>
      <li><a href="http://dev.obliquid.com" target="_blank">Dev Community</a></li>
      <li><a href="https://github.com/obliquid/burro" target="_blank">Burro on GitHub</a></li>
      <li><a href="http://www.opencart.com/index.php?route=extension/extension/info&extension_id=8277&filter_search=scalogno" target="_blank">Scalogno on OpenCart</a></li>
    </ul>
  </div>
</div>
<!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
<div id="powered"><?php echo $powered; ?></div>
<!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
</div>
<!-- Piwik -->
<script type="text/javascript"> 
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://obliquid.com/piwik//";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 1]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
    g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();

</script>
<noscript><p><img src="http://obliquid.com/piwik/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Code -->

</body></html>