<?php
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Models\Employee;
// use Auth;
// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/send-quotation', function () {
   
//     $details = [
//         'title' => 'Mail from ItSolutionStuff.com',
//         'body' => 'This is for testing email using smtp'
//     ];
   
//     \Mail::to('ashokkumarkes@gmail.com')->send(new \App\Mail\TestMail($details));
   
//     dd("Email is Sent.");
// });
Route::get('user-export/', [App\Http\Controllers\HomeController::class, 'export'])->name('user-export');
    // Route::get('/generate_token', function (Request $request) {
    //     if(auth()->user()){
    //     $user = User::where('email', auth()->user()->email)->first();
    //     $token = $user->createToken('myapptoken')->plainTextToken;
    //     $response = $token;
    //     }else{
    //         $response = "";
    //     }
    //     return $response;
    // });
Route::get('/',function(){
    // echo"test";
    // exit;
	if(Session::get('emp_name') != ''){
		// return redirect('/logout');	
        return redirect('/login')->with(Auth::logout());
	}else{
		return redirect('/login')->with(Auth::logout());
	} 
});

Route::post('/verifyOtp', [App\Http\Controllers\Auth\LoginController::class, 'verifyOtp'])->name('verifyOtp');

Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/test_mail', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'test_mail'])->name('test_mail');

// routes/web.php
// captcha code starts
Route::get('/captcha', [App\Http\Controllers\CaptchaController::class, 'generateCaptcha'])->name('captcha');
Route::get('/refresh-captcha', [App\Http\Controllers\CaptchaController::class, 'refreshCaptcha'])->name('refreshcaptcha');
// captcha code ends

//Route::get('/import_address', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'import_address'])->name('import_address');

Auth::routes();
Route::group(['prefix'=>'approver', 'middleware' => ['isUser','isApprover']],function() {
    Route::get('/listing/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'approverDetails'])->name('approverDetails');
    Route::get('/activity/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activityDetailsApprover'])->name('approverActivity');
    Route::get('/listing', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'approver'])->name('approver_listing');
    Route::get('/request_listing', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'getSKU'])->name('ceo_approver_listing');//for ceo request listing
    Route::get('/', [App\Http\Controllers\StaticPages\DashboardController::class, 'approverDashboard'])->name('approver_dashboard');
    // Route::get('/dashboard', [App\Http\Controllers\StaticPages\DashboardController::class, 'approverDashboard'])->name('approver_dashboard');
    Route::POST('/update_discount', [App\Http\Controllers\Api\VqListingController::class, 'updateDiscount']);
    Route::get('/pdms_discount', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'getPdmsDiscount'])->name('getPdmsDiscount');
    Route::POST('/delete_vq', [App\Http\Controllers\Api\VqListingController::class, 'deleteVQ']);
    Route::POST('/bulk_update', [App\Http\Controllers\Api\VqListingController::class, 'bulkUpdate']);
    Route::POST('/single_approve', [App\Http\Controllers\Api\VqListingController::class, 'singleApprove']);
    Route::POST('/bulk_approve', [App\Http\Controllers\Api\VqListingController::class, 'bulkApprove'])->name('bulk_approve');
    Route::POST('/add_comment', [App\Http\Controllers\Api\VqListingController::class, 'addCommentSku']);
    Route::GET('/vq_comment', [App\Http\Controllers\Api\VqListingController::class, 'vqComments']);
    Route::GET('/sku_comment', [App\Http\Controllers\Api\VqListingController::class, 'skuComments']);
    Route::get('/manage-request-by-sku',[App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class,'manageSku'])->name('manage-request-by-sku');
    Route::get('/vq-export', [App\Http\Controllers\Api\VqListingController::class, 'vqExport'])->name('vq-export-approver');
    Route::post('/update-sku',[App\Http\Controllers\Api\VqListingController::class, 'updateSku'])->name('update-sku');
    Route::post('/list-base-institute',[App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class,'listBaseInstitute'])->name('list-base-institute');
    Route::get('/vq_listing_approver_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'getApproverVQlistData'])->name('vq_detail_listing_approver_ajax');//added 16032024 for serverside datatable

    Route::get('/vq_detail_listing_approver_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'getApproverVQdetaillistData'])->name('getApproverVQdetaillistData');//added 16032024 for serverside datatable

    Route::get('/manage_request_sku_criteria', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'getApproverVQdetailListCriteria'])->name('getApproverVQdetailListCriteria');//added 03062024 for serverside datatable

    Route::get('/criteriaExport', [App\Http\Controllers\Api\VqListingController::class, 'criteriaExport'])->name('vq-export-criteria');//for ceo export
    Route::POST('/single_approve_criteria', [App\Http\Controllers\Api\VqListingController::class, 'singleApproveCriteria']);//for ceo approve and submit
    Route::post('/filter-latest-report', [App\Http\Controllers\Api\VqListingController::class, 'filterLatestReport'])->name('filter-latest-report');
    Route::get('/newPriceSheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'newPriceSheet']);

    Route::post('/activity_filter', [App\Http\Controllers\Api\VqListingController::class, 'activity_filter'])->name('activity_filter');
    /** product wise cumulative report */
    Route::get('/product_cumulative_report', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'productwiseCumulativeReport'])->name('approver_product_cumulative_report');
    Route::POST('/cumulative_report_new', [App\Http\Controllers\Api\VqListingController::class, 'cumulative_report_new'])->name('cumulative_report_new');
    Route::get('/CumulativeReportExport', [App\Http\Controllers\Api\VqListingController::class, 'CumulativeReportExport'])->name('approver-export-cumulative-report');//for ceo export
    Route::get('/filter_division_by_brand', [App\Http\Controllers\Api\VqListingController::class, 'filter_division_by_brand'])->name('filter_division_by_brand');
    Route::get('/cumulative_report_get_stockist', [App\Http\Controllers\Api\VqListingController::class, 'cumulative_report_get_stockist'])->name('cumulative_report_get_stockist');
    Route::post('/set_session_filter_report', [App\Http\Controllers\Api\VqListingController::class, 'set_session_filter_report'])->name('set_session_filter_report');
});

Route::group(['prefix'=>'initiator', 'middleware' => ['isUser','isInitiator']],function() {
    Route::get('/listing', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'initiator'])->name('initiator_listing');
    Route::get('/listing/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'initiatorDetails'])->name('initiatorDetails');
    Route::get('/activity/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activityDetailsInitiator'])->name('activityDetailsInitiator');
    Route::get('/', [App\Http\Controllers\StaticPages\DashboardController::class, 'initiatorDashboard'])->name('initiator_dashboard');
    // Route::get('/dashboard', [App\Http\Controllers\StaticPages\DashboardController::class, 'initiatorDashboard'])->name('initiator_dashboard');
    Route::get('/create_request', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'create_request'])->name('create_request');
    Route::get('/getVq', [App\Http\Controllers\Api\VqListingController::class, 'getInitiatorVqListing']);
    Route::get('/createVQ', [App\Http\Controllers\Api\VqListingController::class, 'createVq']);
    Route::get('/getVqDetail', [App\Http\Controllers\Api\VqListingController::class, 'getInitiatorVqDetail']);
    Route::get('/approve_vq', [App\Http\Controllers\Api\VqListingController::class, 'approveVq']);
    Route::get('/send_metis', [App\Http\Controllers\Api\VqListingController::class, 'SendApprovedVq']);
    Route::get('/reinitiate/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'reinitiate_listing'])->name('reinitiate_request');
    Route::POST('/reinitiate/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'reinitiate_listing'])->name('reinitiate_request');

    Route::get('/parent_vq_checker', [App\Http\Controllers\Api\VqListingController::class, 'parentVqChecker']);
    Route::post('/reinitiateNewVQ', [App\Http\Controllers\Api\VqListingController::class, 'reinitiateNewVQ']);
    Route::post('/reinitiateVQWithNewPack', [App\Http\Controllers\Api\VqListingController::class, 'reinitiateVQWithNewPack']);
    Route::get('/modify_stockist/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'viewStockist'])->name('modify_stockist');
    Route::post('/save_stockist', [App\Http\Controllers\Api\VqListingController::class, 'saveStockist']);

    Route::get('/payment_mode/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingStockistController::class, 'paymentMode'])->name('paymentMode');
    Route::get('/edit_payment_mode/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingStockistController::class, 'editPaymentMode'])->name('editPaymentMode');
    Route::post('/saveSkuPaymentMode', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingStockistController::class, 'saveSkuPaymentMode'])->name('saveSkuPaymentMode');

    Route::get('/view_poc/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'viewPoc'])->name('view_poc');
    Route::get('/vq_comment', [App\Http\Controllers\Api\VqListingController::class, 'vqComments']);
    Route::get('/sku_comment', [App\Http\Controllers\Api\VqListingController::class, 'skuComments']);
    Route::POST('/add_comment', [App\Http\Controllers\Api\VqListingController::class, 'addCommentSku']);
    Route::get('/price-sheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'export'])->name('price-sheet');
    Route::get('/cover-letter-pdf/{id}', [App\Http\Controllers\Api\VqListingController::class, 'downloadPDF'])->name('cover-letter-pdf');
    Route::POST('/reinitiate', [App\Http\Controllers\Api\VqListingController::class, 'reinitiateVQApi']);
    Route::get('/send-quotation', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'sendQuotation']);	
    Route::get('/vq-export', [App\Http\Controllers\Api\VqListingController::class, 'vqExport'])->name('vq-export');

    Route::POST('/add_new_counter', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'addNewCounter']);
    Route::get('/history-report', [App\Http\Controllers\Api\VqListingController::class, 'historyReport'])->name('history-report');

    Route::get('/deleteVQ/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'deleteCounter']);

    Route::get('/vq_listing_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'getVQlistData'])->name('vq_listing_ajax');

    Route::get('/vq_detail_listing_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'getVQdetaillistData'])->name('getVQdetaillistData');
    Route::get('/discount_margin_view_logs_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'getDiscountMarginViewLogData'])->name('getDiscountMarginViewLogData');
    

    // added for add-del stockist 20062024 starts
    Route::get('/edit_stockist/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'editStockist'])->name('edit_stockist');
    Route::post('/edit_stockist', [App\Http\Controllers\Api\VqListingController::class, 'editStockist']);
    // added for add-del stockist 20062024 ends

    Route::post('/check_financial_year_report', [App\Http\Controllers\Api\VqListingController::class, 'checkFinancialReport']);//added for finacial year report 06072024
    Route::post('/financial-history-report', [App\Http\Controllers\Api\VqListingController::class, 'financialhistoryReport'])->name('financial-history-report');
    Route::get('/genereate_request', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'genereate_request'])->name('genereate_request');
    Route::get('/generate_vq_data', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'generate_vq_data'])->name('generate_vq_data');
    Route::post('/generate_vq_save_change', [App\Http\Controllers\Api\VqListingController::class, 'generate_vq_save_change']);
    Route::post('/generate_vq_send_quotation', [App\Http\Controllers\Api\VqListingController::class, 'generate_vq_send_quotation']);
    Route::get('/newPriceSheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'newPriceSheet']);
    Route::post('/reinitiateVQCopyCounter', [App\Http\Controllers\Api\VqListingController::class, 'reinitiateVQCopyCounter']);
    Route::post('/filter-latest-report-initiator', [App\Http\Controllers\Api\VqListingController::class, 'filterLatestReport'])->name('filter-latest-report-initiator');
    Route::post('/filter-historical-report-initiator', [App\Http\Controllers\Api\VqListingController::class, 'filterHistoricalReport'])->name('filter-historical-report-initiator');
    Route::post('/checkMultiInstituteNewItem', [App\Http\Controllers\Api\VqListingController::class, 'checkMultiInstituteNewItemFn'])->name('checkMultiInstituteNewItem');

    // admin functionality for initiator starts
    Route::get('/employee', [App\Http\Controllers\Admin\EmployeeController::class, 'index'])->name('initiator-employee');
    Route::get('/employee-list', [App\Http\Controllers\Admin\EmployeeController::class, 'list'])->name('initiator-employee-list');
    Route::post('/employee-store', [App\Http\Controllers\Admin\EmployeeController::class, 'store'])->name('initiator-employee-store');
    Route::get('/employee-edit/{id?}', [App\Http\Controllers\Admin\EmployeeController::class, 'edit'])->name('initiator-employee-edit');
    Route::post('/employee-update', [App\Http\Controllers\Admin\EmployeeController::class, 'update'])->name('initiator-employee-update');
    Route::get('/employee-delete/{id?}', [App\Http\Controllers\Admin\EmployeeController::class, 'delete'])->name('initiator-employee-delete');

    Route::get('/signature', [App\Http\Controllers\Admin\SignatureController::class, 'index'])->name('initiator-signature');
    Route::get('/signature-list', [App\Http\Controllers\Admin\SignatureController::class, 'list'])->name('initiator-signature-list');
    Route::post('/signature-store', [App\Http\Controllers\Admin\SignatureController::class, 'store'])->name('initiator-signature-store');
    Route::get('/signature-edit/{id?}', [App\Http\Controllers\Admin\SignatureController::class, 'edit'])->name('initiator-signature-edit');
    Route::post('/signature-update', [App\Http\Controllers\Admin\SignatureController::class, 'update'])->name('initiator-signature-update');
    Route::get('/signature-delete/{id?}', [App\Http\Controllers\Admin\SignatureController::class, 'delete'])->name('initiator-signature-delete');

    Route::get('/poc-master', [App\Http\Controllers\Admin\PocMasterController::class, 'index'])->name('initiator-poc-master');
    Route::get('/poc-master-list', [App\Http\Controllers\Admin\PocMasterController::class, 'list'])->name('initiator-poc-master-list');
    Route::post('/poc-master-store', [App\Http\Controllers\Admin\PocMasterController::class, 'store'])->name('initiator-poc-master-store');
    Route::get('/poc-master-edit/{id?}', [App\Http\Controllers\Admin\PocMasterController::class, 'edit'])->name('initiator-poc-master-edit');
    Route::post('/poc-master-update', [App\Http\Controllers\Admin\PocMasterController::class, 'update'])->name('initiator-poc-master-update');
    Route::get('/poc-master-delete/{id?}', [App\Http\Controllers\Admin\PocMasterController::class, 'delete'])->name('initiator-poc-master-delete');

    Route::get('/max-discount-cap', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'index'])->name('initiator-max-discount-cap');
    Route::get('/max-discount-cap-list', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'list'])->name('initiator-max-discount-cap-list');
    Route::post('/max-discount-cap-store', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'store'])->name('initiator-max-discount-cap-store');
    Route::get('/max-discount-cap-edit/{id?}', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'edit'])->name('initiator-max-discount-cap-edit');
    Route::post('/max-discount-cap-update', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'update'])->name('initiator-max-discount-cap-update');
    Route::get('/max-discount-cap-delete/{id?}', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'delete'])->name('initiator-max-discount-cap-delete');

    Route::get('/duration', [App\Http\Controllers\Admin\VqDurationController::class, 'index'])->name('initiator-duration');
    Route::get('/duration-list', [App\Http\Controllers\Admin\VqDurationController::class, 'list'])->name('initiator-duration-list');
    Route::post('/duration-store', [App\Http\Controllers\Admin\VqDurationController::class, 'store'])->name('initiator-duration-store');
    Route::get('/duration-edit/{id?}', [App\Http\Controllers\Admin\VqDurationController::class, 'edit'])->name('initiator-duration-edit');
    Route::post('/duration-update', [App\Http\Controllers\Admin\VqDurationController::class, 'update'])->name('initiator-duration-update');
    Route::get('/duration-delete/{id?}', [App\Http\Controllers\Admin\VqDurationController::class, 'delete'])->name('initiator-duration-delete');

    Route::get('/ignored-institutions', [App\Http\Controllers\Admin\VqIgnoredController::class, 'index'])->name('initiator-ignored-institutions');
    Route::get('/ignored-institutions-list', [App\Http\Controllers\Admin\VqIgnoredController::class, 'list'])->name('initiator-ignored-institutions-list');
    Route::post('/ignored-institutions-store', [App\Http\Controllers\Admin\VqIgnoredController::class, 'store'])->name('initiator-ignored-institutions-store');
    Route::get('/ignored-institutions-edit/{id?}', [App\Http\Controllers\Admin\VqIgnoredController::class, 'edit'])->name('initiator-ignored-institutions-edit');
    Route::post('/ignored-institutions-update', [App\Http\Controllers\Admin\VqIgnoredController::class, 'update'])->name('initiator-ignored-institutions-update');
    Route::get('/ignored-institutions-delete/{id?}', [App\Http\Controllers\Admin\VqIgnoredController::class, 'delete'])->name('initiator-ignored-institutions-delete');

    Route::get('/discount-margin', [App\Http\Controllers\Admin\DiscountMarginController::class, 'index'])->name('initiator-discount-margin');
    Route::get('/discount-margin-list', [App\Http\Controllers\Admin\DiscountMarginController::class, 'list'])->name('initiator-discount-margin-list');
    Route::post('/discount-margin-store', [App\Http\Controllers\Admin\DiscountMarginController::class, 'store'])->name('initiator-discount-margin-store');
    Route::get('/discount-margin-edit/{id?}', [App\Http\Controllers\Admin\DiscountMarginController::class, 'edit'])->name('initiator-discount-margin-edit');
    Route::post('/discount-margin-update', [App\Http\Controllers\Admin\DiscountMarginController::class, 'update'])->name('initiator-discount-margin-update');
    Route::get('/discount-margin-delete/{id?}', [App\Http\Controllers\Admin\DiscountMarginController::class, 'delete'])->name('initiator-discount-margin-delete');

    Route::get('/approval-email-schedule', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'index'])->name('initiator-approval-email-schedule');
    Route::get('/approval-email-schedule-list', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'list'])->name('initiator-approval-email-schedule-list');
    Route::post('/approval-email-schedule-store', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'store'])->name('initiator-approval-email-schedule-store');
    Route::get('/approval-email-schedule-edit/{id?}', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'edit'])->name('initiator-approval-email-schedule-edit');
    Route::post('/approval-email-schedule-update', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'update'])->name('initiator-approval-email-schedule-update');
    Route::get('/approval-email-schedule-delete/{id?}', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'delete'])->name('initiator-approval-email-schedule-delete');
    // admin functionality for initiator ends

    Route::get('/adjust_workflow', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'adjust_workflow'])->name('adjust_workflow_initiator');
    Route::get('/get_pending_vq_data_workflow', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'get_pending_vq_data_workflow'])->name('get_pending_vq_data_workflow_initiator');
    Route::post('/workflow_adjust', [App\Http\Controllers\Api\VqListingController::class, 'workflow_adjust'])->name('workflow_adjust_initiator');

    Route::get('/enable_locking', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'enable_locking'])->name('enable_locking_initiator');
    Route::post('/change_locking_period', [App\Http\Controllers\Api\VqListingController::class, 'change_locking_period'])->name('change_locking_period_initiator');

    Route::get('/genereate_request_existing', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'genereate_request_existing'])->name('genereate_request_existing');
    Route::get('/generate_vq_data_existing', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'generate_vq_data_existing'])->name('generate_vq_data_existing');

    Route::get('/pending_item_export', [App\Http\Controllers\Api\VqListingController::class, 'pendingItemExport'])->name('pending-item-export');
    Route::get('/pending_inistitution_export', [App\Http\Controllers\Api\VqListingController::class, 'pendingInstitutionExport'])->name('pending-institution-export');

    Route::post('/activity_filter', [App\Http\Controllers\Api\VqListingController::class, 'activity_filter'])->name('activity_filter');


    Route::post('/make_parent', [App\Http\Controllers\Api\VqListingController::class, 'make_parent'])->name('initator-make-parent');

    Route::post('/stockist_update_institution', [App\Http\Controllers\Api\VqListingController::class, 'stockist_update_institution'])->name('stockist_update_institution');
    Route::get('/ajax/stockists/{institutionId}', [App\Http\Controllers\Api\VqListingController::class, 'getStockists'])->name('ajax.stockists');
    Route::put('/stockists/{stockistCode}', [App\Http\Controllers\Api\VqListingController::class, 'updateStockist'])->name('stockists.update');
    Route::get('/stockist_update', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'create_stock'])->name('stockist_initiator');
    Route::get('/ajax/payment/{stockistId}', [App\Http\Controllers\Api\VqListingController::class, 'getPayment'])->name('ajax.payment');
    Route::get('/download_institution/{id}',  [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'downloadPDF'])->name('initiator.download');
    Route::get('/download_stockist/{id}',  [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'stockist_downloadPDF'])->name('initiator.download');

     Route::get('/activity_tracker', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activity_tracker_new'])->name('activity_tracker_new_initiator');
     Route::post('/activity_filter_new', [App\Http\Controllers\Api\VqListingController::class, 'activity_filter_new'])->name('activity_filter_new');
     
     Route::post('/generate_vq_save_selected_paymode', [App\Http\Controllers\Api\VqListingController::class, 'generate_vq_save_selected_paymode'])->name('generate_vq_save_selected_paymode');
     Route::post('/generate_vq_save_selected_stockist', [App\Http\Controllers\Api\VqListingController::class, 'generate_vq_save_selected_stockist'])->name('generate_vq_save_selected_stockist');

     /*Route::get('/activity_tracker', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activityDetails'])->name('activityDetails');
    Route::post('/activity_tracker', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activityDetailsJson'])->name('activityDetailsJson');*/

    Route::get('/initiate_date', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'initiateDate'])->name('initiate_date');
    Route::get('/initiate_date_add', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'initiateDateAdd'])->name('initiate_date_add');
    Route::post('/initiate_date', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'initiateDateUpdate'])->name('initiate_date_update');

    /** product wise cumulative report */
    Route::get('/product_cumulative_report', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'productwiseCumulativeReport'])->name('product_cumulative_report');
    Route::POST('/cumulative_report_new', [App\Http\Controllers\Api\VqListingController::class, 'cumulative_report_new'])->name('cumulative_report_new');
    Route::get('/CumulativeReportExport', [App\Http\Controllers\Api\VqListingController::class, 'CumulativeReportExport'])->name('initiator-export-cumulative-report');//for ceo export
    Route::get('/filter_division_by_brand', [App\Http\Controllers\Api\VqListingController::class, 'filter_division_by_brand'])->name('filter_division_by_brand');
    Route::get('/cumulative_report_get_stockist', [App\Http\Controllers\Api\VqListingController::class, 'cumulative_report_get_stockist'])->name('cumulative_report_get_stockist');
    Route::post('/set_session_filter_report', [App\Http\Controllers\Api\VqListingController::class, 'set_session_filter_report'])->name('set_session_filter_report');
    
    Route::get('/initiator_bulk_counter_update', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'initiator_bulk_counter_update'])->name('initiator_bulk_counter_update');
    Route::get('/bulk_counter_update_vq_data', [App\Http\Controllers\Api\VqListingController::class, 'bulk_counter_update_vq_data'])->name('bulk_counter_update_vq_data');   
    Route::POST('/BulkUpdateCounterSendQuotation', [App\Http\Controllers\Api\VqListingController::class, 'BulkUpdateCounterSendQuotation'])->name('BulkUpdateCounterSendQuotation');   
    
    Route::POST('/approvalemailscheduleDays', [App\Http\Controllers\Api\VqListingController::class, 'approvalemailscheduleDays'])->name('approvalemailscheduleDays');   

    Route::get('/workflow_adjust_product_wise', [App\Http\Controllers\Admin\WorkflowAdjustController::class, 'workflow_adjust_product_wise'])->name('workflow_adjust_product_wise');
    Route::get('/get_pending_vq_data_workflow_product_wise', [App\Http\Controllers\Admin\WorkflowAdjustController::class, 'get_pending_vq_data_workflow_product_wise'])->name('get_pending_vq_data_workflow_product_wise_initiator');
    Route::post('/workflow_adjust_forward_backward_levels', [App\Http\Controllers\Admin\WorkflowAdjustController::class, 'workflow_adjust_forward_backward_levels'])->name('workflow_adjust_forward_backward_levels');

    Route::get('/productwise_discard', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'productwise_discard_data'])->name('productwise_discard_data');
    Route::get('/productwise_discard_getdata', [App\Http\Controllers\Api\VqListingController::class, 'productwise_discard_getdata'])->name('productwise_discard_getdata');
    Route::post('/productwise_discard_selection', [App\Http\Controllers\Api\VqListingController::class, 'productwise_discard_selection'])->name('productwise_discard_selection');
});


Route::group(['prefix'=>'poc', 'middleware' => ['isUser','isPoc']],function() {
    Route::get('/listing', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'pocUser'])->name('poc_listing');
    Route::get('/listing/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'pocDetails'])->name('pocDetails');
    Route::get('/activity/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activityDetailsPoc'])->name('activityDetailsPoc');
    Route::get('/', [App\Http\Controllers\StaticPages\DashboardController::class, 'pocDashboard'])->name('poc_dashboard');
    // Route::get('/dashboard', [App\Http\Controllers\StaticPages\DashboardController::class, 'initiatorDashboard'])->name('initiator_dashboard');
    Route::get('/vq_comment', [App\Http\Controllers\Api\VqListingController::class, 'vqComments']);
    Route::get('/sku_comment', [App\Http\Controllers\Api\VqListingController::class, 'skuComments']);
   
    Route::get('/price-sheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'export'])->name('price-sheet-poc');
    Route::get('/cover-letter-pdf/{id}', [App\Http\Controllers\Api\VqListingController::class, 'downloadPDF'])->name('cover-letter-pdf-poc');
    Route::get('poc_feedback', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'pocFeedbackForm'])->name('poc_feedback');
    Route::post('poc_feedback', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'savePocFeedback'])->name('poc_feedback');
    Route::get('/history-report', [App\Http\Controllers\Api\VqListingController::class, 'historyReport'])->name('history-report');
    Route::get('/vq-export', [App\Http\Controllers\Api\VqListingController::class, 'vqExport'])->name('vq-export');
    Route::get('/newPriceSheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'newPriceSheet']);
    Route::post('/filter-latest-report-poc', [App\Http\Controllers\Api\VqListingController::class, 'filterLatestReportDP'])->name('filter-latest-report-poc');
    Route::post('/activity_filter', [App\Http\Controllers\Api\VqListingController::class, 'activity_filter'])->name('activity_filter');
    Route::get('/institutions/search', [App\Http\Controllers\Api\VqListingController::class, 'searchInstitutions']);
    /** product wise cumulative report */
    Route::get('/product_cumulative_report', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'productwiseCumulativeReport'])->name('poc_product_cumulative_report');
    Route::POST('/cumulative_report_new', [App\Http\Controllers\Api\VqListingController::class, 'cumulative_report_new'])->name('cumulative_report_new');
    Route::get('/CumulativeReportExport', [App\Http\Controllers\Api\VqListingController::class, 'CumulativeReportExport'])->name('poc-export-cumulative-report');//for ceo export
    Route::get('/filter_division_by_brand', [App\Http\Controllers\Api\VqListingController::class, 'filter_division_by_brand'])->name('filter_division_by_brand');
    Route::get('/cumulative_report_get_stockist', [App\Http\Controllers\Api\VqListingController::class, 'cumulative_report_get_stockist'])->name('cumulative_report_get_stockist');
    Route::post('/set_session_filter_report', [App\Http\Controllers\Api\VqListingController::class, 'set_session_filter_report'])->name('set_session_filter_report');
});

Route::group(['prefix'=>'distribution', 'middleware' => ['isUser','isDistribution']],function() {
    Route::get('/listing', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'distributionUser'])->name('distribution_listing');
    Route::get('/listing/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'distributionDetails'])->name('distributionDetails');
    Route::get('/activity/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activityDetailsDistribution'])->name('activityDetailsDistribution');
    Route::get('/', [App\Http\Controllers\StaticPages\DashboardController::class, 'distributionDashboard'])->name('distribution_dashboard');
    Route::get('/vq_comment', [App\Http\Controllers\Api\VqListingController::class, 'vqComments']);
    Route::get('/sku_comment', [App\Http\Controllers\Api\VqListingController::class, 'skuComments']);
   
    Route::get('/price-sheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'export'])->name('price-sheet-distribution');
    Route::get('/cover-letter-pdf/{id}', [App\Http\Controllers\Api\VqListingController::class, 'downloadPDF'])->name('cover-letter-pdf-distribution');
    Route::get('/history-report', [App\Http\Controllers\Api\VqListingController::class, 'historyReport'])->name('history-report');
    Route::get('/vq-export', [App\Http\Controllers\Api\VqListingController::class, 'vqExport'])->name('vq-export');

    Route::get('/vq_listing_distribution_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'getDistributionVQlistData'])->name('vq_detail_listing_distribution_ajax');//added 19032024 for serverside datatable

    Route::get('/vq_detail_listing_distribution_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'getdistributionVQdetaillistData'])->name('getDistributionVQdetaillistData');//added 19032024 for serverside datatable
    Route::get('/newPriceSheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'newPriceSheet']);
    Route::post('/filter-latest-report-distribution', [App\Http\Controllers\Api\VqListingController::class, 'filterLatestReportDP'])->name('filter-latest-report-distribution');
    Route::post('/activity_filter', [App\Http\Controllers\Api\VqListingController::class, 'activity_filter'])->name('activity_filter');
    /** product wise cumulative report */
    Route::get('/product_cumulative_report', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'productwiseCumulativeReport'])->name('distribution_product_cumulative_report');
    Route::POST('/cumulative_report_new', [App\Http\Controllers\Api\VqListingController::class, 'cumulative_report_new'])->name('cumulative_report_new');
    Route::get('/CumulativeReportExport', [App\Http\Controllers\Api\VqListingController::class, 'CumulativeReportExport'])->name('distribution-export-cumulative-report');//for ceo export
    Route::get('/filter_division_by_brand', [App\Http\Controllers\Api\VqListingController::class, 'filter_division_by_brand'])->name('filter_division_by_brand');
    Route::get('/cumulative_report_get_stockist', [App\Http\Controllers\Api\VqListingController::class, 'cumulative_report_get_stockist'])->name('cumulative_report_get_stockist');
    Route::post('/set_session_filter_report', [App\Http\Controllers\Api\VqListingController::class, 'set_session_filter_report'])->name('set_session_filter_report');
});

Route::group(['prefix'=>'ho', 'middleware' => ['isUser','isHo']],function() {
    Route::get('/listing', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'hoUser'])->name('ho_listing');
    Route::get('/listing/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'hoDetails'])->name('hoDetails');
    Route::get('/activity/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activityDetailsHo'])->name('activityDetailsHo');
    Route::get('/', [App\Http\Controllers\StaticPages\DashboardController::class, 'hoDashboard'])->name('ho_dashboard');
    Route::get('/vq_comment', [App\Http\Controllers\Api\VqListingController::class, 'vqComments']);
    Route::get('/sku_comment', [App\Http\Controllers\Api\VqListingController::class, 'skuComments']);
    Route::get('/latest-report', [App\Http\Controllers\Api\VqListingController::class, 'latestReport'])->name('latest-report');
    Route::get('/history-report', [App\Http\Controllers\Api\VqListingController::class, 'historyReport'])->name('history-report');
    Route::get('/price-sheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'export'])->name('price-sheet-ho');
    Route::get('/cover-letter-pdf/{id}', [App\Http\Controllers\Api\VqListingController::class, 'downloadPDF'])->name('cover-letter-pdf-ho');
    Route::get('/vq-export', [App\Http\Controllers\Api\VqListingController::class, 'vqExport'])->name('vq-export');

    Route::get('/vq_listing_ho_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'getHoVQlistData'])->name('vq_detail_listing_ho_ajax');//added 18032024 for serverside datatable

    Route::get('/vq_detail_listing_ho_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'getHoVQdetaillistData'])->name('getHoVQdetaillistData');//added 18032024 for serverside datatable
    Route::get('/newPriceSheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'newPriceSheet']);

});
Route::get('/phpinfo', function () {
    phpinfo();
});
Route::group(['prefix'=>'user', 'middleware' => ['isUser','isNormalUser']],function() {
    Route::get('/listing', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'normalUser'])->name('user_listing');
    Route::get('/listing/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'userDetails'])->name('userDetails');
    Route::get('/activity/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activityDetailsUser']);
    Route::get('/', [App\Http\Controllers\StaticPages\DashboardController::class, 'userDashboard'])->name('user_dashboard');
    // Route::get('/dashboard', [App\Http\Controllers\StaticPages\DashboardController::class, 'userDashboard'])->name('user_dashboard');
    Route::get('/getVq', [App\Http\Controllers\Api\VqListingController::class, 'getInitiatorVqListing']);
    Route::get('/getVqDetail', [App\Http\Controllers\Api\VqListingController::class, 'getInitiatorVqDetail']);
    Route::get('/modify_stockist/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'viewStockist'])->name('modify_stockist');
    Route::post('/save_stockist', [App\Http\Controllers\Api\VqListingController::class, 'saveStockist']);
    Route::get('/view_poc/{id}', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'viewPoc']);
    Route::get('/vq_comment', [App\Http\Controllers\Api\VqListingController::class, 'vqComments']);
    Route::get('/sku_comment', [App\Http\Controllers\Api\VqListingController::class, 'skuComments']);
    Route::get('/price-sheet/{id}', [App\Http\Controllers\Api\VqListingController::class, 'export'])->name('user-price-sheet');
    Route::get('/cover-letter-pdf/{id}', [App\Http\Controllers\Api\VqListingController::class, 'downloadPDF'])->name('user-cover-letter-pdf');
});

Route::group(['prefix' =>'admin',  'middleware' => ['isAdmin']],function() {

    Route::get('/user-register',function(){
        return view('admin.pages.register');
    });
	if(env('APP_URL') == 'localhost/idap'){
		Route::get('/', [App\Http\Controllers\HomeController::class,'index'])->name('home');
	}else{
		Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('home');	
	}
    
    Route::get('/institution', [App\Http\Controllers\Admin\InstitutionController::class, 'index'])->name('institution');
    Route::get('/list', [App\Http\Controllers\Admin\InstitutionController::class, 'list'])->name('list');
    Route::post('/store', [App\Http\Controllers\Admin\InstitutionController::class, 'store'])->name('store');
    Route::get('/edit/{id?}', [App\Http\Controllers\Admin\InstitutionController::class, 'edit'])->name('edit');
    Route::post('/update', [App\Http\Controllers\Admin\InstitutionController::class, 'update'])->name('update');
    Route::get('/delete/{id?}', [App\Http\Controllers\Admin\InstitutionController::class, 'delete'])->name('delete');

    Route::get('/stockist', [App\Http\Controllers\Admin\StockistMasterController::class, 'index'])->name('stockist');
    Route::get('/stockist-list', [App\Http\Controllers\Admin\StockistMasterController::class, 'list'])->name('stockist-list');
    Route::post('/stockist-store', [App\Http\Controllers\Admin\StockistMasterController::class, 'store'])->name('stockist-store');
    Route::get('/stockist-edit/{id?}', [App\Http\Controllers\Admin\StockistMasterController::class, 'edit'])->name('stockist-edit');
    Route::post('/stockist-update', [App\Http\Controllers\Admin\StockistMasterController::class, 'update'])->name('stockist-update');
    Route::get('/stockist-delete/{id?}', [App\Http\Controllers\Admin\StockistMasterController::class, 'delete'])->name('stockist-delete');

    Route::get('/duration', [App\Http\Controllers\Admin\VqDurationController::class, 'index'])->name('duration');
    Route::get('/duration-list', [App\Http\Controllers\Admin\VqDurationController::class, 'list'])->name('duration-list');
    Route::post('/duration-store', [App\Http\Controllers\Admin\VqDurationController::class, 'store'])->name('duration-store');
    Route::get('/duration-edit/{id?}', [App\Http\Controllers\Admin\VqDurationController::class, 'edit'])->name('duration-edit');
    Route::post('/duration-update', [App\Http\Controllers\Admin\VqDurationController::class, 'update'])->name('duration-update');
    Route::get('/duration-delete/{id?}', [App\Http\Controllers\Admin\VqDurationController::class, 'delete'])->name('duration-delete');

    Route::get('/employee', [App\Http\Controllers\Admin\EmployeeController::class, 'index'])->name('employee');
    Route::get('/employee-list', [App\Http\Controllers\Admin\EmployeeController::class, 'list'])->name('employee-list');
    Route::post('/employee-store', [App\Http\Controllers\Admin\EmployeeController::class, 'store'])->name('employee-store');
    Route::get('/employee-edit/{id?}', [App\Http\Controllers\Admin\EmployeeController::class, 'edit'])->name('employee-edit');
    Route::post('/employee-update', [App\Http\Controllers\Admin\EmployeeController::class, 'update'])->name('employee-update');
    Route::get('/employee-delete/{id?}', [App\Http\Controllers\Admin\EmployeeController::class, 'delete'])->name('employee-delete');

    Route::get('/signature', [App\Http\Controllers\Admin\SignatureController::class, 'index'])->name('signature');
    Route::get('/signature-list', [App\Http\Controllers\Admin\SignatureController::class, 'list'])->name('signature-list');
    Route::post('/signature-store', [App\Http\Controllers\Admin\SignatureController::class, 'store'])->name('signature-store');
    Route::get('/signature-edit/{id?}', [App\Http\Controllers\Admin\SignatureController::class, 'edit'])->name('signature-edit');
    Route::post('/signature-update', [App\Http\Controllers\Admin\SignatureController::class, 'update'])->name('signature-update');
    Route::get('/signature-delete/{id?}', [App\Http\Controllers\Admin\SignatureController::class, 'delete'])->name('signature-delete');


    Route::get('/special-price', [App\Http\Controllers\Admin\SpecialPriceController::class, 'index'])->name('special-price');
    Route::get('/special-price-list', [App\Http\Controllers\Admin\SpecialPriceController::class, 'list'])->name('special-price-list');
    Route::post('/special-price-store', [App\Http\Controllers\Admin\SpecialPriceController::class, 'store'])->name('special-price-store');
    Route::get('/special-price-edit/{id?}', [App\Http\Controllers\Admin\SpecialPriceController::class, 'edit'])->name('special-price-edit');
    Route::post('/special-price-update', [App\Http\Controllers\Admin\SpecialPriceController::class, 'update'])->name('special-price-update');
    Route::get('/special-price-delete/{id?}', [App\Http\Controllers\Admin\SpecialPriceController::class, 'delete'])->name('special-price-delete');

    Route::get('/ceiling-master', [App\Http\Controllers\Admin\ceilingMasterController::class, 'index'])->name('ceiling-master');
    Route::get('/ceiling-master-list', [App\Http\Controllers\Admin\ceilingMasterController::class, 'list'])->name('ceiling-master-list');
    Route::post('/ceiling-master-store', [App\Http\Controllers\Admin\ceilingMasterController::class, 'store'])->name('ceiling-master-store');
    Route::get('/ceiling-master-edit/{id?}', [App\Http\Controllers\Admin\ceilingMasterController::class, 'edit'])->name('ceiling-master-edit');
    Route::post('/ceiling-master-update', [App\Http\Controllers\Admin\ceilingMasterController::class, 'update'])->name('ceiling-master-update');
    Route::get('/ceiling-master-delete/{id?}', [App\Http\Controllers\Admin\ceilingMasterController::class, 'delete'])->name('ceiling-master-delete');

    Route::get('/poc-master', [App\Http\Controllers\Admin\PocMasterController::class, 'index'])->name('poc-master');
    Route::get('/poc-master-list', [App\Http\Controllers\Admin\PocMasterController::class, 'list'])->name('poc-master-list');
    Route::post('/poc-master-store', [App\Http\Controllers\Admin\PocMasterController::class, 'store'])->name('poc-master-store');
    Route::get('/poc-master-edit/{id?}', [App\Http\Controllers\Admin\PocMasterController::class, 'edit'])->name('poc-master-edit');
    Route::post('/poc-master-update', [App\Http\Controllers\Admin\PocMasterController::class, 'update'])->name('poc-master-update');
    Route::get('/poc-master-delete/{id?}', [App\Http\Controllers\Admin\PocMasterController::class, 'delete'])->name('poc-master-delete');

    Route::get('/last-year-price', [App\Http\Controllers\Admin\LastYearPriceController::class, 'index'])->name('last-year-price');
    Route::get('/get_last_year', [App\Http\Controllers\Admin\LastYearPriceController::class, 'allLastYear'])->name('last-year-price-list-new');
    Route::get('/last-year-price-list', [App\Http\Controllers\Admin\LastYearPriceController::class, 'list'])->name('last-year-price-list');
    Route::post('/last-year-price-store', [App\Http\Controllers\Admin\LastYearPriceController::class, 'store'])->name('last-year-price-store');
    Route::get('/last-year-price-edit/{id?}', [App\Http\Controllers\Admin\LastYearPriceController::class, 'edit'])->name('last-year-price-edit');
    Route::post('/last-year-price-update', [App\Http\Controllers\Admin\LastYearPriceController::class, 'update'])->name('last-year-price-update');
    Route::get('/last-year-price-delete/{id?}', [App\Http\Controllers\Admin\LastYearPriceController::class, 'delete'])->name('last-year-price-delete');

    Route::get('/ignored-institutions', [App\Http\Controllers\Admin\VqIgnoredController::class, 'index'])->name('ignored-institutions');
    Route::get('/ignored-institutions-list', [App\Http\Controllers\Admin\VqIgnoredController::class, 'list'])->name('ignored-institutions-list');
    Route::post('/ignored-institutions-store', [App\Http\Controllers\Admin\VqIgnoredController::class, 'store'])->name('ignored-institutions-store');
    Route::get('/ignored-institutions-edit/{id?}', [App\Http\Controllers\Admin\VqIgnoredController::class, 'edit'])->name('ignored-institutions-edit');
    Route::post('/ignored-institutions-update', [App\Http\Controllers\Admin\VqIgnoredController::class, 'update'])->name('ignored-institutions-update');
    Route::get('/ignored-institutions-delete/{id?}', [App\Http\Controllers\Admin\VqIgnoredController::class, 'delete'])->name('ignored-institutions-delete');

    Route::get('/discount-margin', [App\Http\Controllers\Admin\DiscountMarginController::class, 'index'])->name('discount-margin');
    Route::get('/discount-margin-list', [App\Http\Controllers\Admin\DiscountMarginController::class, 'list'])->name('discount-margin-list');
    Route::post('/discount-margin-store', [App\Http\Controllers\Admin\DiscountMarginController::class, 'store'])->name('discount-margin-store');
    Route::get('/discount-margin-edit/{id?}', [App\Http\Controllers\Admin\DiscountMarginController::class, 'edit'])->name('discount-margin-edit');
    Route::post('/discount-margin-update', [App\Http\Controllers\Admin\DiscountMarginController::class, 'update'])->name('discount-margin-update');
    Route::get('/discount-margin-delete/{id?}', [App\Http\Controllers\Admin\DiscountMarginController::class, 'delete'])->name('discount-margin-delete');

    Route::get('/approval-email-schedule', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'index'])->name('approval-email-schedule');
    Route::get('/approval-email-schedule-list', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'list'])->name('approval-email-schedule-list');
    Route::post('/approval-email-schedule-store', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'store'])->name('approval-email-schedule-store');
    Route::get('/approval-email-schedule-edit/{id?}', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'edit'])->name('approval-email-schedule-edit');
    Route::post('/approval-email-schedule-update', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'update'])->name('approval-email-schedule-update');
    Route::get('/approval-email-schedule-delete/{id?}', [App\Http\Controllers\Admin\ApprovalEmailScheduleController::class, 'delete'])->name('approval-email-schedule-delete');
     Route::POST('/approvalemailscheduleDays', [App\Http\Controllers\Api\VqListingController::class, 'approvalemailscheduleDays'])->name('approvalemailscheduleDays');   
    
    Route::get('/config-data-list', [App\Http\Controllers\Admin\ConfigDataController::class, 'list'])->name('config-data-list');
    Route::get('/config-data-edit/{id?}', [App\Http\Controllers\Admin\ConfigDataController::class, 'edit'])->name('config-data-edit');
    Route::post('/config-data-update', [App\Http\Controllers\Admin\ConfigDataController::class, 'update'])->name('config-data-update');

    Route::get('/license', [App\Http\Controllers\Admin\LicenseController::class, 'index'])->name('license');
    Route::get('/license-list', [App\Http\Controllers\Admin\LicenseController::class, 'list'])->name('license-list');
    Route::post('/license-store', [App\Http\Controllers\Admin\LicenseController::class, 'store'])->name('license-store');
    Route::get('/license-edit/{id?}', [App\Http\Controllers\Admin\LicenseController::class, 'edit'])->name('license-edit');
    Route::post('/license-update', [App\Http\Controllers\Admin\LicenseController::class, 'update'])->name('license-update');
    Route::get('/license-delete/{id?}', [App\Http\Controllers\Admin\LicenseController::class, 'delete'])->name('license-delete');
    Route::get('/license-download/{fileType}/{fileName}', [App\Http\Controllers\Admin\LicenseController::class, 'download'])->name('license-download');

    Route::get('/max-discount-cap', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'index'])->name('max-discount-cap');
    Route::get('/max-discount-cap-list', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'list'])->name('max-discount-cap-list');
    Route::post('/max-discount-cap-store', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'store'])->name('max-discount-cap-store');
    Route::get('/max-discount-cap-edit/{id?}', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'edit'])->name('max-discount-cap-edit');
    Route::post('/max-discount-cap-update', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'update'])->name('max-discount-cap-update');
    Route::get('/max-discount-cap-delete/{id?}', [App\Http\Controllers\Admin\MaxDiscountCapController::class, 'delete'])->name('max-discount-cap-delete');

    Route::get('/adjust_workflow', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'adjust_workflow'])->name('adjust_workflow_admin');
    Route::get('/get_pending_vq_data_workflow', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'get_pending_vq_data_workflow'])->name('get_pending_vq_data_workflow_admin');
    Route::post('/workflow_adjust', [App\Http\Controllers\Api\VqListingController::class, 'workflow_adjust'])->name('workflow_adjust_admin');

    Route::get('/enable_locking', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'enable_locking'])->name('enable_locking_admin');
    Route::post('/change_locking_period', [App\Http\Controllers\Api\VqListingController::class, 'change_locking_period'])->name('change_locking_period_admin');


    Route::post('/stockist_update_institution', [App\Http\Controllers\Api\VqListingController::class, 'stockist_update_institution'])->name('stockist_update_institution');
    Route::get('/ajax/stockists/{institutionId}', [App\Http\Controllers\Api\VqListingController::class, 'getStockists'])->name('ajax.stockists');
    Route::put('/stockists/{stockistCode}', [App\Http\Controllers\Api\VqListingController::class, 'updateStockist'])->name('stockists.update');
    Route::get('/stockist_update', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'create_stock'])->name('stockist_admin');
    Route::get('/ajax/payment/{stockistId}', [App\Http\Controllers\Api\VqListingController::class, 'getPayment'])->name('ajax.payment');
    Route::get('/download_institution/{id}',  [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'downloadPDF'])->name('admin.download');
    Route::get('/download_stockist/{id}',  [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'stockist_downloadPDF'])->name('admin.download');

    Route::get('/activity_tracker', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'activity_tracker_new'])->name('activity_tracker_new_admin');
    Route::post('/activity_filter_new', [App\Http\Controllers\Api\VqListingController::class, 'activity_filter_new'])->name('activity_filter_new_admin');
    /** Discount margin*/
    Route::get('/discount_margin_view_logs_ajax', [App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController::class, 'getDiscountMarginViewLogData'])->name('getDiscountMarginViewLogData');
    /** product wise cumulative report */
    Route::get('/product_cumulative_report', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'productwiseCumulativeReport'])->name('admin_product_cumulative_report');
    Route::POST('/cumulative_report_new', [App\Http\Controllers\Api\VqListingController::class, 'cumulative_report_new'])->name('cumulative_report_new');
    Route::get('/CumulativeReportExport', [App\Http\Controllers\Api\VqListingController::class, 'CumulativeReportExport'])->name('admin-export-cumulative-report');//for ceo export
    Route::get('/filter_division_by_brand', [App\Http\Controllers\Api\VqListingController::class, 'filter_division_by_brand'])->name('filter_division_by_brand');

    Route::get('/workflow_adjust_product_wise', [App\Http\Controllers\Admin\WorkflowAdjustController::class, 'workflow_adjust_product_wise'])->name('workflow_adjust_product_wise_admin');
    Route::get('/get_pending_vq_data_workflow_product_wise', [App\Http\Controllers\Admin\WorkflowAdjustController::class, 'get_pending_vq_data_workflow_product_wise'])->name('get_pending_vq_data_workflow_product_wise_admin');
    Route::post('/workflow_adjust_forward_backward_levels', [App\Http\Controllers\Admin\WorkflowAdjustController::class, 'workflow_adjust_forward_backward_levels'])->name('workflow_adjust_forward_backward_levels_admin');

    Route::get('/productwise_discard', [App\Http\Controllers\StaticPages\VoluntaryQuotationController::class, 'productwise_discard_data'])->name('productwise_discard_data_admin');
    Route::get('/productwise_discard_getdata', [App\Http\Controllers\Api\VqListingController::class, 'productwise_discard_getdata'])->name('productwise_discard_getdata_admin');
    Route::post('/productwise_discard_selection', [App\Http\Controllers\Api\VqListingController::class, 'productwise_discard_selection'])->name('productwise_discard_selection_admin');
});
