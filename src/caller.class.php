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
       * The version number
       *
       * @var string
       */
      protected $version = '0.1';
      
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
       *
       * @return 
       */
      public function __construct($ua = null) {
         foreach($this->config_fields as $cf) {
            $this->config[$cf] = '';
         }
         
         $this->set_timeout(30);
         $this->set_post(false);
         $this->set_useragent(((($ua !== null) && (trim($ua) != '')) ? $ua : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'n/a')));
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
            $this->config['user_agent'] = trim($string);
            return true;
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
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);


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
       *
       * @return array('result' => string, 'info' => array)
       */
      public function get_contents($url, $post = array()) {
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
               'header'  => '',
               'ignore_errors' => true,
               'header'  =>"Content-Type: application/x-www-form-urlencoded\r\n" .
                           "Accept-language: en\r\n" .
                           "User-Agent: ".$this->get_user_agent()."\r\n" 
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
         if(function_exists('curl_exec')) {
            $ch = curl_init($url);
            if($this->config['post'] == true) {
               curl_setopt($ch, CURLOPT_POST, true);
            }
            
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            curl_setopt($ch, CURLOPT_FRESH_CONNECT,  true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout']);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->get_user_agent());
            $result  = (curl_exec($ch));
            $inf     = (curl_getinfo($ch));
            curl_close($ch);

            $info['http_code'] = $inf['http_code'];
            if($result != '') {
               $info['total_time']  = (microtime(true) - $start);
               return array('result' => $result, 'info' => $info);
            }
         }

         // FILE_GET_CONTENTS
         if(function_exists('file_get_contents')) {
            $result = file_get_contents($url, false, stream_context_create($opts));
            $inf    = $http_response_header;

            preg_match('{HTTP\/\S*\s(\d{3})}', $inf[0], $match);
            $info['http_code'] = $match[1];

            if($result != '') {
               $info['total_time']  = (microtime(true) - $start);
               return array('result' => $result, 'info' => $info);
            }
         }

         //FOPEN
         if(function_exists('fopen') && function_exists('stream_get_contents')) {
            $handle = fopen($url, 'r', false, stream_context_create($opts));
            $result = stream_get_contents($handle);
            $inf    = $http_response_header;

            preg_match('{HTTP\/\S*\s(\d{3})}', $inf[0], $match);
            $info['http_code'] = $match[1];
            
            if($result != '') {
               $info['total_time']  = (microtime(true) - $start);
               return array('result' => $result, 'info' => $info);
            }
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
   }
}
