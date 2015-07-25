<?php

class Pixels extends Controller {

	static function get() {
		$x = GET( 'x' );
		$y = GET( 'y' );
		$Pixel = Pixel::newFromCoords( $x, $y );
		$RESPONSE['Pixel'] = $Pixel; // May be null
		if ( $Pixel ) {
			$Author = $Pixel->getAuthor();
			$RESPONSE['Author'] = $Author;
		}
		return $RESPONSE;
	}

	static function post() {
		global $gUser;

		$x = POST( 'x' );
		$y = POST( 'y' );
		$color = POST( 'color' );
		$tool = POST( 'tool' );

		$Pixel = Pixel::newFromCoords( $x, $y );

		if ( $tool === 'brush' and !$gUser->brush ) {
			$RESPONSE['message'] = 'Buy the brush to use it';
			$RESPONSE['Pixel'] = $Pixel;
			return $RESPONSE;
		}

		if ( $Pixel ) {
			if ( $gUser->canEdit( $Pixel ) ) {
				if ( $color ) {
					$Pixel->color = $color;
					$Pixel->update();
					$RESPONSE['message'] = 'Pixel updated';
					$RESPONSE['Pixel'] = $Pixel;
				} else {
					$Pixel->delete();
					$RESPONSE['message'] = 'Pixel deleted';
					$RESPONSE['Pixel'] = $Pixel;
					$Author = $Pixel->getAuthor();
					$Author->pixel_count--;
					$Author->update();
				}
			} else {
				$RESPONSE['message'] = 'Not your pixel';
				$RESPONSE['Author'] = $Pixel->getAuthor();
				$RESPONSE['Pixel'] = $Pixel;
			}
		} else if ( $color ) {
			$Pixel = new Pixel;
			$Pixel->x = $x;
			$Pixel->y = $y;
			$Pixel->author_id = $gUser->id;
			$Pixel->color = $color;
			$Pixel->insert();
			$RESPONSE['message'] = 'Pixel saved';
			$RESPONSE['Pixel'] = $Pixel;
			$gUser->pixel_count++;
			$gUser->update();
		}
		return $RESPONSE;
	}

	static function put() {
		// TO DO
	}

	static function delete() {
		// TO DO
	}
}