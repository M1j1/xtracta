<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		
		<title>Extracta Sample</title>

		<link href="./css/app.css" rel="stylesheet"/>
		<link href="./css/style.css" rel="stylesheet"/>
		
		<script type="text/javascript" src="./js/jquery.min.js"></script>
		<script type="text/javascript" src="./js/app.js"></script>
	</head>
	<body>
	
		<style type="text/css">
			.sub-nav .container {
				padding: 3px;
				padding-left: 15px;
			}
			
			.body .container {
				
				border: 1px solid #ccc;
				border-radius: 5px;
				background-color:white;
				
				margin:10px auto;
				
				width: 88%;
				
				padding: 10px;
			}
			
			.read_invoice,
			.read_suppliers,
			.scan {
				color:#ccc;
				padding: 10px;
				background-color: #eee;
			}
			
			.read_invoice.active,
			.read_suppliers.active,
			.scan.active {
				background-color: #f8dba6;
				color: black;
				padding: 11px;
			}
			
			.read_invoice.done,
			.read_suppliers.done,
			.scan.done {
				padding: 10px;
				background-color: #9cee9c;
				color: green;
			}
			
			.read_invoice.error,
			.read_suppliers.error,
			.scan.error {
				padding: 10px;
				background-color: #f4bebe;
				color: maroon;
			}
			
			.result {
				padding: 10px;
				background-color: #9ce0ee;
				color: #386de9;
			}

			.result b {
				color: #1175a7;
			}
		</style>
	
		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="./index.php">Extracta Sample!</a>
				</div>
			</div>
		</nav>
		
		<div class="sub-nav">
			<div class="container">
			</div>
		</div>

		<div class="body">

			<div class="container">
				
				<p class="read_suppliers">Reading the suppliers file</p>
				<p class="read_invoice">Reading the invoice data file</p>
				<p class="scan">Scanning for possible matches</p>
				
			</div>
		
		</div>
		
		<script type="text/javascript">
		
			$(function() {
				
				//
				var $bod = $( '.body > .container' );
				$bod.find( '.read_suppliers' ).addClass( 'active' );
				
				//
				var supplierData = false;
				var invoiceData = false;

				$.get( './tasks/read_suppliers.php', function( json_supplier_data ) {

					// evaluate json text into a javascript object - doing a blind eval is dangerous, but for this example its fine.
					var supplier_data = eval( '(' + json_supplier_data + ')' );

					$bod.find( '.read_suppliers' ).removeClass( 'active' );
				
					// if for some reason our task failed
					if ( supplier_data.status == 0 ) {
						$bod.find( '.read_suppliers' ).addClass( 'error' ).append( '<p>' + supplier_data.response + '</p>' );
					}
					else {
						
						// cache our supplier data
						supplierData = JSON.stringify( supplier_data.response );

						// indicate the completion of the supplier read step.
						$bod.find( '.read_suppliers' ).addClass( 'done' );

						// and now we drop to the next task.. reading the invoice data.
						$bod.find( '.read_invoice' ).addClass( 'active' );

						// now we need to read in our invoice data.
						$.get( './tasks/read_invoice.php', function( invoice_data ) {
							
							// evaluate json text into a javascript object - doing a blind eval is dangerous, but for this example its fine.
							var invoice_data = eval( '(' + invoice_data + ')' );
							
							$bod.find( '.read_invoice' ).removeClass( 'active' );
						
							// if for some reason our task failed
							if ( invoice_data.status == 0 ) {
								$bod.find( '.read_invoice' ).addClass( 'error' ).append( '<p>' + invoice_data.response + '</p>' );
							}
							else {
								
								//
								invoiceData = JSON.stringify( invoice_data.response );
								
								//
								$bod.find( '.read_invoice' ).addClass( 'done' );
								
								//
								$bod.find( '.scan' ).addClass( 'active' );
								
								//
								$.post( './tasks/scan.php', { 'invoice_data': invoiceData, 'supplier_data': supplierData }, function( scan_data ) {

									//
									var scan_data = eval( '(' + scan_data + ')' );
									
									//
									$bod.find( '.scan' ).removeClass( 'active' );
									
									if ( scan_data.status == 0 ) {
										$bod.find( '.scan' ).addClass( 'error' ).append( '<p>' + scan_data.response + '</p>' );
									}
									else {
										
										$bod.find( '.scan' ).addClass( 'done' );
										
										$bod.append( '<p class="result">This Invoice belongs to: <b>' + scan_data.response.company + '</b></p>' );
									}
								});
							}
						});
					}
				});
			});
		</script>
	</body>
</html>


<?php

	// Helpful Functions;;
	
	
	//
	function is_empty_dir( $dir ) {
		if ( !is_readable( $dir ) ) 
			return true;
		return ( count( scandir( $dir ) ) == 2 );
	}
?>