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
      <h1><img src="view/image/about.png" alt="" /> <?php echo $heading_title; ?></h1>
      
      <div class="buttons"></div>
    </div>
    <div class="content">
      <p><i><?php echo $heading_subtitle; ?></i></p>
      <p><?php echo $body01; ?> <a href="https://github.com/obliquid/burro" target="_blank">github.com/obliquid/burro</a></p>
      <p>author: federico carrara ( federico - at - obliquid.it )</p>
    </div>
  </div>
</div>
<?php echo $footer; ?>