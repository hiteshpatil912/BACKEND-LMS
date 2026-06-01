<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DiscussionController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::get('/categories', [StudentController::class, 'categories']);

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/profile', 'profile');
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
        Route::get('/roles', 'roles');
    });

    Route::controller(EnrollmentController::class)->group(function () {
        Route::get('/my-courses', 'myCourses');
    });

    Route::controller(LessonController::class)->group(function () {
        Route::get('/courses/{courseId}/lessons', 'courseLessons');
    });

    Route::controller(ReviewController::class)->group(function () {
        Route::get('/courses/{id}/reviews', 'courseReviews');
    });

    Route::controller(QuestionController::class)->group(function () {
        Route::get('/quizzes/{id}/questions', 'quizQuestions');
    });

    Route::controller(UploadController::class)->group(function () {
        Route::post('/upload-thumbnail', 'thumbnail');
        Route::post('/upload-pdf', 'pdf');
        Route::post('/upload-video', 'video');
    });

    Route::controller(CourseController::class)->group(function () {
        Route::get('/courses', 'index');
        Route::get('/courses/{id}', 'show');
    });

    Route::middleware('admin')->group(function () {
        Route::prefix('admin')->controller(AdminController::class)->group(function () {
            Route::get('/users', 'users');
            Route::get('/reports', 'reports');
        });

        Route::get('/admin/courses', [CourseController::class, 'index']);
        Route::get('/admin/lessons', [LessonController::class, 'index']);
        Route::get('/admin/quizzes', [QuizController::class, 'index']);
        Route::get('/admin/orders', [OrderController::class, 'index']);
        Route::get('/admin/reviews', [ReviewController::class, 'index']);
        Route::get('/admin/notifications', [NotificationController::class, 'index']);

        Route::controller(NotificationController::class)->group(function () {
            Route::post('/notifications', 'store');
        });
    });

    Route::middleware('teacher')->group(function () {
        Route::controller(CourseController::class)->group(function () {
            Route::post('/courses', 'store');
            Route::get('/courses/{id}/students', 'enrolledStudents');
            Route::put('/courses/{id}', 'update');
            Route::delete('/courses/{id}', 'destroy');
        });

        Route::controller(LessonController::class)->group(function () {
            Route::post('/lessons', 'store');
            Route::put('/lessons/{id}', 'update');
            Route::delete('/lessons/{id}', 'destroy');
        });

        Route::controller(QuizController::class)->group(function () {
            Route::post('/quizzes', 'store');
        });

        Route::controller(QuestionController::class)->group(function () {
            Route::post('/questions', 'store');
        });
    });

    Route::middleware('student')->group(function () {
        Route::controller(EnrollmentController::class)->group(function () {
            Route::post('/courses/{id}/enroll', 'enroll');
        });

        Route::controller(ProgressController::class)->group(function () {
            Route::post('/lessons/{id}/complete', 'completeLesson');
            Route::get('/courses/{id}/progress', 'courseProgress');
            Route::get('/courses/{id}/continue-learning', 'continueLearning');
        });

        Route::controller(QuizController::class)->group(function () {
            Route::get('/student/quizzes/{id}/attempt', 'studentAttempt');
            Route::post('/student/quizzes/{id}/submit', 'studentSubmit');
            Route::get('/student/quizzes/{id}/result', 'studentResult');
            Route::post('/quizzes/{id}/submit', 'submit');
        });

        Route::controller(CertificateController::class)->group(function () {
            Route::get('/student/certificates/{id}/download', 'studentDownload');
            Route::post('/student/certificates/generate', 'studentGenerate');
            Route::get('/student/certificates/{id}', 'studentShow');
            Route::post('/courses/{id}/generate-certificate', 'generateForCourse');
        });

        Route::controller(OrderController::class)->group(function () {
            Route::post('/student/payments/purchase-course', 'studentPurchaseCourse');
            Route::post('/courses/{id}/purchase', 'purchaseCourse');
            Route::get('/my-orders', 'myOrders');
        });

        Route::controller(ReviewController::class)->group(function () {
            Route::post('/courses/{id}/review', 'store');
        });

        Route::controller(WishlistController::class)->group(function () {
            Route::post('/courses/{id}/wishlist', 'store');
            Route::get('/my-wishlist', 'myWishlist');
        });

        Route::controller(NotificationController::class)->group(function () {
            Route::get('/my-notifications', 'myNotifications');
            Route::put('/notifications/{id}/read', 'markAsRead');
        });

        Route::prefix('student')->controller(StudentController::class)->group(function () {
            Route::get('/dashboard', 'dashboard');
            Route::get('/submissions', 'submissions');
            Route::get('/subscriptions', 'subscriptions');
            Route::post('/subscriptions', 'storeSubscription');
            Route::get('/watch-history', 'watchHistory');
            Route::get('/continue-learning', 'continueLearning');
            Route::get('/my-courses', 'enrolledCourses');
            Route::get('/enrolled-courses', 'enrolledCourses');
            Route::get('/progress', 'progress');
            Route::get('/progress/{courseId}', 'progress');
            Route::get('/wishlist', 'wishlist');
            Route::get('/certificates', 'certificates');
            Route::get('/notifications', 'notifications');
            Route::get('/quizzes', 'quizzes');
            Route::get('/resources', 'resources');
            Route::get('/assignments', 'assignments');
            Route::get('/discussions', 'discussions');
            Route::get('/payments', 'payments');
            Route::get('/invoices', 'invoices');
            Route::get('/lessons', 'lessons');
        });

        Route::prefix('student')->controller(DiscussionController::class)->group(function () {
            Route::post('/discussions', 'store');
            Route::post('/chat/discussions', 'chatDiscussion');
        });

        Route::prefix('student')->controller(ChatController::class)->group(function () {
            Route::get('/chat', 'index');
            Route::post('/chat', 'store');
        });
    });
});
