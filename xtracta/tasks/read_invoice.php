<?php  

	//
	$invoice_path = "../data/invoice.txt";
	
	if ( !file_exists( $invoice_path ) ) {
		die( json_encode( [ 'status' => 0, 'response' => 'The invoice file seems to be missing, Please ensure the invoice.txt file is locatable in the data folder' ] ) );
	}
	
	//
	$lines = file( $invoice_path );
	
	
	$response = [];
	foreach ( $lines as $line ) {
		
		// the example invoice that i was provided used single quotes, php doesn't like singles.
		$line = json_decode( str_replace( "'", '"', trim( $line ) ), true );
	
		//
		if ( !empty( $line[ 'word_id' ] ) )
			$response[ (int) $line[ 'word_id' ] ] = $line;
	}
	
	if ( empty( $response ) ) {
		
		die( json_encode( [ 'status' => 0, 'response' => 'Could not find any Invoice data, please check the invoice.txt file in the data folder and try again.' ] ) );
	}
	
	//
	echo json_encode( [ 'status' => 1, 'response' => $response ] );