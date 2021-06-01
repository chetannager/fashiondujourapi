<?php

    function returnOfferImageData($con,$offerId){

        $query = "select * from fd_offers_images where offer_id=:offer_id";
        $stmt = $con->prepare($query);
        $stmt->bindParam(':offer_id',$offerId);
        $stmt->execute();

        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $img = array();
        if(count($row)>0){
            for($i=0;$i<count($row);$i++){
                $data1 = [
                    "offer_image_id"=>$row[$i]["offer_image_id"],
                    "offer_image_url"=>$row[$i]['offer_image_url'],
                    "offer_discount_percentage"=>$row[$i]['offer_discount_percentage'],
                ];
                array_push($img,$data1);
            }
            return $img;
        }else{
            return null;
        }
    }
    
    $app->get('/v1/offers',function($req,$res,$args){

        $db = new db();
        $con = $db->connect();
        $query = "select * from fd_offers";
        $stmt = $con->prepare($query);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(count($rows)>0){
            $response = array();
            for($i=0;$i<count($rows);$i++){
                $images = returnOfferImageData($con,$rows[$i]["offer_id"]);
                $data = [
                    "offer_id"=>$rows[$i]['offer_id'],
                    "offer_name"=>$rows[$i]['offer_name'],
                    "offer_image"=>$images,
                    "created_at"=>$rows[$i]['created_at'],
                ];
                array_push($response,$data);
            }
            $responseData = [
                "message"=>"Successful",
                "RESPONSE"=>$response,
                "STATUS_CODE"=>"200",
                "request-id"=>uniqid(rand(),true),
                "timestamp"=>time(),
            ];
            return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
        }else{
            $responseData = [
                "RESPONSE"=>[
                    "error"=>true,
                    "errorCode"=>"OFFERS_01",
                    "errorMessage"=>"No Data Available",
                ],
                "STATUS_CODE"=>"200",
                "request-id"=>uniqid(rand(),true),
                "timestamp"=>time(),
            ];

            return $res->withStatus(200)->withHeader('content-type','application/json;charset=utf-8')->write(json_encode($responseData));
        }
        return "hello world";
    });
?>