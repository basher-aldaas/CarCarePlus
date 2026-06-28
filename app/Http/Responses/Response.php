<?php

namespace App\Http\Responses;

use App\Constants\HttpStatusConstants;
use Illuminate\Http\JsonResponse;

class Response
{
    public static function Success($data,$message,$code=HttpStatusConstants::HTTP_200_OK) : JsonResponse
    {
        return response()->json([
            'status'=>1,
            'data'=>$data,
            'message'=>$message
        ],$code);
    }

    public static function Error($data,$message,$code=HttpStatusConstants::HTTP_500_INTERNAL_SERVER_ERROR) : JsonResponse
    {
        return response()->json([
            'status'=>0,
            'data'=>$data,
            'message'=>$message
        ],$code);
    }

    public static function Validation($data,$message,$code=HttpStatusConstants::HTTP_422_UNPROCESSABLE_ENTITY) : JsonResponse
    {
        return response()->json([
            'status'=>0,
            'data'=>$data,
            'message'=>$message
        ],$code);
    }

}
