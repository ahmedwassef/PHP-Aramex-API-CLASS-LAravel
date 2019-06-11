<?php


namespace App\Http\Controllers\Admin;


use App\Cart;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use SoapClient;
use SoapFault;

class Aramex extends Controller
{
    public function store(Request $request,$id)
    {
        $validator = \Validator::make($request->all(),[
            'NumberOfPieces'=>'required|numeric',
            'DescriptionOfGoods'=>'required|max:100',
            'SReference1'=>'required|max:50',
            'ActualWeight'=>'numeric|max:5',
            'comment'=>'required|max:1000',
            'OperationsInstructions'=>'required|max:1000',
        ]);

        if ($validator->fails())
        {
            popup('error',$validator);

            return back()->withInput(Input::all());
        }

        $cart=Cart::find($id);
        $ConsigneeName='';
        if(!is_null($cart->user_id))
        {$ConsigneeName=$cart->delivery_way == 'home'?$cart->cart_address->first_name  .' '.$cart->cart_address->last_name:$cart->client->name;}
        else
            $ConsigneeName=$cart->delivery_way == 'home'?$cart->cart_address->first_name  .' '.$cart->cart_address->last_name:$cart->client_name;

        $ConsigneePhone='';
        if(!is_null($cart->user_id))
        {$ConsigneePhone=$cart->delivery_way == 'home'?$cart->cart_address->phone:$cart->client->phone;}
        else
            $ConsigneePhone=$cart->delivery_way == 'home'?$cart->cart_address->phone:$cart->client_phone;


        $ConsigneeAddress='';
        if($cart->company_id!=null) {
            if ($cart->address_id != 0)
                $ConsigneeAddress = $cart->cart_address->address;
            else {$ConsigneeAddress = $cart->address;}
        }
        else {
            if ($cart->address_id != 0) {$ConsigneeAddress=  $cart->cart_address->address;}
            else {$ConsigneeAddress=$cart->address;}

        }

        // Aramex start  ///

        $soapClient = new SoapClient("http://ws.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?wsdl");
        echo '<pre>';
//        print_r($soapClient->__getFunctions());

        $params = array(
            'Shipments' => array(
                'Shipment' => array(
                    'Shipper'	=> array(
                        'Reference1' 	=> $request->SReference1,  //client details  optional
                        'Reference2' 	=> '',                     //client details  optional
                        'AccountNumber' => '',              //client AUTH  AccountNumber MUST BE SAME WITH ClientInfo AccountNumber
                        'PartyAddress'	=> array(
                            'Line1'					=> 'your Valid address',
                            'Line2' 				=> '',
                            'Line3' 				=> '',
                            'City'					=> 'your Valid City ',
                            'StateOrProvinceCode'	=> '',
                            'PostCode'				=> 'your Valid PostCode',
                            'CountryCode'			=> 'SA',
                            'Latitude'			=> '0',
                            'Longitude'			=> '0',
                        ),
                        'Contact'		=> array(
                            'Department'			=> '',
                            'PersonName'			=> 'your Valid Name ',
                            'Title'					=> '',
                            'CompanyName'			=> 'your Valid Company Name',
                            'PhoneNumber1'			=> 'your Valid NUMBER', // MUST BE VALID NUMBER
                            'PhoneNumber1Ext'		=> '125',
                            'PhoneNumber2'			=> '',
                            'PhoneNumber2Ext'		=> '',
                            'FaxNumber'				=> '',
                            'CellPhone'				=> '0552963722', //MUST BE VALID NUMBER
                            'EmailAddress'			=> 'your Valid Email',
                            'Type'					=> ''
                        ),
                    ),

                    'Consignee'	=> array(
                        'Reference1'	=> '',  // customer details  optional
                        'Reference2'	=> '', // customer details  optional
                        'AccountNumber' => '',
                        'PartyAddress'	=> array(
                            'Line1'					=> $ConsigneeAddress,
                            'Line2'					=> '',
                            'Line3'					=> '',
                            'City'					=> $cart->city->en_name, //MUST BE VALID CITY NAME
                            'StateOrProvinceCode'	=> '',
                            'PostCode'				=> '',
                            'CountryCode'			=> $cart->city->country_id?$cart->city->country->code:"SA", //MUST BE VALID COUNTRY CODE
                            'Latitude'			=> '0',
                            'Longitude'			=> '0',
                        ),

                        'Contact'		=> array(
                            'Department'			=> '',
                            'PersonName'			=> $ConsigneeName,
                            'Title'					=> '',
                            'CompanyName'			=> $ConsigneeName,
                            'PhoneNumber1'			=> $ConsigneePhone, //MUST BE VALID NUMBER
                            'PhoneNumber1Ext'		=> '',
                            'PhoneNumber2'			=> '',
                            'PhoneNumber2Ext'		=> '',
                            'FaxNumber'				=> '',
                            'CellPhone'				=> $ConsigneePhone, //MUST BE VALID NUMBER
                            'EmailAddress'			=> $cart->email,
                            'Type'					=> ''
                        ),
                    ),

                    'ThirdParty' => array(
                        'Reference1' 	=> '',
                        'Reference2' 	=> '',
                        'AccountNumber' => '',
                        'PartyAddress'	=> array(
                            'Line1'					=> '',
                            'Line2'					=> '',
                            'Line3'					=> '',
                            'City'					=> '',
                            'StateOrProvinceCode'	=> '',
                            'PostCode'				=> '',
                            'CountryCode'			=> '',
                            'Latitude'			=> '0',
                            'Longitude'			=> '0',
                        ),
                        'Contact'		=> array(
                            'Department'			=> '',
                            'PersonName'			=> '',
                            'Title'					=> '',
                            'CompanyName'			=> '',
                            'PhoneNumber1'			=> '',
                            'PhoneNumber1Ext'		=> '',
                            'PhoneNumber2'			=> '',
                            'PhoneNumber2Ext'		=> '',
                            'FaxNumber'				=> '',
                            'CellPhone'				=> '',
                            'EmailAddress'			=> '',
                            'Type'					=> ''
                        ),
                    ),

                    'Reference1' 				=> '',
                    'Reference2' 				=> '',
                    'Reference3' 				=> '',
                    'ForeignHAWB'				=> '',
                    'TransportType'				=> 0,
                    'ShippingDateTime' 			=> time(),
                    'DueDate'					=> time(),
                    'PickupLocation'			=> 'Reception',
                    'PickupGUID'				=> '',
                    'Comments'					=> $request->$request,
                    'AccountingInstrcutions' 	=> ' Give us a good price',
                    'OperationsInstructions'	=> $request->OperationsInstructions.' and  Please take care ... fragile',

                    'Details' => array(
                        'Dimensions' => array(
                            'Length'				=> 0,
                            'Width'					=> 0,
                            'Height'				=> 0,
                            'Unit'					=> 'cm',

                        ),

                        'ActualWeight' => array(
                            'Value'					=> $request->ActualWeight,
                            'Unit'					=> 'Kg'
                        ),

                        'ProductGroup' 			=> 'DOM',
                        'ProductType'			=> 'CDS',
                        'PaymentType'			=> 'P',
                        'PaymentOptions' 		=> 'Cash',
                        'Services'				=> 'CODS', // MUST BE ENABLE  CODS SERVICES
                        'NumberOfPieces'		=> $request->NumberOfPieces,
                        'DescriptionOfGoods' 	=> $request->DescriptionOfGoods,
                        'GoodsOriginCountry' 	=> 'SA',

                        'CashOnDeliveryAmount' 	=> array(
                            'Value'					=> $cart->total_cart + $cart->charge_fees,
                            'CurrencyCode'			=> 'SAR'
                        ),

                        'InsuranceAmount'		=> array(
                            'Value'					=> 0,
                            'CurrencyCode'			=> 'SAR'
                        ),

                        'CollectAmount'			=> array(
                            'Value'					=> 0,
                            'CurrencyCode'			=> 'SAR'
                        ),

                        'CashAdditionalAmount'	=> array(
                            'Value'					=> 0,
                            'CurrencyCode'			=> 'SAR'
                        ),

                        'CashAdditionalAmountDescription' => '',

                        'CustomsValueAmount' => array(
                            'Value'					=> 0,
                            'CurrencyCode'			=> 'SAR'
                        ),

                        'Items' 				=> array(

                        )
                    ),
                ),
            ),


            'ClientInfo'  			=> array(
                'AccountCountryCode'	=> 'SA',
                'AccountEntity'		 	=> ' ',//your Valid  live AccountEntity
                'AccountNumber'		 	=> ' ', //your Valid  live AccountNumber
                'AccountPin'		 	=> ' ', //your Valid  live AccountPin
                'UserName'			 	=> ' ', //your Valid  live UserName
                'Password'			 	=> ' ', //your Valid  live Password
                'Version'			 	=> '1.0'
            ),

            'Transaction' 			=> array(
                'Reference1'			=> '',
                'Reference2'			=> '',
                'Reference3'			=> '',
                'Reference4'			=> '',
                'Reference5'			=> '',
            ),
            'LabelInfo'				=> array(
                'labelID' 				=> 9729,
                'ReportID' 				=> 9729, // ['9201',''9729']
                'ReportType'			=> 'URL',
            ),
        );

        try {
            $auth_call = $soapClient->CreateShipments($params);
            if($auth_call->HasErrors){
                // some error happened popup('err');
                dd($auth_call);
                return back();
            }else{
                $cart->aramex=$auth_call->Shipments->ProcessedShipment->ShipmentLabel->LabelURL;
                $cart->save();

                return redirect($auth_call->Shipments->ProcessedShipment->ShipmentLabel->LabelURL);
            }

        } catch (SoapFault $fault) {
            dd($fault);
            
            die('Error : ' . $fault->faultstring);
        }

    }

}