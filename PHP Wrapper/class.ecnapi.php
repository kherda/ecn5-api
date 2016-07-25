<?php
/**
 *  ECN List Manager
 *
 *  The ECN List Manager is a set of API calls used to fully manage your
 *  Folders, lists and subscriber based information in the ECN.
 *
 * @package    ECN Suite
 * @author     Kevin Herda <kherda@sgcmail.com.com>
 * @version    Release: 2.1
 */

require_once 'class.ecncommunicator.php';

/**
 * The List Manager Class is used to push and get information from the ListManager API section in the ECN.
 *
 * Note:  The function naming convention must match the API function naming method exactly.
 */
class ECNManager extends Communicator {

  /**
   * The token is the authentication key APIAccessKey used to validate the API request.
   * @var String
   */
  protected $token;

  /**
   * The Customer ID is the authentication X-Customer-ID used to validate the API request.
   * @var Integer
   */
  protected $customerid;

   /**
   * Construct the protected elements of the class.
   * @param String $token
   */
  public function __construct($token, $customerid) {

    $this->token = $token;
    $this->customerid= $customerid;
  }

  /*********************************************************
   ***************** Content Methods ************************
   *********************************************************/

  /**
   * [SearchContent description]
   *
   * EXAMPLES -- Optional array values to search from.
   *
   * $arr = array(
   *   'Title' => 'Test',
   *   'FolderID' => '123',
   *   'UpdatedDateFrom' =>'2014-10-17 07:45:00',
   *   'UpdatedDateTo' => '2014-10-17 07:45:00',
   *   'Archived' => false
   * );
   * $result = $lm->SearchContent($arr);
   *
   * @param array $arrayData [description]
   */
  public function SearchContent($arrayData) {

    $searchArg = new ECNSearchArguments('Content');

    foreach ($arrayData as $k => $v) {
      $searchArg->AddCriteria($k, $v);
    }

    $searchArg = $searchArg->ToSearchCriteriaArray();
    $params = array('SearchCriteria' => $searchArg);

    return parent::execute('search/content', $params, 'POST');
  }

  /**
   * [GetContent description]
   * @param integer $id [description]
   */
  public function GetContent($id) {

     $params = NULL;
     return parent::execute('content/' . $id, $params, 'GET');
  }

  /**
   * AddContent
   * @param integer $FolderID     [description]
   * @param string  $ContentHTML  [description]
   * @param string  $ContentText  [description]
   * @param string  $ContentTitle [description]
   */
  public function AddContent($FolderID, $ContentHTML, $ContentText, $ContentTitle) {

    $params = array(
      'FolderID' => $FolderID,
      'ContentSource' => $ContentHTML,
      'ContentText' => $ContentText,
      'ContentTitle' => $ContentTitle
    );

    return parent::execute('content', $params, 'POST');
  }

  /**
   * UpdateContent
   * @param integer $FolderID     [description]
   * @param string  $ContentHTML  [description]
   * @param string  $ContentText  [description]
   * @param string  $ContentTitle [description]
   * @param integer $ContentID    [description]
   */
  public function UpdateContent($FolderID, $ContentHTML, $ContentText, $ContentTitle, $ContentID) {

    $params = array(
      'ContentID' => $ContentID,
      'FolderID' => $FolderID,
      'ContentSource' => $ContentHTML,
      'ContentText' => $ContentText,
      'ContentTitle' => $ContentTitle
    );
    return parent::execute('content/' . $ContentID, $params, 'PUT');
  }

  /**
   * DeleteContent
   * @param integer $ContentID [description]
   */
  public function DeleteContent($ContentID) {

    $params = NULL;
    return parent::execute('content/' . $ContentID, $params, 'DELETE');
  }

  /********************************************************************
   ********************* CustomField Methods **************************
   ********************************************************************/

  /**
   * SearchCustomField
   *
   * EXAMPLES -- Optional array values to search from.
   *
   * $arr = array(
   *   'GroupID' => '123',
   * );
   * $result = $lm->SearchCustomField($arr);
   *
   * @param array $arrayData [description]
   */
  public function SearchCustomField($arrayData) {

    $searchArg = new ECNSearchArguments('CustomField');

    foreach ($arrayData as $k => $v) {
      $searchArg->AddCriteria($k, $v);
    }

    $searchArg = $searchArg->ToSearchCriteriaArray();
    $params = array('SearchCriteria' => $searchArg);

    return parent::execute('search/customfield', $params, 'POST');
  }

  /**
   * GetCustomFieldByID
   * @param integer $udfID [description]
   */
  public function GetCustomFieldByID($udfID) {

    $params = NULL;
    return parent::execute('customfield/' . $udfID, $params, 'GET');
  }

  /**
   * AddCustomField
   * @param integer $listID                 Group ID
   * @param string  $customFieldName        [description]
   * @param string  $customFieldDescription [description]
   * @param string  $isPublic               [description]
   */
  public function AddCustomField($listID, $customFieldName, $customFieldDescription, $isPublic = 'N') {

    $params = array(
      'GroupID' => $listID,
      'ShortName' => $customFieldName,
      'LongName' => $customFieldDescription,
      'IsPublic' => $isPublic
    );
    return parent::execute('customfield', $params, 'POST');
  }

  /**
   * DeleteCustomField -- NOT IMPLEMENTED YET
   * @param integer $udfID  [description]
   */
  public function DeleteCustomField($udfID) {

    $params = NULL;
    return parent::execute('customfield/' . $udfID, $params, 'DELETE');
  }


  /**
   * UpdateCustomField -- NOT IMPLEMENTED YET
   * @param integer $listID                 [description]
   * @param integer $udfID                  [description]
   * @param string  $customFieldName        [description]
   * @param string  $customFieldDescription [description]
   * @param string  $isPublic               [description]
   */
  public function UpdateCustomField($listID, $udfID, $customFieldName, $customFieldDescription, $isPublic = 'N') {

    $params = array(
      'GroupDataFieldsID' => $udfID,
      'GroupID' => $listID,
      'ShortName' => $customFieldName,
      'LongName' => $customFieldDescription,
      'IsPublic' => $isPublic
    );
    return parent::execute('customfield/' . $udfID, $params, 'PUT');
  }

  /*********************************************************************
   ******************* EmailGroup / Subscribers ************************
   *********************************************************************/

  /**
   * [ddSubscribers -- Can add more than one to the same group
   * @param integer $listID           [description]
   * @param string  $subscriptionType [description]
   * @param string  $formatType       [description]
   * @param string  $arrayData        [description]
   */
  public function AddSubscribers($listID, $subscriptionType = 'S', $formatType = 'html', $arrayData) {

    $params = array(
      'GroupID' => $listID,
      'Format' => $formatType,
      'SubscribeType' => $subscriptionType,
      'Profiles' => $arrayData
    );

    return parent::execute('emailgroup/methods/ManageSubscriberWithProfile', $params, 'POST');
  }

  /**
   * UpdateEmailAddressForGroup
   * @param [type] $arrayData
   */

  /**
   Updates a subscriber’s Email Address and profile data directly within a single group.
   Note: Under normal circumstances please use UpdateEmailAddress to update a subscriber’s Email Address or ManageSubscriberWithProfile to update a subscriber’s profile data and check with your Digital Specialist before using this method as it may have unintended results.
   */
  public function UpdateEmailAddressForGroup($arrayData) {

    $params = $arrayData;
    return parent::execute('emailgroup/methods/UpdateEmailAddressForGroup', $params, 'POST');
  }

  /**
   * UpdateEmailAddress
   * @param string $oldEmail [description]
   * @param string $newEmail [description]
   */
  /**
   Updates a subscriber’s Email Address by Master Suppressing the old Email Address and creating a new Email Address record with the old profile data.
   */
  public function UpdateEmailAddress($oldEmail, $newEmail) {

    $params = array(
      'OldEmailAddress' => $oldEmail,
      'NewEmailAddress' => $newEmail,
    );
    return parent::execute('emailgroup/methods/UpdateEmailAddress', $params, 'POST');
  }

  /**
   * GetSubscriberCount
   * @param integer $GroupID [description]
   */
  public function GetSubscriberCount($GroupID) {

    $params = NULL;
    return parent::execute('emailgroup/' . $GroupID . '/Count', $params, 'GET');
  }

  /**
   * AddToMasterSuppressionList -- NEW IMPLEMENTATION
   * [ 'foo-suppressed@bar.com', 'too-suppressed@bar.com' ]
   * @param array $email [description]
   */
  public function AddToMasterSuppressionList($arrayData) {

    $params = $arrayData;
    return parent::execute('emailgroup/methods/MasterSuppress', $params, 'POST');
  }

  /**
   * DeleteSubscriber
   * @param integer $listID       [description]
   * @param string  $EmailAddress [description]
   */
  public function DeleteSubscriber($listID, $emailAddress) {
    return parent::execute('emailGroup/' . $listID . '/DeleteByEmailAddress', $emailAddress, 'DELETE');
  }

  /**
   * [GetListEmailProfilesByEmailAddress description] Get data by email
   * Get the profile information for a given email address within a customer for a specific group. If the email address is associated with the given group we will return standard profile fields as well as their relationship to the group and any UDF data. If the email address exists within the customer but is not associated with the given group we will only return standard profile information. If the email address is not associated with the customer we will return a null/empty result set.
   *
   * @param integer $listID       [description]
   * @param string  $emailAddress [description]
   */
  public function BestProfileForEmailAddress($listID, $emailAddress) {
    return parent::execute('emailGroup/' . $listID . '/BestProfileForEmailAddress', $emailAddress, 'GET');
  }

  /**
   * [GetProfileByEmailAddress description] Get data by list id + Email
   *
   * @param integer $listID       [description]
   * @param string  $emailAddress [description]
   */
  public function GetProfileByEmailAddress($listID, $emailAddress) {
    return parent::execute('emailGroup/' . $listID . '/ProfilesByEmailAddress', $emailAddress, 'GET');
  }

  /**
   * [GetSubscriberStatus description] Get group subscription status
   * @param string $emailAddress [description]
   */
  public function GetSubscriberStatus($emailAddress) {
    return parent::execute('emailGroup/methods/StatusByEmailAddress', $emailAddress, 'GET');
  }

  /****************************************************************************
   ************************** Filter ******************************************
   ****************************************************************************/

  /**
   * [SearchFilter description]
   *
   * EXAMPLES -- Optional array values to search from.
   *
   * $arr = array(
   *   'GroupID' => '123',
   *   'Archived' => false
   * );
   * $result = $lm->SearchFilter($arr);
   *
   * @param array $arrayData [description]
   */
  public function SearchFilter($arrayData) {

    $searchArg = new ECNSearchArguments('Filter');

    foreach ($arrayData as $k => $v) {
      $searchArg->AddCriteria($k, $v);
    }

    $searchArg = $searchArg->ToSearchCriteriaArray();
    $params = array('SearchCriteria' => $searchArg);

    return parent::execute('search/filter', $params, 'POST');
  }

  /**
   * [GetFilterByID description] NOT IMPLEMENTED YET
   * @param integer $filterID [description]
   */
  public function GetFilterByID($filterID, $filterName,$listID) {

    $params = array(
      'FilterID' => $filterID,
      'FilterName' => $filterName,
      'GroupID' => $listID,
      'Archived' => TRUE
    );

    return parent::execute('filter/' . $filterID, $params, 'GET');
  }

  /**
   * [GetFilter description]  NOT IMPLEMENTED YET
   * @param integer $filterID   [description]
   * @param string  $filterName [description]
   * @param integer $listID     [description]
   */
  public function GetFilter($filterID, $filterName,$listID) {

    $params = array(
      'FilterID' => $filterID,
      'FilterName' => $filterName,
      'GroupID' => $listID,
      'Archived' => TRUE,
    );
    return parent::execute('filter', $params, 'GET');
  }

  /**
   * [DeleteFilter description] NOT IMPLEMENTED YET
   * @param integer $filterID [description]
   */
  public function DeleteFilter($filterID) {

    $params = NULL;
    return parent::execute('filter/' . $filterID, $params, 'DELETE');
  }


  /**
   * [UpdateFilter description] NOT IMPLEMENTED YET
   * @param integer $filterID   [description]
   * @param string  $filterName [description]
   * @param integer $listID     [description]
   */
  public function UpdateFilter($filterID, $filterName,$listID) {

    $params = array(
      'FilterID' => $filterID,
      'FilterName' => $filterName,
      'GroupID' => $listID,
      'Archived' => TRUE,
    );
    return parent::execute('filter/' . $filterID, $params, 'PUT');
  }

  /******************************************************************
   ************************** Folder ********************************
   ******************************************************************/

  /**
   * [GetFolderByID description]
   * @param integer $FolderID [description]
   */
  public function GetFolderByID($folderID) {

    $params = NULL;
    return parent::execute('folder/' . $folderID, $params, 'GET');
  }

  /**
   * [AddFolder description]  NEW IMPLEMENTATION
   * @param string  $folderName        [description]
   * @param string  $folderDescription [description]
   * @param integer $parentFolderID    default 0
   * @param string  $folderType        [description]
   */
  public function AddFolderToParent($folderName, $folderDescription, $parentFolderID, $folderType = 'CNT') {

    $params = array(
      'FolderName' => $folderName,
      'FolderType' => $folderType,
      'ParentID' => $parentFolderID,
      'FolderDescription' => $folderDescription
    );
    return parent::execute('folder/', $params, 'POST');
  }

  /**
   * [UpdateFolder description]
   * @param string  $folderName        [description]
   * @param string  $folderDescription [description]
   * @param integer $parentFolderID    [description]
   * @param string  $folderType        [description]
   * @param integer $folderID          [description]
   */
  public function UpdateFolder($folderName, $folderDescription, $parentFolderID, $folderType = 'CNT', $folderID) {

    $params = array(
      'FolderID' => $folderID,
      'FolderName' => $folderName,
      'FolderType' => $folderType,
      'ParentID' => $parentFolderID,
      'FolderDescription' => $folderDescription
    );
    return parent::execute('folder/' . $folderID, $params, 'PUT');
  }

  /**
   * [DeleteFolder description]
   * @param integer $FolderID [description]
   */
  public function DeleteFolder($folderID) {

    $params = NULL;
    return parent::execute('folder/' . $folderID, $params, 'DELETE');
  }

  /**
   * [SearchFolder description]
   *
   * EXAMPLES -- Optional array values to search from.
   * Type CNT, GRP
   *
   * $arr = array(
   *   'Type' => 'CNT'
   * );
   * $result = $lm->SearchFolder($arr);
   *
   * @param array $arrayData [description]
   */
  public function SearchFolder($arrayData) {

    $searchArg = new ECNSearchArguments('Folder');

    foreach ($arrayData as $k => $v) {
      $searchArg->AddCriteria($k, $v);
    }

    $searchArg = $searchArg->ToSearchCriteriaArray();
    $params = array('SearchCriteria' => $searchArg);

    //get & post should work
    return parent::execute('search/folder', $params, 'POST');
  }


  /************************************************************
   **************** Group / List methods **********************
   ************************************************************/

  /**
   * [GetLists description]
   * @param array $arrayData [description]
   *
   * EXAMPLES -- Optional array values to search from.
   *
   * $arr = array(
   *   'Name' => 'Test',
   *   'FolderID' => '123',
   *   'Archived' => false
   * );
   * $result = $lm->GetLists($arr);
   */
  public function GetLists($arrayData = '') {

    $searchArg = new ECNSearchArguments('Group');

    if (!empty($arrayData)) {
      foreach ($arrayData as $k => $v) {
        $searchArg->AddCriteria($k, $v);
      }
    }

    $searchArg = $searchArg->ToSearchCriteriaArray();
    $params = array('SearchCriteria' => $searchArg);

    return parent::execute('search/group', $params, 'POST');
  }

  /**
   * [GetListByID description]
   * @param integer $id [description]
   */
  public function GetListByID($id) {

     $params = NULL;
     return parent::execute('group/' . $id, $params, 'GET');
  }

  /**
   * [AddListToFolder description]
   * @param string $listName        [description]
   * @param string $listDescription [description]
   * @param integer $FolderID        [description]
   */
  public function AddListToFolder($listName, $listDescription, $FolderID) {

    $params = array(
      'FolderID' => $FolderID,
      'GroupName' => $listName,
      'GroupDescription' => $listDescription
    );

    return parent::execute('group', $params, 'POST');
  }


  /**
   * [UpdateListWithFolder description] NOT IMPLEMENTED YET
   * @param integer $listID          [description]
   * @param string  $listName        [description]
   * @param string  $listDescription [description]
   * @param integer $folderID        [description]
   */
  public function UpdateList($listID, $listName, $listDescription, $folderID) {

    $params = array(
      'GroupID' => $listID,
      'FolderID' => $FolderID,
      'GroupName' => $listName,
      'GroupDescription' => $listDescription
    );
    return parent::execute('group/' . $listID, $params, 'PUT');
  }

  /**
   * [DeleteList description] NOT IMPLEMENTED YET
   * @param integer $listID [description]
   */
  public function DeleteList($listID) {

    $params = NULL;
    return parent::execute('group/' . $listID, $params, 'DELETE');
  }


  /******************************************************************
   *********************** Image ************************************
   ******************************************************************/

  /**
   * [SearchImage description]
   * Search Criteria could be empty array POST/GET
   *
   * EXAMPLES -- Optional array values to search from.
   *
   * $arr = array(
   *   'ImageName' => 'MyImage.jpg',
   *   'FolderName' => 'MyFolder',
   *   'Recursive' => 'TRUE'
   * );
   * $result = $lm->SearchImage($arr);
   *
   * @param array $arrayData [description]
   */
  public function SearchImage($arrayData) {

    $searchArg = new ECNSearchArguments('Image');

    foreach ($arrayData as $k => $v) {
      $searchArg->AddCriteria($k, $v);
    }

    $searchArg = $searchArg->ToSearchCriteriaArray();
    $params = array('SearchCriteria' => $searchArg);

    return parent::execute('search/image', $params, 'POST');
  }

  /**
   * [GetImage description]  NOT IMPLEMENTED YET
   * @param integer $imageID [description]
   */
  public function GetImage($imageID){

    $params = NULL;
    return parent::execute('image/' . $imageID, $params, 'GET');
  }

  /**
   * [AddImage description]
   * @param string $folderName [description]
   * @param string $imageName  [description]
   * @param string $omageData  [description]
   */
  public function AddImage($folderName, $imageName, $imageData) {

    $params = array(
      'FolderName' => $folderName,
      'ImageName' => $imageName,
      'ImageData' => $ImageData,
    );

    return parent::execute('image/', $params, 'POST');
  }

  /**
   * [UpdateImage description]
   * @param string  $folderName [description]
   * @param integer $folderID   [description]
   * @param integer $imageID    [description]
   * @param string  $imageName  [description]
   * @param string  $imageData  [description]
   * @param string  $imageURL   [description]
   */
  public function UpdateImage($folderName, $folderID, $imageID, $imageName, $imageData, $imageURL) {

    $params = array(
      'FolderName' => $folderName,
      'FolderID' => $folderID,
      'ImageID' => $imageID,
      'ImageName' => $imageName,
      'ImageData' => $ImageData,
      'ImageURL' => $imageURL,
    );
    return parent::execute('image/' . $imageID, $params, 'PUT');
  }

  /**
   * [DeleteImage description]
   * @param string $folderName [description]
   * @param string $imageName  [description]
   */
  public function DeleteImage($folderName, $imageName) {

    $params = array(
      'FolderName' => $folderName,
      'ImageName' => $imageName,
    );
    return parent::execute('image', $params, 'DELETE');
  }

  /************************************************************
   ******************** Image Folder **************************
   ************************************************************/

  /**
   * [GetImageFolder description] NOT IMPLEMENTED YET
   * @param integer $imageFolderID [description]
   */
  public function GetImageFolder($imageFolderID) {

    $params = NULL;
    return parent::execute('imagefolder/' . $imageFolderID, $params, 'GET');
  }

  /**
   * [UpdateImageFolder description]
   * @param integer $imageFolderID          [description]
   * @param string  $imageFolderName        [description]
   * @param string  $imageFolderDescription [description]
   */
  public function UpdateImageFolder($imageFolderID, $imageFolderName, $imageFolderDescription) {

    $params = array(
      'FolderID' => $imageFolderID,
      'FolderName' => $imageFolderName,
      'FolderFullName' => $imageFolderDescription,
    );
    return parent::execute('imagefolder/' . $imageFolderID, $params, 'PUT');
  }

  /**
   * [DeleteImageFolder description] NOT IMPLEMENTED YET
   * @param integer $imageFolderID [description]
   */
  public function DeleteImageFolder($imageFolderID) {

    $params = NULL;
    return parent::execute('imagefolder/' . $imageFolderID, $params, 'DELETE');
  }

  /**
   * [SearchImageFolder description]
   *
   * EXAMPLES -- Optional array values to search from.
   *
   * $arr = array(
   *   'FolderName' => 'MySubFolder',
   *   'Recursive' => TRUE
   * );
   * $result = $lm->SearchImageFolder($arr);
   *
   * @param array $arrayData [description]
   */
  public function SearchImageFolder($arrayData) {

    $searchArg = new ECNSearchArguments('ImageFolder');

    foreach ($arrayData as $k => $v) {
      $searchArg->AddCriteria($k, $v);
    }

    $searchArg = $searchArg->ToSearchCriteriaArray();
    $params = array('SearchCriteria' => $searchArg);

    return parent::execute('search/imagefolder', $params, 'GET');
  }

  /****************************************************************
  ******************* Message *************************************
  *****************************************************************/

   /**
    * [SerachMessageList description]
    *
    * EXAMPLES -- Optional array values to search from.
    *
    * $arr = array(
    *   'Title' => 'test',
    *   'FolderID' => '123',
    *   'UpdatedDateFrom' =>'2014-10-17 07:45:00',
    *   'UpdatedDateTo' => '2015-01-01 00:00:00',
    *   'LastUpdatedByUser' => '1234',
    *   'Archived' => false
    * );
    * $result = $lm->SearchMessageList($arr);
    *
    * @param array $arrayData [description]
    */
  public function SearchMessageList($arrayData) {

    $searchArg = new ECNSearchArguments('Message');

    foreach ($arrayData as $k => $v) {
      $searchArg->AddCriteria($k, $v);
    }

    $searchArg = $searchArg->ToSearchCriteriaArray();
    $params = array('SearchCriteria' => $searchArg);

    return parent::execute('search/message', $params, 'GET');
  }

  /**
  * [GetMessage description]
  * @param integer $messageID [description]
  */
  public function GetMessage($messageID) {

    $params = NULL;
    return parent::execute('message/' . $messageID, $params, 'GET');
  }

  /**
   * [AddMessage description] including AddMessageWithType, AddMessageToFolder
  * @param array $arrayData [description]
  */
  public function AddMessage($arrayData) {

    $params = $arrayData;
    return parent::execute('message', $params, 'POST');
  }

  /**
   * [UpdateMessage description]
   * @param integer $messageID [description]
   * @param array   $arrayData [description]
   */
  public function UpdateMessage($messageID, $arrayData) {

    $params = $arrayData;
    return parent::execute('message/' . $messageID, $params, 'PUT');
  }

   /**
    * [DeleteMessage description]
    * @param integer $messageID [description]
    */
  public function DeleteMessage($messageID) {

    $params = NULL;
    return parent::execute('message/' . $messageID, $params, 'DELETE');
  }

  /*******************************************************************
  ********************* SimpleBlastV2 ********************************
  ********************************************************************/

  /**
    * [GetBlast description]
    * @param integer $blastID [description]
  */
  public function GetBlast($blastID) {

    $params = NULL;
    return parent::execute('simpleblastV2/' . $blastID, $params, 'GET');
  }

  /**
  * [AddBlast description]
  * @param array $arrayData [description]
  */
  public function AddBlast($arrayData) {

    $params = $arrayData;
    return parent::execute('simpleblastV2', $params, 'GET');
  }

  /**
  * [UpdateBlast description]
  * @param integer $blastID   [description]
  * @param array   $arrayData [description]
  */
  public function UpdateBlast($blastID, $arrayData) {

    $params = $arrayData;
    return parent::execute('simpleblastV2/' . $blastID, $params, 'GET');
  }

  /**
  * [DeleteBlast description]
  * @param integer $blastID [description]
  */
  public function DeleteBlast($blastID) {

    $params = NULL;
    return parent::execute('simpleblastV2/' . $blastID, $params, 'DELETE');
  }


  /**
  * [GetBlastBounceReport description]
  * @param integer $blastId     [description]
  * @param boolean $withDetails [description]
  */
  public function GetBlastBounceReport($blastId, $withDetails = false) {

    $params = NULL;
    return parent::execute('SimpleBlastV2/' . $blastID . '/Report/Bounces?withDetail=' . $withDetails , $params, 'GET');
  }

  /**
  * [GetBlastClicksReport description]
  * @param integer  $blastId     [description]
  * @param integer  $filterType  [description]
  * @param boolean  $withDetails [description]
  */
  public function GetBlastClicksReport($blastId, $filterType = 0, $withDetails = false) {

    $params = NULL;
    return parent::execute('SimpleBlastV2/' . $blastID . '/Report/Clicks?filterType=' . $filterType . '&withDetail=' . $withDetails , $params, 'GET');
  }

  /**
  * [GetBlastDeliveryReport description]
  * @param integer $blastId  [description]
  * @param string  $fromDate [description]
  * @param string  $toDate   [description]
  */
  public function GetBlastDeliveryReport($blastId, $fromDate, $toDate) {

    $params = NULL;
    return parent::execute('simpleblastV2/' . $blastID . '/Report/Delivery?fromDate=' . $fromDate . '&toDate=' . $toDate, $params, 'GET');
  }

  /**
  * [GetBlastOpensReport description]
  * @param integer  $blastId     [description]
  * @param integer  $filterType  [description]
  * @param boolean  $withDetails [description]
  */
  public function GetBlastOpensReport($blastId, $filterType = 1, $withDetails = false) {

    $params = NULL;
    return parent::execute('simpleblastV2/' . $blastID . '/Report/Opens?filterType=' . $filterType . '&withDetail=' . $withDetails, $params, 'GET');
  }

  /**
  * [GetBlastReport description]
  * @param ineteger $blastID [description]
  */
  public function GetBlastReport($blastID) {

    $params = NULL;
    return parent::execute('simpleblastV2/' . $blastID . '/Report', $params, 'GET');
  }

  /**
  * [GetBlastReportByISP description]
  * @param integer $blastID   [description]
  * @param integer $arrayData [description]
  */
  public function GetBlastReportByISP($blastID, $arrayData = array('Comcast', 'Verizon', 'Frontier')) {

     $params = $arrayData;
     return parent::execute('simpleblastV2/' . $blastID . '/Report/ISP', $params, 'GET');
  }

  /**
  * [GetBlastUnsubscribeReport description]
  * @param integer $blastID [description]
  */
  public function GetBlastUnsubscribeReport($blastID) {

    $params = NULL;
    return parent::execute('simpleblastV2/' . $blastID . '/Report/Unsubscribe', $params, 'GET');
  }

  /**
  * [TestBlastLimit description]
  */
  public function TestBlastLimit() {

    $params = NULL;
    return parent::execute('simpleblastV2/methods/TestBlastLimit', $params, 'GET');
  }

  /**
   * [SearchForBlast description]
   *
   * EXAMPLES -- Optional array values to search from.
   *
   * $arr = array(
   *   'EmailSubject' => 'test',
   *   'GroupID' => '123',
   *   'IsTest' => false,
   *   'StatusCode' => 'Sent',
   *   'ModifiedTo' => '2015-09-01',
   *   'ModifiedFrom' => '2015-01-01',
   *   'CampaignID' => 123,
   *   'CampaignName' => 'Marketing Campaign',
   *   'CampaignItemName' => 'Campaign Item Name'
   * );
   * $result = $lm->SearchForBlast($arr);
   *
   * @param array $arrayData [description]
   */
  public function SearchForBlast($arrayData) {

    $searchArg = new ECNSearchArguments('SimpleBlast');

    foreach ($arrayData as $k => $v) {
      $searchArg->AddCriteria($k, $v);
    }

    $searchArg = $searchArg->ToSearchCriteriaArray();
    $params = array('SearchCriteria' => $searchArg);

    return parent::execute('search/SimpleBlastV2', $params, 'POST');
  }

}

/**
 * Search Argument Class
 */
class ECNSearchArguments{

  protected $resource;
  static private $criteriaArray;

  static private $comparatorPerCriteria = array(
    'Content' => array(
      'FolderID' => array('comparator' => '='),
      'Title' => array('comparator' => 'contains'),
      'Archived' => array('comparator' => '='),
      'UpdatedDateFrom' => array('comparator' => '>='),
      'UpdatedDateTo' => array('comparator' => '<='),
    ),
    'CustomField' => array(
      'GroupID' => array('comparator' => '='),
    ),
    'Filter' => array(
        'GroupID' => array('comparator' => '='),
        'Archived' => array('comparator' => '='),
    ),
    'Folder' => array(
      'Type' => array('comparator' => '='),
    ),
    'Group' => array(
      'Name' => array('comparator' => 'contains'),
      'FolderID' => array('comparator' => '='),
      'Archived' => array('comparator' => '='),
    ),
    'Image' => array(
      'ImageName' => array('comparator' => 'contains'),
      'FolderName' => array('comparator' => '='),
      'Recursive' => array('comparator' => '='),
      'Archived' => array('comparator' => '='),
    ),
    'ImageFolder' => array(
      'FolderName' => array('comparator' => '='),
      'Recursive' => array('comparator' => '='),
    ),
    'Message' => array(
      'FolderID' => array('comparator' => '='),
      'Title' => array('comparator' => 'contains'),
      'UpdatedDateFrom' => array('comparator' => '>='),
      'UpdatedDateTo' => array('comparator' => '<='),
      'Archived' => array('comparator' => '='),
    ),
    'SimpleBlast' => array(
      'EmailSubject' => array('comparator' => 'contains'),
      'GroupID' => array('comparator' => '='),
      'IsTest' => array('comparator' => '='),
      'StatusCode' => array('comparator' => '='),
      'ModifiedTo' => array('comparator' => '<='),
      'ModifiedFrom' => array('comparator' => '>='),
    )
  );


  public function __construct($resource) {
    $this->resource = $resource;
  }

  public function AddCriteria($propertyName, $value) {
    self::$criteriaArray[$propertyName] = array($value);
  }

  public function ToSearchCriteriaArray() {

    $result = array();
    $i = 0;

    $criteriaArray = self::$criteriaArray;
    $comparatorPerCriteria = self::$comparatorPerCriteria;

    if (empty($criteriaArray)) {
      $result = array();
    }
    else {
      foreach($criteriaArray as $key => $value){
        $result[$i++] = array(
          'Name' => $key,
          'Comparator' => $comparatorPerCriteria[$this->resource][$key]['comparator'],
          'ValueSet' => $value[0],
        );
      }
    }

    return $result;
  }
}

