<?php
/**
 * ECN Communicator
 *
 * The ECN Communicator is the common file that does the leg work for making API Calls
 * work between various Knowledge Marketing Libraries.
 *
 * Note:  Inherited objects are required for this class to operate.
 *
 * @package    ECN Suite
 * @author     Kevin Herda <kherda@sgcmail.com.com>
 * @version    Release: 2.1
 */

class Communicator {

  /**
   * ECN URL
   * @var String
   */
  protected $ecn_url = 'http://api.ecn5.com/api/';

  /**
   * Executor for the ECN Post call
   * @param  String $request
   *   This must be the function we are return, that is case senstive from KM.
   * @param  String $params
   *   Params are constructed in the inherited class object.
   * @return mixed
   *   Returning an array.
   */
  public function execute($request, $params, $method){

    $url = $this->ecn_url . $request;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json; charset=utf-8',
      'Accept: application/json',
      'APIAccessKey: ' . $this->token,
      'X-Customer-ID: ' . $this->customerid,
      'Host: api.ecn5.com',
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    if (($method == 'POST' || $method == 'GET') && count($params) > 0) {
      curl_setopt($ch, CURLOPT_POST, TRUE );
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    }
    elseif ($method == 'GET' && count($params) == 0) {
      curl_setopt($ch, CURLOPT_POST, FALSE );
    }
    else { //if ($method == 'PUT' || $method == 'DELETE')
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method );
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    }

    // Send the request
    $response = curl_exec($ch);

    // Check for errors
    if($response === FALSE){
      return curl_error($ch);
    }
    else{
      $responseData = json_decode($response);
      return $responseData;
    }
  }
}
