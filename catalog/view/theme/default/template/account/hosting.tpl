<?php echo $header; ?>


<?php if ( isset($success) && $success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>


<?php echo $column_left; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  
		<div class=''>
			<?php echo $hostingsView; ?>
		</div>
		<br/>
		<br/>
  
  
  
  
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?> 