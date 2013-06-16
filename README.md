# burro
##### *alpha / in progress*

---

**burro: opencart ispconfig integration [VQMOD].** 

burro is an opencart extension to sell hosting / isp products and have them created / managed in ispconfig, in an automatic process.

[opencart](http://www.opencart.com/) is a leading open source ecommerce software, and [ispconfig](http://www.ispconfig.org/) is a leading open source hosting control panel software

with burro your customers buy hosting services on your opencart shop, and these services are automatically created and activated on your ispconfig server. burro also sends automatic reminder emails to your customers when a service renewal is due, and (if you want) automatically disable not-renewed services.

---

### note

burro is an ongoing project. right now only a minimal part of the functionalities have been implemented. come back later!

---

### prerequisites

- an **opencart** installation
	- burro will be installed as an opencart extension
	- opencart must have vqmod installed
	
- an **ispconfig** installation
	- this could be on the same server where opencart is, or in another server, since ispconfig integration is made through its API

---

### installation

- first make sure you have **vqmod** installed!
- then, simply copy (upload) *vqmod* and *admin* folders into your opencart installation, as usual. don't worry: no files will be overwritten.
- don't forget to go to *System > Users > User Groups*, edit *Top Administrators* group, and add *Access* and *Modify* permissions to the new *burro/\** checkboxes
- now burro is available with its own menu: go to *Burro > Settings* and configure it.

---

#### author

carrara federico ( federico - at - obliquid.it )

