<?php
// Database Connection
require"db.php";


$app = new \Slim\App($config);
$c = new \Slim\Container();
$app = new \Slim\App($c);

require"address.php";
require"offers.php";
require"wishlist.php";

sleep(1);

/* Default  TimeZone Configuration */
date_default_timezone_set("Asia/Kolkata");

/* Not Found Configuration */
unset($app->getContainer()['notFoundHandler']);
$app->getContainer()['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        $response = new \Slim\Http\Response(400);
        return $response->write("<h1>Access Denied</h1><p>You don't have permission to access ".'"http://'.$_SERVER['HTTP_HOST'].$request->getUri()->getPath().'"'." on this server.</p><p>Reference #".md5(uniqid(rand(), true)),);
    };
};

/* Bad Request Configuration */
$c['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        return $response->withStatus(400)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'text/html')
			->write("<h1>Access Denied</h1><p>You don't have permission to access ".'"http://'.$_SERVER['HTTP_HOST'].$request->getUri()->getPath().'"'." on this server.</p><p>Reference #".md5(uniqid(rand(), true)),);
    };
};

/* Clean Input Data */
function clean_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function isValidEmailPatter($emailAddress){
        return filter_var($emailAddress,FILTER_VALIDATE_EMAIL);
    }
	
	function validateMOBILENUMBER($mobile)
{
    return preg_match('/^[0-9]{10}+$/', $mobile);
}

function validateEMAIL($str)
{
	return preg_match('/^([A-Za-z0-9\+_\-]+)(\.[A-Za-z0-9\+_\-]+)*@([A-Za-z0-9\-]+\.)+[A-Za-z]{2,6}$/',$str); 
}

$app->post('/v1/login', function ($request, $response, $args) {
	$body = $request->getBody();
	$data = json_decode($body);
	if(isset($data->email) and isset($data->password)){
		$email=clean_input($data->email);
		$password=clean_input($data->password);
		try{
			$db= new db();
			$db=$db->connect();
			$stmt=$db->prepare("SELECT * FROM fd_customers WHERE customer_email_address=:email AND customer_password=:password");
			$stmt->execute([':email' => $email,':password' => $password]);
			$customerDetails=$stmt->fetch(PDO::FETCH_ASSOC);
			$num=$stmt->rowCount();
			if($num==1){
				$responseData=array(
					"isLoggedIn"=>true,
					"success"=>true,
					"customer_data"=>array(
						"customer_id" => (int)$customerDetails["customer_id"],
						"customer_full_name" => $customerDetails["customer_full_name"],
						"customer_email_address" => $customerDetails["customer_email_address"],
						"customer_mobile_number" => (int)$customerDetails["customer_mobile_number"],
					),
					"STATUS_CODE"=>200,
					"REQUEST-ID"=>md5(uniqid(rand(), true)),
					"timestamp"=>time()
				);
				return $response->withStatus(200)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));
			}else{
				$responseData=array(
					"success"=>true,
					"isLoggedIn"=>false,
					"message"=>"Invalid email and password!",
					"STATUS_CODE"=>200,
					"REQUEST-ID"=>md5(uniqid(rand(), true)),
					"timestamp"=>time()
				);
				return $response->withStatus(200)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));
			}
		}catch(PDOException $e){
			echo $e;
		}
	}else{
		$responseData=array(
			"RESPONSE"=>[
				"error"=>true,
				"errorCode"=> "error_101",
				"error_message"=>"Request token missing"
			],
			"STATUS_CODE"=>400,
			"REQUEST-ID"=>md5(uniqid(rand(), true)),
			"timestamp"=>time()
		);
		return $response->withStatus(400)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));
	}
});

$app->post('/v1/register',function($request,$response,$args){
        $name;
        $mobile;
        $email;
        $password;

        //Object of Database.
        $db = new db();

        //getConnection function.
        $con = $db->connect();

        //getting data from the body.
        $body = $request->getBody();

        //decoding from JSON format.
        $data = json_decode($body);

        //Checking whether the data is empty or not.
        if(isset($data->name) && isset($data->mobile) && isset($data->email) && isset($data->password)){

            //Checking whether email have valid pattern.
            if(validateEMAIL($data->email)){

                //Checking length of the number.
                if(validateMOBILENUMBER($data->mobile)){

                    //Passing data to the local variable from the json body.
                    $name = $data->name;
                    $mobile= $data->mobile;
                    $email = $data->email;
                    $password = $data->password;

                    //Query for fetching all customer data from data for checking.
                    $query = "select * from fd_customers where customer_email_address=:customer_email";
                    $stmt = $con->prepare($query);
                    $stmt->bindParam(":customer_email",$email);
                    $stmt->execute();

                    //Store fetched Data in variable $rows.
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if(count($rows)>0){
						$responseData=array(
							"success"=>false,
							"isRegistered"=>false,
							"message"=>"User Already Exist with this email address",
							"STATUS_CODE"=>200,
							"REQUEST-ID"=>md5(uniqid(rand(), true)),
							"timestamp"=>time()
						);
						return $response->withStatus(200)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));

                    }else{//Checking for the mobile number is already exist

                            //Query to fetch mobile number existance.
                            $query = "select * from fd_customers where customer_mobile_number=:customer_mobile";
                            $stmt = $con->prepare($query);
                            $stmt->bindParam(":customer_mobile",$mobile);
                            $stmt->execute();

                            //Fteching all details.
                            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if(count($rows) > 0){//If number already exists error through.
							$responseData=array(
								"success"=>true,
								"isRegistered"=>false,
								"message"=>"User Already Exist with this mobile number.",
								"STATUS_CODE"=>200,
								"REQUEST-ID"=>md5(uniqid(rand(), true)),
								"timestamp"=>time()
							);
							return $response->withStatus(200)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));
                        }else{
                            //Query for fetching all customer data from data for checking.
                            $query = "insert into fd_customers(customer_full_name,customer_mobile_number,customer_email_address,customer_password) values (?,?,?,?)";
                            $stmt = $con->prepare($query);
                            $stmt->execute([$name,$mobile,$email,$password]);
                            $rows = $stmt->rowCount();
                            if($rows == 1){
								$responseData=array(
									"isRegistered"=>true,
									"success"=>true,
									"message"=>"Registered successfully!",
									"STATUS_CODE"=>200,
									"REQUEST-ID"=>md5(uniqid(rand(), true)),
									"timestamp"=>time()
								);
								return $response->withStatus(200)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));

                            }else{//Error message for Customer authentication failed.
                                $responseData=array(
									"success"=>false,
									"error"=>true,
									"errorCode"=>"SIGN_05",
									"message"=>"Customer registration failed",
									"STATUS_CODE"=>200,
									"REQUEST-ID"=>md5(uniqid(rand(), true)),
									"timestamp"=>time()
								);

								return $response->withStatus(200)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));
                            }
                        }
                    }
                    
                }else{//Error message for mobile authentication failed.
					$responseData=array(
									"success"=>false,
									"error"=>true,
									"errorCode"=>"SIGN_03",
									"message"=>"Please enter valid mobile number",
									"STATUS_CODE"=>200,
									"REQUEST-ID"=>md5(uniqid(rand(), true)),
									"timestamp"=>time()
								);

					return $response->withStatus(200)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));
                }

            }else{
				$responseData=array(
									"success"=>false,
									"error"=>true,
									"errorCode"=>"SIGN_02",
									"message"=>"Please enter valid email address",
									"STATUS_CODE"=>200,
									"REQUEST-ID"=>md5(uniqid(rand(), true)),
									"timestamp"=>time()
								);

					return $response->withStatus(200)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));
            }
        }else{
			$responseData=array(
									"success"=>false,
									"error"=>true,
									"errorCode"=>"SIGN_01",
									"message"=>"Fields are Empty",
									"STATUS_CODE"=>200,
									"REQUEST-ID"=>md5(uniqid(rand(), true)),
									"timestamp"=>time()
								);

					return $response->withStatus(200)->withHeader('Cache-Control', 'no-store')->withHeader('Pragma', 'no-cache')->withHeader('Content-Type', 'application/json;charset=utf-8')->withHeader('Access-Control-Allow-Origin', '*')->withHeader('Access-Control-Allow-Methods', 'POST')->write(json_encode($responseData));
        }
    });

//function to return list of images for each product.
    function returnProductImage($con,$id){

        //Query to fetch only mens products.
        $query = "select * from fd_products_images where product_id=:product_id";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':product_id',$id);
        $stmt->execute();

        //fetching all data in an $row.
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $image = array();

        //checking whether the data is fetched or not.
        if(count($row)>0){

            //loop to fetch and push each data into an array $image.
            for($i=0;$i<count($row);$i++){
                array_push($image,$row[$i]['product_image_url']);
            }

            //return list of images to the '/product' post api.
            return $image;

        }else{
            //If no image returning null.
            return null;
        }

    }
	
	//function to return list of images for each product.
    function returnProductSubCategoryName($id){
		$db= new db();
			$db=$db->connect();
			$stmt=$db->prepare("select * from fd_sub_categories where sub_category_id =:sub_category_id");
			$stmt->execute([':sub_category_id' => $id]);
			$details=$stmt->fetch(PDO::FETCH_ASSOC);
			$num=$stmt->rowCount();
			if($num==1){
				return $details["sub_category_name"];
			}else{
				return null;
			}
    }

    //All product display.
    $app->get('/v1/products/{categoryId}',function($req,$res,$args){
		if(isset($args["categoryId"])){
			//Connecting to database.
			$db = new db();

			$con = $db->connect();

			$query = "select * from fd_products where category_id = '".$args["categoryId"]."'";
			$stmt = $con->prepare($query);
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$num=$stmt->rowCount();
			if($num>0){
				$products = array();
				for($i=0;$i<count($rows);$i++){
					$product_image = returnProductImage($con,$rows[$i]["product_id"]);
					$product = array(
						"product_id"=>$rows[$i]["product_id"],
						"product_name"=>$rows[$i]["product_name"],
						"product_description"=>$rows[$i]["product_description"],
						"product_images"=>$product_image,
						"product_discount_price"=>$rows[$i]["product_discount_price"],
						"product_original_price"=>$rows[$i]["product_original_price"],
						"product_discount_percentage"=>$rows[$i]["product_discount_percentage"],
						"product_current_rating"=>$rows[$i]["product_current_rating"],
						"product_max_rating"=>$rows[$i]["product_max_rating"],
						"category_id"=>$rows[$i]["category_id"],
						"sub_category_id"=>$rows[$i]["sub_category_id"],
						"sub_category_name"=>returnProductSubCategoryName($rows[$i]["sub_category_id"]),
						"created_at"=>$rows[$i]["created_at"],
					);
					array_push($products,$product);
				}
					$responseData = [
						"products"=>$products,
						"success"=>true,
						"STATUS_CODE"=>"200",
						"request_id"=>md5(uniqid(rand(),true)),
						"timestamp"=>time(),
					];
				return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));

			}else{
				$responseData = [
					"products"=>$rows,
					"success"=>true,
					"STATUS_CODE"=>"400",
					"request_id"=>md5(uniqid(rand(),true)),
					"timestamp"=>time(),
				];
				return $res->withStatus(400)->write(json_encode($responseData));
			}
		}
    });
	
	//All product display.
    $app->get('/v1/product/details/{productId}',function($req,$res,$args){
		if(isset($args["productId"])){
			$db= new db();
			$db=$db->connect();
			$stmt=$db->prepare("select * from fd_products where product_id =:product_id");
			$stmt->execute([':product_id' => $args["productId"]]);
			$details=$stmt->fetch(PDO::FETCH_ASSOC);
			$num=$stmt->rowCount();
			if($num==1){
				return $res->withStatus(200)->write(json_encode($details));
			}else{
				return null;
			}
		}
	});
	
	
?>