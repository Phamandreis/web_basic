<?php
require_once('./private/core/Controller.php');
require_once('./private/core/jwt/vendor/autoload.php');
require_once('./private/middlewares/Api.middleware.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class AdminApi extends Controller
{
    protected $middleware;

    function __construct($route, $param)
    {
        $this->middleware = new ApiMiddleware();
        // $this->middleware->authentication();
        // $payload = $this->middleware->jwt_get_payload();
        // !($payload) ? 
        // $this->middleware->json_send_response(200, array(
        //     'status' => false,
        //     "header_status_code" => 200,
        //     'msg' => 'Please login first!',
        //     'redirect' => getenv('BASE_URL') . 'login',
        // )) : null;
        // !($payload->phoneNumber == 'admin')? 
        // $this->middleware->json_send_response(404, array(
        //     'status' => false,
        //     "header_status_code" => 404,
        //     'msg' => 'This account does not have permission!!'
        // )) : null;
        switch ($route) {
            case 'list-account':
                $this->middleware->request_method('get');
                $this->listAccount($param);
                break;
            case 'list-transaction-confirm':
                $this->middleware->request_method('get');
                $this->listTransNeedConfirm($param);
                break;
            case 'user-details':
                $this->middleware->request_method('get');
                $this->userDetails($param);
                break;
            case 'transaction-detail':
                $this->middleware->request_method('get');
                $this->transactionDetails($param);
                break;
            case 'accept-transaction':
                $this->middleware->request_method('get');
                $this->acceptTransaction($param);
                break;
            case 'cancel-transaction':
                $this->middleware->request_method('get');
                $this->cancelTransaction($param);
                break;
            default:
                $this->middleware->json_send_response(404, array(
                    'status' => false,
                    "header_status_code" => 404,
                    'msg' => 'This endpoint cannot be found, please contact adminstrator for more information!'
                ));
        }
    }

    function listAccount($param){
        switch($param){
            case 'pending' : 
                $accountPending =$this->model('Account')->SELECT_ORDER_BY_DESC('role', 'pending','updatedAt');
                !$accountPending ?   $this->middleware->json_send_response(200, array(
                    'status' => false,
                    'header_status_code' => 200,
                    'msg' => 'Do not have any account pending!'
                )): $this->middleware->json_send_response(200, array(
                    'status' => true,
                    "header_status_code" => 200,
                    'msg' => 'Load List User successfully!',
                    'data' => $accountPending,
                ));
                break;
            case 'actived' :
                $accountActived =$this->model('Account')->SELECT_ORDER_BY_DESC('role', 'actived','createdAt');
                !$accountActived ?   $this->middleware->json_send_response(200, array(
                    'status' => false,
                    'header_status_code' => 200,
                    'msg' => 'Do not have any account actived!',
                )): $this->middleware->json_send_response(200, array(
                    'status' => true,
                    "header_status_code" => 200,
                    'msg' => 'Load List User successfully!',
                    'data' => $accountActived,
                ));
                break;
            case 'disabled':
                $accountDisabled =$this->model('Account')->SELECT_ORDER_BY_DESC('role', 'disabled','createdAt');
                !$accountDisabled ?   $this->middleware->json_send_response(200, array(
                    'status' => false,
                    'header_status_code' => 200,
                    'msg' => 'Do not have any account disabled!',
                )): $this->middleware->json_send_response(200, array(
                    'status' => true,
                    "header_status_code" => 200,
                    'msg' => 'Load List User successfully!',
                    'data' => $accountDisabled,
                ));
                break;
            case 'blocked':
                $accountBlocked =$this->model('Account')->SELECT_ORDER_BY_DESC('deleted', '1','createdAt');
                !$accountBlocked ?   $this->middleware->json_send_response(200, array(
                    'status' => false,
                    'header_status_code' => 200,
                    'msg' => 'Do not have any account blocked!',
                )): $this->middleware->json_send_response(200, array(
                    'status' => true,
                    "header_status_code" => 200,
                    'msg' => 'Load List User successfully!',
                    'data' => $accountBlocked,
                ));
                break;
            case '':
                $accountAll = $this->model('Account')->SELECT_ALL();
                !$accountAll ?  $this->middleware->json_send_response(200, array(
                    'status' => false,
                    'header_status_code' => 200,
                    'msg' => 'Do not have any account!',
                )): $this->middleware->json_send_response(200, array(
                    'status' => true,
                    "header_status_code" => 200,
                    'msg' => 'Load List User successfully!',
                    'data' => $accountAll,
                ));
                break;
            default :
            $this->middleware->json_send_response(404, array(
                'status' => false,
                "header_status_code" => 404,
                'msg' => 'This endpoint cannot be found, please contact adminstrator for more information!'
            ));

        }
    }

    
    function userDetails($phoneNumber){
        !$phoneNumber ? $this->middleware->json_send_response(404, array(
            'status' => false,
            "header_status_code" => 404,
            'msg' => 'This endpoint cannot be found, please contact adminstrator for more information!'
        )) : null;

        $userInfor = $this->model('account')->SELECT_ONE('phoneNumber',$phoneNumber);
        !$userInfor ? $this->middleware->json_send_response(200, 'This account does not exist') : 
        $this->middleware->json_send_response(200, array(
            'status' => true,
            "header_status_code" => 200,
            'msg' => 'Load User successfully!',
            'data' => $userInfor,
        ));
    }

    
    
    function listTransNeedConfirm($param){
       
        $transNeedConfirm = $this->model('transaction')->SELECT('action', 0);
        switch($param){

            case 'withdraw' : 
                $transWithdraw = [];
                foreach($transNeedConfirm as $key => $value){
                    if($value['type_transaction'] == '3'){
                        array_push($transWithdraw,$transNeedConfirm[$key]);
                    }
                }
                !$transWithdraw ? $this->middleware->json_send_response(200, array(
                    'status' => false,
                    'header_status_code' => 200,
                    'msg' => 'Do not have any transaction to confirm !',
                )): $this->middleware->json_send_response(200, array(
                    'status' => true,
                    "header_status_code" => 200,
                    'msg' => 'Load List User successfully!',
                    'data' => $transWithdraw,
                ));
                print_r($transWithdraw);
                break;
            case 'transfer' :
                $transTransfer = [];
                foreach($transNeedConfirm as $key => $value){
                    if($value['type_transaction'] == '2'){
                        array_push($transTransfer,$transNeedConfirm[$key]);
                    }
                }
                !$transTransfer ? $this->middleware->json_send_response(200, array(
                    'status' => false,
                    'header_status_code' => 200,
                    'msg' => 'Do not have any transaction to confirm !',
                )): $this->middleware->json_send_response(200, array(
                    'status' => true,
                    "header_status_code" => 200,
                    'msg' => 'Load List User successfully!',
                    'data' => $transTransfer,
                ));
                break;
            case '':
                !$transNeedConfirm ? $this->middleware->json_send_response(200, array(
                    'status' => false,
                    'header_status_code' => 200,
                    'msg' => 'Do not have any transaction to confirm !',
                )): $this->middleware->json_send_response(200, array(
                    'status' => true,
                    "header_status_code" => 200,
                    'msg' => 'Load List Transaction successfully!',
                    'data' => $transNeedConfirm,
                ));
                break;
            default :
            $this->middleware->json_send_response(404, array(
                'status' => false,
                "header_status_code" => 404,
                'msg' => 'This endpoint cannot be found, please contact adminstrator for more information!'
            ));

        }
    }

    function transactionDetails($transaction_id){
        !$transaction_id ? $this->middleware->json_send_response(404, array(
            'status' => false,
            "header_status_code" => 404,
            'msg' => 'This endpoint cannot be found, please contact adminstrator for more information!'
        )) : null;

        $transaction = $this->model('transaction')->SELECT_ONE('transaction_id',$transaction_id);
        !$transaction ? $this->middleware->json_send_response(200, 'This transaction does not exist') : 
        $this->middleware->json_send_response(200, array(
            'status' => true,
            "header_status_code" => 200,
            'msg' => 'Load transaction successfully!',
            'data' => $transaction,
        ));
    }

    function acceptTransaction($transaction_id){
        (!isset($transaction_id) || empty($transaction_id)) ? $this->middleware->json_send_response(200, array(
            'status' => false,
            "header_status_code" => 200,
            'msg' => 'Transaction id is not allow to be empty',
        )) : null;
        $transaction = $this->model('transaction')->SELECT_ONE('transaction_id',$transaction_id);
        
        $userInfor = $this->model('account')->SELECT_ONE('email',$transaction['email']);
        !$userInfor ?  $this->middleware->json_send_response(200, array(
                    'status' => false,
                    "header_status_code" => 200,
                    'msg' => 'Sender account does not exist or has been deleted by the administrator',
                )) : null;
        if($transaction ["type_transaction"] == '2'){
            $recipient = $this->model('account')->SELECT_ONE('phoneNumber',$transaction['phoneRecipient']);
            !$recipient ?  $this->middleware->json_send_response(200, array(
                'status' => false,
                "header_status_code" => 200,
                'msg' => 'Recipient account does not exist or has been deleted by the administrator',
            )) : null;
            if($transaction['costBearer'] == 'sender'){
                $totalForUser = ((int)($transaction['value_money']) + (int)($transaction['value_money'])*0.05);
                $totalForRecipient = (int)($transaction['value_money']);
            }else{
                $totalForUser = (int)($transaction['value_money']) ;
                $totalForRecipient = ((int)($transaction['value_money']) - (int)($transaction['value_money'])*0.05);
            }
            ($userInfor['wallet'] < $totalForUser) 
            ? $this->middleware->error_handler(200, 'This account does not enough money to make this transaction!') 
            : null;
        
            $condition1 = $this->model('account')->UPDATE_ONE(array('email' =>$userInfor['email']),array('wallet' =>$userInfor['wallet']-$totalForUser));
            $condition2 = $this->model('account')->UPDATE_ONE(array('phoneNumber' =>$recipient['phoneNumber']),array('wallet' =>$recipient['wallet'] + $totalForRecipient));
            $condition3 = $this->model('transaction')->UPDATE_ONE(array('transaction_id' =>$transaction['transaction_id']),array('action' =>1));
            $condition4 = $this->model('transaction')->UPDATE_ONE(array('transaction_id' =>$transaction['transaction_id']),array('updatedAt' =>time()));
                
            if($condition1 && $condition2 && $condition3 && $condition4){
                $this->utils()->sendMail(array(
                    "email" => $userInfor['email'],
                    'title' => 'Payment recevie',
                    'content' => '
                        <body style ="background-color: honeydew;">
                        <h1 
                        style="
                        text-align: center;
                        margin-top: 80px;
                        margin-bottom: 20px">PAYMENT RECEIPT</h1>
                        <div class="container" style = " 
                            margin: 0 auto;
                            position: relative;
                            ">
                            <table style=" 
                                text-align: center;
                                margin: 0 auto;
                                border: 1px dashed rgb(8, 8, 8);
                                border-collapse: collapse;
                                border: 1px solid rgb(8, 7, 7);
                                padding: 5px;
                                ">
                                <tr>
                                    <th class="first_row firt_col" style= " 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;
                                    ">Transaction Date,time</th>
                                    <td class="first_row" style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;"
                                    >'.date('Y-m-d H:i:s',$transaction['createdAt']).'</td>
                                </tr>
                                <tr style=" 
                                border: 1px solid rgb(8, 7, 7);
                                padding: 5px;">
                                    <th style = " border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">Transaction ID </th>
                                    <td>'. $transaction['transaction_id'].'</td>
                                </tr>
                                <tr style=" 
                                border: 1px solid rgb(8, 7, 7);
                                padding: 5px;">
                                    <th class="firt_col"  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">From</th>
                                    <td class="lab"  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">'.$userInfor['fullname'].'</td>
                                </tr>
                                <tr style=" 
                                border: 1px solid rgb(8, 7, 7);
                                padding: 5px;">
                                    <th class="firt_col"  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">To</th>
                                    <td  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">'.$recipient['fullname'].'</td>
                                </tr>
                                <tr style=" 
                                border: 1px solid rgb(8, 7, 7);
                                padding: 5px;">
                                    <th class="firt_col"  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">Amount</th>
                                    <td  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">'.$transaction['value_money'].'</td>
                                </tr>
                                <tr style=" 
                                border: 1px solid rgb(8, 7, 7);
                                padding: 5px;">
                                    <th class="firt_col"  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">Cost Bearder</th>
                                    <td  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">'.$transaction['costBearer'].'</td>
                                </tr>
                                <tr style=" 
                                border: 1px solid rgb(8, 7, 7);
                                padding: 5px;">
                                    <th class="firt_col"  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">Charge amount</th>
                                    <td  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">'. $transaction['value_money'] * 0.05 .'</td>
                                </tr>
                                <tr style=" 
                                border: 1px solid rgb(8, 7, 7);
                                padding: 5px;">
                                    <th  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">Description</th>
                                    <td  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">'. $transaction['description'] .'</td>
                                </tr>
                                <tr style=" 
                                border: 1px solid rgb(8, 7, 7);
                                padding: 5px;">
                                    <th class="firt_col"  style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">Account balance</th>
                                    <td   style=" 
                                    border: 1px solid rgb(8, 7, 7);
                                    padding: 5px;">'. $userInfor['wallet'] .'</td>
                                </tr>
                            </table>
                            <p><strong>Thank you for banking with KIWI e-wallet</strong></p>
                            <p>This confirmation is not a commitment regarding customer"s obligation with thỉrd party  </p>
                            <p>To ensure safety and security as well as to protect your rights and benefits, when making transactions via e-wallet
                            please read carefully and follow transaction instructions here.</p>
                        </body>
                    ',
                ))
                ?
                $this->middleware->json_send_response(200, array(
                    'status' => true,
                    'header_status_code' => 200,
                    'msg' => 'Update transfer transaction successfully!',
                ))
                : $this->middleware->json_send_response(500, array(
                    'status' => false,
                    'header_status_code' => 500,
                    'debug' => 'Admin API function acceptTranssaction(sendmail)',
                    'msg' => 'An error occurred while processing, please try again!'
                ));
               
            }else{
                $this->middleware->json_send_response(500, array(
                    'status' => false,
                    'header_status_code' => 500,
                    'debug' => 'Admin API function confirmTransaction(condition)',
                    'msg' => 'An error occurred while processing, please try again!'
                ));
            }
        }else{
            $total = (int)($transaction['value_money']) + ((int)($transaction['value_money'])*0.05);
            $condition1 = $this->model('account')->UPDATE_ONE(array('email' =>$userInfor['email']),array('wallet' =>$userInfor['wallet']-$total));
            $condition3 = $this->model('transaction')->UPDATE_ONE(array('transaction_id' =>$transaction['transaction_id']),array('action' =>1));
            $condition4 = $this->model('transaction')->UPDATE_ONE(array('transaction_id' =>$transaction['transaction_id']),array('updatedAt' =>time()));
            if($condition1 && $condition3 && $condition4 ){
                $this->middleware->json_send_response(200, array(
                    'status' => true,
                    "header_status_code" => 200,
                    'msg' => 'Update withdraw transaction successfully!',
                ));
            }else{
                $this->middleware->json_send_response(500, array(
                    'status' => false,
                    'header_status_code' => 500,
                    'debug' => 'Admin API function confirmTransaction(condition)',
                    'msg' => 'An error occurred while processing, please try again!'
                ));
            }
        }
    }

    function cancelTransaction($transaction_id){
        (!isset($transaction_id) || empty($transaction_id)) ? $this->middleware->json_send_response(200, array(
            'status' => false,
            "header_status_code" => 200,
            'msg' => 'Transaction id is not allow to be empty',
        )) : null;
        $transaction = $this->model('transaction')->SELECT_ONE('transaction_id',$transaction_id);
        
        $userInfor = $this->model('account')->SELECT_ONE('email',$transaction['email']);
        !$userInfor ?  $this->middleware->json_send_response(200, array(
                    'status' => false,
                    "header_status_code" => 200,
                    'msg' => 'Sender account does not exist or has been deleted by the administrator',
                )) : null;
        $condition1 = $this->model('transaction')->UPDATE_ONE(array('transaction_id' =>$transaction['transaction_id']),array('action' =>2));
        $condition2 = $this->model('transaction')->UPDATE_ONE(array('transaction_id' =>$transaction['transaction_id']),array('updatedAt' =>time()));
        if($condition1 && $condition2){
            $this->middleware->json_send_response(200, array(
                'status' => true,
                "header_status_code" => 200,
                'msg' => 'Update transfer transaction successfully!',
            ));
        }else{
            $this->middleware->json_send_response(500, array(
                'status' => false,
                'header_status_code' => 500,
                'debug' => 'Admin API function confirmTransaction(condition)',
                'msg' => 'An error occurred while processing, please try again!'
            ));
        }
    }
}