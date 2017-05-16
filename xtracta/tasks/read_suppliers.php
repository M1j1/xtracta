<?php 
	//
	$key_length = 2;
	$supl_path = "../data/suppliernames300k.txt";

	$suppliers = [];

	//
	if ( !file_exists( $supl_path ) ) {
		die( json_encode( [ 'status' => 0, 'response' => 'The suppliernames file seems to be missing, Please ensure the suppliernames.txt file is locatable in the data folder' ] ) );
	}
	
	//
	if ( ( $h = fopen( $supl_path, "r" ) ) !== false ) {
		
		
		while ( ( $data = fgetcsv( $h, 1000, "," ) ) !== false ) {
			
			if ( count( $data ) != 2 || !is_numeric( $data[ 0 ] ) ) continue;
			
			//
			$name = trim( $data[ 1 ] );

			//
			$key = substr( $name, 0, min( strlen( $name ), $key_length ) );
			
			//
			if ( !isset( $suppliers[ $key ] ) ) {
				$suppliers[ $key ] = [];
			}
			
			//
			$suppliers[ $key ][] = $name;
		}
		fclose($h);
	}
	
	if ( count( $suppliers ) > 0 ) {
		die( json_encode( [ 'status' => 1, 'response' => $suppliers ] ) );
	}
	else {
		die( json_encode( [ 'status' => 0, 'response' => 'Could not locate any suppliers, please ensure the suppliernames.txt file is in the data folder' ] ) );
	}
	
