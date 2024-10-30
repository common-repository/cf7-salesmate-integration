<?php

 class Cf7_Salesmate_Admin_API
 {


   public function __construct(){

   }

   /**
	 * Handle admin api call
	 *
	 * @return object
	 */
   public function createCall($domain=false, $headerData = false, $endPoint = false,$type = false){
      $url = $domain.'/apis/'.$endPoint;
   
      $request = array(
          'timeout' => 100,
          'headers' => $headerData,
          'method'  => $type,
      );

      $response = wp_remote_request( str_replace(' ','%20',$url), $request );

      if(!is_wp_error($response) && isset( $response['body'] )){
        $data = json_decode($response['body']);

        if($data->Status == 'success')
        {
           return $data;

        }else {

          return $data->Error->Code;

        }
    }else {
      //echo  $response->WP_Error->errors['http_request_failed'][0];
      return;
      // return '<script>alert</script>';
    }

   }

 }
