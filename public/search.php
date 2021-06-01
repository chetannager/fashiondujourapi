<?php

    //function to return list of images for each product.
    function returnImage($con,$id){

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

    $app->post('/v1/search',function($req,$res,$args){
        $body = $req->getBody();
        $data = json_decode($body);

        if(isset($data->key)){
            $key = $data->key;
            $db = new db();
            $con = $db->connect();
            $query = "select * from fd_products where product_name like '%".$key."%'";
            $stmt = $con->prepare($query);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($rows)>0){
                $products = array();
                for($i=0;$i<count($rows);$i++){
                    $product_image = returnImage($con,$rows[$i]["product_id"]);
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
                        "created_at"=>$rows[$i]["created_at"],
                    );
                    array_push($products,$product);
                }
                $responseData = array(
                    "RESPONSE"=>[
                        "successful"=>true,
                        "data"=>$products,
                    ],
                    "status_code"=>"CODE_01",
                    "REQUEST_ID"=>md5(uniqid(rand(),true)),
                    "timestamp"=>date("Y-m-d H:i:s"),
                );
                return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
            }else{
                $responseData = array(
                    "RESPONSE"=>[
                        "successful"=>true,
                        "data"=>[],
                    ],
                    "status_code"=>"CODE_02",
                    "REQUEST_ID"=>md5(uniqid(rand(),true)),
                    "timestamp"=>date("Y-m-d H:i:s"),
                );
                return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
            }
        }else{
            $responseData = array(
                "RESPONSE"=>[
                    "successful"=>false,
                    "message"=>"Search parameter empty",
                ],
                "status_code"=>"CODE_01",
                "REQUEST_ID"=>md5(uniqid(rand(),true)),
                "timestamp"=>date("Y-m-d H:i:s"),
            );
            return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
        }

    });
?>