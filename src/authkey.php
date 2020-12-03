<?php

namespace euroglas\authkey;

class authkey extends \euroglas\eurorest\auth
{
	// Nombre del cliente autenticado
	private $authName = "";

    // Nombre oficial del modulo
    public function name() { return "authkey"; }

    // Descripcion del modulo
    public function description() { return "Módulo de autenticacion usando Llaves Compartidas"; }

    // Regresa un arreglo con las rutas del modulo
    public function rutas()
    {
		$items = parent::rutas();

        $items['/auth/name']['GET'] = array(
            'name' => 'Nombre del cliente autenticado',
            'callback' => 'getAuthName',
            'token_required' => TRUE,
        );

        return $items;
    }

    /**
     * Parsea el token, y verifica que sea valido
	 * 
     * @param array $args Arreglo con la información necesaria para autenticar al usuario.
     * 
     * @return string El token generado para el usuario
     */
    public function auth( $args = NULL )
    {
        //print_r($args);

        // Verifica que recibimos el Key
        if( FALSE == array_key_exists("key",$args) )
        {
            header('content-type: application/json');
            http_response_code(401); // 401 Unauthorized
            die(json_encode( array(
                'codigo' => 401001,
                'mensaje' => 'No autorizado',
                'descripcion' => 'La solicitud no contenia el Token requerido',
                'detalles' => $args
            )));
        }

        $key = $args['key'];
        $userName = NULL;

		switch (trim($key)) {
			case 'GYSTZ-U724P-TJB3W-BQ48R-6ZORU':
				$userName = 'Octavio Galindo';
				break;
			case 'IRZAJ-IJL5B-Z1MGJ-RJSL8-O0BMA':
				$userName = 'TheRing';
				break;
			case 'UGIWM-W7JEX-T6YBH-4WNLH-BGV7F':
				$userName = 'Kevin Torruco';
				break;
			case 'IRZAJ-IJL5B-Z1MGJ-RJSL8-O0BMA':
				$userName = 'Vigilancia';
				break;
			case 'JMWC0-70LXL-62RLC-7SNJD-G7XTR':
				$userName = 'EuroApiClient';
				break;
			case '50KXS-8ER8P-C6CLJ-5RBJ0-ZU08O':
				$userName = 'Intranet';
				break;
			case 'U1TOB-JDJHN-178YQ-EFPIT-NY89S':
				$userName = 'ClienteVIP';
				break;
			case 'HIJ96-2NHZJ-SUFZJ-J4JS4-UBFWM-LOY5N':
				$userName = 'NO USAR';
				break;
			case 'DE8VC-3413X-GESYY-F2B2C-HLWGR-ME7BQ':
				$userName = 'AUTOMATED TESTING';
				break;
			default:
				$userName = false;
				break;
		}
		if( empty($userName) )
		{
            header('content-type: application/json');
            http_response_code(401); // 401 Unauthorized
            die(json_encode( array(
                'codigo' => 401002,
                'mensaje' => 'No autorizado',
                'descripcion' => 'Invalid Serial Key',
                'detalles' => $key
            )));
		}


        $uData = array();
        $uData['login'] = $userName;
        $uData['name'] = $userName;
        $uData['vrfy'] = 'key';

        die($this->generaToken( $uData ));
    }

    public function authFromJWT( $serializedToken )
    {

        $jwt = new \Emarref\Jwt\Jwt();
        $token = $jwt->deserialize($serializedToken);

        // Prepara la encriptacion
        $algorithm = new \Emarref\Jwt\Algorithm\Hs256($this->_Secreto);
        $encryption = \Emarref\Jwt\Encryption\Factory::create($algorithm);
        
        // Este es el contexto con el que se va a validar el Token
        $context = new \Emarref\Jwt\Verification\Context( $encryption );
        $context->setIssuer($_SERVER["SERVER_NAME"]);
		$context->setSubject('eurorest');
		$options = array();

        // Normalmente aqui usaría un try/catch,
		// pero al final de nuevo lanzaría una excepcion.
		// Mejor voy a dejar que la excepcion se propague.

        $jwt->verify($token, $context);

        // Lista los claims del token
        //$jsonPayload = $token->getPayload()->getClaims()->jsonSerialize();
        //print($jsonPayload);

	    $autoRenewClaim = $token->getPayload()->findClaimByName('Autorenew');
	    if($autoRenewClaim !== null)
	    {
	    	$options["Autorenew"] = $autoRenewClaim->getValue();
        }
        $options["Autorenew"] = true;

	    $renewTimeClaim = $token->getPayload()->findClaimByName('RTime');
	    if($renewTimeClaim !== null)
	    {
	    	$options['RTime'] = $renewTimeClaim->getValue();
        }
        
		$options['vrfy'] = null;
	    $vrfyClaim = $token->getPayload()->findClaimByName('vrfy');
	    if($vrfyClaim !== null)
	    {
	    	$options['vrfy'] = $vrfyClaim->getValue();
	    	$vrfyClaimValue = $options['vrfy'];
	    	switch ($vrfyClaimValue) {
	    		case 'key':
	    		case 'email':
	    		case 'ldap':
	    			// Omite las validaciones por ahora
	    			break;

	    		default:
	    			http_response_code(401); // 401 Unauthorized
	    			header('content-type: application/json');
                    die(json_encode( array(
                        'codigo' => 401111,
                        'mensaje' => 'Vrfy Code Error',
                        'descripcion' => "El codigo VRFY no es reconocido",
                        'detalles' => $vrfyClaimValue
                    )));
	    			break;
	    	}
	    }

		$options['login'] = $token->getPayload()->findClaimByName('login');

		$this->authName = $token->getPayload()->findClaimByName('name')->getValue();
		//print( 'AuthName: '.$this->authName);

	    // Autorenew debe ser el ultimo, ya que tengamos todo lo necesario en Options
	    if( isset( $options["Autorenew"] ) && $options["Autorenew"] == true )
	    {
	    	$newToken = $this->generaToken($options);
	    	//header("Access-Control-Expose-Headers","New-JWT-Token");
	    	header("Authorization: {$newToken}");
	    }
	}
	
	public function getAuthName() 
	{
		//print("AuthName Requested: ".$this->authName);
		die( $this->authName );
	}
}
