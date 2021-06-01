<?php

    //function to return list of images for each product.
    function productImage($con,$id){

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


    $app->post('/v1/wishlist',function ($req,$res,$args){
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
            $query = "select * from fd_wishlist where customer_id=:customer_id";
            
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
                 $products = array();

                 // starting loop since one customer having 'n' number of products.
                 for($i=0;$i<count($rows);$i++){

                     //storing each product id.
                     $product_id = $rows[$i]["product_id"];

                     //start a query to get data of each product from the product table.
                     $query2 = "select * from fd_products where product_id=:product_id";
                     $stmt = $con->prepare($query2);
                     $stmt->bindParam(":product_id",$product_id);
                     $stmt->execute();
                     $rows2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

                     // getting array of each product images.
                     $product_image = productImage($con,$product_id);

                     // creating map of product.
                     $product = array(
                         "wishlist_id"=>$rows[$i]["wishlist_id"],
                         "product_id"=>$rows2[0]["product_id"],
                         "product_name"=>$rows2[0]["product_name"],
                         "product_description"=>$rows2[0]["product_description"],
                         "product_images"=>$product_image,
                         "product_discount_price"=>$rows2[0]["product_discount_price"],
                         "product_original_price"=>$rows2[0]["product_original_price"],
                         "product_discount_percentage"=>$rows2[0]["product_discount_percentage"],
                         "product_current_rating"=>$rows2[0]["product_current_rating"],
                         "product_max_rating"=>$rows2[0]["product_max_rating"],
                         "category_id"=>$rows2[0]["category_id"],
                         "sub_category_id"=>$rows2[0]["sub_category_id"],
                         "created_at"=>$rows[0]["created_at"],
                     );

                     // storing each map in product array.
                     array_push($products,$product);
                 }
                 $responseData = array(
                     "RESPONSE"=>[
                         'successful'=>true,
                         'products'=>$products,
                     ],
                     'status_code'=>'CODE_01',
                     'REQUEST_ID'=>md5(uniqid(rand(),true)),
                     'timestamp'=>time()
                 );
                 return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
            }else{
                $responseData = array(
                    "RESPONSE"=>[
                        'successful'=>true,
                        'products'=>[],
                    ],
                    'status_code'=>'CODE_01',
                    'REQUEST_ID'=>md5(uniqid(rand(),true)),
                    'timestamp'=>time()
                );
                return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
            }
        }else{
            $responseData = array(
                'RESPONSE'=>[
                    'message'=>'Customer id not set'
                ],
                'RESPONSE_ID'=>md5(uniqid(rand(),true)),
                'timestamp'=>time(),
            );
            return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
        }
    });


    /********************************************************************************************************/
    /********************************************************************************************************/
    /************************************ REMOVE ITEM FROM WHISHLIST ****************************************/
    /********************************************************************************************************/
    /********************************************************************************************************/

    $app->post('/v1/wishlist-remove',function ($req, $res, $args){
        $body1 = $req->getBody();
        $data1 = json_decode($body1);
        
        if(isset($data1->customer_id) && isset($data1->product_id)){
            $product_id = $data1->product_id;
            $customer_id = $data1->customer_id;

            $db = new db();
            $con = $db->connect();
     
            $query = "DELETE FROM fd_wishlist WHERE customer_id=:customer_id AND product_id=:product_id";
            $stmt = $con->prepare($query);
            $stmt->bindParam(":customer_id",$customer_id);
            $stmt->bindParam(":product_id",$product_id);
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
                        'successful'=>false,
                        'message'=>'not executed'
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
    /************************************ ADD ITEM TO WHISHLIST ****************************************/
    /********************************************************************************************************/
    /********************************************************************************************************/

    $app->post('/v1/wishlist-add',function ($req, $res, $args){
        $body1 = $req->getBody();
        $data1 = json_decode($body1);
        
        if(isset($data1->customer_id) && isset($data1->product_id)){
            $product_id = $data1->product_id;
            $customer_id = $data1->customer_id;

            $db = new db();
            $con = $db->connect();
     
            $query = "INSERT INTO fd_wishlist SET customer_id=:customer_id, product_id=:product_id";
            $stmt = $con->prepare($query);
            $stmt->bindParam(":customer_id",$customer_id);
            $stmt->bindParam(":product_id",$product_id);
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
         }
        else{
            $responseData = array(
                'RESPONSE'=>[
                    'successful'=>false,
                    'message'=>'empty req body'
                ],
                'status_code'=>'CODE_13',
                'REQUEST_ID'=>md5(uniqid(rand(),true)),
                'timestamp'=>time()
            );
            return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
        }
    });
?>