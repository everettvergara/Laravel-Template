<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class authentication_controller extends Controller
{
    //CREATE TOKEN FILE
    private function create_token_file($user_code, $device_imei, $password){
        $today = Carbon::now('UTC')->toDateString();
        $token = hash('sha256', $user_code.$device_imei.$password.$today);
        $file_path = "Auth/".$user_code."/".$device_imei;

        if(is_dir($file_path) === false){
            mkdir($file_path, 0700, true);
        }

        $file = $file_path.'/'.$token;

        if(!file_exists($file)){
            file_put_contents($file, '', LOCK_EX);
        }

        return $token;
    }

    //CREATE TOKEN
    public function create_token(Request $request){
        $user_code = $request->user_code;
        $hashed_password = $request->hashed_password;
        $device_imei = $request->device_imei;

        $user = DB::table('salesman')
                    ->select( 
                        'id as erp_id',
                        'code',
                        DB::raw("lastname + ', ' + firstname as name"),
                        'firstname as first_name',
                        'lastname as last_name',
                        'email'
                    )
                    ->where('code', $user_code)
                    ->where('password',$hashed_password)
                    ->where('active',1)
                    ->get();

        if(count($user) == 1){
            try {
                $token = $this->create_token_file($user_code, $device_imei, $hashed_password);
                return json_encode([
                    'status' => 'Success',
                    'token' => $token,
                    'user'   => $user[0]
                ]);

            } catch (\Throwable $th) {
                return json_encode([
                    'status' => 'Failed',
                    'error' => $th->getMessage(),
                ]);
            }
        } else {
            return json_encode([
                'status'    => 'Failed',
                'error'     => 'No User Found'
            ]);
        }
    }

    //RESET PASSWORD
    public function reset_password(Request $request){
        $user_code = $request->user_code;
        $old_password = $request->old_password;
        $new_password = $request->new_password;
        $device_imei = $request->device_imei;
        $token = $request->token;

        $status = null;
        $new_token = null;

        $query = DB::table('salesman')
                    ->select(DB::raw("COUNT(*) as existing"))
                    ->where('code', $user_code)
                    ->where('password',$old_password)
                    ->where('active',1)
                    ->get();
        
        if($query[0]->existing == 1){
            $update_password = DB::table('salesman')
                                    ->where('code', $user_code)
                                    ->where('password',$old_password)
                                    ->update([
                                        'passsword' => $new_password
                                    ]);
            $new_token = $this->create_token_file($user_code, $device_imei, $new_password);
            $status = 'Success';
        }
        else{
            $status = 'Failed';
        }

        return json_encode([
            'status'    => $status,
            'token'     => $new_token
        ]);
    }

    // CHECK TOKEN
    public function check_token($user_code, $device_imei, $token){
        switch (true) {
            case (is_null($user_code)) :
                return false;
                break;
            case (is_null($device_imei)) :
                return false;
                break;
            case (is_null($token)) :
                return false;
                break;
            
            default:
                $file = "Auth/".$user_code."/".$device_imei."/".$token;
                return file_exists($file);
                break;
        }
    }

    //AUTH FAILED
    public function auth_fail_error(){  
        return json_encode([
            'status'    => 'Failed',
            'error'     => 'Auth Failed',
        ]);
    }

}