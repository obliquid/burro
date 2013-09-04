# burro
##### *alpha / in progress*

---

**burro: opencart ispconfig integration [VQMOD].** 

*burro* is an opencart extension to sell hosting / isp products and have them created / managed in ispconfig, in an automatic process.

[opencart](http://www.opencart.com/) is a leading open source ecommerce software, and [ispconfig](http://www.ispconfig.org/) is a leading open source hosting control panel software

with *burro* your customers buy hosting services on your opencart shop, and these services are automatically created and activated on your ispconfig server. *burro* also sends automatic reminder emails to your customers when a service renewal is due, or when a paymend is expected.

---

### note

*burro* is an ongoing project. this is an alpha version. come back later!

---

### prerequisites

- an **opencart** installation
	- *burro* will be installed as an opencart extension
	- opencart must have *vqmod* installed
	
- an **ispconfig** installation
	- this could be on the same server where opencart is, or in another server, since ispconfig integration is made through its API

---

### installation and first use

- first make sure you have *vqmod* installed!
- insert your personal configurations in *admin/config_burro.php*
- then, simply copy (upload) *vqmod*, *catalog*, *system*, *image* and *admin* folders into your opencart installation, as usual. don't worry: no files will be overwritten.
	- after file upload, MUST set permissions 777 to folder *system/library/dompdf/lib/fonts*, with a command like: *chmod -R 777 system/library/dompdf/lib/fonts*
- don't forget to go to *System > Users > User Groups*, edit *Top Administrators* group, and add *Access* and *Modify* permissions to the new *burro/\** and *module/domainCheck* checkboxes
- now *burro* is installed: go to the new menu *Hostings > Configuration* to trigger creation of burro specific database tables
- for each product, in the editing form, you'll have a new tab *Hostings*: here you can define your product to be an hosting product, by adding to it one or more hosting services.
	- note: right now it's not allowed to add to a product more than one service of each type.
- note: if you use a custom theme (that is not the default theme or the bundled obliquid theme), then read troubleshootings below.

---

### features

- *burro* comes bundled with a custom minimalistic theme, named *obliquid theme*. using this theme is not necessary to correctly run *burro*, but if you like it, you can use it.
- *burro* comes bundled with a Domain Check module, you can put it on your pages to let users do a realtime whois control for domain availability
	- requires whois

---

### troubleshooting

- using **custom themes**
	- *burro* uses *vqmod* to also modify some files in custom themes you may have installed. 
	- *vqmod* is used keeping in mind to search the more obvious and important code elements in the page as anchor points for code replacement. that should grant maximum compatibility with custom themes.
	- however could happen that *search strings* matched by *vqmod* are not found in custom theme you are using. if this is the case, then manually execute on your theme the operations stated in *vqmod/xml/* files, choosing different *search strings* when they are not matched.

---



#### author

carrara federico ( federico - at - obliquid.it )
