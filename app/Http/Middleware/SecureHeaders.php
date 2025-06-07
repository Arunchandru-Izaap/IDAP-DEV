<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str; // Add this line at the top of your file

class SecureHeaders
{
	// Enumerate unwanted headers
	private $unwantedHeaderList = [
		'Server',
		'Connection',
	];

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$excludedUrls = [
			'approver/vq-export',
			'initiator/vq-export',
			'distribution/vq-export',
			'poc/vq-export',
			'ho/vq-export',
			'ho/cover-letter-pdf',
			'ho/price-sheet',
			'poc/cover-letter-pdf',
			'poc/price-sheet',
			'distribution/cover-letter-pdf',
			'distribution/price-sheet',
			'user/cover-letter-pdf',
			'user/price-sheet',
			'initiator/cover-letter-pdf',
			'initiator/price-sheet',
			'admin/license-download',
			'approver/criteriaExport',//added on 03052024,
			'initiator/newPriceSheet',//added on 23072024
			'approver/newPriceSheet',//added on 23072024
			'ho/newPriceSheet',//added on 23072024
			'poc/newPriceSheet',//added on 23072024
			'distribution/newPriceSheet',//added on 23072024,
			'initiator/pending_item_export',//added on 21092024
			'initiator/pending_inistitution_export',//added on 21092024
			'initiator/activity',
			'approver/activity',
			'poc/activity',
			'distribution/activity',
			'ho/activity',
			'initiator/download_institution',
			'initiator/download_stockist',
			'admin/download_institution',
			'admin/download_stockist',
			'admin/activity',
			'initiator/genereate_request_existing',
			'initiator/genereate_request',
			'initiator/CumulativeReportExport', //added on 28012025
			'approver/CumulativeReportExport', //added on 28012025
			'poc/CumulativeReportExport', //added on 28012025
			'distribution/CumulativeReportExport', //added on 28012025
			'admin/CumulativeReportExport', //added on 28012025
		];
		
		if (in_array($request->path(), $excludedUrls) || Str::startsWith($request->path(), $excludedUrls)) {
			return $next($request);
		}
		$response = $next($request);
		// dd($response);

		foreach ($this->unwantedHeaderList as $header){
			$response->headers->remove($header);
			// dd($header);
		}
		$response->header('Server', null);
		$response->header('X-Powered-By', null);
		// added on 18042024 start
		// Set Content-Security-Policy header with advanced configuration
		$csp = "default-src *; script-src * 'unsafe-inline' 'unsafe-eval';style-src * 'unsafe-inline';font-src * https://fonts.gstatic.com data:; img-src  * data: 'unsafe-inline'; connect-src * 'unsafe-inline'; frame-src *;";

		$response->headers->set('Content-Security-Policy', $csp);
		$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
		$response->headers->set('X-Content-Type-Options', 'nosniff');
		$response->headers->set('X-XSS-Protection', '1; mode=block');
		$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
		//added on 18042024 ends

		return $response;
	}

	public function startsWith($string, $prefix)
	{
		return strncmp($string, $prefix, strlen($prefix)) === 0;
	}
	
}