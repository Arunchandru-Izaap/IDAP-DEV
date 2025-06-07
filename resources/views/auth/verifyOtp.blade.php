@extends('layouts.app')
@section('content')
<style type="text/css">
    .copyright
    {
        text-align: center;
        bottom: 8px;
    }
    .body-login .login-pg .text-center img {
        margin-top: -15%;
        width: 100px;
        height: 100px;
    }
    .body-login .login-pg {
        max-width: 335px;
        margin-top: 2.8rem;
    }
    .body-login .login-pg .btn-primary-ct {
        margin-top: 14px;
    }
    .body-login
    {
        min-height: 100vh;
    }
</style>
<div class="body-login">
      <img src="admin/images/Sun_Pharma_logo.png" alt="Sun Pharma" class="sunpharma">
  <div class="login-pg">
    <div class="text-center">
        <img src="admin/images/Idap_Login.png">
      <h2>WELCOME</h2>
    </div>

    

    <form class="login-form" id="verifyOtpForm" method="POST" action="{{ route('login') }}">
        @csrf()

        @if($status)
            <div class="alert alert-success" role="alert">
                {!! $message !!}
            </div>
        @else
            <div class="alert alert-danger" role="alert">
                {!! $message !!}
            </div>
        @endif

        <div class="form-group">
            <label>OTP:</label>
        </div>
        <input type="hidden" class="form-control" id="otp" name="otp" placeholder="Enter Otp" />
        <div class="form-group">
            <input type="text" class="form-control" id="enteredOtp" name="" placeholder="Enter Otp" />
            @error('otp')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <!-- captcha code starts here -->
        <!-- <div class="form-group">
            <label for="captcha">Please enter text shown as below:</label>
        </div>
        <div class="form-group">
            <img src="{{ route('captcha') }}" alt="CAPTCHA">
        </div>
        <div class="form-group">
            <input type="text" class="form-control" id="captcha" name="captcha" placeholder="Enter text as above" />
             @error('captcha')
                <span class="invalid-feedback" role="alert" style="display: block;">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div> -->
        <!-- captcha code ends here -->
        <input type="hidden" id="userId" name="userId" value="{{$userId}}">
        
        <button class="btn btn-primary-ct" id="btnVerifyOtp" type="submit">Verify</button>

    </form>

  </div>
  <p class="copyright">© Copyright {{ date('Y') }} Sun Pharma. All rights reserved.</p>
</div>

<script>
   
function encrypt() {
  let text = document.getElementById('enteredOtp').value;
  let key = '2023';
    let encryptedText = '';
    for (let i = 0; i < text.length; i++) {
        encryptedText += String.fromCharCode(text.charCodeAt(i) ^ key.charCodeAt(i % key.length));
    }
    console.log(encryptedText);
    document.getElementById('otp').value = encryptedText;
}

function encryptNumber() {
    let number = document.getElementById('enteredOtp').value;
    let key = 2023;
    // Convert the number to a string
    const numberStr = number.toString();

    // Initialize an empty array to store the encrypted characters
    const encryptedChars = [];

    // Iterate over each character in the number string
    for (let i = 0; i < numberStr.length; i++) {
        // XOR the character code with the key code and convert it back to a character
        const encryptedChar = String.fromCharCode(numberStr.charCodeAt(i) ^ key);

        // Push the encrypted character to the array
        encryptedChars.push(encryptedChar);
    }
 
    // Join the encrypted characters into a string and save it
    document.getElementById('otp').value = encryptedChars.join('');
}

document.querySelector('form').addEventListener('submit', encrypt);



</script>
<!-- <script type="text/javascript">
    var isNavigationAction = false; // Flag to track page navigation action
    var unload_settings = {
        "url": "/clear_current_login",
        "method": "POST",
        "timeout": 0,
        "headers": {
          "Accept-Language": "application/json",
        },
        "data": {
          "_token": "{{ csrf_token() }}",
          "emp_code": document.getElementById('userId').value,
        }
    };
    window.addEventListener('beforeunload', function(e) {
        if (!isNavigationAction) {
            setTimeout(function() {}, 3000);
            $.ajax(unload_settings).done(function (response) {
              console.log(response);
            })
        }

    });
    // Event listener for link clicks
    document.addEventListener('click', function(e) {
        // Check if the clicked element is a hyperlink
        if (e.target.tagName.toLowerCase() === 'a') {
          // Set the flag to indicate a page navigation action
          isNavigationAction = true;
        }
    });

      // Event listener for form submissions
    document.addEventListener('submit', function(e) {
        // Check if the submitted element is a form
        if (e.target.tagName.toLowerCase() === 'form') {
          // Set the flag to indicate a page navigation action
          isNavigationAction = true;
        }
    });
</script> -->

@endsection
