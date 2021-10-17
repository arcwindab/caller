<?php 
/**
 * @package arcwindab/caller
 * @link    https://github.com/arcwindab/caller/
 * @author  Tobias Jonson <suport@bot.arcwind.se>
 * @license https://github.com/arcwindab/caller/blob/main/LICENSE
 */

namespace arcwindab {
   class caller {
      /**
       * Url to debug script
       *
       * @var string
       */
      public $debug_url = 'https://app.arcwind.se/published/caller.test.php';
      
      /**
       * The version number
       *
       * @var string
       */
      protected $version = '0.3';
      
      /**
       * Configurable variables
       *
       * @var string
       */
      protected $config = array();
      protected $config_fields = array('user_agent', 'timeout', 'post');

      /**
       * Sets up
       *
       * @param string $ua      Useragent
       * @param string $ip      IP
       *
       * @return 
       */
      public function __construct($ua = null, $ip = null) {
         foreach($this->config_fields as $cf) {
            $this->config[$cf] = '';
         }
         
         $this->set_timeout(30);
         $this->set_post(false);
         $this->set_useragent(((($ua !== null) && (trim($ua) != '')) ? $ua : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'n/a')));
         $this->set_ip(((($ip !== null) && (trim($ip) != '')) ? $ip : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'n/a')));
      }
      
      /**
       * Set user agent
       *
       * @param string $string   Useragent
       *
       * @return bool
       */
      public function set_useragent($string) {
         if(trim($string) != '') {            
            $this->config['user_agent'] = trim($string).' (PHP '.phpversion().')';
            return true;
         }
         
         return false;
      }
      
      /**
       * Set ip adress
       *
       * @param string $string   IP
       *
       * @return bool
       */
      public function set_ip($string) {
         if(trim($string) != '') {            
            if((filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) || (filter_var($string, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))) {
                $this->config['ip'] = trim($string);
                return true;
            }
         }
         
         return false;
      }
      
      /**
       * Set timeout time
       *
       * @param number $number   Timeout in seconds
       *
       * @return bool
       */
      public function set_timeout($number) {
         if(is_numeric($number)) {
            $this->config['timeout'] = $number;
            
            return true;
         }
         
         return false;
      }
      
      /**
       * Set if post request
       *
       * @param bool $bool       True/false
       *
       * @return bool
       */
      public function set_post($bool) {
         if(is_bool($bool)) {
            $this->config['post'] = $bool;
            
            return true;
         }
         
         return false;
      }
      
      /**
       * Get version number
       *
       * @return string
       */
      public function get_version() {
         return $this->version;
      }
      
      /**
       * Get user agent
       *
       * @return string
       */
      public function get_user_agent() {
         if((isset($this->config['user_agent'])) && ($this->config['user_agent'] != '')) {
            return $this->config['user_agent'];
         }
         return '';
      }
      
      /**
       * Get ip
       *
       * @return string
       */
      public function get_ip() {
         if((isset($this->config['ip'])) && ($this->config['ip'] != '')) {
            return $this->config['ip'];
         }
         return '';
      }
      
      
      /**
       * Get headers to send to server
       *
       * @return array
       */
      public function get_header_array() {
         return array(
            'Content-Type: application/x-www-form-urlencoded', 
            'Accept-language: en', 
            'User-Agent: '.$this->get_user_agent(), 
            'Remote_Addr: '.$this->get_ip(),
            'HTTP_X_FORWARDED_FOR: '.$this->get_ip()
         );
      }
      
      
      /**
       * Get some return headers from url
       *
       * @param string $url      Url to target
       * @param array $post      array with post variables
       *
       * @return array('result' => false, 'info' => array)
       */
      public function get_headers($url, $post = array()) {
         $info['total_time']  = 0;
         $start = microtime(true);
         
         if(function_exists('curl_exec')) {
            $ch = curl_init($url);
            
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            curl_setopt($ch, CURLOPT_FRESH_CONNECT,  true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout']);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->get_user_agent());
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->get_header_array());
            $result  = (curl_exec($ch));
            $inf     = (curl_getinfo($ch));
            curl_close($ch);

            $info['http_code'] = $inf['http_code'];
            if($result != '') {
               $info['total_time']  = (microtime(true) - $start);
               return array('result' => false, 'info' => $info);
            }
         }

         return array('result' => false, 'info' => $info);
      }
      
      
      /**
       * Get content and some headers from url
       *
       * @param string $url      Url to target
       * @param array $post      array with post variables
       * @param string $method   Method to use when making call
       *
       * @return array('result' => string, 'info' => array)
       */
      public function get_contents($url, $post = array(), $method = '') {
         $method = trim(strtolower($method));
         if(($method == 'curl') || ($method == 'file_get_contents') || ($method == 'fopen')) {} else {
            $method = '';
         }
         
         // Remove all illegal characters from a url
         $url = filter_var($url, FILTER_SANITIZE_URL);
         if(filter_var($url, FILTER_VALIDATE_URL)) {
            $postdata = http_build_query($post);

            $result = '';
            $info = array();


            $info['domain']      = rtrim(substr($url, 0, strrpos( $url, '/')), '/').'/';
            $info['http_code']   = '';
 
            $opts = array(
               'http' => array(
                  'timeout' => $this->config['timeout'],
                  'method'  => (($this->config['post'] == true) ? 'POST' : 'GET'),
                  'content' => (($this->config['post'] == true) ? $postdata : ''),
                  'ignore_errors' => true,
                  'header'  => implode("\n\r", $this->get_header_array())
               ),

               'ssl' => array(
                  'verify_peer' => false,
                  'verify_peer_name' => false,
                  'allow_self_signed' => true
               )
            );

            $info['total_time']  = 0;
            $start = microtime(true);
            // CURL
            if((function_exists('curl_exec')) && (($method == '') || ($method == 'curl'))) {
               $ch = curl_init($url);
               if($this->config['post'] == true) {
                  curl_setopt($ch, CURLOPT_POST, true);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
               }

               curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
               curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
               curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

               curl_setopt($ch, CURLOPT_FRESH_CONNECT,  true);
               curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
               curl_setopt($ch, CURLINFO_HEADER_OUT, true);
               curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
               curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout']);
               curl_setopt($ch, CURLOPT_USERAGENT, $this->get_user_agent());
               curl_setopt($ch, CURLOPT_HTTPHEADER, $this->get_header_array());
               $result  = (curl_exec($ch));
               $inf     = (curl_getinfo($ch));
               curl_close($ch);

               $info['http_code'] = $inf['http_code'];
               if($result != '') {
                  $info['total_time']  = (microtime(true) - $start);
                  return array('result' => $result, 'info' => $info, 'method' => $method);
               }
            }

            // FILE_GET_CONTENTS
            if((function_exists('file_get_contents')) && (($method == '') || ($method == 'file_get_contents'))) {
               $result = file_get_contents($url, false, stream_context_create($opts));
               $inf    = $http_response_header;

               preg_match('{HTTP\/\S*\s(\d{3})}', $inf[0], $match);
               $info['http_code'] = $match[1];

               if($result != '') {
                  $info['total_time']  = (microtime(true) - $start);
                  return array('result' => $result, 'info' => $info, 'method' => $method);
               }
            }

            //FOPEN
            if((function_exists('fopen') && function_exists('stream_get_contents')) && (($method == '') || ($method == 'fopen'))) {
               $handle = fopen($url, 'r', false, stream_context_create($opts));
               $result = stream_get_contents($handle);
               $inf    = $http_response_header;

               preg_match('{HTTP\/\S*\s(\d{3})}', $inf[0], $match);
               $info['http_code'] = $match[1];

               if($result != '') {
                  $info['total_time']  = (microtime(true) - $start);
                  return array('result' => $result, 'info' => $info, 'method' => $method);
               }
            }
         } else {
            $info['error'] = 'Invalid url';
         }
         
         return array('result' => false, 'info' => $info);
      }
      
      /**
       * Get content from url
       *
       * @param string $url      Url to target
       * @param array $post      array with post variables
       *
       * @return string
       */
      public function get($url, $post = array()) {
         return $this->get_contents($url, $post)['result'];
      }
      
      /**
       * Get content from url using CURL
       *
       * @param string $url      Url to target
       * @param array $post      array with post variables
       *
       * @return string
       */
      public function curl($url, $post = array()) {
         return $this->get_contents($url, $post, 'curl');
      }
      
      /**
       * Get content from url using CURL
       *
       * @param string $url      Url to target
       * @param array $post      array with post variables
       *
       * @return string
       */
      public function file_get_contents($url, $post = array()) {
         return $this->get_contents($url, $post, 'file_get_contents');
      }
      
      /**
       * Get content from url using fopen
       *
       * @param string $url      Url to target
       * @param array $post      array with post variables
       *
       * @return string
       */
      public function fopen($url, $post = array()) {
         return $this->get_contents($url, $post, 'fopen');
      }
   }
}
