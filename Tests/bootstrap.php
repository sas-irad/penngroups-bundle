<?php

$globalTestDir = __DIR__;

$globalParams = array('username'      => "irad-authz/sas.upenn.edu",
                      'password_file' => "$globalTestDir/Resources/pg-irad-authz.txt");

$globalStorageParams = array( 'keys' => 
                        array('public'  => "$globalTestDir/Resources/public.pem",
                              'private' => "$globalTestDir/Resources/private.pem"));

require_once "../vendor/sas-irad/person-info-bundle/PersonInfo/PersonInfoInterface.php";
require_once "../vendor/sas-irad/person-info-bundle/PersonInfo/PersonInfo.php";
require_once "../vendor/sas-irad/file-storage-bundle/Service/EncryptedFileStorageService.php";
require_once "../vendor/sas-irad/file-storage-bundle/Storage/EncryptedFileStorage.php";
require_once "../vendor/sas-irad/file-storage-bundle/Storage/FileStorage.php";
require_once "../Service/LDAPQuery.php";
require_once "../Service/WebServiceQuery.php";
require_once "../Service/PennGroupsQueryCache.php";
require_once "../PersonInfo/PennGroupsSubjectInfo.php";
require_once "../Request/WsRestAbstractRequest.php";
require_once "../Request/WsRestGetGroupsRequest.php";
require_once "../Request/WsRestGetSubjectsRequest.php";
require_once "../Request/WsRestGetMembersRequest.php";
require_once "../Request/WsRestHasMemberRequest.php";
require_once "../Response/WsAbstractResultsResponse.php";
require_once "../Response/WsGetSubjectsResultsResponse.php";
require_once "../Response/WsGetMembersResultsResponse.php";
require_once "../Response/WsGetGroupsResultsResponse.php";
require_once "../Response/WsHasMemberResultsResponse.php";
require_once "./Resources/Mock/Session.php";
