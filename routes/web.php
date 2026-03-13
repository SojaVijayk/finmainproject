<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\laravel_example\UserManagement;
use App\Http\Controllers\Leave\LeaveRequestController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentDespatchController;

use App\Http\Controllers\PMS\RequirementController;
use App\Http\Controllers\PMS\ProposalController;
use App\Http\Controllers\PMS\ProjectController;
use App\Http\Controllers\PMS\MilestoneController;
use App\Http\Controllers\PMS\TaskController;
use App\Http\Controllers\PMS\InvoiceController;
use App\Http\Controllers\PMS\TimesheetController;
use App\Http\Controllers\PMS\ReportController;
use App\Http\Controllers\PMS\ProjectDocumentController;
use App\Http\Controllers\PMS\DashboardController;
use App\Http\Controllers\PMS\FinanceDashboardController;
use App\Http\Controllers\PMS\ExpenseController;
use App\Http\Controllers\PMS\ExpenseCategoryController;
use App\Http\Controllers\PMS\VendorController;
use App\Http\Controllers\PMS\AttachmentController;
use App\Http\Controllers\Project\ProjectEmployeeController;
use App\Http\Controllers\Project\DeductionMasterController;
use App\Http\Controllers\Project\PayItemMasterController;
use App\Http\Controllers\CertificateController;

use App\Exports\ProjectsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$controller_path = 'App\Http\Controllers';

// Main Page Auth Route
Route::get('/', $controller_path . '\authentications\LoginCover@index')->name('login');
Route::get('/login', $controller_path . '\Auth\LoginController@index')->name('login');
//AUTH
Route::post('/login', $controller_path . '\Auth\LoginController@login')->name('login.custom');

Route::get('/home', $controller_path . '\Auth\LoginController@index')->name('login');

Route::post('/logout', $controller_path . '\Auth\LoginController@logout')->name('logout');
Route::get('forget-password', $controller_path . '\Auth\ForgotPasswordController@index')->name('forget.password.get');
Route::post('forget-password', $controller_path . '\Auth\ForgotPasswordController@submitForgetPasswordForm')->name(
  'forget.password.post'
);
Route::get('reset-password/{token}', $controller_path . '\Auth\ForgotPasswordController@showResetPasswordForm')->name(
  'reset.password.get'
);
Route::post('reset-password', $controller_path . '\Auth\ForgotPasswordController@submitResetPasswordForm')->name(
  'reset.password.post'
);

//EVENT CERTIFICATE

Route::get('/certificate', [CertificateController::class, 'index'])->name('certificate.form');
Route::post('/request-certificate-otp', [CertificateController::class, 'sendOtp'])->name('request.certificate.otp');
Route::post('/certificate-otp-verification', [CertificateController::class, 'verifyOtp'])->name(
  'certificate-otp-verify'
);
Route::post('/certificate/check', [CertificateController::class, 'verify'])->name('certificate.verify');
Route::get('/certificate/view/{id}', [CertificateController::class, 'view'])->name('certificate.view');
Route::get('/certificate/download/{id}', [CertificateController::class, 'download'])->name('certificate.download');

//

Route::get('/lms-certificate', $controller_path . '\LMS\CertificateController@index')->name('lms-certificate');
Route::post('/request-lms-otp', $controller_path . '\LMS\CertificateController@sendOtp')->name('request.lms.otp');
Route::post('/otp-verification', $controller_path . '\LMS\CertificateController@verifyOtp')->name('lms-otp-verify');

//Research centre
Route::get('/research', $controller_path . '\Research\ResearchController@index')->name('research-register');
Route::post('/research-submit', $controller_path . '\Research\ResearchController@register')->name('research-save');
//middlewear start
Route::group(['middleware' => 'auth'], function () {
  $controller_path = 'App\Http\Controllers';
  //Dashboard
  Route::get('/dashboard', $controller_path . '\dashboard\Analytics@index')->name('dashboard-admin');
  Route::get('/auditor/dashboard', $controller_path . '\dashboard\Analytics@auditor')->name('dashboard-auditor');
  Route::get('/user/dashboard', $controller_path . '\layouts\Horizontal@index')->name('dashboard-user');

  Route::get('/home', $controller_path . '\layouts\Horizontal@index')->name('dashboard-user');

  //ROLE & Permission
  Route::get('/app/roles', $controller_path . '\Configuration\RoleController@index')->name('app-roles');
  Route::post('/app/roles/store', $controller_path . '\Configuration\RoleController@store')->name('app-roles-store');
  Route::get('/app/roles/edit/{id}', $controller_path . '\Configuration\RoleController@editRole')->name(
    'app-roles-edit'
  );
  Route::post('/app/roles/edit/{id}', $controller_path . '\Configuration\RoleController@update')->name(
    'app-roles-update'
  );
  Route::get('/app/permission', $controller_path . '\Configuration\PermissionController@index')->name('app-permission');
  Route::get('/app/permission/list', $controller_path . '\Configuration\PermissionController@getAllPermissions')->name(
    'app-permission-list'
  );
  Route::post('/app/permission/store', $controller_path . '\Configuration\PermissionController@store')->name(
    'app-permission-store'
  );

  //clients
  Route::get('/client/list', $controller_path . '\Client\ClientController@index')->name('client-list');
  Route::post('/client/store', $controller_path . '\Client\ClientController@store')->name('client-store');
  Route::get('/client/edit/{id}', $controller_path . '\Client\ClientController@editClient')->name('client-edit');
  Route::post('/client/edit/{id}', $controller_path . '\Client\ClientController@update')->name('client-update');
  Route::get('/client/contactPersons', $controller_path . '\Client\ClientController@getAllContactPersons')->name(
    'client-contact-persons'
  );
  Route::post(
    '/client/contactPerson/store/{client}',
    $controller_path . '\Client\ClientController@contactPersonStore'
  )->name('client-contact-store');
  Route::get('/client/contactPerson/edit/{id}', $controller_path . '\Client\ClientController@editContactPerson')->name(
    'client-contact-edit'
  );
  Route::post(
    '/client/contactPerson/update/{id}',
    $controller_path . '\Client\ClientController@updateContactPerson'
  )->name('client-contact-update');
  Route::post('/client/check-code', $controller_path . '\Client\ClientController@checkCode');
  //Projects
  Route::get('/projects', $controller_path . '\Client\ProjectController@index')->name('client-projects');
  Route::get('/project/list', $controller_path . '\Client\ProjectController@getAllProjects')->name(
    'client-project-list'
  );
  Route::post('/project/store', $controller_path . '\Client\ProjectController@store')->name('app-permission-store');
  Route::get('/project/edit/{id}', $controller_path . '\Client\ProjectController@editProject')->name('project-edit');
  Route::post('/project/edit/{id}', $controller_path . '\Client\ProjectController@update')->name('project-update');
  
  // Project Employee Global Store
  Route::get('/pms/employees/projects', [ProjectEmployeeController::class, 'projectList'])->name('pms.employees.projects');
  Route::get('/pms/employees/project/{id}', [ProjectEmployeeController::class, 'index'])->name('pms.employees.project-index');
  Route::post('/pms/employees/store', [ProjectEmployeeController::class, 'globalStore'])->name('pms.employees.store');
  Route::get('/pms/employees/list', [ProjectEmployeeController::class, 'globalList'])->name('pms.employees.list');
  Route::get('/pms/employees/details/{id}', [ProjectEmployeeController::class, 'globalDetails'])->name('pms.employees.details');
  Route::get('/pms/employees/edit/{id}', [ProjectEmployeeController::class, 'edit'])->name('pms.employees.edit');
  
  Route::post('/pms/employees/update-master/{id}', [ProjectEmployeeController::class, 'updateMaster'])->name('pms.employees.update-master');
  // Route::post('/pms/employees/update-service/{id}', [ProjectEmployeeController::class, 'updateService'])->name('pms.employees.update-service'); // Duplicate removed
  Route::post('/pms/employees/update-payroll/{id}', [ProjectEmployeeController::class, 'updatePayroll'])->name('pms.employees.update-payroll');

  //Project Managaement
  Route::get('/project/dashboard/{project_id}', $controller_path . '\Client\ProjectController@dashboard')->name(
    'project-dashboard'
  );
  Route::post('/project/milestone/store', $controller_path . '\Client\ProjectController@milestoneStore')->name(
    'project-milestone-store'
  );

  //User Management admin

  Route::get('/user/employee', $controller_path . '\Employee\EmployeeController@index')->name('user-employee');
  Route::get('/user/employee/list', $controller_path . '\Employee\EmployeeController@employeeList')->name(
    'user-employee-list'
  );
  Route::get('/user/employee/view/account/{id}', $controller_path . '\Employee\EmployeeController@employeeView')->name(
    'user-employee-view'
  );
  Route::post('/user/employee/store', $controller_path . '\Employee\EmployeeController@store')->name(
    'user-employee-store'
  );
  Route::get('/user/employee/edit/{id}', $controller_path . '\Employee\EmployeeController@edit')->name(
    'user-employee-edit'
  );
  Route::post('/user/employee/update/{id}', $controller_path . '\Employee\EmployeeController@update')->name(
    'user-employee-update'
  );
  Route::get('/user/employee/editInfo/{id}', $controller_path . '\Employee\EmployeeController@editInfo')->name(
    'user-employee-editInfo'
  );
  Route::post('/user/employee/editInfo/{id}', $controller_path . '\Employee\EmployeeController@updateInfo')->name(
    'user-employee-updateInfo'
  );
  Route::post(
    '/user/employee/addBankAccount/{id}',
    $controller_path . '\Employee\EmployeeController@addBankAccount'
  )->name('user-account-updateInfo');
  Route::post(
    '/user/employee/updateBankAccount',
    $controller_path . '\Employee\EmployeeController@updateBankAccount'
  )->name('user-account-status-change');
  Route::post('/user/employee/uploadImage', $controller_path . '\Employee\EmployeeController@uploadImage')->name(
    'user-account-image-change'
  );

  Route::post('/user/employee/resetPasssword', $controller_path . '\Employee\EmployeeController@resetPassword')->name(
    'user.employee.resetPassword'
  );
  //fetchEvents
  Route::get('/fetchEvents', $controller_path . '\Employee\EmployeeController@fetchEvents')->name('employee-event');

  //Masters
  Route::get('/master/designation', $controller_path . '\Masters\DesignationController@index')->name(
    'master-designation'
  );
  Route::get('/master/designation/list', $controller_path . '\Masters\DesignationController@getAllDesignations')->name(
    'master-designation-list'
  );
  Route::post('/master/designation/store', $controller_path . '\Masters\DesignationController@store')->name(
    'master-designation-store'
  );
  Route::get('/master/designation/edit/{id}', $controller_path . '\Masters\DesignationController@edit')->name(
    'master-designation-edit'
  );
  Route::post('/master/designation/update/{id}', $controller_path . '\Masters\DesignationController@update')->name(
    'master-designation-update'
  );

  Route::get('/master/employment-type', $controller_path . '\Masters\EmploymentTypeController@index')->name(
    'master-employment-type'
  );
  Route::get(
    '/master/employment-type/list',
    $controller_path . '\Masters\EmploymentTypeController@getAllEmploymentTypes'
  )->name('master-designation-list');
  Route::post('/master/employment-type/store', $controller_path . '\Masters\EmploymentTypeController@store')->name(
    'master-designation-store'
  );
  Route::get('/master/employment-type/edit/{id}', $controller_path . '\Masters\EmploymentTypeController@edit')->name(
    'master-designation-edit'
  );
  Route::post(
    '/master/employment-type/update/{id}',
    $controller_path . '\Masters\EmploymentTypeController@update'
  )->name('master-designation-update');
  Route::get(
    '/helpers/emplymenttypeDesignation/{id}',
    $controller_path . '\MasterFunctionController@getEmploymenttypeDesignations'
  )->name('emplymenttype-designation-list');

  Route::get('/master/holiday', $controller_path . '\HolidayController@index')->name('master-holiday');
  Route::get('/master/holiday/list', $controller_path . '\HolidayController@getAllHolidays')->name(
    'master-holiday-list'
  );
  Route::post('/master/holiday/store', $controller_path . '\HolidayController@store')->name('master-holiday-store');
  Route::get('/master/holiday/edit/{id}', $controller_path . '\HolidayController@edit')->name('master-holiday-edit');
  Route::post('/master/holiday/update/{id}', $controller_path . '\HolidayController@update')->name(
    'master-holiday-update'
  );

  //Leave Master
  Route::get('/leave', $controller_path . '\Leave\LeaveController@index')->name('leave-master-index');
  Route::get('/leave/list', $controller_path . '\Leave\LeaveController@getAllLeaves')->name('leave-list');
  Route::post('/leave/store', $controller_path . '\Leave\LeaveController@store')->name('leave-store');
  Route::get('/leave/edit/{id}', $controller_path . '\Leave\LeaveController@edit')->name('leave-edit');
  Route::post('/leave/update/{id}', $controller_path . '\Leave\LeaveController@update')->name('leave-update');

  Route::get('geneateHoliday', $controller_path . '\Leave\LeaveController@generateHoliday')->name('generate-holiday');

  Route::get('/leave-assign', $controller_path . '\Leave\LeaveAssignController@index')->name('leave-assign');
  Route::get('/leave-assign/list', $controller_path . '\Leave\LeaveAssignController@getAllAssignLeaves')->name(
    'leave-assign-list'
  );
  Route::post('/leave-assign/store', $controller_path . '\Leave\LeaveAssignController@store')->name(
    'leave-assign-store'
  );
  Route::get('/leave-assign/edit/{id}', $controller_path . '\Leave\LeaveAssignController@assignEdit')->name(
    'leave-assign-edit'
  );
  Route::post('/leave-assign/update/{id}', $controller_path . '\Leave\LeaveAssignController@assignUpdate')->name(
    'leave-assign-update'
  );

  //LEave cancel
  Route::get('/leave/user/cancel-requests/{id}', [LeaveRequestController::class, 'employeeCancelRequests'])->name(
    'leave.cancel.employee.list'
  );
  Route::get('/leave/{id}/dates', [LeaveRequestController::class, 'getLeaveDates']);
  Route::post('/leave/cancel-request', [LeaveRequestController::class, 'cancelRequest'])->name('leave.cancel.request');

  // HR
  Route::get('/leave/hr/cancel-requests', [LeaveRequestController::class, 'hrCancelRequests'])->name(
    'leave.cancel.hr.list'
  );
  Route::post('/leave/cancel/hr/{id}', [LeaveRequestController::class, 'cancelActionByHR'])->name(
    'leave.cancel.hr.action'
  );

  // Reporting Officer
  Route::get('/leave/ro/cancel-requests', [LeaveRequestController::class, 'roCancelRequests'])->name(
    'leave.cancel.ro.list'
  );
  Route::post('/leave/cancel/ro/{id}', [LeaveRequestController::class, 'cancelActionByReportingOfficer'])->name(
    'leave.cancel.ro.action'
  );

  Route::get('/attendance', $controller_path . '\Attendance\AttendanceController@index')->name('attendance-index');
  // Route::post('/attendance/import', $controller_path . '\Attendance\AttendanceController@import')->name('attendance-import');
  Route::post('/attendance/import', $controller_path . '\Attendance\AttendanceLogController@import')->name(
    'attendance-import'
  );
  Route::get(
    '/attendance-management',
    $controller_path . '\Attendance\AttendanceController@attendanceManagement'
  )->name('attendance-hrView');

  Route::get('/attendance/monitor/{id}', $controller_path . '\Attendance\AttendanceController@attendanceMonitor')->name(
    'attendance-Monitor'
  );
  Route::post(
    '/attendance/monitor-report',
    $controller_path . '\Attendance\AttendanceController@attendanceMonitorReport'
  )->name('attendance-Monitor');

  Route::get('/download', $controller_path . '\Attendance\AttendanceController@download')->name('attendance-download');
  Route::POST('/downloadBulk', $controller_path . '\Attendance\AttendanceLogController@downloadBulk1')->name(
    'attendance-download-bulk'
  );
  Route::get('/movement', $controller_path . '\Attendance\MovementController@index')->name('attendance-movement');
  Route::post('/movement/store', $controller_path . '\Attendance\MovementController@store')->name(
    'attendance-movement-store'
  );
  Route::get('/movement/edit/{id}', $controller_path . '\Attendance\MovementController@edit')->name(
    'attendance-movement-edit'
  );
  Route::post('/movement/update/{id}', $controller_path . '\Attendance\MovementController@update')->name(
    'attendance-movement-update'
  );
  Route::get('/movement/delete/{id}', $controller_path . '\Attendance\MovementController@destroy')->name(
    'attendance-movement-delete'
  );

  Route::get('/movement/approve-list', $controller_path . '\Attendance\MovementController@approveList')->name(
    'leave-movement-approve-list'
  );
  Route::get('/movement/request-list', $controller_path . '\Attendance\MovementController@requestList')->name(
    'attendance-movement-request-list'
  );
  Route::get('/movement/list', $controller_path . '\Attendance\MovementController@movementList')->name(
    'attendance-movement-list'
  );
  Route::post('/movement/action/{id}', $controller_path . '\Attendance\MovementController@action')->name(
    'attendance-movement-action'
  );
  Route::post('/movement/submit-report/{id}', $controller_path . '\Attendance\MovementController@reportSubmit')->name(
    'attendance-movement-submit-report'
  );
  Route::POST('/movement/downloadBulk', $controller_path . '\Attendance\MovementController@downloadBulk')->name(
    'attendance-download-bulk'
  );
  Route::get('/movement/get-references', $controller_path . '\Attendance\MovementController@getReferenceList')->name(
    'attendance-movement-get-references'
  );

  Route::get('/leave/request', $controller_path . '\Leave\LeaveRequestController@index')->name(
    'attendance-leave-request'
  );

  Route::get('/leave/approve-list', $controller_path . '\Leave\LeaveRequestController@approveList')->name(
    'leave-approve-list'
  );
  Route::get('/leave/request-list', $controller_path . '\Leave\LeaveRequestController@requestList')->name(
    'attendance-leave-list'
  );
  Route::get('/leave/request/list', $controller_path . '\Leave\LeaveRequestController@leaveList')->name(
    'attendance-leave-list'
  );
  Route::post('/leave/request/store', $controller_path . '\Leave\LeaveRequestController@store')->name(
    'attendance-leave-request-store'
  );

  Route::get('/leave/request/edit/{id}', $controller_path . '\Leave\LeaveRequestController@edit')->name(
    'attendance-leave-request-edit'
  );
  Route::post('/leave/request/action/{id}', $controller_path . '\Leave\LeaveRequestController@action')->name(
    'attendance-movement-action'
  );

  Route::get('/leave/request/delete/{id}', $controller_path . '\Leave\LeaveRequestController@destroy')->name(
    'attendance-leave-delete'
  );

  Route::POST('/leave/downloadBulk', $controller_path . '\Leave\LeaveRequestController@downloadBulk')->name(
    'attendance-download-bulk'
  );

  //Missed punch
  Route::get('/misspunch', $controller_path . '\Attendance\MissedPunchController@index')->name('attendance-misspunch');
  Route::post('/misspunch/store', $controller_path . '\Attendance\MissedPunchController@store')->name(
    'attendance-misspunch-store'
  );
  Route::get('/misspunch/edit/{id}', $controller_path . '\Attendance\MissedPunchController@edit')->name(
    'attendance-misspunch-edit'
  );
  Route::post('/misspunch/update/{id}', $controller_path . '\Attendance\MissedPunchController@update')->name(
    'attendance-misspunch-update'
  );
  Route::get('/misspunch/delete/{id}', $controller_path . '\Attendance\MissedPunchController@destroy')->name(
    'attendance-misspunch-delete'
  );

  Route::get('/misspunch/approve-list', $controller_path . '\Attendance\MissedPunchController@approveList')->name(
    'leave-misspunch-approve-list'
  );
  Route::get('/misspunch/request-list', $controller_path . '\Attendance\MissedPunchController@requestList')->name(
    'attendance-misspunch-request-list'
  );
  Route::get('/misspunch/list', $controller_path . '\Attendance\MissedPunchController@misspunchList')->name(
    'attendance-misspunch-list'
  );
  Route::post('/misspunch/action/{id}', $controller_path . '\Attendance\MissedPunchController@action')->name(
    'attendance-misspunch-action'
  );
  Route::POST('/misspunch/downloadBulk', $controller_path . '\Attendance\MissedPunchController@downloadBulk')->name(
    'attendance-misspunch-download-bulk'
  );
  //file Management
  Route::get('/officefiles', $controller_path . '\Employee\OfficeFilesController@index')->name('filemanagement-index');
  Route::get('/officefiles/list', $controller_path . '\Employee\OfficeFilesController@filesList')->name(
    'attendance-movement-list'
  );
  Route::post('/officefiles/store', $controller_path . '\Employee\OfficeFilesController@store')->name(
    'filemanagement-store'
  );
  Route::get('/officefiles/edit/{id}', $controller_path . '\Employee\OfficeFilesController@edit')->name(
    'filemanagement-edit'
  );
  Route::post('/officefiles/update/{id}', $controller_path . '\Employee\OfficeFilesController@update')->name(
    'filemanagement-update'
  );
  Route::get('/officefiles/delete/{id}', $controller_path . '\Employee\OfficeFilesController@destroy')->name(
    'filemanagement-delete'
  );

  //Employee login Routes
  //user view
  Route::get('/user/profile', $controller_path . '\Employee\EmployeeController@profileView')->name('user-profile');
  Route::get('/user/project/list', $controller_path . '\Client\ProjectController@userProjectList')->name(
    'user-project-list'
  );

  //Project employee Mngmnt
  Route::get('/project/employee/{project_id}', $controller_path . '\Project\ProjectEmployeeController@index')->name(
    'project-employee'
  );
  Route::get(
    '/project/employees/detail/list',
    $controller_path . '\Project\ProjectEmployeeController@employeeList'
  )->name('project-employee-list');
  // Route::get('/project/employee/view/account/{id}' , $controller_path . '\ProjectProjectEmployeeController@employeeView')->name('project-employee-view');
  Route::post('/project/employee/store', $controller_path . '\Project\ProjectEmployeeController@store')->name(
    'project-employee-store'
  );

  //project Document

  Route::get('/project/docs/{project_id}', $controller_path . '\Project\ProjectDocumentController@index')->name(
    'project-employee'
  );

  // Route::resource('venues', VenueController::class);

  Route::get('/calendar', [EventController::class, 'index'])->name('calendar');

  Route::get('/availability/calendar', [EventController::class, 'Avialability'])->name('availability.calendar');
  Route::get('/events', [EventController::class, 'getEvents']);
  Route::post('/events', [EventController::class, 'store']);
  Route::put('/events/{event}', [EventController::class, 'update']);
  Route::delete('/events/{event}', [EventController::class, 'destroy']);
  Route::get('/events/form-data', [EventController::class, 'getFormData']);
  Route::get('/events/{event}', [EventController::class, 'show']);
  Route::get('/venues/available', [EventController::class, 'getAvailableVenuesForTime']);

  Route::get('/hall-availability', [EventController::class, 'getHallAvailability'])->name(
    'getHallAvailabilityCalendar'
  );
  Route::get('/my-events', [EventController::class, 'getMyEvents']);
  Route::get('/upcoming-events', [EventController::class, 'getUpcomingEvents']);
  Route::delete('/events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');

  Route::get('/venues', [EventController::class, 'getVenues'])->name('venues.list');
  Route::get('/bookings', [EventController::class, 'getBookings'])->name('bookings.list');

  // Tapal Routes
  // Route::resource('tapals', $controller_path .'\TapalController');
  // List all tapals
  Route::get('tapals', $controller_path . '\TapalController@index')->name('tapals.index');

  // Show create form
  Route::get('tapals/create', $controller_path . '\TapalController@create')->name('tapals.create');

  // Store new tapal
  Route::post('tapals', $controller_path . '\TapalController@store')->name('tapals.store');

  // Show a specific tapal
  Route::get('tapals/{tapal}', $controller_path . '\TapalController@show')->name('tapals.show');

  // Show edit form
  Route::get('tapals/{tapal}/edit', $controller_path . '\TapalController@edit')->name('tapals.edit');

  // Update a tapal
  Route::put('tapals/{tapal}', $controller_path . '\TapalController@update')->name('tapals.update');

  // Delete a tapal
  Route::delete('tapals/{tapal}', $controller_path . '\TapalController@destroy')->name('tapals.destroy');

  // Additional Tapal Routes
  Route::post('/tapals/{tapal}/forward', $controller_path . '\TapalController@forward')->name('tapals.forward');
  Route::post('/tapals/{tapal}/share', $controller_path . '\TapalController@share')->name('tapals.share');
  Route::post('/tapals/movements/{movement}/accept', $controller_path . '\TapalController@accept')->name(
    'tapals.accept'
  );
  // Route::post('/tapals/movements/{movement}/complete', $controller_path .'\TapalController@complete')->name('tapals.complete');
  Route::post('/tapals/movements/{movement}/complete', $controller_path . '\TapalController@complete')->name(
    'tapals.complete'
  );

  // Tracing Routes

  Route::get('tapal/tracing', $controller_path . '\TapalController@tracing')->name('tapals.tracing');
  Route::get('/tapal/tracing/{tapal}', $controller_path . '\TapalController@tracingShow')->name('tapals.tracing.show');

  Route::delete('/attachments/{attachment}', $controller_path . '\AttachmentController@destroy')->name(
    'attachments.destroy'
  );

  //Document Number
  Route::resource('documents', DocumentController::class);

  Route::post('/{document}/upload', [DocumentController::class, 'uploadAttachment'])->name('documents.upload');
  Route::post('/{document}/part/upload', [DocumentController::class, 'uploadPartAttachment'])->name(
    'documents.part.upload'
  );
  Route::post('/{document}/confirm', [DocumentController::class, 'confirmDocument'])->name('documents.confirm');
  Route::post('/{document}/cancel', [DocumentController::class, 'cancelDocument'])->name('documents.cancel');
  Route::post('/{document}/revise', [DocumentController::class, 'reviseDocument'])->name('documents.revise');
  Route::get('/attachment/{attachment}/download', [DocumentController::class, 'downloadAttachment'])->name(
    'documents.attachment.download'
  );
  Route::post('/generate-preview', [DocumentController::class, 'generateNumberPreview'])->name(
    'documents.generate.preview'
  );
  Route::delete('/documents/{document}/attachment/{attachment}', [DocumentController::class, 'removeAttachment'])->name(
    'documents.attachment.remove'
  );

  Route::get('document/tracking', [DocumentController::class, 'tracking'])->name('documents.tracking');
  Route::get('document/tracking/{id}', [DocumentController::class, 'trackingShow'])->name('documents.tracking.show');
  Route::get('document/export', [DocumentController::class, 'export'])->name('documents.export');
  Route::get('document/statistics', [DocumentController::class, 'statistics'])->name('documents.statistics');
  Route::get('document/user/statistics', [DocumentController::class, 'userStatistics'])->name(
    'documents.user.statistics'
  );

  //Document Despatch
  Route::get('/despatch/create', [DocumentDespatchController::class, 'create'])->name('despatch.create');
  Route::post('/documents/{document}/despatch', [DocumentDespatchController::class, 'store'])->name('despatch.store');
  Route::get('/despatch/{despatch}/edit', [DocumentDespatchController::class, 'edit'])->name('despatch.edit');
  Route::put('/despatch/{despatch}', [DocumentDespatchController::class, 'update'])->name('despatch.update');
  Route::delete('/despatch/{despatch}', [DocumentDespatchController::class, 'destroy'])->name('despatch.destroy');
  Route::post('/despatch/{despatch}/upload-ack', [DocumentDespatchController::class, 'uploadAcknowledgement'])->name(
    'despatch.uploadAck'
  );

  //     Route::prefix('pms')->name('pms.')->middleware(['auth'])->group(function () {
  //     // Requirements
  //     Route::resource('requirements', RequirementController::class);
  //     Route::post('requirements/{requirement}/submit', [RequirementController::class, 'submitForApproval'])
  //         ->name('requirements.submit');
  //     Route::post('requirements/{requirement}/approve', [RequirementController::class, 'approve'])
  //         ->name('requirements.approve');
  //     Route::post('requirements/{requirement}/reject', [RequirementController::class, 'reject'])
  //         ->name('requirements.reject');
  //     Route::post('requirements/{requirement}/allocate', [RequirementController::class, 'allocate'])
  //         ->name('requirements.allocate');

  //     // Proposals
  //     Route::resource('proposals', ProposalController::class)->except(['create']);
  //     Route::get('requirements/{requirement}/proposals/create', [ProposalController::class, 'create'])
  //         ->name('proposals.create');
  //     Route::post('proposals/{proposal}/submit', [ProposalController::class, 'submitForApproval'])
  //         ->name('proposals.submit');
  //     Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve'])
  //         ->name('proposals.approve');
  //     Route::post('proposals/{proposal}/reject', [ProposalController::class, 'reject'])
  //         ->name('proposals.reject');
  //     Route::post('proposals/{proposal}/return', [ProposalController::class, 'returnForClarification'])
  //         ->name('proposals.return');
  //     Route::post('proposals/{proposal}/send', [ProposalController::class, 'sendToClient'])
  //         ->name('proposals.send');
  //     Route::post('proposals/{proposal}/client-status', [ProposalController::class, 'updateClientStatus'])
  //         ->name('proposals.client-status');

  //     // Projects
  //     Route::resource('projects', ProjectController::class)->except(['create']);
  //     Route::get('proposals/{proposal}/projects/create', [ProjectController::class, 'create'])
  //         ->name('projects.create');
  //     Route::post('projects/{project}/start', [ProjectController::class, 'start'])
  //         ->name('projects.start');
  //     Route::post('projects/{project}/complete', [ProjectController::class, 'complete'])
  //         ->name('projects.complete');
  //     Route::post('projects/{project}/documents', [ProjectController::class, 'addDocument'])
  //         ->name('projects.add-document');
  //         Route::get('projects/{project}/gantt', [ProjectController::class, 'gantt'])->name('projects.gantt');

  //     // Project Milestones
  //     Route::prefix('projects/{project}/milestones')->name('milestones.')->group(function () {
  //         Route::get('/', [MilestoneController::class, 'index'])->name('index');
  //         Route::get('/create', [MilestoneController::class, 'create'])->name('create');
  //         Route::post('/', [MilestoneController::class, 'store'])->name('store');
  //         Route::get('/{milestone}', [MilestoneController::class, 'show'])->name('show');
  //         Route::get('/{milestone}/edit', [MilestoneController::class, 'edit'])->name('edit');
  //         Route::put('/{milestone}', [MilestoneController::class, 'update'])->name('update');
  //         Route::post('/{milestone}/start', [MilestoneController::class, 'start'])->name('start');
  //         Route::post('/{milestone}/complete', [MilestoneController::class, 'complete'])->name('complete');
  //         Route::post('/{milestone}/request-invoice', [MilestoneController::class, 'requestInvoice'])
  //             ->name('request-invoice');
  //     });

  //     // Project Tasks
  //     Route::prefix('projects/{project}/milestones/{milestone}/tasks')->name('tasks.')->group(function () {
  //         Route::get('/', [TaskController::class, 'index'])->name('index');
  //         Route::get('/create', [TaskController::class, 'create'])->name('create');
  //         Route::post('/', [TaskController::class, 'store'])->name('store');
  //         Route::get('/{task}', [TaskController::class, 'show'])->name('show');
  //         Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
  //         Route::put('/{task}', [TaskController::class, 'update'])->name('update');
  //         Route::post('/{task}/start', [TaskController::class, 'start'])->name('start');
  //         Route::post('/{task}/complete', [TaskController::class, 'complete'])->name('complete');
  //     });

  //     // Project Invoices
  //     Route::prefix('projects/{project}/invoices')->name('invoices.')->group(function () {
  //         Route::get('/', [InvoiceController::class, 'index'])->name('index');
  //         Route::get('/create', [InvoiceController::class, 'create'])->name('create');
  //         Route::post('/', [InvoiceController::class, 'store'])->name('store');
  //         Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
  //         Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
  //         Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
  //         Route::post('/{invoice}/generate', [InvoiceController::class, 'generate'])->name('generate');
  //         Route::post('/{invoice}/pay', [InvoiceController::class, 'markAsPaid'])->name('pay');
  //         Route::post('/{invoice}/payments', [InvoiceController::class, 'addPayment'])->name('add-payment');
  //     });

  //     // Timesheets
  //     Route::prefix('timesheets')->name('timesheets.')->group(function () {
  //         Route::get('/', [TimesheetController::class, 'index'])->name('index');
  //         Route::get('/calendar', [TimesheetController::class, 'calendar'])->name('calendar');
  //         Route::get('/report', [TimesheetController::class, 'report'])->name('report');
  //         Route::get('/export', [TimesheetController::class, 'export'])->name('export');
  //         Route::post('/', [TimesheetController::class, 'store'])->name('store');
  //         Route::put('/{timesheet}', [TimesheetController::class, 'update'])->name('update');
  //         Route::delete('/{timesheet}', [TimesheetController::class, 'destroy'])->name('destroy');
  //     });

  //     Route::prefix('reports')->name('reports.')->group(function () {
  //     Route::get('project-status', [ReportController::class, 'projectStatus'])->name('project-status');
  //     Route::get('financial', [ReportController::class, 'financial'])->name('financial');
  //     Route::get('timesheet', [ReportController::class, 'timesheet'])->name('timesheet');
  //     Route::get('resource-utilization', [ReportController::class, 'resourceUtilization'])->name('resource-utilization');
  //     Route::get('export/{type}', [ReportController::class, 'export'])->name('export');
  //     });

  // });

  Route::get('project-categories/{category}/subcategories', [RequirementController::class, 'getSubcategories'])->name(
    'categories.subcategories'
  );
  Route::get('clients/{client}/contacts', [RequirementController::class, 'getClientContacts'])->name(
    'clients.contacts'
  );

  Route::prefix('pms')
    ->name('pms.')
    ->middleware(['auth'])
    ->group(function () {
      Route::get('/invoices/import/path', [InvoiceController::class, 'importFromPath']);

      Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
      Route::get('/employees', [ProjectEmployeeController::class, 'globalIndex'])->name('employees.index');
      Route::get('/employees/list', [ProjectEmployeeController::class, 'globalList'])->name('employees.list');
      Route::post('/employees/store', [ProjectEmployeeController::class, 'globalStore'])->name('employees.store');
      Route::get('/employees/details/{id}', [ProjectEmployeeController::class, 'globalDetails'])->name('employees.details');
      Route::post('/employees/update-master/{id}', [ProjectEmployeeController::class, 'updateMaster'])->name('employees.update-master');
      Route::post('/employees/update-service/{p_id}', [ProjectEmployeeController::class, 'updateService'])->name('employees.update-service');
      Route::post('/employees/update-salary/{p_id}', [ProjectEmployeeController::class, 'updateSalary'])->name('employees.update-salary');
      Route::post('/employees/update-deduction/{p_id}', [ProjectEmployeeController::class, 'updateDeduction'])->name('employees.update-deduction');

      // Deduction Master
      Route::get('/deduction-master/{project_id?}', [DeductionMasterController::class, 'index'])->name('deduction-master.index');
      Route::get('/deduction-master/salary-slip/{id}/{month}/{year}', [DeductionMasterController::class, 'generateSalarySlip'])->name('deduction-master.salary-slip');
      Route::get('/deduction-master/salary-slip/pdf/{id}/{month}/{year}', [DeductionMasterController::class, 'downloadSalarySlipPdf'])->name('deduction-master.salary-slip-pdf');
      Route::get('/deduction-master/salary-slip/word/{id}/{month}/{year}', [DeductionMasterController::class, 'downloadSalarySlipWord'])->name('deduction-master.salary-slip-word');
      Route::match(['get', 'post'], '/deduction-master/select-employees/{project_id?}', [DeductionMasterController::class, 'selectEmployees'])->name('deduction-master.select-employees');
      Route::post('/deduction-master/store/{project_id?}', [DeductionMasterController::class, 'storeDeductions'])->name('deduction-master.store');

      // Pay Item Master
      Route::get('/pay-item-master/{project_id?}', [PayItemMasterController::class, 'index'])->name('pay-item-master.index');
      Route::post('/pay-item-master/store', [PayItemMasterController::class, 'store'])->name('pay-item-master.store');
      Route::delete('/pay-item-master/{id}', [PayItemMasterController::class, 'destroy'])->name('pay-item-master.destroy');
      Route::post('/pay-item-master/generate-bill', [PayItemMasterController::class, 'generateBillList'])->name('pay-item-master.generate-bill');
      Route::post('/pay-item-master/store-bill', [PayItemMasterController::class, 'storeBill'])->name('pay-item-master.store-bill');

      // Requirements
      Route::get('requirements/master-list', [RequirementController::class, 'masterList'])->name(
        'requirements.master-list'
      );
      Route::resource('requirements', RequirementController::class);
      Route::get('requirements-proposals', [RequirementController::class, 'proposalsList'])->name(
        'requirements.proposals'
      );
      Route::post('requirements/{requirement}/submit', [RequirementController::class, 'submitForApproval'])->name(
        'requirements.submit'
      );
      Route::post('requirements/{requirement}/approve', [RequirementController::class, 'approve'])->name(
        'requirements.approve'
      );
      Route::post('requirements/{requirement}/sent-to-pac', [RequirementController::class, 'sentToPac'])->name(
        'requirements.sent-to-pac'
      );
      Route::post('requirements/{requirement}/reject', [RequirementController::class, 'reject'])->name(
        'requirements.reject'
      );
      Route::post('requirements/{requirement}/allocate', [RequirementController::class, 'allocate'])->name(
        'requirements.allocate'
      );

      Route::post('requirements/bulk-actions/process', [RequirementController::class, 'process'])->name(
        'requirements.bulk-actions.process'
      );

      // Dynamic data loading for requirements

      // Proposals
      Route::resource('proposals', ProposalController::class)->except(['create', 'store']);
      Route::get('requirements/{requirement}/proposals/create', [ProposalController::class, 'create'])->name(
        'proposals.create'
      );
      Route::post('requirements/{requirement}/proposals', [ProposalController::class, 'store'])->name(
        'proposals.store'
      );
      Route::post('proposals/{proposal}/submit', [ProposalController::class, 'submitForApproval'])->name(
        'proposals.submit'
      );
      // Route::post('proposals/{proposal}/sent-to-pac', [ProposalController::class, 'sentToPac'])->name('proposals.sent-to-pac');
      Route::post('proposals/{proposal}/approve', [ProposalController::class, 'approve'])->name('proposals.approve');
      Route::post('proposals/{proposal}/reject', [ProposalController::class, 'reject'])->name('proposals.reject');
      Route::post('proposals/{proposal}/return', [ProposalController::class, 'returnForClarification'])->name(
        'proposals.return'
      );
      Route::post('proposals/{proposal}/send', [ProposalController::class, 'sendToClient'])->name('proposals.send');
      Route::post('proposals/{proposal}/client-status', [ProposalController::class, 'updateClientStatus'])->name(
        'proposals.client-status'
      );

      // Projects
      Route::resource('projects', ProjectController::class)->except(['create', 'store']);
      Route::get('proposals/{proposal}/projects/create', [ProjectController::class, 'create'])->name('projects.create');
      Route::post('proposals/{proposal}/projects', [ProjectController::class, 'store'])->name('projects.store');
      Route::post('projects/{project}/start', [ProjectController::class, 'start'])->name('projects.start');
      Route::post('projects/{project}/complete', [ProjectController::class, 'complete'])->name('projects.complete');
      Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
      Route::post('projects/{project}/documents', [ProjectController::class, 'addDocument'])->name(
        'projects.add-document'
      );
      Route::get('projects/{project}/gantt', [ProjectController::class, 'gantt'])->name('projects.gantt');

      // Dynamic data loading for projects
      Route::get('projects/{project}/team-members', [ProjectController::class, 'getTeamMembers'])->name(
        'projects.team-members'
      );

      Route::get('projects/{id}/dashboard', [ProjectController::class, 'dashboard'])->name('projects.dashboard');
      Route::get('projects/{id}/financial-data', [ProjectController::class, 'getProjectFinancialData'])->name(
        'projects.financial-data'
      );
      Route::get('projects/{id}/timeline-data', [ProjectController::class, 'getProjectTimelineData'])->name(
        'projects.timeline-data'
      );

      Route::prefix('projects/{project}/documents')
        ->name('projects.documents.')
        ->group(function () {
          Route::get('/', [ProjectDocumentController::class, 'index'])->name('index');
          Route::get('/create', [ProjectDocumentController::class, 'create'])->name('create');
          Route::post('/', [ProjectDocumentController::class, 'store'])->name('store');
          Route::get('/{document}', [ProjectDocumentController::class, 'show'])->name('show');
          Route::get('/{document}/download', [ProjectDocumentController::class, 'download'])->name('download');
          Route::delete('/{document}', [ProjectDocumentController::class, 'destroy'])->name('destroy');
        });

      Route::get('/attachments/tapal', [AttachmentController::class, 'tapalList']);
      Route::get('/attachments/documents', [AttachmentController::class, 'documentList']);

      // Project Milestones
      Route::prefix('projects/{project}/milestones')
        ->name('milestones.')
        ->group(function () {
          Route::get('/', [MilestoneController::class, 'index'])->name('index');
          Route::get('/create', [MilestoneController::class, 'create'])->name('create');
          Route::post('/', [MilestoneController::class, 'store'])->name('store');
          Route::get('/{milestone}', [MilestoneController::class, 'show'])->name('show');
          Route::get('/{milestone}/edit', [MilestoneController::class, 'edit'])->name('edit');
          Route::put('/{milestone}', [MilestoneController::class, 'update'])->name('update');
          Route::post('/{milestone}/start', [MilestoneController::class, 'start'])->name('start');
          Route::post('/{milestone}/complete', [MilestoneController::class, 'complete'])->name('complete');
          Route::post('/{milestone}/request-invoice', [MilestoneController::class, 'requestInvoice'])->name(
            'request-invoice'
          );
        });

      // Project Tasks
      Route::prefix('projects/{project}/milestones/{milestone}/tasks')
        ->name('tasks.')
        ->group(function () {
          Route::get('/', [TaskController::class, 'index'])->name('index');
          Route::get('/create', [TaskController::class, 'create'])->name('create');
          Route::post('/', [TaskController::class, 'store'])->name('store');
          Route::get('/{task}', [TaskController::class, 'show'])->name('show');
          Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
          Route::put('/{task}', [TaskController::class, 'update'])->name('update');
          Route::post('/{task}/start', [TaskController::class, 'start'])->name('start');
          Route::post('/{task}/complete', [TaskController::class, 'complete'])->name('complete');
        });

      // Route::get('/projects/{project}/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
      // Route::post('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('pms.tasks.update-status');
      Route::prefix('projects/{project}/kanban')
        ->name('projects.kanban.')
        ->group(function () {
          Route::get('/', [TaskController::class, 'kanban'])->name('index'); // Show Kanban board
          Route::get('/data', [TaskController::class, 'kanbanData'])->name('data'); // Filters
          Route::post('/tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name(
            'tasks.update-status'
          ); // Drag-drop
        });

      Route::prefix('projects/{project}/tasks/{task}/comments')
        ->name('tasks.comments.')
        ->group(function () {
          Route::get('/', [TaskController::class, 'comments'])->name('index');
          Route::post('/', [TaskController::class, 'addComment'])->name('store');
          Route::put('/{comment}', [TaskController::class, 'updateComment'])->name('update');
          Route::delete('/{comment}', [TaskController::class, 'deleteComment'])->name('destroy');
        });

      // Project Invoices
      Route::prefix('projects/{project}/invoices')
        ->name('invoices.')
        ->group(function () {
          Route::get('/', [InvoiceController::class, 'index'])->name('index');
          Route::get('/create', [InvoiceController::class, 'create'])->name('create');
          Route::post('/', [InvoiceController::class, 'store'])->name('store');
          Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
          Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
          Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
          Route::post('/{invoice}/generate', [InvoiceController::class, 'generate'])->name('generate');
          Route::post('/{invoice}/pay', [InvoiceController::class, 'markAsPaid'])->name('pay');
          Route::post('/{invoice}/payments', [InvoiceController::class, 'addPayment'])->name('add-payment');
        });

      //EXPENSE
      Route::resource('expenses', ExpenseController::class);
      Route::get('expenses/export/excel', [ExpenseController::class, 'exportExcel'])->name('expenses.export.excel');
      Route::get('expenses/export/pdf', [ExpenseController::class, 'exportPdf'])->name('expenses.export.pdf');

      Route::resource('expense-categories', ExpenseCategoryController::class);
      Route::resource('vendors', VendorController::class);

      // Finance Dashboard Route
      Route::get('expense-dashboard', [ExpenseController::class, 'report'])->name('expense.dashboard');

      // Finance Routes
      Route::prefix('finance')
        ->name('finance.')
        ->group(function () {
          // Dashboard
          Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');

          // Invoices
          Route::get('/invoices', [FinanceDashboardController::class, 'invoiceIndex'])->name('invoices.index');
          Route::get('/invoices/{invoice}', [FinanceDashboardController::class, 'showInvoice'])->name('invoices.show');

          // Draft Processing
          Route::get('/invoices/{invoice}/process', [FinanceDashboardController::class, 'processDraft'])->name(
            'invoices.process'
          );
          Route::put('/invoices/{invoice}/process', [FinanceDashboardController::class, 'updateDraft'])->name(
            'invoices.update'
          );
          Route::post('/invoices/{invoice}/generate', [FinanceDashboardController::class, 'generateInvoice'])->name(
            'invoices.generate'
          );

          // Payments
          Route::post('/invoices/{invoice}/pay', [FinanceDashboardController::class, 'markAsPaid'])->name(
            'invoices.pay'
          );
          Route::get('/invoices/{invoice}/payment', [FinanceDashboardController::class, 'recordPayment'])->name(
            'invoices.payment'
          );
          Route::post('/invoices/{invoice}/payment', [FinanceDashboardController::class, 'storePayment'])->name(
            'invoices.store-payment'
          );

          // web.php
          // Route::get('/invoices/{invoice}/convert', [FinanceDashboardController::class, 'convert'])->name('invoices.convert');
          // Route::post('/invoices/{invoice}/convert', [FinanceDashboardController::class, 'storeConverted'])->name('invoices.storeConverted');
          Route::get('/invoices/{invoice}/convert', [FinanceDashboardController::class, 'getConvertView'])->name(
            'invoices.convert.view'
          );

          Route::post('/invoices/{invoice}/convert', [
            FinanceDashboardController::class,
            'convertProformaToTaxInvoice',
          ])->name('invoices.convert');
          Route::get('/invoices/partial/edit', [FinanceDashboardController::class, 'editPartial'])->name(
            'invoices.partial.edit'
          );

          // New Finance Module Routes
          Route::resource('accounts', \App\Http\Controllers\Finance\FinanceBankAccountController::class);
          Route::resource('transactions', \App\Http\Controllers\Finance\BankTransactionController::class)->only([
            'index',
            'create',
            'store',
          ]);
          Route::post('transactions/import', [
            \App\Http\Controllers\Finance\BankTransactionController::class,
            'import',
          ])->name('transactions.import');
          Route::get('bank-dashboard', [
            \App\Http\Controllers\Finance\BankTransactionController::class,
            'dashboard',
          ])->name('bank-dashboard');
          Route::resource('requirements', \App\Http\Controllers\Finance\FundRequirementController::class);
        });

      // Timesheets
      Route::prefix('timesheets')
        ->name('timesheets.')
        ->group(function () {
          Route::get('/', [TimesheetController::class, 'index'])->name('index');
          Route::get('/calendar', [TimesheetController::class, 'calendar'])->name('calendar');
          Route::get('/report', [TimesheetController::class, 'report'])->name('report');
          Route::get('/resource-utilization', [TimesheetController::class, 'resourceUtilization'])->name(
            'resource-utilization'
          );
          Route::get('/export/{type}', [TimesheetController::class, 'export'])->name('export');
          Route::post('/', [TimesheetController::class, 'store'])->name('store');
          Route::put('/{timesheet}', [TimesheetController::class, 'update'])->name('update');
          Route::delete('/{timesheet}', [TimesheetController::class, 'destroy'])->name('destroy');
          Route::post('/bulk', [TimesheetController::class, 'bulkStore'])->name('bulk');

          Route::delete('/timesheets/{timesheet}/item/{item}', [TimesheetController::class, 'destroyItem'])->name(
            'destroyItem'
          );

          Route::get('/projects', [TimesheetController::class, 'getProjects'])->name('projects');
        });

      // Reports
      Route::prefix('reports')
        ->name('reports.')
        ->group(function () {
          Route::get('project-status', [ReportController::class, 'projectStatus'])->name('project-status');
          Route::get('project-status-detailed', [ReportController::class, 'projectStatusDetailed'])->name(
            'project-status-detailed'
          );

          Route::get('/project-status-report', [ReportController::class, 'projectDetailedReport'])->name(
            'project-status-report'
          );
          Route::get('/projects/export', function (Request $request) {
            $fileName = 'projects_export_' . now()->format('Ymd_His') . '.xlsx';
            return Excel::download(new ProjectsExport($request), $fileName);
          })->name('projects.export');
          // Route::get('/project-status-report/export', [ReportController::class, 'exportProjectStatusReport'])
          //     ->name('pms.reports.project-status-report.export');

          Route::get('financial', [ReportController::class, 'financial'])->name('financial');
          Route::get('timesheet', [ReportController::class, 'timesheet'])->name('timesheet');
          Route::get('resource-utilization', [ReportController::class, 'resourceUtilization'])->name(
            'resource-utilization'
          );
          Route::get('export/{type}', [ReportController::class, 'export'])->name('export');
        });
    });

  // Asset Management
  Route::group(['prefix' => 'asset', 'as' => 'asset.'], function () {
    Route::resource('departments', \App\Http\Controllers\Asset\AssetDepartmentController::class);
    Route::resource('categories', \App\Http\Controllers\Asset\AssetCategoryController::class);
    Route::resource('vendors', \App\Http\Controllers\Asset\AssetVendorController::class);
    Route::resource('brands', \App\Http\Controllers\Asset\AssetBrandController::class);
    Route::resource('locations', \App\Http\Controllers\Asset\LocationController::class);
    Route::resource('floors', \App\Http\Controllers\Asset\FloorController::class);
    Route::resource('rooms', \App\Http\Controllers\Asset\RoomController::class);
    Route::post('masters/{master}/change-status', [
      \App\Http\Controllers\Asset\AssetMasterController::class,
      'changeStatus',
    ])->name('masters.change-status');
    Route::get('masters/{master}/history', [\App\Http\Controllers\Asset\AssetMasterController::class, 'history'])->name(
      'masters.history'
    );
    Route::resource('masters', \App\Http\Controllers\Asset\AssetMasterController::class);
    Route::resource('allocations', \App\Http\Controllers\Asset\AssetAllocationController::class);
    Route::get('my-assets', [\App\Http\Controllers\Asset\AssetMasterController::class, 'myAssets'])->name('my-assets');
    Route::get('categories/{id}/schema', [
      \App\Http\Controllers\Asset\AssetMasterController::class,
      'getCategorySchema',
    ]);

    // Reports
    Route::get('reports', [\App\Http\Controllers\Asset\AssetReportController::class, 'index'])->name('reports.index');
    Route::get('reports/register', [\App\Http\Controllers\Asset\AssetReportController::class, 'assetRegister'])->name(
      'reports.register'
    );
    Route::get('reports/depreciation', [
      \App\Http\Controllers\Asset\AssetReportController::class,
      'depreciation',
    ])->name('reports.depreciation');
  });

  //end
}); //middlewear end

// Route::get('/dashboard/analytics', $controller_path . '\dashboard\Analytics@index')->name('dashboard-analytics');
Route::get('/dashboard/crm', $controller_path . '\dashboard\Crm@index')->name('dashboard-crm');
Route::get('/dashboard/ecommerce', $controller_path . '\dashboard\Ecommerce@index')->name('dashboard-ecommerce');

// locale
Route::get('lang/{locale}', $controller_path . '\language\LanguageController@swap');

// layout
Route::get('/layouts/collapsed-menu', $controller_path . '\layouts\CollapsedMenu@index')->name(
  'layouts-collapsed-menu'
);
Route::get('/layouts/content-navbar', $controller_path . '\layouts\ContentNavbar@index')->name(
  'layouts-content-navbar'
);
Route::get('/layouts/content-nav-sidebar', $controller_path . '\layouts\ContentNavSidebar@index')->name(
  'layouts-content-nav-sidebar'
);
Route::get('/layouts/navbar-full', $controller_path . '\layouts\NavbarFull@index')->name('layouts-navbar-full');
Route::get('/layouts/navbar-full-sidebar', $controller_path . '\layouts\NavbarFullSidebar@index')->name(
  'layouts-navbar-full-sidebar'
);
// Route::get('/layouts/horizontal', $controller_path . '\layouts\Horizontal@index')->name('dashboard-analytics');
// Route::get('/layouts/vertical', $controller_path . '\layouts\Vertical@index')->name('dashboard-analytics');
Route::get('/layouts/without-menu', $controller_path . '\layouts\WithoutMenu@index')->name('layouts-without-menu');
Route::get('/layouts/without-navbar', $controller_path . '\layouts\WithoutNavbar@index')->name(
  'layouts-without-navbar'
);
Route::get('/layouts/fluid', $controller_path . '\layouts\Fluid@index')->name('layouts-fluid');
Route::get('/layouts/container', $controller_path . '\layouts\Container@index')->name('layouts-container');
Route::get('/layouts/blank', $controller_path . '\layouts\Blank@index')->name('layouts-blank');

// apps
Route::get('/app/email', $controller_path . '\apps\Email@index')->name('app-email');
Route::get('/app/chat', $controller_path . '\apps\Chat@index')->name('app-chat');
Route::get('/app/calendar', $controller_path . '\apps\Calendar@index')->name('app-calendar');
Route::get('/app/kanban', $controller_path . '\apps\Kanban@index')->name('app-kanban');
Route::get('/app/invoice/list', $controller_path . '\apps\InvoiceList@index')->name('app-invoice-list');
Route::get('/app/invoice/preview', $controller_path . '\apps\InvoicePreview@index')->name('app-invoice-preview');
Route::get('/app/invoice/print', $controller_path . '\apps\InvoicePrint@index')->name('app-invoice-print');
Route::get('/app/invoice/edit', $controller_path . '\apps\InvoiceEdit@index')->name('app-invoice-edit');
Route::get('/app/invoice/add', $controller_path . '\apps\InvoiceAdd@index')->name('app-invoice-add');
Route::get('/app/user/list', $controller_path . '\apps\UserList@index')->name('app-user-list');
Route::get('/app/user/view/account', $controller_path . '\apps\UserViewAccount@index')->name('app-user-view-account');
Route::get('/app/user/view/security', $controller_path . '\apps\UserViewSecurity@index')->name(
  'app-user-view-security'
);
Route::get('/app/user/view/billing', $controller_path . '\apps\UserViewBilling@index')->name('app-user-view-billing');
Route::get('/app/user/view/notifications', $controller_path . '\apps\UserViewNotifications@index')->name(
  'app-user-view-notifications'
);
Route::get('/app/user/view/connections', $controller_path . '\apps\UserViewConnections@index')->name(
  'app-user-view-connections'
);
Route::get('/app/access-roles', $controller_path . '\apps\AccessRoles@index')->name('app-access-roles');
Route::get('/app/access-permission', $controller_path . '\apps\AccessPermission@index')->name('app-access-permission');

// pages
Route::get('/pages/profile-user', $controller_path . '\pages\UserProfile@index')->name('pages-profile-user');
Route::get('/pages/profile-teams', $controller_path . '\pages\UserTeams@index')->name('pages-profile-teams');
Route::get('/pages/profile-projects', $controller_path . '\pages\UserProjects@index')->name('pages-profile-projects');
Route::get('/pages/profile-connections', $controller_path . '\pages\UserConnections@index')->name(
  'pages-profile-connections'
);
Route::get('/pages/account-settings-account', $controller_path . '\pages\AccountSettingsAccount@index')->name(
  'pages-account-settings-account'
);
Route::get('/pages/account-settings-security', $controller_path . '\pages\AccountSettingsSecurity@index')->name(
  'pages-account-settings-security'
);
Route::get('/pages/account-settings-billing', $controller_path . '\pages\AccountSettingsBilling@index')->name(
  'pages-account-settings-billing'
);
Route::get(
  '/pages/account-settings-notifications',
  $controller_path . '\pages\AccountSettingsNotifications@index'
)->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', $controller_path . '\pages\AccountSettingsConnections@index')->name(
  'pages-account-settings-connections'
);
Route::get('/pages/faq', $controller_path . '\pages\Faq@index')->name('pages-faq');
Route::get('/pages/help-center-landing', $controller_path . '\pages\HelpCenterLanding@index')->name(
  'pages-help-center-landing'
);
Route::get('/pages/help-center-categories', $controller_path . '\pages\HelpCenterCategories@index')->name(
  'pages-help-center-categories'
);
Route::get('/pages/help-center-article', $controller_path . '\pages\HelpCenterArticle@index')->name(
  'pages-help-center-article'
);
Route::get('/pages/pricing', $controller_path . '\pages\Pricing@index')->name('pages-pricing');
Route::get('/pages/misc-error', $controller_path . '\pages\MiscError@index')->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', $controller_path . '\pages\MiscUnderMaintenance@index')->name(
  'pages-misc-under-maintenance'
);
Route::get('/pages/misc-comingsoon', $controller_path . '\pages\MiscComingSoon@index')->name('pages-misc-comingsoon');
Route::get('/pages/misc-not-authorized', $controller_path . '\pages\MiscNotAuthorized@index')->name(
  'pages-misc-not-authorized'
);

// authentication
Route::get('/auth/login-basic', $controller_path . '\authentications\LoginBasic@index')->name('auth-login-basic');
Route::get('/auth/login-cover', $controller_path . '\authentications\LoginCover@index')->name('auth-login-cover');
Route::get('/auth/register-basic', $controller_path . '\authentications\RegisterBasic@index')->name(
  'auth-register-basic'
);
Route::get('/auth/register-cover', $controller_path . '\authentications\RegisterCover@index')->name(
  'auth-register-cover'
);
Route::get('/auth/register-multisteps', $controller_path . '\authentications\RegisterMultiSteps@index')->name(
  'auth-register-multisteps'
);
Route::get('/auth/verify-email-basic', $controller_path . '\authentications\VerifyEmailBasic@index')->name(
  'auth-verify-email-basic'
);
Route::get('/auth/verify-email-cover', $controller_path . '\authentications\VerifyEmailCover@index')->name(
  'auth-verify-email-cover'
);
Route::get('/auth/reset-password-basic', $controller_path . '\authentications\ResetPasswordBasic@index')->name(
  'auth-reset-password-basic'
);
Route::get('/auth/reset-password-cover', $controller_path . '\authentications\ResetPasswordCover@index')->name(
  'auth-reset-password-cover'
);
Route::get('/auth/forgot-password-basic', $controller_path . '\authentications\ForgotPasswordBasic@index')->name(
  'auth-reset-password-basic'
);
Route::get('/auth/forgot-password-cover', $controller_path . '\authentications\ForgotPasswordCover@index')->name(
  'auth-forgot-password-cover'
);
Route::get('/auth/two-steps-basic', $controller_path . '\authentications\TwoStepsBasic@index')->name(
  'auth-two-steps-basic'
);
Route::get('/auth/two-steps-cover', $controller_path . '\authentications\TwoStepsCover@index')->name(
  'auth-two-steps-cover'
);

// wizard example
Route::get('/wizard/ex-checkout', $controller_path . '\wizard_example\Checkout@index')->name('wizard-ex-checkout');
Route::get('/wizard/ex-property-listing', $controller_path . '\wizard_example\PropertyListing@index')->name(
  'wizard-ex-property-listing'
);
Route::get('/wizard/ex-create-deal', $controller_path . '\wizard_example\CreateDeal@index')->name(
  'wizard-ex-create-deal'
);

// modal
Route::get('/modal-examples', $controller_path . '\modal\ModalExample@index')->name('modal-examples');

// cards
Route::get('/cards/basic', $controller_path . '\cards\CardBasic@index')->name('cards-basic');
Route::get('/cards/advance', $controller_path . '\cards\CardAdvance@index')->name('cards-advance');
Route::get('/cards/statistics', $controller_path . '\cards\CardStatistics@index')->name('cards-statistics');
Route::get('/cards/analytics', $controller_path . '\cards\CardAnalytics@index')->name('cards-analytics');
Route::get('/cards/actions', $controller_path . '\cards\CardActions@index')->name('cards-actions');

// User Interface
Route::get('/ui/accordion', $controller_path . '\user_interface\Accordion@index')->name('ui-accordion');
Route::get('/ui/alerts', $controller_path . '\user_interface\Alerts@index')->name('ui-alerts');
Route::get('/ui/badges', $controller_path . '\user_interface\Badges@index')->name('ui-badges');
Route::get('/ui/buttons', $controller_path . '\user_interface\Buttons@index')->name('ui-buttons');
Route::get('/ui/carousel', $controller_path . '\user_interface\Carousel@index')->name('ui-carousel');
Route::get('/ui/collapse', $controller_path . '\user_interface\Collapse@index')->name('ui-collapse');
Route::get('/ui/dropdowns', $controller_path . '\user_interface\Dropdowns@index')->name('ui-dropdowns');
Route::get('/ui/footer', $controller_path . '\user_interface\Footer@index')->name('ui-footer');
Route::get('/ui/list-groups', $controller_path . '\user_interface\ListGroups@index')->name('ui-list-groups');
Route::get('/ui/modals', $controller_path . '\user_interface\Modals@index')->name('ui-modals');
Route::get('/ui/navbar', $controller_path . '\user_interface\Navbar@index')->name('ui-navbar');
Route::get('/ui/offcanvas', $controller_path . '\user_interface\Offcanvas@index')->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', $controller_path . '\user_interface\PaginationBreadcrumbs@index')->name(
  'ui-pagination-breadcrumbs'
);
Route::get('/ui/progress', $controller_path . '\user_interface\Progress@index')->name('ui-progress');
Route::get('/ui/spinners', $controller_path . '\user_interface\Spinners@index')->name('ui-spinners');
Route::get('/ui/tabs-pills', $controller_path . '\user_interface\TabsPills@index')->name('ui-tabs-pills');
Route::get('/ui/toasts', $controller_path . '\user_interface\Toasts@index')->name('ui-toasts');
Route::get('/ui/tooltips-popovers', $controller_path . '\user_interface\TooltipsPopovers@index')->name(
  'ui-tooltips-popovers'
);
Route::get('/ui/typography', $controller_path . '\user_interface\Typography@index')->name('ui-typography');

// extended ui
Route::get('/extended/ui-avatar', $controller_path . '\extended_ui\Avatar@index')->name('extended-ui-avatar');
Route::get('/extended/ui-blockui', $controller_path . '\extended_ui\BlockUI@index')->name('extended-ui-blockui');
Route::get('/extended/ui-drag-and-drop', $controller_path . '\extended_ui\DragAndDrop@index')->name(
  'extended-ui-drag-and-drop'
);
Route::get('/extended/ui-media-player', $controller_path . '\extended_ui\MediaPlayer@index')->name(
  'extended-ui-media-player'
);
Route::get('/extended/ui-perfect-scrollbar', $controller_path . '\extended_ui\PerfectScrollbar@index')->name(
  'extended-ui-perfect-scrollbar'
);
Route::get('/extended/ui-star-ratings', $controller_path . '\extended_ui\StarRatings@index')->name(
  'extended-ui-star-ratings'
);
Route::get('/extended/ui-sweetalert2', $controller_path . '\extended_ui\SweetAlert@index')->name(
  'extended-ui-sweetalert2'
);
Route::get('/extended/ui-text-divider', $controller_path . '\extended_ui\TextDivider@index')->name(
  'extended-ui-text-divider'
);
Route::get('/extended/ui-timeline-basic', $controller_path . '\extended_ui\TimelineBasic@index')->name(
  'extended-ui-timeline-basic'
);
Route::get('/extended/ui-timeline-fullscreen', $controller_path . '\extended_ui\TimelineFullscreen@index')->name(
  'extended-ui-timeline-fullscreen'
);
Route::get('/extended/ui-tour', $controller_path . '\extended_ui\Tour@index')->name('extended-ui-tour');
Route::get('/extended/ui-treeview', $controller_path . '\extended_ui\Treeview@index')->name('extended-ui-treeview');
Route::get('/extended/ui-misc', $controller_path . '\extended_ui\Misc@index')->name('extended-ui-misc');

// icons
Route::get('/icons/tabler', $controller_path . '\icons\Tabler@index')->name('icons-tabler');
Route::get('/icons/font-awesome', $controller_path . '\icons\FontAwesome@index')->name('icons-font-awesome');

// form elements
Route::get('/forms/basic-inputs', $controller_path . '\form_elements\BasicInput@index')->name('forms-basic-inputs');
Route::get('/forms/input-groups', $controller_path . '\form_elements\InputGroups@index')->name('forms-input-groups');
Route::get('/forms/custom-options', $controller_path . '\form_elements\CustomOptions@index')->name(
  'forms-custom-options'
);
Route::get('/forms/editors', $controller_path . '\form_elements\Editors@index')->name('forms-editors');
Route::get('/forms/file-upload', $controller_path . '\form_elements\FileUpload@index')->name('forms-file-upload');
Route::get('/forms/pickers', $controller_path . '\form_elements\Picker@index')->name('forms-pickers');
Route::get('/forms/selects', $controller_path . '\form_elements\Selects@index')->name('forms-selects');
Route::get('/forms/sliders', $controller_path . '\form_elements\Sliders@index')->name('forms-sliders');
Route::get('/forms/switches', $controller_path . '\form_elements\Switches@index')->name('forms-switches');
Route::get('/forms/extras', $controller_path . '\form_elements\Extras@index')->name('forms-extras');

// form layouts
Route::get('/form/layouts-vertical', $controller_path . '\form_layouts\VerticalForm@index')->name(
  'form-layouts-vertical'
);
Route::get('/form/layouts-horizontal', $controller_path . '\form_layouts\HorizontalForm@index')->name(
  'form-layouts-horizontal'
);
Route::get('/form/layouts-sticky', $controller_path . '\form_layouts\StickyActions@index')->name('form-layouts-sticky');

// form wizards
Route::get('/form/wizard-numbered', $controller_path . '\form_wizard\Numbered@index')->name('form-wizard-numbered');
Route::get('/form/wizard-icons', $controller_path . '\form_wizard\Icons@index')->name('form-wizard-icons');
Route::get('/form/validation', $controller_path . '\form_validation\Validation@index')->name('form-validation');

// tables
Route::get('/tables/basic', $controller_path . '\tables\Basic@index')->name('tables-basic');
Route::get('/tables/datatables-basic', $controller_path . '\tables\DatatableBasic@index')->name(
  'tables-datatables-basic'
);
Route::get('/tables/datatables-advanced', $controller_path . '\tables\DatatableAdvanced@index')->name(
  'tables-datatables-advanced'
);
Route::get('/tables/datatables-extensions', $controller_path . '\tables\DatatableExtensions@index')->name(
  'tables-datatables-extensions'
);

// charts
Route::get('/charts/apex', $controller_path . '\charts\ApexCharts@index')->name('charts-apex');
Route::get('/charts/chartjs', $controller_path . '\charts\ChartJs@index')->name('charts-chartjs');

// maps
Route::get('/maps/leaflet', $controller_path . '\maps\Leaflet@index')->name('maps-leaflet');

// laravel example
Route::get('/laravel/user-management', [UserManagement::class, 'UserManagement'])->name(
  'laravel-example-user-management'
);
Route::resource('/user-list', UserManagement::class);

// Audit Module Routes
Route::prefix('audit')
  ->name('audit.')
  ->group(function () {
    // Login Routes
    Route::get('/login', [App\Http\Controllers\AuditController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\AuditController::class, 'processLogin'])->name('login.post');
    Route::post('/logout', [App\Http\Controllers\AuditController::class, 'logout'])->name('logout');

    // Protected Audit Routes
    Route::middleware('audit.auth')->group(function () {
      Route::get('/dashboard', [App\Http\Controllers\AuditController::class, 'dashboard'])->name('dashboard');

      // Tapals
      Route::get('/tapals', [App\Http\Controllers\AuditController::class, 'tapalIndex'])->name('tapals.index');
      Route::get('/tapals/export', [App\Http\Controllers\AuditController::class, 'exportTapals'])->name(
        'tapals.export'
      );
      Route::get('/tapals/{id}', [App\Http\Controllers\AuditController::class, 'tapalShow'])->name('tapals.show');

      // Documents
      Route::get('/documents', [App\Http\Controllers\AuditController::class, 'documentIndex'])->name('documents.index');
      Route::get('/documents/export', [App\Http\Controllers\AuditController::class, 'exportDocuments'])->name(
        'documents.export'
      );
      Route::get('/documents/{id}', [App\Http\Controllers\AuditController::class, 'documentShow'])->name(
        'documents.show'
      );
      Route::get('/documents/attachment/{attachment}/download', [
        App\Http\Controllers\AuditController::class,
        'downloadDocumentAttachment',
      ])->name('documents.download');

      // Projects
      Route::get('/projects', [App\Http\Controllers\AuditController::class, 'projectIndex'])->name('projects.index');
      Route::get('/projects/export', [App\Http\Controllers\AuditController::class, 'exportProjects'])->name(
        'projects.export'
      );
      Route::get('/projects/{id}', [App\Http\Controllers\AuditController::class, 'projectShow'])->name('projects.show');
      Route::get('/projects/{project}/documents/{document}/download', [
        App\Http\Controllers\AuditController::class,
        'downloadProjectDocument',
      ])->name('projects.document.download');
    });
  });

// Salary Management Routes
Route::prefix('pms/salary-management')->name('pms.salary-management.')->middleware(['auth'])->group(function () {
    Route::match(['get', 'post'], '/select-employees/{project_id?}', [App\Http\Controllers\Project\SalaryManagementController::class, 'selectEmployees'])->name('select-employees');
    Route::match(['get', 'post'], '/calculation/{project_id?}', [App\Http\Controllers\Project\SalaryManagementController::class, 'calculation'])->name('calculation');
    Route::match(['get', 'post'], '/summary/{project_id?}', [App\Http\Controllers\Project\SalaryManagementController::class, 'summary'])->name('summary');
    Route::post('/store/{project_id?}', [App\Http\Controllers\Project\SalaryManagementController::class, 'store'])->name('store');
    Route::get('/salary-statement/{project_id?}', [App\Http\Controllers\Project\SalaryManagementController::class, 'statement'])->name('statement');
    Route::get('/{project_id?}', [App\Http\Controllers\Project\SalaryManagementController::class, 'index'])->name('index');
});
