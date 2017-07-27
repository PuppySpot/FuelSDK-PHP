<?php

namespace Salesforce;

use DateInterval;
use DateTime;
use DOMDocument;
use DOMXPath;
use Exception;
use Firebase\JWT\JWT;
use RobRichards\WsePhp\WSSESoap;
use Salesforce\Action\EmailSendDefinition;
use Salesforce\Action\Import;
use Salesforce\Action\TriggeredSend;
use Salesforce\DataExtension\DataExtension;
use Salesforce\Request\Soap\PostRequest;
use Salesforce\Type\ContentArea;
use Salesforce\Type\Folder;
use Salesforce\Type\ProfileAttribute;
use Salesforce\Type\Subscriber;
use Salesforce\Util\Util;
use SoapClient;
use stdClass;

/**
 * Defines a Client interface class which manages the authentication process.
 * This is the main client class which performs authentication, obtains auth token, if expired refresh auth token.
 * Settings/Configuration can be passed to this class during construction or set it in config.php file.
 * Configuration passed as parameter overrides the values from the configuration file.
 *
 */
class Client extends SoapClient
{

    /**
     * @var string $packageName Folder/Package Name
     */
    public $packageName;

    /**
     * @var array $packageFolders Array of Folder object properties.
     */
    public $packageFolders;

    /**
     * @var Folder Parent folder object.
     */
    public $parentFolders;

    /**
     * @var string Proxy host.
     */
    public $proxyHost;

    /**
     * @var string Proxy port.
     */
    public $proxyPort;

    /**
     * @var string Proxy username.
     */
    public $proxyUserName;

    /**
     * @var string Proxy password.
     */
    public $proxyPassword;

    private $debugSOAP = false;

    private $clientId;
    private $clientSecret;

    private $wsdlLoc = 'https://webservice.exacttarget.com/etframework.wsdl';
    private $xmlLoc = 'ExactTargetWSDL.xml';
    private $baseUrl = 'https://www.exacttargetapis.com';
    private $baseAuthUrl = 'https://auth.exacttargetapis.com';

    private $tenantTokens;
    private $tenantKey;

    private $lastHTTPCode;

    /**
     * Initializes a new instance of the ET_Client class.
     *                         Logging is enabled when the value is set to true and disabled when set to false.
     *
     * @param array   $params  Array of settings as string.</br>
     *                         <b>Following are the possible settings.</b></br>
     *                         <i><b>get_wsdl</b></i> -  Flag to indicate whether to load WSDL from source.
     *                         <i><b>debug</b></i> - Flag to indicate whether debug information needs to be logged.
     *                         <i><b>defaultwsdl</b></i> - WSDL location/path</br>
     *                         <i><b>clientid</b></i> - Client Identifier optained from App Center</br>
     *                         <i><b>clientsecred</b></i> - Client secret associated with clientid</br>
     *                         <i><b>appsignature</b></i> - Application signature optained from App Center</br>
     *                         <i><b>baseUrl</b></i> - ExactTarget SOAP API Url</br>
     *                         <i><b>baseAuthUrl</b></i> - ExactTarget authentication rest api resource url</br>
     *                         <b>If your application behind a proxy server, use the following setting</b></br>
     *                         <i><b>proxyhost</b></i> - proxy server host name or ip address</br>
     *                         <i><b>proxyport</b></i> - proxy server prot number</br>
     *                         <i><b>proxyusername</b></i> - proxy server user name</br>
     *                         <i><b>proxypassword</b></i> - proxy server password</br>
     *
     * @throws \Exception
     */
    public function __construct($params = [])
    {
        if (isset($params['client_id'])) {
            $this->clientId = $params['client_id'];
        } else {
            throw new \InvalidArgumentException('"client_id" is a required configuration value.');
        }

        if (isset($params['client_secret'])) {
            $this->clientSecret = $params['client_secret'];
        } else {
            throw new \InvalidArgumentException('"client_secret" is a required configuration value.');
        }

        if (isset($params['jwt']) && \class_exists(JWT::class)) {
            $missing = [];
            foreach (['token', 'keys', 'tenant_key'] as $required) {
                if (empty($params['jwt'][$required])) {
                    $missing[] = $required;
                }
            }

            if (\count($missing)) {
                throw new \InvalidArgumentException(\sprintf(
                    'Unable to utilize JWT for SSO, missing required configuration values for "%s"',
                    \implode('", "', $missing)
                ));
            }

            $this->tenantKey = $params['jwt']['tenant_key'];

            $jwt = JWT::decode($params['jwt']['token'], $params['jwt']['keys']);
            $dv = new DateInterval('PT' . $jwt->request->user->expiresIn . 'S');
            $newExpTime = new DateTime();

            $this->setAuthToken($this->tenantKey, $jwt->request->user->oauthToken, $newExpTime->add($dv));
            $this->setInternalAuthToken($this->tenantKey, $jwt->request->user->internalOauthToken);
            $this->setRefreshToken($this->tenantKey, $jwt->request->user->refreshToken);
            $this->packageName = $jwt->request->application->package;
        }

        if (\array_key_exists('debug', $params)) {
            $this->debugSOAP = $params['debug'];
        }

        if (\array_key_exists('default_wsdl', $params)) {
            $this->wsdlLoc = $params['default_wsdl'];
        }

        if (\array_key_exists('xml_loc', $params)) {
            $this->xmlLoc = $params['xml_loc'];
        }

        // these are used at the end when we construct the soap client
        $soapOptions = [
            'trace'              => 1,
            'exceptions'         => 0,
            'connection_timeout' => 120,
        ];

        if (\array_key_exists('proxy', $params) && count($params['proxy'])) {
            if (\array_key_exists('host', $params['proxy'])) {
                $this->proxyHost = $params['proxy']['host'];
                $soapOptions['proxy_host'] = $this->proxyHost;
            }

            if (\array_key_exists('port', $params['proxy'])) {
                $this->proxyPort = $params['proxy']['port'];
                $soapOptions['proxy_port'] = $this->proxyPort;
            }

            if (\array_key_exists('username', $params['proxy'])) {
                $this->proxyUserName = $params['proxy']['username'];
                $soapOptions['proxy_username'] = $this->proxyUserName;
            }

            if (\array_key_exists('password', $params['proxy'])) {
                $this->proxyPassword = $params['proxy']['password'];
                $soapOptions['proxy_password'] = $this->proxyPassword;
            }
        }

        if (\array_key_exists('base_url', $params)) {
            $this->baseUrl = $params['base_url'];
        }

        if (\array_key_exists('base_auth_url', $params)) {
            $this->baseAuthUrl = $params['base_auth_url'];
        }

        // store it locally
        if (isset($params['get_wsdl']) && $params['get_wsdl']) {
            $this->CreateWSDL($this->wsdlLoc);
        }

        $this->refreshToken();

        try {
            $url = $this->baseUrl . '/platform/v1/endpoints/soap?access_token=' . $this->getAuthToken($this->tenantKey);
            $endpointResponse = Util::restGet($url, $this);
            $endpointObject = json_decode($endpointResponse->body);

            if (!isset($endpointObject->url)) {
                throw new \RuntimeException('Unable to determine stack using /platform/v1/endpoints/:' . $endpointResponse->body);
            }
        } catch (Exception $e) {
            throw new \RuntimeException('Unable to determine stack using /platform/v1/endpoints/: ' . $e->getMessage());
        }

        parent::__construct($this->xmlLoc, $soapOptions);

        $this->__setLocation($endpointObject->url);
    }

    /**
     * Create the WSDL file at specified location.
     *
     * @param $wsdlLoc
     *
     * @return void
     * @throws \Exception
     * @internal param \location $string or path of the WSDL file to be created.
     */
    public function CreateWSDL($wsdlLoc)
    {
        try {
            $getNewWSDL = true;

            $remoteTS = $this->GetLastModifiedDate($wsdlLoc);
            if (file_exists($this->xmlLoc)) {
                $localTS = filemtime($this->xmlLoc);
                if ($remoteTS <= $localTS) {
                    $getNewWSDL = false;
                }
            }
            if ($getNewWSDL) {
                $newWSDL = file_get_contents($wsdlLoc);
                file_put_contents($this->xmlLoc, $newWSDL);
            }
        } catch (Exception $e) {
            throw new Exception('Unable to store local copy of WSDL file:' . $e->getMessage() . "\n");
        }
    }

    /**
     * Returns last modified date of the URL
     *
     * @param $remotepath
     *
     * @return string Last modified date
     * @throws \Exception
     * @internal param $ [type] $remotepath
     */
    public function GetLastModifiedDate($remotepath)
    {
        $curl = curl_init($remotepath);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FILETIME, true);

        if (!empty($this->proxyHost)) {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxyHost);
        }
        if (!empty($this->proxyPort)) {
            curl_setopt($curl, CURLOPT_PROXYPORT, $this->proxyPort);
        }
        if (!empty($this->proxyUserName) && !empty($this->proxyPassword)) {
            curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxyUserName . ':' . $this->proxyPassword);
        }

        $result = curl_exec($curl);

        if ($result === false) {
            throw new Exception(curl_error($curl));
        }

        return curl_getinfo($curl, CURLINFO_FILETIME);
    }

    /**
     * Set the authentication token in the tenantTokens array.
     *
     * @param  string $tenantKey           Tenant key for which auth toke to be set
     * @param  string $authToken           Authentication token to be set
     * @param  string $authTokenExpiration Authentication token expiration value
     */
    public function setAuthToken($tenantKey, $authToken, $authTokenExpiration)
    {
        if ($this->tenantTokens[$tenantKey] == null) {
            $this->tenantTokens[$tenantKey] = [];
        }
        $this->tenantTokens[$tenantKey]['authToken'] = $authToken;
        $this->tenantTokens[$tenantKey]['authTokenExpiration'] = $authTokenExpiration;
    }

    /**
     * Set the internal auth tokan.
     *
     * @param  string $tenantKey
     * @param string  $internalAuthToken
     */
    public function setInternalAuthToken($tenantKey, $internalAuthToken)
    {
        if ($this->tenantTokens[$tenantKey] == null) {
            $this->tenantTokens[$tenantKey] = [];
        }
        $this->tenantTokens[$tenantKey]['internalAuthToken'] = $internalAuthToken;
    }

    /**
     * Set the refresh authentication token.
     *
     * @param  string $tenantKey    Tenant key to which refresh token is set
     * @param  string $refreshToken Refresh authenication token
     */
    public function setRefreshToken($tenantKey, $refreshToken)
    {
        if ($this->tenantTokens[$tenantKey] == null) {
            $this->tenantTokens[$tenantKey] = [];
        }
        $this->tenantTokens[$tenantKey]['refreshToken'] = $refreshToken;
    }

    /**
     * Gets the refresh token using the authentication URL.
     *
     * @param boolean $forceRefresh Flag to indicate a force refresh of authentication toekn.
     *
     * @return void
     * @throws \Exception
     */
    public function refreshToken($forceRefresh = false)
    {
        if (property_exists($this, 'sdl') && $this->sdl == 0) {
            parent::__construct($this->xmlLoc, ['trace' => 1, 'exceptions' => 0]);
        }
        try {
            $currentTime = new DateTime();
            if (null === $this->getAuthTokenExpiration($this->tenantKey)) {
                $timeDiff = 0;
            } else {
                $timeDiff = $currentTime->diff($this->getAuthTokenExpiration($this->tenantKey))->format('%i');
                $timeDiff += 60 * $currentTime->diff($this->getAuthTokenExpiration($this->tenantKey))->format('%H');
            }
            if (null === $this->getAuthToken($this->tenantKey) || ($timeDiff < 5) || $forceRefresh) {
                $url = $this->tenantKey == null
                    ? $this->baseAuthUrl . '/v1/requestToken?legacy=1'
                    : $this->baseUrl . "/provisioning/v1/tenants/{$this->tenantKey}/requestToken?legacy=1";

                $jsonRequest = new stdClass();
                $jsonRequest->clientId = $this->clientId;
                $jsonRequest->clientSecret = $this->clientSecret;
                $jsonRequest->accessType = 'offline';
                if (null !== $this->getRefreshToken($this->tenantKey)) {
                    $jsonRequest->refreshToken = $this->getRefreshToken($this->tenantKey);
                }
                $authResponse = Util::restPost($url, json_encode($jsonRequest), $this);
                $authObject = json_decode($authResponse->body);

                if ($authResponse && property_exists($authObject, 'accessToken')) {
                    $dv = new DateInterval('PT' . $authObject->expiresIn . 'S');
                    $newexpTime = new DateTime();
                    $this->setAuthToken($this->tenantKey, $authObject->accessToken, $newexpTime->add($dv));
                    $this->setInternalAuthToken($this->tenantKey, $authObject->legacyToken);
                    if (property_exists($authObject, 'refreshToken')) {
                        $this->setRefreshToken($this->tenantKey, $authObject->refreshToken);
                    }
                } else {
                    throw new Exception('Unable to validate App Keys(ClientID/ClientSecret) provided, requestToken response:' . $authResponse->body);
                }
            }
        } catch (Exception $e) {
            throw new Exception('Unable to validate App Keys(ClientID/ClientSecret) provided.: ' . $e->getMessage());
        }
    }

    /**
     * Get the Auth Token Expiration.
     *
     * @param  string $tenantKey Tenant key for which authenication token is returned
     *
     * @return string Authenticaiton token for the tenant key
     */
    public function getAuthTokenExpiration($tenantKey)
    {
        $tenantKey = $tenantKey == null ? $this->tenantKey : $tenantKey;
        if ($this->tenantTokens[$tenantKey] == null) {
            $this->tenantTokens[$tenantKey] = [];
        }

        return isset($this->tenantTokens[$tenantKey]['authTokenExpiration'])
            ? $this->tenantTokens[$tenantKey]['authTokenExpiration']
            : null;
    }

    /**
     * Get the authentication token.
     *
     * @param null $tenantKey
     *
     * @return string
     */
    public function getAuthToken($tenantKey = null)
    {
        $tenantKey = $tenantKey == null ? $this->tenantKey : $tenantKey;
        if ($this->tenantTokens[$tenantKey] == null) {
            $this->tenantTokens[$tenantKey] = [];
        }

        return isset($this->tenantTokens[$tenantKey]['authToken'])
            ? $this->tenantTokens[$tenantKey]['authToken']
            : null;
    }

    /**
     * Get the refresh token for the tenant.
     *
     * @param string $tenantKey
     *
     * @return string Refresh token for the tenant
     */
    public function getRefreshToken($tenantKey)
    {
        $tenantKey = $tenantKey == null ? $this->tenantKey : $tenantKey;
        if ($this->tenantTokens[$tenantKey] == null) {
            $this->tenantTokens[$tenantKey] = [];
        }

        return isset($this->tenantTokens[$tenantKey]['refreshToken'])
            ? $this->tenantTokens[$tenantKey]['refreshToken']
            : null;
    }

    /**
     * Returns the  HTTP code return by the last SOAP/Rest call
     *
     * @return int
     */
    public function getLastResponseHTTPCode()
    {
        return $this->lastHTTPCode;
    }

    /**
     * Perfoms an soap request.
     *
     * @param string  $request  Soap request xml
     * @param string  $location Url as string
     * @param string  $saction  Soap action name
     * @param string  $version  Future use
     * @param integer $one_way  Future use
     *
     * @return string Soap web service request result
     */
    public function __doRequest($request, $location, $saction, $version, $one_way = 0)
    {
        $doc = new DOMDocument();
        $doc->loadXML($request);
        $objWSSE = new WSSESoap($doc);
        $objWSSE->addUserToken('*', '*', false);
        $this->addOAuth($doc, $this->getInternalAuthToken($this->tenantKey));

        $content = $objWSSE->saveXML();
        $content_length = strlen($content);
        if ($this->debugSOAP) {
            error_log('FuelSDK SOAP Request: ');
            error_log(str_replace($this->getInternalAuthToken($this->tenantKey), 'REMOVED', $content));
        }

        $headers = ['Content-Type: text/xml', 'SOAPAction: ' . $saction, 'User-Agent: ' . Util::getSDKVersion()];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $location);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, Util::getSDKVersion());

        if (!empty($this->proxyHost)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyHost);
        }
        if (!empty($this->proxyPort)) {
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyPort);
        }
        if (!empty($this->proxyUserName) && !empty($this->proxyPassword)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyUserName . ':' . $this->proxyPassword);
        }

        $output = curl_exec($ch);
        $this->lastHTTPCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $output;
    }

    /**
     * Add OAuth token to the header of the soap request
     *
     * @param \DOMDocument $doc   Soap request as xml string
     * @param string       $token OAuth token
     *
     * @return void
     */
    public function addOAuth($doc, $token)
    {
        $soapDoc = $doc;
        $envelope = $doc->documentElement;
        $soapNS = $envelope->namespaceURI;
        $soapPFX = $envelope->prefix;
        $SOAPXPath = new DOMXPath($doc);
        $SOAPXPath->registerNamespace('wssoap', $soapNS);
        $SOAPXPath->registerNamespace('wswsse', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');

        $headers = $SOAPXPath->query('//wssoap:Envelope/wssoap:Header');
        $header = $headers->item(0);
        if (!$header) {
            $header = $soapDoc->createElementNS($soapNS, $soapPFX . ':Header');
            $envelope->insertBefore($header, $envelope->firstChild);
        }

        $authnode = $soapDoc->createElementNS('http://exacttarget.com', 'oAuth');
        $header->appendChild($authnode);

        $oauthtoken = $soapDoc->createElementNS(null, 'oAuthToken', $token);
        $authnode->appendChild($oauthtoken);
    }

    /**
     * Get the internal authentication token.
     *
     * @param  string $tenantKey
     *
     * @return string Internal authenication token
     */
    public function getInternalAuthToken($tenantKey)
    {
        $tenantKey = $tenantKey == null ? $this->tenantKey : $tenantKey;
        if ($this->tenantTokens[$tenantKey] == null) {
            $this->tenantTokens[$tenantKey] = [];
        }

        return isset($this->tenantTokens[$tenantKey]['internalAuthToken'])
            ? $this->tenantTokens[$tenantKey]['internalAuthToken']
            : null;
    }

    /**
     * Add subscriber to list.
     *
     * @param string $emailAddress  Email address of the subscriber
     * @param array  $listIDs       Array of list id to which the subscriber is added
     * @param string $subscriberKey Newly added subscriber key
     *
     * @return mixed post or patch response object. If the subscriber already existing patch response is returned
     *               otherwise post response returned.
     */
    public function AddSubscriberToList($emailAddress, $listIDs, $subscriberKey = null)
    {
        $newSub = new Subscriber;
        $newSub->authStub = $this;
        $lists = [];

        foreach ($listIDs as $key => $value) {
            $lists[] = ['ID' => $value];
        }

        //if (is_string($emailAddress)) {
        $newSub->props = ['EmailAddress' => $emailAddress, 'Lists' => $lists];
        if ($subscriberKey != null) {
            $newSub->props['SubscriberKey'] = $subscriberKey;
        }

        // Try to add the subscriber
        $postResponse = $newSub->post();
        // If the subscriber already exists in the account then we need to do an update.
        // Update Subscriber On List
        if ($postResponse->status == false && $postResponse->results[0]->ErrorCode == '12014') {
            return $newSub->patch();
        }

        return $postResponse;
    }

    public function AddSubscribersToLists($subs, $listIDs)
    {
        $lists = [];

        //Create Lists
        foreach ($listIDs as $key => $value) {
            $lists[] = ['ID' => $value];
        }

        for ($i = 0, $j = count($subs); $i < $j; $i++) {
            $copyLists = [];
            foreach ($lists as $k => $v) {
                $NewProps = [];
                foreach ($v as $prop => $value) {
                    $NewProps[$prop] = $value;
                }
                $copyLists[$k] = $NewProps;
            }
            $subs[$i]['Lists'] = $copyLists;
        }

        return new PostRequest($this, 'Subscriber', $subs, true);
    }

    /**
     * Create a new data extension based on the definition passed
     *
     * @param array $dataExtensionDefinitions Data extension definition properties as an array
     *
     * @return mixed post response object
     */
    public function CreateDataExtensions($dataExtensionDefinitions)
    {
        $newDEs = new DataExtension();
        $newDEs->authStub = $this;
        $newDEs->props = $dataExtensionDefinitions;

        return $newDEs->post();
    }

    /**
     * Starts an send operation for the TriggerredSend records
     *
     * @param array $arrayOfTriggeredRecords Array of TriggeredSend records
     *
     * @return mixed Send reponse object
     */
    public function SendTriggeredSends($arrayOfTriggeredRecords)
    {
        $sendTS = new TriggeredSend();
        $sendTS->authStub = $this;
        $sendTS->props = $arrayOfTriggeredRecords;

        return $sendTS->Send();
    }

    /**
     * Create an email send definition, send the email based on the definition and delete the definition.
     *
     * @param string $emailID                      Email identifier for which the email is sent
     * @param string $listID                       Send definition list identifier
     * @param string $sendClassficationCustomerKey Send classification customer key
     *
     * @return mixed Final delete action result
     * @throws \Exception
     */
    public function SendEmailToList($emailID, $listID, $sendClassficationCustomerKey)
    {
        $email = new EmailSendDefinition();
        $email->props = [
            'Name'               => uniqid('', true),
            'CustomerKey'        => uniqid('', true),
            'Description'        => 'Created with FuelSDK',
            'Email'              => [
                'ID' => $emailID,
            ],
            'SendClassification' => [
                'CustomerKey' => $sendClassficationCustomerKey,
            ],
            'SendDefinitionList' => [
                'List' => [
                    'ID'               => $listID,
                    'DataSourceTypeID' => 'List',
                ],
            ],
        ];

        $email->authStub = $this;
        $result = $email->post();

        if ($result->status) {
            $sendresult = $email->send();
            if ($sendresult->status) {
                $deleteresult = $email->delete();

                return $sendresult;
            }

            throw new Exception('Unable to send using send definition due to: ' . print_r($result, true));
        }

        throw new Exception('Unable to create send definition due to: ' . print_r($result, true));
    }

    /**
     * Create an email send definition, send the email based on the definition and delete the definition.
     *
     * @param string $emailID                          Email identifier for which the email is sent
     * @param string $sendableDataExtensionCustomerKey Sendable data extension customer key
     * @param string $sendClassficationCustomerKey     Send classification customer key
     *
     * @return mixed Final delete action result
     * @throws \Exception
     */
    public function SendEmailToDataExtension($emailID, $sendableDataExtensionCustomerKey, $sendClassficationCustomerKey)
    {
        $email = new EmailSendDefinition();
        $email->props = [
            'Name'               => uniqid('', true),
            'CustomerKey'        => uniqid('', true),
            'Description'        => 'Created with FuelSDK',
            'Email'              => [
                'ID' => $emailID,
            ],
            'SendClassification' => [
                'CustomerKey' => $sendClassficationCustomerKey,
            ],
            'SendDefinitionList' => [
                'CustomerKey'      => $sendableDataExtensionCustomerKey,
                'DataSourceTypeID' => 'CustomObject',
            ],
        ];

        $email->authStub = $this;
        $result = $email->post();
        if ($result->status) {
            $sendresult = $email->send();
            if ($sendresult->status) {
                $deleteresult = $email->delete();

                return $sendresult;
            }

            throw new Exception('Unable to send using send definition due to:' . print_r($result, true));
        }

        throw new Exception('Unable to create send definition due to: ' . print_r($result, true));
    }

    /**
     * Create an import definition and start the import process
     *
     * @param string $listId   List identifier. Used as the destination object identifier.
     * @param string $fileName Name of the file to be imported
     *
     * @return mixed Returns the import process result
     * @throws \Exception
     */
    public function CreateAndStartListImport($listId, $fileName)
    {
        $import = new Import();
        $import->authStub = $this;
        $import->props = ['Name' => 'SDK Generated Import ' . uniqid('', true)];
        $import->props['CustomerKey'] = uniqid('', true);
        $import->props['Description'] = 'SDK Generated Import';
        $import->props['AllowErrors'] = 'true';
        $import->props['DestinationObject'] = ['ID' => $listId];
        $import->props['FieldMappingType'] = 'InferFromColumnHeadings';
        $import->props['FileSpec'] = $fileName;
        $import->props['FileType'] = 'CSV';
        $import->props['RetrieveFileTransferLocation'] = ['CustomerKey' => 'ExactTarget Enhanced FTP'];
        $import->props['UpdateType'] = 'AddAndUpdate';
        $result = $import->post();

        if ($result->status) {
            return $import->start();
        }

        throw new Exception('Unable to create import definition due to: ' . print_r($result, true));
    }

    /**
     * Create an import definition and start the import process
     *
     * @param string $dataExtensionCustomerKey Data extension customer key. Used as the destination object identifier.
     * @param string $fileName                 Name of the file to be imported
     * @param bool   $overwrite                Flag to indicate to overwrite the uploaded file
     *
     * @return mixed Returns the import process result
     * @throws \Exception
     */
    public function CreateAndStartDataExtensionImport($dataExtensionCustomerKey, $fileName, $overwrite)
    {
        $import = new Import();
        $import->authStub = $this;
        $import->props = ['Name' => 'SDK Generated Import ' . uniqid('', true)];
        $import->props['CustomerKey'] = uniqid('', true);
        $import->props['Description'] = 'SDK Generated Import';
        $import->props['AllowErrors'] = 'true';
        $import->props['DestinationObject'] = ['CustomerKey' => $dataExtensionCustomerKey];
        $import->props['FieldMappingType'] = 'InferFromColumnHeadings';
        $import->props['FileSpec'] = $fileName;
        $import->props['FileType'] = 'CSV';
        $import->props['RetrieveFileTransferLocation'] = ['CustomerKey' => 'ExactTarget Enhanced FTP'];
        if ($overwrite) {
            $import->props['UpdateType'] = 'Overwrite';
        } else {
            $import->props['UpdateType'] = 'AddAndUpdate';
        }

        $result = $import->post();

        if ($result->status) {
            return $import->start();
        }

        throw new Exception('Unable to create import definition due to: ' . print_r($result, true));
    }

    /**
     * Create a profile attribute
     *
     * @param array $allAttributes Profile attribute properties as an array.
     *
     * @return mixed Post operation result
     */
    public function CreateProfileAttributes($allAttributes)
    {
        $attrs = new ProfileAttribute();
        $attrs->authStub = $this;
        $attrs->props = $allAttributes;

        return $attrs->post();
    }

    /**
     * Create one or more content areas
     *
     * @param array $arrayOfContentAreas Content areas properties as an array
     *
     * @return PostRequest
     */
    public function CreateContentAreas($arrayOfContentAreas)
    {
        $postC = new ContentArea();
        $postC->authStub = $this;
        $postC->props = $arrayOfContentAreas;

        return $postC->post();
    }
}
