<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CaptchaController extends Controller
{
    public function generateCaptcha()
	{
	    // Generate CAPTCHA image
	    $image = imagecreatetruecolor(200, 50);
	    $background_color = imagecolorallocate($image, 255, 255, 255); // White background
	    $text_color = imagecolorallocate($image, 0, 0, 0); // Black text

	    // Fill the background with white color
		imagefilledrectangle($image, 0, 0, 199, 49, $background_color);
	    $captcha_text = substr(str_shuffle('abcdefghijkmnopqrstuvwxyz'), 0, 6); // CAPTCHA text
	    //imagestring($image, 5, 20, 20, $captcha_text, $text_color);
	    imagettftext($image, 20, 0, 10, 30, $text_color, 'fonts/arial.ttf', $captcha_text);
	    // Output the image
	    header('Content-Type: image/png');
	    imagepng($image);
	    imagedestroy($image);

	    // Store CAPTCHA text in session
	    Session::put('captcha', $captcha_text);
	}
	public function refreshCaptcha(Request $request)
	{
	    // Generate New CAPTCHA image
	    $image = imagecreatetruecolor(200, 50);
	    $background_color = imagecolorallocate($image, 255, 255, 255); // White background
	    $text_color = imagecolorallocate($image, 0, 0, 0); // Black text

	    // Fill the background with white color
	    imagefilledrectangle($image, 0, 0, 199, 49, $background_color);
	    $captcha_text = substr(str_shuffle('abcdefghijkmnopqrstuvwxyz'), 0, 6); // CAPTCHA text
	    imagettftext($image, 20, 0, 10, 30, $text_color, public_path('fonts/arial.ttf'), $captcha_text);

	    // Store CAPTCHA text in session
	    $request->session()->put('captcha', $captcha_text);

	    // Output the image
	    ob_start(); // Start buffering
	    imagepng($image); // Output the image to the buffer
	    $image_data = ob_get_clean(); // Get the buffered output and clean the buffer

	    // Destroy the image resource
	    imagedestroy($image);

	    // Return the captcha image data and text as a JSON response
	     return response()->json([
	        'captcha_image' => 'data:image/png;base64,' . base64_encode($image_data),
	    ]);
	}

}

