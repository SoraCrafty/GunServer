<?php

namespace gun;

use pocketmine\item\Item;
use pocketmine\block\Block;

class Blocks {
    
    public static function isSolid($id){
        switch($id){
            case 0:
            case 6:
            case 8:
            case 9:
            case 10:
            case 11:
            case 18:
            case 27:
            case 28:
            case 30:
            case 31:
            case 32:
            case 34:
            case 37:
            case 38:
            case 39:
            case 40:
            case 50:
            case 51:
            case 55:
            case 59:
            case 63:
            case 65:
            case 66:
            case 68:
            case 69:
            case 70:
            case 72:
            case 75:
            case 76:
            case 77:
            case 78:
            case 83:
            case 93:
            case 94:
            case 95:
            case 96:
            case 104:
            case 105:
            case 106:
            case 107:
            case 111:
            case 115:
            case 126:
            case 127:
            case 131:
            case 132:
            case 141:
            case 142:
            case 143:
            case 147:
            case 148:
            case 149:
            case 150:
            case 151:
            case 161:
            case 167:
            case 171:
            case 175:
            case 178:
            case 183:
            case 184:
            case 185:
            case 186:
            case 187:
            case 199:
            case 244:
                return false;
            break;

        }
        return true;
    }

    public static function isLiquid($id){
        switch($id){
            case 8:
            case 9:
            case 10:
            case 11:
                return true;
            break;
        }
        return false;
    }

    public static function isHalf($id, $data){
        switch($id){
            case 44:
            case 158:
                switch($data){
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                        return true;
                    break;
                }
            break;
            
        }
        return false;
    }
}
