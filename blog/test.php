<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

class bill_permissions  extends CI_Model {
    public function __construct() {
        parent::__construct();
    }


    function check_permission($bill,$stage) {

        if ($stage ==  BILL::ACCEPT_DELIVERY_STAGE){ //dispatch
            //is not posted
            if ($bill['stage'] == BILL::ACCEPT_DELIVERY_STAGE){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE(BILL::ACCEPT_DELIVERY_STAGE));

            }elseif(($bill['stage'] == BILL::PENDING_STAGE || $bill['stage'] == BILL::DISPATCHED_STAGE || $bill['stage'] == BILL::ONHOLD_STAGE) &&
                ($bill['state'] == BILL::REQUESTED_STATE || $bill['state'] == BILL::CONFIRMED_STATE )){
                return true;
            }else{
                throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));
            }
        }elseif ($stage ==  BILL::DISPATCHED_STAGE){ //accept delivery
            //is not posted
            if ($bill['stage'] == BILL::DISPATCHED_STAGE && ($bill['state'] == BILL::REQUESTED_STATE || $bill['state'] == BILL::CONFIRMED_STATE)){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE(BILL::DISPATCHED_STAGE));
            }elseif(($bill['stage'] == BILL::ACCEPT_DELIVERY_STAGE) &&
                ($bill['state'] == BILL::REQUESTED_STATE || $bill['state'] == BILL::CONFIRMED_STATE )){
                return true;
            }else{
                throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));
            }

        }elseif ($stage == BILL::CONFIRM_STAGE){ //confirm
            //is not posted

            if (( $bill['stage'] ==  BILL::PENDING_STAGE || $bill['stage'] ==  BILL::DISPATCHED_STAGE || $bill['stage'] ==  BILL::ONHOLD_STAGE) && ($bill['state'] == BILL::CONFIRMED_STATE) ){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE(BILL::CONFIRM_STAGE));                    }elseif( ( $bill['stage'] ==  BILL::PENDING_STAGE || $bill['stage'] ==  BILL::DISPATCHED_STAGE || $bill['stage'] ==  BILL::ONHOLD_STAGE) && ($bill['state'] == BILL::REQUESTED_STATE)){
                return true;
            }else{
                throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));

            }


        }elseif ($stage ==  BILL::OUT_FOR_DELIVERY_STAGE){ //out for delivery
            //is not posted

            if (($bill['stage'] ==  BILL::PENDING_STAGE || $bill['stage'] ==  BILL::DISPATCHED_STAGE ) && ($bill['state'] == BILL::READY_STATE)){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE(BILL::OUT_FOR_DELIVERY_STAGE));             }else{
                if (($bill['stage'] ==  BILL::PENDING_STAGE || $bill['stage'] ==  BILL::DISPATCHED_STAGE) && ($bill['state'] ==  BILL::CONFIRMED_STATE)){
                    return true;
                }else{
                    throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));
                }
            }

        }elseif($stage ==  BILL::PAID_STAGE || $stage ==  BILL::FREE_ORDER_STAGE ||$stage ==  BILL::COMPLEMENTARY_STAGE ){//delivery + free + compl
            //is not posted

            if($bill['stage'] ==  BILL::PAID_STAGE || $bill['stage'] ==  BILL::FREE_ORDER_STAGE ||$bill['stage'] ==  BILL::COMPLEMENTARY_STAGE){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE($stage));

            }else{
                if(($bill['stage'] ==  BILL::PENDING_STAGE || $bill['stage'] ==  BILL::DISPATCHED_STAGE) && ($bill['state'] ==  BILL::READY_STATE)){
                    return true;
                }else{
                    throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));
                }

            }
        }elseif($stage == BILL::REFUSE_STAGE){//refused
            //is not posted
            if($bill['state'] == BILL::REFUSED_STATE){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE(BILL::REFUSE_STAGE));
            }else{
                if(($bill['stage'] == BILL::PENDING_STAGE || $bill['stage'] == BILL::ACCEPT_DELIVERY_STAGE || $bill['stage'] ==  BILL::DISPATCHED_STAGE || $bill['stage'] ==  BILL::ONHOLD_STAGE ) && ($bill['state'] == BILL::REQUESTED_STATE)){
                    return true;
                }else{
                    throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));
                }
            }
        }elseif($stage == BILL::NOT_DELIVERED_STAGE){ // nor deliverd
            //is not posted
            if($bill['stage'] == BILL::NOT_DELIVERED_STAGE){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE(BILL::NOT_DELIVERED_STAGE));
            }else{
                if(($bill['stage'] == BILL::PENDING_STAGE || $bill['stage'] ==  BILL::DISPATCHED_STAGE || $bill['stage'] ==  BILL::ONHOLD_STAGE ) && ($bill['state'] == BILL::READY_STATE || $bill['state'] == BILL::CONFIRMED_STATE)){
                    return true;
                }else{
                    throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));
                }
            }
        }elseif($stage == BILL::FAKE_ORDER_STAGE || $stage == BILL::REFUNDED_STAGE || $stage == BILL::WASTED_ORDER_STAGE || $stage == BILL::WASTED_ORDER_CLIENT_FEEDBACK_STAGE ){
            //is posted
            if($bill['stage'] == BILL::FAKE_ORDER_STAGE || $bill['stage'] == BILL::REFUNDED_STAGE || $bill['stage'] == BILL::WASTED_ORDER_STAGE || $bill['stage'] == BILL::WASTED_ORDER_CLIENT_FEEDBACK_STAGE ){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE($stage));
            }else{
                if($bill['stage'] == BILL::PAID_STAGE & $bill['state'] == BILL:: READY_STATE){
                    return true;
                }else{
                    throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));
                }
            }
        }elseif($stage == BILL::REISSUED_STAGE){ // reissued
            //is not posted
            if($bill['stage'] == BILL::REISSUED_STAGE){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE(BILL::REISSUED_STAGE));
            }else{
                if(($bill['stage'] == BILL::PENDING_STAGE || $bill['stage'] ==  BILL::DISPATCHED_STAGE || $bill['stage'] ==  BILL::ONHOLD_STAGE ) && ($bill['state'] == BILL::READY_STATE || $bill['state'] == BILL::CONFIRMED_STATE)){
                    return true;
                }else{
                    throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));
                }
            }

        }elseif($stage == BILL::UNDER_UPDATE_STAGE){ // edit order
            //is not posted

            if($bill['stage'] == BILL::UNDER_UPDATE_STAGE){
                throw new ActionBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STAGE(BILL::UNDER_UPDATE_STAGE));
            }else{

                if(($bill['stage'] == BILL::PENDING_STAGE || $bill['stage'] == BILL::DISPATCHED_STAGE || $bill['stage'] == BILL::ONHOLD_STAGE) && ($bill['state'] == BILL::REQUESTED_STATE)){
                    return true;
                }else{
                    throw new InactiveBillException($bill["restaurant_queue"], BILL::toStringArabic_BILL_STATE($bill["state"]));
                }

            }
        }

    }

}



?>



