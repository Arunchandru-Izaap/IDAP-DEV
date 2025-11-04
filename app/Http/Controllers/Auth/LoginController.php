<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use Session;
use App\Models\Employee;
use App\Models\JwtToken;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function verifyOtp(Request $request){
        /*captcha code starts*/
        $request->validate([
            'captcha' => 'required',
        ]);
        $userCaptcha = $request->input('captcha');
        $captcha = Session::get('captcha');
        if ($userCaptcha === $captcha) {
            // CAPTCHA verification passed
            // Process form submission
        } else {
            // CAPTCHA verification failed
            // Redirect back with error message
            return redirect()->back()->with('error', "CAPTCHA verification failed.");
        }
        /*captcha code ends*/


        $data['userId'] = data_get($request, 'userId');
        if(!Employee::where('emp_code', $data['userId'])->exists()){
            return redirect()->back()->with('error', "User ID doesn't exist!");
        }
        $input = $request->all();
        $ip_address = request()->ip();
        $this->validate($request, [
            'userId' => 'required',
        ]);
        /*check for concurrent login starts*/
        /*$employee = Employee::where('emp_code', $input['userId'])->where('is_login_now', 1)->first();
        if ($employee) {
            return redirect()->back()->with('error', 'Already Logged in User. Please logout active session!');
        } else {
            Employee::where('emp_code', $input['userId'])->update(['is_login_now' => 1, 'last_login' => now()]);
        }*/
        /*check for concurrent login ends*/
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $res = $client->post(
            env('API_URL').'/api/generateOtp',
            [
                \GuzzleHttp\RequestOptions::JSON => 
                ['UserID' => 'E00251','ipAddress' =>$ip_address,]
            ],
            ['Content-Type' => 'application/json']
        );
        $data = json_decode($res->getBody(),true);
        // Code to generate OTP from pragati api ends here
        //         dd($data);
        if( $data['Status'] == true){
            return view('auth.verifyOtp')->with([
                'status' => true,
                'userId' => $input['userId'],
                'message' => $data['StatusDesc'],
            ]);
        }else{
            return redirect()->back()->with('error', $data['StatusDesc']);
        }
        
    }

    public function decryptOtp($encryptedText, $key) {
        // Initialize an empty string to store the decrypted characters
       $decryptedText = '';
       for ($i = 0; $i < strlen($encryptedText); $i++) {
           $decryptedText .= chr(ord($encryptedText[$i]) ^ ord($key[$i % strlen($key)]));
       }
       return $decryptedText;
      }

    public function login(Request $request)
    {   
        // $request->validate([
        //     'otp' => 'required'
        // ]);
        $input = $request->all();
        /*captcha code starts*/
        /*$userCaptcha = $request->input('captcha');
        $captcha = Session::get('captcha');
        if ($userCaptcha === $captcha) {
            // CAPTCHA verification passed
            // Process form submission
        } else {
            // CAPTCHA verification failed
            // Redirect back with error message
            return view('auth.verifyOtp')->with([
                'status' => false,
                'userId' => $input['userId'],
                'message' => 'CAPTCHA verification failed.',
            ]);
        }*/
        /*captcha code ends*/
        $input = $request->all();
        if(is_null($input['otp']) || $input['otp'] == ''){

            return view('auth.verifyOtp')->with([
                'status' => false,
                'userId' => $input['userId'],
                'message' => 'Please enter the OTP!',
            ]);
        }
        $input['otp'] = $this->decryptOtp($input['otp'], '2023');
        //$ip_address = env('APP_URL') == 'https://idap.noesis.dev' ? '172.30.58.98': request()->ip();
        $ip_address = '172.30.58.98';
        // Code to verify OTP starts here
        $this->validate($request, [
            'otp' => 'required',
        ]);
        $client = new \GuzzleHttp\Client(['verify'=>false]);
        $res = $client->post(
            env('API_URL').'/api/verifyOtp',
            [
                \GuzzleHttp\RequestOptions::JSON => 
                ['UserID' => $input['userId'],'OtpCode' =>$input['otp'], 'ipAddress' => $ip_address]
            ],
            ['Content-Type' => 'application/json']
        );
        $data = json_decode($res->getBody(),true);
        // Code to verify OTP starts here
        // dd($data);dd($ip_address);
        //for development added to bypass invalid otp
        $data['Status'] = true;
        if($data['Status'] == true){

            $emp_info= Employee::where('emp_code',$input['userId'])->first();
            if(!is_null($emp_info)){
                 $all_div_id = Employee::where('emp_code',$input['userId'])->pluck('div_code')->toArray();
                 $all_div_name = Employee::where('emp_code',$input['userId'])->pluck('div_name')->toArray();

                 JwtToken::updateOrCreate(['emp_code' => $input['userId']], [ 
                    'jwt_token' => $data['Token']
                ]);

                //  use for whole frontend 
                // type initiator / approver
                // level L1 / L2 / L3 / L4
                Session::put('type',$emp_info->emp_category);
                Session::put('emp_type',$emp_info->emp_type);
                Session::put("level",$emp_info->emp_level);
                Session::put("division_name",implode (",", $all_div_name));
                Session::put("division_id",implode (",", $all_div_id));
                Session::put("emp_code",$emp_info->emp_code);
                Session::put("emp_name",$emp_info->emp_name);

                // Update last activity timestamp in session data during login for concurrent login check
                Session::put('last_activity', now());

                if($emp_info->emp_category != 'admin'){
                    $email = "nonadmin@noesis.tech";
                    $password = "noesistech";
                }else{
                    $email = "mm@gmail.com";
                    $password = "noesistech";
                }
                
            }else{
                if(auth()->attempt(array('email' => $input['userId'], 'password' => "noesistech")))
                {
                    $request->session()->regenerate();
                    if (auth()->user()->user_type == 'Administrator') {
                        return redirect()->route('home');
                    }else{
                        return redirect()->route('initiator_dashboard');
                    }
                }else{
                    Session::put('type','normal_user');
                    Session::put("emp_code",$data->UserID);
                    Session::put("emp_name",$data->UserName);
                    $email = "nonadmin@noesis.tech";
                    $password = "noesistech";
                }
                
            }
            
        }else{
            return view('auth.verifyOtp')->with([
                'status' => false,
                'userId' => $input['userId'],
                'message' => 'Please enter correct the OTP!',
            ]);

            /*   if(auth()->attempt(array('email' => $input['userId'], 'password' => "noesistech")))
            {
                if (auth()->user()->user_type == 'Administrator') {
                    return redirect()->route('home');
                }else{
                    return redirect()->route('initiator_dashboard');
                }
            }else{
                return redirect()->back()->with([
                    'status' => false,
                    'userId' => data_get($data, 'UserID'),
                    'message' => data_get($data, 'StatusDesc')
                ]);
            }
           */
        }
        //dd("hi");
        if(auth()->attempt(array('email' => $email, 'password' => $password)))
        {
            $request->session()->regenerate();
            if (auth()->user()->user_type == 'Administrator') {
                return redirect()->route('home');
            }else{
                return redirect()->route('initiator_dashboard');
            }
        }else{
            return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors([
                'wrong' => 'Employee Code or Password are Wrong.',
            ]);
        }
     
    }

    public function logout(Request $request)
    {   
        $emp_code = Session::get('emp_code');
        Employee::where('emp_code', $emp_code)->update(['is_login_now' => 0]);
        Session::flush();
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('login');
    }

    public function clearCurrentLogin(Request $request)//added to clear active login when user leaves
    {   
        $input = $request->all();
        $update_active_session = Employee::where('emp_code', $input['emp_code'])->update(['is_login_now' => 0]);
        if($update_active_session)
        {
            $result['success'] = true;
        } 
        else
        {
            $result['success'] = false;
        }
        return response()->json($result);
    }
}
