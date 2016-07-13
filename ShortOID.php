<?php

require_once 'vendor/autoload.php';

define("MACPID", "297759dc04");

//objectid = 55da83a5 297759 dc04 000029

class ShortOID
{
    public static function encode($objectId){
        $hashids = new Hashids\Hashids('imprest', 10);
        if(!$objectId) return false;
        $shortId = $hashids->encode(hexdec(substr($objectId, 0, 8)), hexdec(substr($objectId, 18, 23)));
        return $shortId;
    }

    public static function decode($shortId){
        $hashids = new Hashids\Hashids('imprest', 10);
        if(!$shortId) return false;
        $numbers = $hashids->decode($shortId);
        $objectId = vsprintf('%08x' . MACPID . '%06x', $numbers);
        return $objectId;
    }

    public static function fixMongoId($id){
        $objectId = substr($id, 0, 8) . MACPID . substr($id, 18, 23);
        return new MongoId($objectId);
    }

    /* 将 _id 替换为 objectId */
    public static function replace(&$data){
        if( ! array_key_exists('_id', $data)) return false;

        $shortId = ShortOID::encode($data['_id']);
        unset($data['_id']);
        $data[OBJECTID] = $shortId;
        return true;
    }

    /* 将 objectId 还原为 _id */
    public static function revert(&$data, $addId){
        if(array_key_exists(OBJECTID, $data)){
            $objectId = ShortOID::decode($data[OBJECTID]);
            unset($data[OBJECTID]);
            $data['_id'] = new MongoId($objectId);
        }else if($addId){
            $data['_id'] = ShortOID::fixMongoId(new MongoId());
        }else{
            return false;
        }

        return true;
    }
}
