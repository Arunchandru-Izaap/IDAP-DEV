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
        margin-top: 1.1rem;
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

    <form class="login-form" id="userIdForm" method="POST" action="{{ route('verifyOtp') }}">
        
        @csrf()

        @if(Session::has('error'))
            <div class="alert alert-danger" role="alert">
                {!! Session::get('error') !!}
            </div>
        @endif

        <div class="form-group">
            <label>User Id:</label>
        </div>
        <div class="form-group">
            <input type="text" class="form-control" id="userId" name="userId" placeholder="User Id" />
            @error('userId')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <!-- captcha code starts here -->
        <div class="form-group">
            <label for="captcha">Please enter text shown as below:</label>
        </div>
        <div class="form-group">
            <img src="{{ route('captcha') }}" id="captcha-image" alt="CAPTCHA">
            <a href="#" id="refresh-captcha"><svg style="color: orange;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
              <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
            </svg>
        </a>
        </div>
        <div class="form-group">
            <input type="text" class="form-control" id="captcha" name="captcha" placeholder="Enter text as above" />
             @error('captcha')
                <span class="invalid-feedback" role="alert" style="display: block;">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <!-- captcha code ends here -->
        <button type="submit" class="btn btn-primary-ct">Login</button>

    </form>

  </div>
  <p class="copyright">© Copyright {{ date('Y') }} Sun Pharma. All rights reserved.</p>
</div>

<script>
     document.getElementById('refresh-captcha').addEventListener('click', function(event) {
        event.preventDefault();
        refreshCaptcha();
    });
    localStorage.removeItem('initiatorListSearch');
    localStorage.removeItem('approverListSearch');
    localStorage.removeItem('distributionListSearch');
     localStorage.removeItem('pocListSearch');
    $(document).ready(function(){
        
        $("#verifyOtpForm").hide();
    })

    $("#btnGenerateOtp").on("click", function(){
    // Need to hit an  api to request so that api is generated.
        let email = $("#email").val();
        var settings = {
                "url": "/generateOtp/"+"email",
                "method": "GET",
                "timeout": 0,
                "headers": {
                "Accept-Language": "application/json",
            }
        };
        $.ajax(settings).done(function (response) {
            $("#userIdForm").hide();
            $("#verifyOtpForm").show();
            console.log(response);
            
        });
       
    })
    
    function refreshCaptcha() {
    // Send an AJAX request to the server to refresh the captcha image
        $.ajax({
            url: '{{ route('refreshcaptcha') }}',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(data) {
                // Update the captcha image with the new image received from the server
                $('#captcha-image').attr('src', data.captcha_image);
            },
            error: function(xhr, status, error) {
                console.error('Error refreshing captcha:', error);
            }
        });
    }
</script>

@endsection
