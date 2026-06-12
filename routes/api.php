<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\Teacher\DashboardController;
use App\Http\Controllers\Api\Teacher\AnalyticsController;
use App\Http\Controllers\Api\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Api\Teacher\EarningsController;
use App\Http\Controllers\Api\Teacher\ResourceController as TeacherResourceController;
use App\Http\Controllers\Api\Teacher\AnnouncementController;
use App\Http\Controllers\Api\Teacher\AssignmentController;
use App\Http\Controllers\Api\Teacher\DiscussionController as TeacherDiscussionController;
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

    // Notifications available to any authenticated user
    Route::controller(NotificationController::class)->group(function () {
        Route::get('/my-notifications', 'myNotifications');
        Route::put('/notifications/{id}/read', 'markAsRead');
    });

    Route::controller(CourseController::class)->group(function () {
        Route::get('/courses', 'index');
        Route::get('/courses/{id}', 'show');
            // Teacher-prefixed CRUD routes (reuse existing CourseController logic)
            Route::post('/teacher/courses', 'store');
            Route::put('/teacher/courses/{id}', 'update');
            Route::delete('/teacher/courses/{id}', 'destroy');
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


    Route::get('/teacher/chat', [ChatController::class, 'teacherIndex']);
    Route::post('/teacher/chat', [ChatController::class, 'teacherStore']);


        // Compatibility: teacher-prefixed route for enrolled students
        Route::get('/teacher/courses/{id}/students', [CourseController::class, 'enrolledStudents']);

        Route::controller(LessonController::class)->group(function () {
            Route::post('/lessons', 'store');
            Route::put('/lessons/{id}', 'update');
            Route::delete('/lessons/{id}', 'destroy');
        });

        // Teacher-prefixed lessons listing for a course (frontend calls /api/teacher/courses/{id}/lessons)
        Route::get('/teacher/courses/{courseId}/lessons', [LessonController::class, 'courseLessons']);

        Route::controller(QuizController::class)->group(function () {
            Route::post('/quizzes', 'store');
        });

        // Teacher quiz management
        Route::get('/teacher/quizzes/{id}', [QuizController::class, 'show']);
        Route::put('/teacher/quizzes/{id}', [QuizController::class, 'update']);
        Route::delete('/teacher/quizzes/{id}', [QuizController::class, 'destroy']);

        // Teacher lesson quizzes (list and create)
        Route::get('/teacher/lessons/{lessonId}/quizzes', [QuizController::class, 'quizzesByLesson']);
        Route::post('/teacher/lessons/{lessonId}/quizzes', [QuizController::class, 'storeForLesson']);

        Route::controller(QuestionController::class)->group(function () {
            Route::post('/questions', 'store');
        });

        // Teacher quiz questions CRUD
        Route::get('/teacher/quizzes/{quizId}/questions', [QuestionController::class, 'teacherQuizQuestions']);
        Route::post('/teacher/quizzes/{quizId}/questions', [QuestionController::class, 'storeForQuiz']);
        Route::get('/teacher/questions/{id}', [QuestionController::class, 'teacherShow']);
        Route::put('/teacher/questions/{id}', [QuestionController::class, 'teacherUpdate']);
        Route::delete('/teacher/questions/{id}', [QuestionController::class, 'teacherDestroy']);

        // Teacher frontend expects these endpoints
        Route::get('/teacher/dashboard', [DashboardController::class, 'index']);
        Route::get('/teacher/courses', [CourseController::class, 'teacherIndex']);
        Route::get('/teacher/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'teacherReviews']);
        // Compatibility: course-specific analytics route expected by frontend
        Route::get('/teacher/courses/{id}/analytics', [\App\Http\Controllers\Api\Teacher\AnalyticsController::class, 'courseStats']);
            Route::get('/teacher/analytics', [AnalyticsController::class, 'index']);
            Route::get('/teacher/students', [TeacherStudentController::class, 'index']);
            Route::get('/teacher/students/{id}', [TeacherStudentController::class, 'show']);
            Route::get('/teacher/earnings', [EarningsController::class, 'index']);
            Route::get('/teacher/resources', [TeacherResourceController::class, 'index']);
            // Teacher resources CRUD
            Route::get('/teacher/courses/{courseId}/resources', [TeacherResourceController::class, 'index']);
            Route::post('/teacher/courses/{courseId}/resources', [TeacherResourceController::class, 'store']);
            Route::get('/teacher/resources/{id}', [TeacherResourceController::class, 'show']);
            Route::put('/teacher/resources/{id}', [TeacherResourceController::class, 'update']);
            Route::delete('/teacher/resources/{id}', [TeacherResourceController::class, 'destroy']);
            // Teacher announcements
            Route::get('/teacher/courses/{courseId}/announcements', [AnnouncementController::class, 'index']);
            Route::post('/teacher/courses/{courseId}/announcements', [AnnouncementController::class, 'store']);
            Route::get('/teacher/announcements/{id}', [AnnouncementController::class, 'show']);
            Route::put('/teacher/announcements/{id}', [AnnouncementController::class, 'update']);
            Route::delete('/teacher/announcements/{id}', [AnnouncementController::class, 'destroy']);
            // Teacher discussions
            Route::get('/teacher/courses/{courseId}/discussions', [TeacherDiscussionController::class, 'index']);
            Route::post('/teacher/courses/{courseId}/discussions', [TeacherDiscussionController::class, 'store']);
            Route::get('/teacher/discussions/{id}', [TeacherDiscussionController::class, 'show']);
            Route::put('/teacher/discussions/{id}', [TeacherDiscussionController::class, 'update']);
            Route::delete('/teacher/discussions/{id}', [TeacherDiscussionController::class, 'destroy']);
            // Teacher assignment management
            Route::get('/teacher/courses/{courseId}/assignments', [AssignmentController::class, 'index']);
            Route::post('/teacher/courses/{courseId}/assignments', [AssignmentController::class, 'store']);
            Route::get('/teacher/assignments/{id}', [AssignmentController::class, 'show']);
            Route::put('/teacher/assignments/{id}', [AssignmentController::class, 'update']);
            Route::delete('/teacher/assignments/{id}', [AssignmentController::class, 'destroy']);
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

        // Teacher chat endpoints
        // Route::get('/teacher/chat', [\App\Http\Controllers\Api\ChatController::class, 'teacherIndex']);
        // Route::post('/teacher/chat', [\App\Http\Controllers\Api\ChatController::class, 'teacherStore']);
    });
});
