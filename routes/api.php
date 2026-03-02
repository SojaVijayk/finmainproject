<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([
    'prefix' => 'auth'
  ], function () {
      Route::post('login', [AuthController::class, 'login']);
      Route::post('register', [AuthController::class, 'register']);

      Route::group([
        'middleware' => 'auth:api'
      ], function () {
          Route::get('logout', [AuthController::class, 'logout']);
          Route::get('user', [AuthController::class, 'user']);

          Route::get('/project-categories/{category}/subcategories', function (App\Models\ProjectCategory $category) {
        return $category->subcategories;
    });

    // Get contacts for a client
    Route::get('/clients/{client}/contacts', function (App\Models\Client $client) {
        return $client->contacts;
    });

    // Get team members for a project
    Route::get('/projects/{project}/team-members', function (App\Models\PMS\Project $project) {
        return $project->teamMembers()->with('user')->get();
    });


      });
  });
