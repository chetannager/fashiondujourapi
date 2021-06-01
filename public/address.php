<?php

     /*********************************************************************************************************/
    /*********************************************************************************************************/
    /**************************** ADDING ADDRESS OF THE CUSTOMER *********************************************/
    /*********************************************************************************************************/
    /*********************************************************************************************************/

    $app->post('/v1/address-add',function($req,$res,$args){
        $customer_name;
        $customer_address;
        $customer_mobile_number;
        $customer_id;

        //Object of Database.
        $db = new db();

        //getConnection function.
        $con = $db->connect();

        //getting data from the body.
        $body = $req->getBody();

        //decoding from JSON format.
        $data = json_decode($body);

        if(isset($data->customer_name) && isset($data->customer_address) && isset($data->customer_mobile_number) && isset($data->customer_id)){
            $customer_id = $data->customer_id;
            $customer_name = $data->customer_name;
            $customer_address = $data->customer_address;
            $customer_mobile_number = $data->customer_mobile_number;
     
            $query = "INSERT INTO fd_customer_addresses SET customer_id=:customer_id,customer_name=:customer_name,customer_address=:customer_address, customer_mobile_number=:customer_mobile_number";
            $stmt = $con->prepare($query);
            $stmt->bindParam(":customer_id",$customer_id);
            $stmt->bindParam(":customer_name",$customer_name);
            $stmt->bindParam(":customer_address",$customer_address);
            $stmt->bindParam(":customer_mobile_number",$customer_mobile_number);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num == 1){
                $responseData = array(
                    'RESPONSE'=> 'Added Successfully',
                    'successful'=>true,
                    'status_code'=>'CODE_11',
                    'REQUEST_ID'=>md5(uniqid(rand(),true)),
                    'timestamp'=>time()
                );
                return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
            }else{
                $responseData = array(
                    'RESPONSE'=>[
                        'successful'=>false,
                        'message'=>'Failed to add'
                    ],
                    'status_code'=>'CODE_12',
                    'REQUEST_ID'=>md5(uniqid(rand(),true)),
                    'timestamp'=>time()
                );
                return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
            }
        }else{
            $responseData = array(
                "RESPONSE"=>[
                    "error"=>true,
                    "errorCode"=>"SIGN_01",
                    "errorMessage"=>"Fields are Empty",
                ],
                "STATUS_CODE"=>"400",
                "request-id"=>md5(uniqid(rand(),true)),
                "timestamp"=>time()
            );

            return $res->withStatus(400)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
        }
    });

    /********************************************************************************************************/
    /********************************************************************************************************/
    /************************************ REMOVE ARRESS FROM DATABASE ****************************************/
    /********************************************************************************************************/
    /********************************************************************************************************/

    $app->post('/v1/address-remove',function ($req, $res, $args){
        $body1 = $req->getBody();
        $data1 = json_decode($body1);
        
        if(isset($data1->customer_id) && isset($data1->customer_address_id)){
            $customer_address_id  = $data1->customer_address_id;
            $customer_id = $data1->customer_id;

            $db = new db();
            $con = $db->connect();
     
            $query = "DELETE FROM fd_customer_addresses WHERE customer_id=:customer_id AND customer_address_id =:customer_address_id";
            $stmt = $con->prepare($query);
            $stmt->bindParam(":customer_id",$customer_id);
            $stmt->bindParam(":customer_address_id",$customer_address_id );
            $stmt->execute();
            $num = $stmt->rowCount();
            
            if($num == 1){
                $responseData = array(
                    'RESPONSE'=> 'Deleted',
                    'status_code'=>'CODE_10',
                    'REQUEST_ID'=>md5(uniqid(rand(),true)),
                    'timestamp'=>time()
                );
                return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
            }else{
                $responseData = array(
                    'RESPONSE'=>[
                        'successful'=>true,
                        'message'=>'No data found.'
                    ],
                    'status_code'=>'CODE_11',
                    'REQUEST_ID'=>md5(uniqid(rand(),true)),
                    'timestamp'=>time()
                );
                return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
            }
         }
        else{
            $responseData = array(
                'RESPONSE'=>[
                    'successful'=>false,
                    'message'=>'empty req body'
                ],
                'status_code'=>'CODE_11',
                'REQUEST_ID'=>md5(uniqid(rand(),true)),
                'timestamp'=>time()
            );
            return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
        }
    });

    /********************************************************************************************************/
    /********************************************************************************************************/
    /************************************ CUSTOMER ADDRESS COLLECTION ***************************************/
    /********************************************************************************************************/
    /********************************************************************************************************/

    $app->post('/v1/addresses',function ($req, $res, $args){
        // just an id variable to store id
        $id;

        // getting a data from the request data from from end;
        $body = $req->getBody();

        // decoding the data into normal array.
        $data = json_decode($body);

        // checking whther the data is empty or not.
        if(isset($data->customer_id)){

            //storing id into the id variable.
            $id = $data->customer_id;

            //calling with database class.
            $db = new db();

            // establishing the connection between the mysql database and api.
            $con = $db->connect();

            // performing the query.
            $query = "select * from fd_customer_addresses where customer_id=:customer_id";
            
            // preparing the query to be execute.
            $stmt = $con->prepare($query);

            // binding the id with the query with bindParam method of PDO.
            $stmt->bindParam(":customer_id",$id);

            // executing the query. 
            $stmt->execute();

            // fetching all the rows from the $stmt variable.
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // checking whether data is returned from the database.
            if(count($rows)>0){

                //creating an array to check whether 
                //  $addresses = array();

                //  for($i=0;count($rows);$i++){
                //      $address = array(
                //          "customer_address_id"=>$rows[$i]["customer_address_id"],
                //          "customer_id"=>$rows[$i]["customer_id"],
                //          "customer_name"=>$rows[$i]["customer_name"],
                //          "customer_address"=>$rows[$i]["customer_address"],
                //          "customer_mobile_number"=>$rows[$i]["customer_mobile_number"],
                //          "is_primary"=>$rows[$i]["is_primary"],
                //      );
                //      array_push($addresses,$address);
                //  }

                 $responseData = array(
                     "RESPONSE"=>[
                         'successful'=>true,
                         'message'=>'successfully fetched',
                         'address'=>$rows,
                        ],
                        "status_code"=>"CODE_20",
                        "REQUEST_ID"=>md5(uniqid(rand(),true)),
                        "timestamp"=>date("Y-m-d H:i:s"),
                 );

                 return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));

            }else{
                $responseData = array(
                    'RESPONSE'=>[
                        'successful'=>true,
                        'message'=>'No data found',
                        'address'=>[],
                    ],
                    'status_code'=>'CODE_11',
                    'REQUEST_ID'=>md5(uniqid(rand(),true)),
                    'timestamp'=>time()
                );
                return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
            }
        }else{
            $responseData = array(
                'RESPONSE'=>[
                    'successful'=>false,
                    'message'=>'empty request body'
                ],
                'status_code'=>'CODE_11',
                'REQUEST_ID'=>md5(uniqid(rand(),true)),
                'timestamp'=>time()
            );
            return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
        }

    });
?>