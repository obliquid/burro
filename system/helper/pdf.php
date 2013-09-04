<?php
function pdf($data, $name,$savetofile=false) {
	$invoice_dir = "invoice/";
    if (count($name) > 1) {
		$name = "Orders_Multipage";
	}else{
		$name = $name[0]['invoice_no'].'_ord-'.$name[0]['order_id'];
	}
    $pdf = new DOMPDF;
    $pdf->load_html($data);
    $pdf->render();
	if ( $savetofile ) {
		//save invoice to pdf file
		file_put_contents($invoice_dir.$name.".pdf", $pdf->output());
	}
	$pdf->stream($name.".pdf");
}
?>