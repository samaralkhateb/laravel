<?php

class accountting_module extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('DataSources/account_model');
        $this->load->model('DataSources/driver_model');
        $this->load->model('DataSources/restaurant_model');
        $this->load->model('accounts_helper');

    }

    public function calculate_account($bill,$stage,$head_id) {
        $time = TIME::get_time_and_date();

        $rate = $this->account_model->get_rate()['rate']; //from rate table $ DOLAR
        // $accounts_currency = $this->account_model->get_all_account($bill['driver_account_id'],$bill['res_account_id']);

        $accounts_currency['DeliveryRevenues']=$accounts_currency['FreeDelivery']=$accounts_currency['NotDeliveredOrdersBeeOrder']=
        $accounts_currency['GhostRide']=$accounts_currency['RefundedOrders']=$accounts_currency['FakeOrders']=
        $accounts_currency['WastedOrders']=$accounts_currency['ComplementaryOrders']=$accounts_currency['FreeOrders']=
        $accounts_currency['DriversCommissions']=$accounts_currency['BeeOrderSales']=$accounts_currency['BeeOrderSalesDiscount']=
        $accounts_currency['driver_account']=$accounts_currency['res_account'] = 1;

        $beeOrder_commission =  $this->driver_model->last_driver_commission($bill['driver_id'])['commission'];
        $beeOrder_commission = (100 - ($beeOrder_commission * 100)) /100;

        if ($bill['restaurant_type'] == new_restaurant_type::VIRTUAL) {
            $driver_commission = ( ($bill['virtual_delivery'] + $bill['delivery']) * ($beeOrder_commission)) * $rate ;
        } else {
            $delivery_res = $bill['delivery'];
            if ($bill['free_delivery'] == 1) {
                $delivery_res = $bill['free_delivery_fee']  ;
            }
            $driver_commission =  ($delivery_res * ($beeOrder_commission)) * $rate ;
        }


        if ($stage == BILL::PAID_STAGE) {
            if ($bill['restaurant_type'] == new_restaurant_type::VIRTUAL) {
                $this->account_model->add_details( array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => $bill['std_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,
                        'note' => 'Invoice'
                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,'debit' => 0,
                        'credit' => $bill['purchase_total'] * $rate ,
                        'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,
                        'note' => 'Invoice'
                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>  ($bill['std_total'] - $bill['purchase_total']) * $rate ,
                        'account_id' => ACCOUNTS::BeeOrderSales,
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['BeeOrderSales'],
                        'rate' =>  $rate ,
                        'note' => 'Invoice'
                    ),


                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $bill['delivery'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,
                        'note' => 'Delivery'),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['delivery'] * $rate ,
                        'account_id' => ACCOUNTS::DeliveryRevenues,
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['DeliveryRevenues'],
                        'rate' =>    $rate ,
                        'note' => 'Delivery'
                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $driver_commission,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::DriversCommissions,
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['DriversCommissions'],
                        'rate' =>  $rate ,
                        'note' => 'Driver Commission'
                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>$driver_commission,
                        'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,
                        'note' => 'Driver Commission'
                    ),
                    array('created_at' => $time ,
                        'date' => $time ,'debit' =>  $bill['ghost_fee'] * $rate ,
                        'credit' => 0,'account_id' => ACCOUNTS::GhostRide ,
                        'head_id' => $head_id,'currency' => $accounts_currency['GhostRide'],
                        'rate' =>  $rate ,
                        'note' => 'Ghost Ride'
                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,'debit' => 0,
                        'credit' =>   $bill['ghost_fee'] * $rate ,
                        'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,
                        'note' => 'Ghost Ride'
                    ),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $bill['free_delivery_fee'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::FreeDelivery,
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['FreeDelivery'],
                        'rate' =>  $rate ,
                        'note' => 'Free Delivery Paid By Bee Order - as a Down Payment '),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>$bill['free_delivery_fee'] * $rate ,
                        'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,
                        'note' => 'Free Delivery Paid By Bee Order - as a Down Payment ')




                ));

                if($bill['v_total']!= 0){
                    $this->account_model->add_details(array(
                            array('created_at' => $time ,
                                'date' => $time ,
                                'debit' => $bill['v_total'] * $rate ,
                                'credit' => 0,
                                'account_id' => ACCOUNTS::BeeOrderSalesDiscount,
                                'head_id' => $head_id,
                                'currency' => $accounts_currency['BeeOrderSalesDiscount'],
                                'rate' =>  $rate ,
                                'note' => 'Restaurant Discount'
                            ),
                            array('created_at' => $time ,
                                'date' => $time ,
                                'debit' => 0,
                                'credit' =>  $bill['v_total'] * $rate ,
                                'account_id' => $bill['driver_account_id'],
                                'head_id' => $head_id,
                                'currency' => $accounts_currency['driver_account'],
                                'rate' =>  $rate ,
                                'note' => 'Restaurant Discount'

                            ))
                    );
                }

                if($bill['free_delivery_fee'] != 0){
                    $this->account_model->add_details(array(
                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' =>  $bill['free_delivery_fee'] * $rate ,
                            'credit' => 0,
                            'account_id' => $bill['driver_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,
                            'note' => 'Free Delivery Restaurant - Paid By Order'),
                        array('
                        created_at' => $time ,
                            'date' => $time ,
                            'debit' => 0,
                            'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['res_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['res_account'],
                            'rate' =>  $rate ,
                            'note' => 'Free Delivery Restaurant - Paid By Order'),

                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' =>  $bill['free_delivery_fee'] * $rate ,
                            'credit' => 0,
                            'account_id' => ACCOUNTS::FreeDelivery,
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['FreeDelivery'],
                            'rate' =>  $rate ,'note' => 'Free Delivery Restaurant - Paid By Order'
                        ),
                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' => 0,
                            'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['driver_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,
                            'note' => 'Free Delivery Restaurant - Paid By Order'
                        ),
                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' => 0,'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['res_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['res_account'],
                            'rate' =>  $rate ,
                            'note' => 'When the driver pay the delivery fees'),

                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' =>  $bill['free_delivery_fee'] * $rate ,
                            'credit' => 0,
                            'account_id' => $bill['driver_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,
                            'note' => 'When the driver pay the delivery fees'),

                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' =>  $bill['free_delivery_fee'] * $rate ,
                            'credit' => 0,
                            'account_id' => $bill['res_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['res_account'],
                            'rate' =>  $rate ,
                            'note' => 'Free Delivery "Driver" Restaurant - Paid By BP'),
                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' => 0,
                            'credit' => $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['driver_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,
                            'note' => 'Free Delivery "Driver" Restaurant - Paid By BP'),


                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' =>  $bill['free_delivery_fee'] * $rate ,
                            'credit' => 0,
                            'account_id' => ACCOUNTS::FreeDelivery,
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['FreeDelivery'],
                            'rate' =>  $rate ,
                            'note' => 'Free Delivery "Driver" Restaurant - Paid By BP'),
                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' => 0,
                            'credit' =>$bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['driver_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,
                            'note' => 'Free Delivery "Driver" Restaurant - Paid By BP'),

                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' =>  $bill['free_delivery_fee'] * $rate ,
                            'credit' => 0,
                            'account_id' => $bill['driver_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,
                            'note' => 'When the restaurant paid the delivery fees'
                        ),
                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' => 0,
                            'credit' => $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['res_account_id'],
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['res_account'],
                            'rate' =>  $rate ,
                            'note' => 'When the restaurant paid the delivery fees'
                        )

                    ));
                }


            }else{


                $this->account_model->add_details( array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => $bill['std_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,
                        'note' => 'Invoice'
                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['std_total'] * $rate ,
                        'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,
                        'note' => 'Invoice'
                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $bill['commision'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['res_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['res_account'],
                        'rate' =>  $rate ,
                        'note' => 'Restaurant Commission'),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0, 'credit' =>  $bill['commision'] * $rate ,
                        'account_id' => ACCOUNTS::BeeOrderSales,
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['BeeOrderSales'],
                        'rate' =>  $rate ,
                        'note' => 'Restaurant Commission' ),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $bill['delivery'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>    $rate ,
                        'note' => 'Delivery'
                    ) ,
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0, 'credit' =>   $bill['delivery'] * $rate ,
                        'account_id' => ACCOUNTS::DeliveryRevenues,'head_id' => $head_id,
                        'currency' => $accounts_currency['DeliveryRevenues'],'rate' =>  $rate ,
                        'note' => 'Delivery'
                    ),
                    array('created_at' => $time ,
                        'date' => $time ,  'debit' =>  $driver_commission, 'credit' => 0,
                        'account_id' => ACCOUNTS::DriversCommissions,'head_id' => $head_id,
                        'currency' => $accounts_currency['DriversCommissions'],'rate' =>$rate ,
                        'note' => 'Driver Commission'
                    ),
                    array( 'created_at' => $time ,
                        'date' => $time ,'debit' => 0,  'credit' =>   $driver_commission,
                        'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],'rate' =>$rate ,
                        'note' => 'Driver Commission'
                    ),
                    array('created_at' => $time , 'date' => $time ,'debit' =>  $bill['ghost_fee'] * $rate ,
                        'credit' => 0,'account_id' => ACCOUNTS::GhostRide ,'head_id' => $head_id,
                        'currency' => $accounts_currency['GhostRide'],'rate' =>  $rate ,
                        'note' => 'Ghost Ride'),
                    array('created_at' => $time , 'date' => $time ,'debit' => 0,
                        'credit' =>   $bill['ghost_fee'] * $rate ,'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Ghost Ride'
                    )




                ));

                if($bill['v_total'] != 0){
                    $this->account_model->add_details( array(  'created_at' => $time ,
                        'date' => $time , 'debit' =>  $bill['v_total'] * $rate ,'credit' => 0,
                        'account_id' => ACCOUNTS::BeeOrderSalesDiscount,'head_id' => $head_id,
                        'currency' => $accounts_currency['BeeOrderSalesDiscount'],
                        'rate' =>  $rate ,'note' => 'Restaurant Discount'),
                        array( 'created_at' => $time ,
                            'date' => $time , 'debit' => 0,'credit' =>   $bill['v_total'] * $rate ,
                            'account_id' => $bill['res_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['res_account'], 'rate' =>  $rate ,
                            'note' => 'Restaurant Discount')
                    );
                }

                if($bill['free_delivery_fee']  != 0){
                    $this->account_model->add_details(array(
                        array('created_at' => $time ,
                            'date' => $time ,'debit' =>  $bill['free_delivery_fee'] * $rate ,'credit' => 0,
                            'account_id' => ACCOUNTS::FreeDelivery,'head_id' => $head_id,
                            'currency' => $accounts_currency['FreeDelivery'],'rate' =>  $rate ,
                            'note' => 'Free Delivery Paid By Bee Order - as a Down Payment '),
                        array('created_at' => $time ,
                            'date' => $time ,'debit' => 0,'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],'rate' =>  $rate ,
                            'note' => 'Free Delivery Paid By Bee Order - as a Down Payment '),


                        array('created_at' => $time ,
                            'date' => $time ,'debit' =>  $bill['free_delivery_fee'] * $rate ,'credit' => 0,
                            'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],'rate' =>  $rate ,
                            'note' => 'Free Delivery Restaurant - Paid By Order'),
                        array('created_at' => $time ,
                            'date' => $time ,'debit' => 0,'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['res_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['res_account'],'rate' =>  $rate ,
                            'note' => 'Free Delivery Restaurant - Paid By Order'),

                        array('created_at' => $time ,
                            'date' => $time ,'debit' =>  $bill['free_delivery_fee'] * $rate ,'credit' => 0,
                            'account_id' => ACCOUNTS::FreeDelivery,'head_id' => $head_id,
                            'currency' => $accounts_currency['FreeDelivery'],
                            'rate' =>  $rate ,'note' => 'Free Delivery Restaurant - Paid By Order'
                        ),
                        array('created_at' => $time ,
                            'date' => $time ,'debit' => 0,'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],'rate' =>  $rate ,
                            'note' => 'Free Delivery Restaurant - Paid By Order'
                        ),
                        array('created_at' => $time ,
                            'date' => $time ,'debit' => 0,'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['res_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['res_account'],'rate' =>  $rate ,
                            'note' => 'When the driver pay the delivery fees'),

                        array('created_at' => $time ,
                            'date' => $time ,'debit' =>  $bill['free_delivery_fee'] * $rate ,'credit' => 0,
                            'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],'rate' =>  $rate ,
                            'note' => 'When the driver pay the delivery fees'),

                        array('created_at' => $time ,
                            'date' => $time ,'debit' =>  $bill['free_delivery_fee'] * $rate ,'credit' => 0,
                            'account_id' => $bill['res_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['res_account'],'rate' =>  $rate ,
                            'note' => 'Free Delivery "Driver" Restaurant - Paid By BP'),
                        array('created_at' => $time ,
                            'date' => $time ,'debit' => 0,'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],'rate' =>  $rate ,
                            'note' => 'Free Delivery "Driver" Restaurant - Paid By BP'),


                        array('created_at' => $time ,
                            'date' => $time ,'debit' =>  $bill['free_delivery_fee'] * $rate ,'credit' => 0,
                            'account_id' => ACCOUNTS::FreeDelivery,'head_id' => $head_id,
                            'currency' => $accounts_currency['FreeDelivery'],'rate' =>  $rate ,
                            'note' => 'Free Delivery "Driver" Restaurant - Paid By BP'),
                        array('created_at' => $time ,
                            'date' => $time ,'debit' => 0,'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],'rate' =>  $rate ,
                            'note' => 'Free Delivery "Driver" Restaurant - Paid By BP'),

                        array('created_at' => $time ,
                            'date' => $time ,'debit' =>  $bill['free_delivery_fee'] * $rate ,'credit' => 0,
                            'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,'note' => 'When the restaurant paid the delivery fees'
                        ),
                        array('created_at' => $time ,
                            'date' => $time ,'debit' => 0,'credit' =>   $bill['free_delivery_fee'] * $rate ,
                            'account_id' => $bill['res_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['res_account'],'rate' =>  $rate ,
                            'note' => 'When the restaurant paid the delivery fees'
                        )

                    ));
                }
            }

        }elseif ($stage == BILL::REFUNDED_STAGE) {
            $this->account_model->add_details( array(
                array('created_at' => $time ,
                    'date' => $time ,'debit' => $bill['std_total'] * $rate , 'credit' => 0,
                    'account_id' => ACCOUNTS::RefundedOrders,'head_id' => $head_id,
                    'currency' => $accounts_currency['RefundedOrders'],'rate' =>    $rate ,
                    'note' => 'Invoice'),
                array( 'created_at' => $time ,
                    'date' => $time ,'debit' => 0,'credit' => $bill['std_total'] * $rate ,
                    'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                    'currency' => $accounts_currency['driver_account'], 'rate' =>  $rate ,
                    'note' => 'Invoice'),
                array( 'created_at' => $time ,
                    'date' => $time , 'debit' =>    $bill['delivery'] * $rate , 'credit' => 0,
                    'account_id' => ACCOUNTS::RefundedOrders,
                    'head_id' => $head_id,'currency' => $accounts_currency['RefundedOrders'],
                    'rate' =>    $rate ,'note' => 'Delivery'),
                array( 'created_at' => $time ,
                    'date' => $time ,  'debit' => 0, 'credit' =>   $bill['delivery'] * $rate ,
                    'account_id' => ACCOUNTS::DeliveryRevenues,  'head_id' => $head_id,
                    'currency' => $accounts_currency['DeliveryRevenues'],  'rate' =>  $rate ,
                    'note' => 'Delivery'),
                array(  'created_at' => $time ,
                    'date' => $time ,'debit' =>$driver_commission,'credit' => 0,
                    'account_id' => ACCOUNTS::DriversCommissions,'head_id' => $head_id,
                    'currency' => $accounts_currency['DriversCommissions'],'rate' =>  $rate ,
                    'note' => 'Driver Commission'),
                array( 'created_at' => $time ,
                    'date' => $time , 'debit' => 0, 'credit' =>     $driver_commission,
                    'account_id' => $bill['driver_account_id'],    'head_id' => $head_id,
                    'currency' => $accounts_currency['driver_account'], 'rate' =>  $rate ,
                    'note' => 'Driver Commission')

            ));
        }elseif ($stage == BILL::FAKE_ORDER_STAGE) {

            if ($bill['restaurant_type'] == new_restaurant_type::VIRTUAL) {

                //FACK_ORDER

                $this->account_model->add_details(array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $bill['std_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::FakeOrders ,'head_id' => $head_id,'currency' => $accounts_currency['FakeOrders'],
                        'rate' =>  $rate ,'note' => 'Fake Order'

                    ),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' => $bill['std_total'] * $rate ,
                        'account_id' => $bill['driver_account_id'],// driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Fake Order'

                    )
                ));


            }else{



                $this->account_model->add_details(array(

                    array(
                        'created_at' => $time , 'date' => $time ,
                        'debit' =>  ($bill['std_total']/2) * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::FakeOrders ,
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['FakeOrders'],
                        'rate' =>  $rate ,'note' => 'Fake Order'

                    ),

                    array(
                        'created_at' => $time , 'date' => $time ,
                        'debit' =>   ($bill['std_total']/2) * $rate,
                        'credit' => 0,
                        'account_id' => $bill['res_account_id'],//res
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['res_account'],
                        'rate' =>  $rate ,'note' => 'Fake Order'

                    ),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['std_total'] * $rate ,
                        'account_id' => $bill['driver_account_id'],//  driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' =>'Fake Order'
                    )
                ));
            }


        }elseif ($stage == BILL::WASTED_ORDER_STAGE) {
            if ($bill['restaurant_type'] == new_restaurant_type::VIRTUAL) {


                $this->account_model->add_details(array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => ($bill['std_total']/2) * $rate,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::WastedOrders,
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['WastedOrders'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>  ($bill['std_total']/2) * $rate,
                        'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'
                    )

                ));

                if($bill['v_total'] != 0){
                    $this->account_model->add_details(array( array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => ($bill['v_total']/2)* $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::WastedOrders,
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['WastedOrders'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' => ($bill['v_total']/2)* $rate,
                        'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'
                    )));
                }

            }else{



                $this->account_model->add_details(array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  ($bill['std_total']/2) * $rate,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::WastedOrders,
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['WastedOrders'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   ($bill['std_total']/2) * $rate,
                        'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'
                    )

                ));

                if($bill['v_total'] != 0){
                    $this->account_model->add_details(array(

                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' =>($bill['v_total']/2)* $rate,
                            'credit' => 0,
                            'account_id' => ACCOUNTS::WastedOrders,
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['WastedOrders'],
                            'rate' =>  $rate ,'note' => 'Wasted Order'

                        ),array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' => 0,
                            'credit' => ($bill['v_total']/2)* $rate,
                            'account_id' => $bill['driver_account_id'],'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,'note' => 'Wasted Order'
                        )
                    ));
                }

            }

        }elseif ($stage == BILL::WASTED_ORDER_CLIENT_FEEDBACK_STAGE) {

            if ($bill['restaurant_type'] == new_restaurant_type::VIRTUAL) {
                $this->account_model->add_details(array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $bill['purchase_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],//dr
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['purchase_total'] * $rate ,
                        'account_id' => ACCOUNTS::WastedOrders,'head_id' => $head_id,'currency' => $accounts_currency['WastedOrders'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'

                    )));
            }else{
                $this->account_model->add_details(array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $bill['std_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],//dr
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['std_total'] * $rate ,
                        'account_id' => ACCOUNTS::WastedOrders,'head_id' => $head_id,'currency' => $accounts_currency['WastedOrders'],
                        'rate' =>  $rate ,'note' => 'Wasted Order'

                    )));
            }

        }elseif ($stage == BILL::NOT_DELIVERED_STAGE) {
            if ($bill['restaurant_type'] == new_restaurant_type::VIRTUAL) {


                $this->account_model->add_details(array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>    $bill['delivery'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Not Deliverd Order - On Bee Order Co'

                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['delivery'] * $rate ,
                        'account_id' => ACCOUNTS::NotDeliveredOrdersBeeOrder,'head_id' => $head_id,'currency' => $accounts_currency['NotDeliveredOrdersBeeOrder'],
                        'rate' =>  $rate ,'note' => 'Not Deliverd Order - On Bee Order Co'

                    )
                ));

            }else{



                $this->account_model->add_details(array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $bill['delivery'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Not Deliverd Order - On Bee Order Co'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>    $bill['delivery'] * $rate ,
                        'account_id' => ACCOUNTS::NotDeliveredOrdersBeeOrder,'head_id' => $head_id,'currency' => $accounts_currency['NotDeliveredOrdersBeeOrder'],
                        'rate' =>  $rate ,'note' => 'Not Deliverd Order - On Bee Order Co'

                    )
                ));

            }
        }elseif ($stage == BILL::COPLEMENTARY_STAGE) {

            //VIRTUAL
            if ($bill['restaurant_type'] == new_restaurant_type::VIRTUAL) {
                $this->account_model->add_details(array(
                    array( 'created_at' => $time ,
                        'date' => $time ,
                        'debit' => $bill['std_total'] * $rate , 'credit' => 0, 'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id, 'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Invoice')
                ,array( 'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,'credit' =>  $bill['purchase_total'] * $rate , 'account_id' => $bill['driver_account_id'],'head_id' => $head_id,'currency' => $accounts_currency['driver_account'],'rate' =>  $rate ,
                        'note' => 'Invoice'),
                    array('created_at' => $time , 'date' => $time , 'debit' => 0,
                        'credit' =>  ($bill['std_total'] - $bill['purchase_total']) * $rate ,
                        'account_id' => ACCOUNTS::BeeOrderSales,'head_id' => $head_id,
                        'currency' => $accounts_currency['BeeOrderSales'],'rate' =>  $rate ,
                        'note' => 'Invoice'),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['delivery'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],// driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Delivery')

                ,array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>  $bill['delivery'] * $rate ,
                        'account_id' => ACCOUNTS::DeliveryRevenues,'head_id' => $head_id,'currency' => $accounts_currency['DeliveryRevenues'],
                        'rate' =>  $rate ,'note' => 'Delivery'

                    ),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $driver_commission,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::DriversCommissions,'head_id' => $head_id,'currency' => $accounts_currency['DriversCommissions'],
                        'rate' =>  $rate ,'note' => 'Driver Commission'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' => $driver_commission,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Driver Commission'

                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   ($bill['std_total'] + $bill['v_total']) * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::ComplementaryOrders,'head_id' => $head_id,'currency' => $accounts_currency['ComplementaryOrders'],
                        'rate' =>  $rate ,'note' => 'Complementary Order - Bee Delivery Service Provider (Orginal Invoice + Discount of Orginal Invoice)'


                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   ($bill['std_total'] + $bill['v_total']) * $rate ,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Complementary Order - Bee Delivery Service Provider (Orginal Invoice + Discount of Orginal Invoice)'


                    )

                ));

                if($bill['v_total'] != 0){
                    $this->account_model->add_details(array(array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['v_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::BeeOrderSalesDiscount,'head_id' => $head_id,'currency' => $accounts_currency['BeeOrderSalesDiscount'],
                        'rate' =>  $rate ,'note' => 'Restaurant Discount'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['v_total'] * $rate ,
                        'account_id' => $bill['driver_account_id'],// driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Restaurant Discount'

                    )));
                }


            }else{
                $this->account_model->add_details(array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['std_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],// res
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Invoice'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['std_total'] * $rate ,
                        'account_id' => $bill['driver_account_id'],//res
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Invoice'

                    ),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['commision'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['res_account_id'],// res
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['res_account'],
                        'rate' =>  $rate ,'note' => 'Restaurant Commission'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['commision'] * $rate ,
                        'account_id' => ACCOUNTS::BeeOrderSales,'head_id' => $head_id,'currency' => $accounts_currency['BeeOrderSales'],
                        'rate' =>  $rate ,'note' => 'Restaurant Commission'

                    ),


                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['delivery'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],// driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Delivery'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['delivery'] * $rate ,
                        'account_id' => ACCOUNTS::DeliveryRevenues,'head_id' => $head_id,'currency' => $accounts_currency['DeliveryRevenues'],
                        'rate' =>  $rate ,'note' => 'Delivery'

                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $driver_commission,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::DriversCommissions,'head_id' => $head_id,'currency' => $accounts_currency['DriversCommissions'],
                        'rate' =>  $rate ,'note' => 'Driver Commission'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $driver_commission,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Driver Commission'

                    ),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['ghost_fee'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::GhostRide,'head_id' => $head_id,'currency' => $accounts_currency['GhostRide'],
                        'rate' =>  $rate ,'note' => 'Ghost Ride'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['ghost_fee'] * $rate ,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Ghost Ride'

                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  ( $bill['std_total']+$bill['v_total']) * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::ComplementaryOrders['id'],'head_id' => $head_id,'currency' => $accounts_currency['ComplementaryOrders'],
                        'rate' =>  $rate ,'note' => 'Complementary Order - Bee Delivery Service Provider (Orginal Invoice + Discount of Orginal Invoice)'


                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>  ( $bill['std_total']+$bill['v_total']) * $rate ,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Complementary Order - Bee Delivery Service Provider (Orginal Invoice + Discount of Orginal Invoice)'

                    )
                ));

                if($bill['free_delivery_fee'] != 0){
                    $this->account_model->add_details(array(  array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['free_delivery_fee'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::FreeDelivery,'head_id' => $head_id,'currency' => $accounts_currency['FreeDelivery'],
                        'rate' =>  $rate ,'note' => 'Free Delivery'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['free_delivery_fee'] * $rate ,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Free Delivery'

                    )
                    ));
                }


                if($bill['v_total'] != 0){
                    $this->account_model->add_details(array( array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['v_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::BeeOrderSalesDiscount,'head_id' => $head_id,'currency' => $accounts_currency['BeeOrderSalesDiscount'],
                        'rate' =>  $rate ,'note' => 'Restaurant Discount'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['v_total'] * $rate ,
                        'account_id' => $bill['res_account_id'],//res
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['res_account'],
                        'rate' =>  $rate ,'note' => 'Restaurant Discount'

                    )));
                }
            }

        }elseif ($stage == BILL::FREE_ORDER_STAGE) {
            if ($bill['restaurant_type'] == new_restaurant_type::VIRTUAL) {

                $this->account_model->add_details(array(
                    array( 'created_at' => $time ,
                        'date' => $time ,
                        'debit' => $bill['std_total'] * $rate , 'credit' => 0, 'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id, 'currency' => $accounts_currency['driver_account'], 'rate' =>  $rate ,
                        'note' => 'Invoice'),
                    array( 'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,'credit' =>  $bill['purchase_total'] * $rate , 'account_id' => $bill['driver_account_id'],
                        'head_id' => $head_id,'currency' => $accounts_currency['driver_account'],'rate' =>  $rate ,
                        'note' => 'Invoice'),
                    array('created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,'credit' =>  ($bill['std_total'] - $bill['purchase_total']) * $rate ,  'account_id' => ACCOUNTS::BeeOrderSales,'head_id' => $head_id,
                        'currency' => $accounts_currency['BeeOrderSales'],'rate' =>  $rate ,
                        'note' => 'Invoice'),


                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>  $driver_commission,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::DriversCommissions,'head_id' => $head_id,'currency' => $accounts_currency['DriversCommissions'],
                        'rate' =>  $rate ,'note' => 'Driver Commission'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' => $driver_commission,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Driver Commission'

                    ),

                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['std_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::FreeOrders,'head_id' => $head_id,'currency' => $accounts_currency['FreeOrders'],
                        'rate' =>  $rate ,'note' => 'Free Order - Bee Delivery Service Provider'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['std_total'] * $rate ,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Free Order - Bee Delivery Service Provider'

                    )


                ));

                if($bill['v_total'] != 0){
                    $this->account_model->add_details(array( array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['v_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::BeeOrderSalesDiscount,'head_id' => $head_id,'currency' => $accounts_currency['BeeOrderSalesDiscount'],
                        'rate' =>  $rate ,'note' => 'Restaurant Discount'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['v_total'] * $rate ,
                        'account_id' => $bill['driver_account_id'],// driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Restaurant Discount'

                    )));
                }

            }else{

                $this->account_model->add_details(array(
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['std_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['driver_account_id'],// driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Invoice'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['std_total'] * $rate ,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Invoice'

                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['commision'] * $rate ,
                        'credit' => 0,
                        'account_id' => $bill['res_account_id'],// res
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['res_account'],
                        'rate' =>  $rate ,'note' => 'Restaurant Commission'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['commision'] * $rate ,
                        'account_id' => ACCOUNTS::BeeOrderSales,'head_id' => $head_id,'currency' => $accounts_currency['BeeOrderSales'],
                        'rate' =>  $rate ,'note' => 'Restaurant Commission'

                    ),




                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['delivery'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::FreeOrders,// driver
                        'head_id' => $head_id,
                        'currency' =>  $accounts_currency['FreeOrders'],
                        'rate' =>  $rate ,'note' => 'Delivery'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['delivery'] * $rate ,
                        'account_id' => ACCOUNTS::DeliveryRevenues,'head_id' => $head_id,'currency' => $accounts_currency['DeliveryRevenues'],
                        'rate' =>  $rate ,'note' => 'Delivery'

                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $driver_commission,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::DriversCommissions,'head_id' => $head_id,'currency' => $accounts_currency['DriversCommissions'],
                        'rate' =>  $rate ,'note' => 'Driver Commission'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $driver_commission,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Driver Commission'

                    ),
                    array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['ghost_fee'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::GhostRide,'head_id' => $head_id,'currency' => $accounts_currency['GhostRide'],
                        'rate' =>  $rate ,'note' => 'Ghost Ride'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['ghost_fee'] * $rate ,
                        'account_id' => $bill['driver_account_id'],//driver
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['driver_account'],
                        'rate' =>  $rate ,'note' => 'Ghost Ride'

                    )


                ));

                if($bill['v_total'] != 0){
                    $this->account_model->add_details(array( array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' =>   $bill['v_total'] * $rate ,
                        'credit' => 0,
                        'account_id' => ACCOUNTS::BeeOrderSalesDiscount,'head_id' => $head_id,'currency' => $accounts_currency['BeeOrderSalesDiscount'],
                        'rate' =>  $rate ,'note' => 'Restaurant Discount'

                    ),array(
                        'created_at' => $time ,
                        'date' => $time ,
                        'debit' => 0,
                        'credit' =>   $bill['v_total'] * $rate ,
                        'account_id' => $bill['res_account_id'],//res
                        'head_id' => $head_id,
                        'currency' => $accounts_currency['res_account'],
                        'rate' =>  $rate ,'note' => 'Restaurant Discount'

                    )));
                }

                if($bill['delivery_provider'] == DELIVERY_PROVIDER::BEEORDER){
                    //Free Order - Bee Delivery Service Provider
                    $note = 'Free Order - Bee Delivery Service Provider';


                    $this->account_model->add_details(array(
                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' =>   $bill['std_total'] * $rate ,
                            'credit' => 0,
                            'account_id' => ACCOUNTS::FreeOrders,'head_id' => $head_id,'currency' => $accounts_currency['FreeOrders'],
                            'rate' =>  $rate ,'note' => $note

                        ),array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' => 0,
                            'credit' =>   $bill['std_total'] * $rate ,
                            'account_id' => $bill['driver_account_id'],//driver
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,'note' => $note

                        )));

                }else{
                    //Free Order - Restaurant is Service Provider or Pick-up
                    $note = 'Free Order - Restaurant is Service Provider or Pick-up';


                    $this->account_model->add_details(array(
                        array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' =>  $bill['std_total'] * $rate ,
                            'credit' => 0,
                            'account_id' => $bill['res_account_id'],'head_id' => $head_id,'currency' => $accounts_currency['res_account'],
                            'rate' =>  $rate ,'note' => $note

                        ),array(
                            'created_at' => $time ,
                            'date' => $time ,
                            'debit' => 0,
                            'credit' =>   $bill['std_total'] * $rate ,
                            'account_id' => $bill['driver_account_id'],//driver
                            'head_id' => $head_id,
                            'currency' => $accounts_currency['driver_account'],
                            'rate' =>  $rate ,'note' => $note

                        )));
                }




            }
        }
    }

}