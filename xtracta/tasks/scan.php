<?php

	// This demonstration is very basic, I have assumed that the suppliers name will always be on the same line and would have been found by the scanning process
	// to be in sequencial order - In the real world this assumption would be too simplistic (part of the reason I want to work with you, I would love to learn what kind
	// of exceptions you guys have faced).

	//
	$key_length = 2;

	//
	$inv_data = !empty( $_POST[ 'invoice_data' ] ) ? $_POST[ 'invoice_data' ] : false;
	$sup_data = !empty( $_POST[ 'supplier_data' ] ) ? $_POST[ 'supplier_data' ] : false;
	
	//
	if ( false === $inv_data || false === $sup_data ) {
		die( json_encode( [ 'status' => 0, 'response' => 'Not enough data was posted.. Try again.' ] ) );
	}
	
	//
	$inv_data = json_decode( $inv_data, true );
	$sup_data = json_decode( $sup_data, true );
	
	// now we finally get to the fun stuff.. we will need to loop through the invoice data and compare the word field to the list of suppliers
	// if we find a match or partial match we will need to breakdown the missing words and look through the invoicedata's physically close text for the missing word(s).
	// any matches will need to be ranked and the highest ranked word or phrase will be returned as the supplier. (at least that is the current idea :P)

	// track our partial hits
	$p_hits = [];
	
	// track the full hits, each hit will have the phrase and a score.
	$hits = [];
	
	//
	foreach ( $inv_data as $word_id => $datum ) {
		
		//
		$word = !empty( $datum[ 'word' ] ) ? $datum[ 'word' ] : false;
		if ( $word === false ) {
			continue;
		}
		
		//
		$key = substr( $word, 0, min( strlen( $word ), $key_length ) );
		
		//
		if ( isset( $sup_data[ $key ] ) ) {
			
			// run through each supplier ( whos first two letters match the invoice words first two letters ) => {potential supplier}
			
			foreach ( $sup_data[ $key ] as $p_sup ) {
				
				// does the invoice word match any part of the suppliers name?
				if ( strpos( $p_sup, $word ) !== false ) {

					//
					if ( !isset( $p_hits[ $p_sup ] ) ) {
						$p_hits[ $p_sup ] = [];
					}
				
					// explode and loop through the potential suppliers name, this will give us the ability to test for the rest of the words
					$_words = explode( ' ', $p_sup );
					foreach ( $_words as $word_index => $_word ) {
						
						//
						if ( !isset( $p_hits[ $p_sup ][ $_word ] ) ) {
							$p_hits[ $p_sup ][ $_word ] = [];
						}
						
						// does the invoice word match this part of the suppliers name?
						if ( trim( $_word ) == trim( $word ) ) {

							//
							$p_hits[ $p_sup ][ $word ] = $datum;
							
							
							// I cannot think of a test case where you would need to cycle back as the first word found should always be the left word.
							for ( $a = $word_index - 1; $a >= 0; $a-- ) {
								if ( isset( $inv_data[ $word_id - ( $word_index - $a ) ] ) ) {
									$prev_datum = $inv_data[ $word_id - ( $word_index - $a ) ];
									if ( $prev_datum[ 'line_id' ] == $datum[ 'line_id' ] && $prev_datum[ 'left' ] < $datum[ 'left' ] && $prev_datum[ 'page_id' ] == $datum[ 'page_id' ] && $prev_datum[ 'word' ] == $_words[ $a ] ) {
										$p_hits[ $p_sup ][ $prev_datum[ 'word' ] ] = $next_datum;
									}
								}
							}
							
							// cycle through any remaining words and test to see of the invoice words match.
							for ( $z = $word_index + 1; $z < count( $_words ); $z++ ) {

								if ( isset( $inv_data[ $word_id + $z ] ) ) {
									$next_datum = $inv_data[ $word_id + $z ];
									if ( $next_datum[ 'line_id' ] == $datum[ 'line_id' ] && $next_datum[ 'left' ] > $datum[ 'left' ] && $next_datum[ 'page_id' ] == $datum[ 'page_id' ] && $next_datum[ 'word' ] == $_words[ $z ] ) {
										$p_hits[ $p_sup ][ $next_datum[ 'word' ] ] = $next_datum;
									}
								}
							}
							
							// just a quick test to see if we have ticked all the boxes
							$good = true;
							foreach ( $p_hits[ $p_sup ] as $tmp ) {
								if ( empty( $tmp ) )
									$good = false;
							}
							
							if ( $good ) {
								$hits[ $p_sup ] = $p_hits[ $p_sup ];
							}
						}						
					}
				}
			}
		}
	}

	//
	$response = [];
	
	//
	if ( count( $hits ) > 0 ) {

		$min_top = 99999;
		foreach ( $hits as $name => $hit ) {
			
			$avg_top = 0;
			foreach ( $hit as $word ) {
				$avg_top += (float) $word[ 'top' ];
			}
			$avg_top /= count( $hit );
			
			if ( $avg_top < $min_top ) {
				$min_top = $avg_top;
				
				$response[ 'company' ] = $name;
				$response[ 'data' ] = $hit;
			}
		}

		die( json_encode( [ 'status' => 1, 'response' => $response ] ) );
	}

	die( json_encode( [ 'status' => 0, 'response' => 'No Matches' ] ) );
	
	