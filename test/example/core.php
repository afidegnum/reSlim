<?php 

    class Core {
        public static $basepath = 'http://localhost:1337/reSlim/test/example';
        public static $api = 'http://localhost:1337/reSlim/src/api';
        
        // LIBRARY USER MANAGEMENT AND AUTHENTICATION======================================================================

        // CURL Post Request
	    public static function execPostRequest($url,$post_array){
        
            if(empty($url)){ return false;}
            //build query
            $fields_string =http_build_query($post_array);
        
            //open connection
            $ch = curl_init();
        
            ////curl parameter set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        
            //execute post
            $result = curl_exec($ch);
        
            //close connection
            curl_close($ch);
        
            return $result;
        }

        // CURL Get Request
        public static function execGetRequest($url){
            //open connection
	    	$ch = curl_init($url);
            
            //curl parameter
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
            
            //execute post
		    $data = curl_exec($ch);
            
            //close connection
    		curl_close($ch);

	    	return $data;
    	}

        // Verify API Token
        public static function verifyToken($token){
            $result = false;
            $data = json_decode(self::execGetRequest(self::$api.'/user/verify/'.$token));
            if (!empty($data)){
                if ($data->{'status'} == "success"){
                    $result = true;
                }
            }
            return $result;
        }

        // Revoke API Token
        public static function revokeToken($username,$token){
            $result = false;
            $post_array = array(
                'Username' => urlencode($username),
                'Token' => urlencode($token)
            );
            $url = self::$api.'/user/logout';
            $data = json_decode(self::execPostRequest($url,$post_array));
            if (!empty($data)){
                if ($data->{'status'} == "success"){
                    $result = true;
                }
            }
            return $result;
        }

        // Process Login
	    public static function login($url,$post_array,$useCookies){
            $data = json_decode(self::execPostRequest($url,$post_array));
            if (!empty($data)){
                if ($data->{'status'} == "success"){
                    if ($post_array['remember'] == 0){
						session_start();
                        $_SESSION['username'] = $post_array['Username'];
						$_SESSION['token'] = $data->{'token'};
					} else {
						setcookie('username', $post_array['Username'], time() + (3600 * 168), "/", NULL); // expired = 7 days
				  		setcookie('token', $data->{'token'}, time() + (3600 * 168), "/", NULL); // expired = 7 hari
					}
					header("Location: ".self::$basepath."/index.php");
                } else {
                    echo '<div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <strong>Proses Login Failed!</strong> '.$data->{'message'}.' 
                        </div>';    
                }
            } else {
                echo '<div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <strong>Proses Login Failed!</strong> Can not connected to the server! 
                        </div>';
            }
	    }

        // Process Logout
        public static function logout()
        {
            //Unset SESSION
        	if (!isset($_SESSION['username'])) session_start();
                if (self::revokeToken($_SESSION['username'],$_SESSION['token'])){
                    unset($_SESSION['username']);
                	unset($_SESSION['token']);
                }
        	// unset cookies
        	if (isset($_SERVER['HTTP_COOKIE'])) {
                if (self::revokeToken($_COOKIE['username'],$_COOKIE['token'])){
                    setcookie('username', '', time()-1000, '/');
                    setcookie('token', '', time()-1000, '/');
                }
            	$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            	    foreach($cookies as $cookie) {
                	    $parts = explode('=', $cookie);
                    	$name = trim($parts[0]);
                    	setcookie($name, '', time()-1000);
                        setcookie($name, '', time()-1000, '/');
                	}
	        }
        	header("Location: ".self::$basepath."/modul-login.php?m=1");
        }

        // Check SESSION, COOKIE and Verify Token
        public static function checkSessions()
        {
            //Jika tidak ada cookie maka check session
            if (!isset($_COOKIE['username']) && !isset($_COOKIE['token'])) 
            {
                session_start();
                //jika tidak menggunakan session maka lempar ke login
                if (!isset($_SESSION['username']) && !isset($_SESSION['token']))
                {
                    $out['username'] = null;
                    $out['token'] = null;
                    header("Location: ".self::$basepath."/modul-login.php?m=1");
                }
                else
                {
                    if (self::verifyToken($_SESSION['token'])) {
                        $out['username'] = $_SESSION['username'];
            	    	$out['token'] = $_SESSION['token'];
                    } else {
                        $out['username'] = null;
                        $out['token'] = null;
                        header("Location: ".self::$basepath."/modul-login.php?m=1");
                    }                     
                }
            }
            else //jika ada cookie maka return array
            {
                if (self::verifyToken($_COOKIE['token'])) {
                    $out['username'] = $_COOKIE['username'];
            	    $out['token'] = $_COOKIE['token'];
                } else {
                    $out['username'] = null;
                    $out['token'] = null;
                    header("Location: ".self::$basepath."/modul-login.php?m=1");
                }
    	    }
	        return $out;
        }

        // Redirect Page Location Header
        public static function goToPage($page,$timeout=0)
        {
            header("Refresh:".$timeout.";url= ".self::$basepath."/".$page."");
        }

        // Redirect Page Location by meta header
        public static function goToPageMeta($url,$timeout=0)
        {
            return '<meta http-equiv="refresh" content="'.$timeout.';url='.$url.'">';
        }

}