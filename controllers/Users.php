<?php

class Users extends Controller {

	static function facebookLogin() {
		global $gDatabase, $gUser;

		$Helper = new Facebook\FacebookJavaScriptLoginHelper();
		try {
			$Session = $Helper->getSession();
			$FacebookRequest = new Facebook\FacebookRequest( $Session, 'GET', '/me' );
			$GraphUser = $FacebookRequest->execute()->getGraphObject( Facebook\GraphUser::className() );
			$Response['GraphUser'] = $GraphUser->asArray();

			try {
				$gUser = User::newFromName( $GraphUser->getProperty( 'name' ) );
			} catch ( Exception $Exception ) {
				$gUser = new User;
				$gUser->join_time = $_SERVER['REQUEST_TIME'];
				$gUser->status = 'user';
				$gUser->id = $gUser->insert();
			}
			//Set the token
			$gUser->token = md5( uniqid() );
			$_SESSION['token'] = $gUser->token;
			setcookie( 'token', $gUser->token, time() + 60 * 60 * 24 * 30, '/' ); //Lasts one month

			//Every time the user logs in, make sure all the stats are up to date
			$DATA = $GraphUser->asArray();
			foreach ( $DATA as $key => $value ) {
				if ( property_exists( 'User', $key ) and $key !== 'id' ) {
					$gUser->$key = $value;
				}
			}
			$gUser->facebook_id = $DATA['id'];
			$gUser->last_seen = $_SERVER['REQUEST_TIME'];
			$gUser->update();

			$Response['gUser'] = $gUser;

		} catch( Facebook\FacebookRequestException $FacebookRequestException ) {
			$Response = array( 'code' => $FacebookRequestException->getCode(), 'message' => $FacebookRequestException->getMessage() );
		} catch( Exception $Exception ) {
			$Response = array( 'code' => $Exception->getCode(), 'message' => $Exception->getMessage() );
		}
		Ajax::sendResponse( $Response );
	}

	static function facebookLogout() {
		global $gDatabase, $gUser;
		session_destroy();
		setcookie( 'token', '', 0, '/' );
	}
}