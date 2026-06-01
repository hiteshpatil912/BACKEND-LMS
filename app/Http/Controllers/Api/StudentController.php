<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProgressResource;
use App\Http\Resources\QuizResource;
use App\Http\Resources\WishlistResource;
use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\Discussion;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Quiz;
use App\Models\Resource;
use App\Models\WatchHistory;
use App\Models\Wishlist;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentController extends Controller
{
    use ApiResponse;

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $courses = $user->courses()->with('teacher')->latest()->get();
        $certificates = Certificate::where('user_id', $user->id)
            ->with('course')
            ->latest()
            ->take(5)
            ->get();
        $recentNotifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
        $continueLearning = [];
        $completedCourses = 0;

        foreach ($courses as $course) {
            $totalLessons = Lesson::where('course_id', $course->id)->count();
            $completedLessonIds = $user->completedLessons()
                ->where('lessons.course_id', $course->id)
                ->pluck('lessons.id');

            if ($totalLessons > 0 && $completedLessonIds->count() >= $totalLessons) {
                $completedCourses++;
            }

            $nextLesson = Lesson::where('course_id', $course->id)
                ->whereNotIn('id', $completedLessonIds)
                ->orderBy('lesson_order')
                ->first();

            $continueLearning[] = [
                'course' => new CourseResource($course),
                'next_lesson' => $nextLesson ? new LessonResource($nextLesson) : null,
            ];
        }

        return $this->successResponse([
            'total_courses' => $courses->count(),
            'completed_courses' => $completedCourses,
            'active_courses' => max($courses->count() - $completedCourses, 0),
            'certificates' => CertificateResource::collection($certificates),
            'recent_notifications' => NotificationResource::collection($recentNotifications),
            'continue_learning' => $continueLearning,
        ], 'Dashboard fetched successfully');
    }

    public function submissions(Request $request)
    {
        return $this->successResponse([
            'submissions' => [
                [
                    'id' => 1,
                    'title' => 'Laravel Assignment',
                    'course' => 'Laravel Mastery',
                    'status' => 'submitted',
                    'marks' => 85,
                    'submitted_at' => now()->toDateTimeString(),
                ],
                [
                    'id' => 2,
                    'title' => 'Vue.js Project',
                    'course' => 'Vue Advanced',
                    'status' => 'pending',
                    'marks' => null,
                    'submitted_at' => now()->toDateTimeString(),
                ],
            ],
        ], 'Submissions fetched successfully');
    }

    public function subscriptions()
    {
        return $this->successResponse([
            'subscriptions' => [
                [
                    'id' => 1,
                    'plan' => 'Premium',
                    'status' => 'active',
                    'price' => 999,
                ],
            ],
        ], 'Subscriptions fetched successfully');
    }

    public function storeSubscription(Request $request)
    {
        return $this->successResponse([
            'subscription' => [
                'id' => rand(1000, 9999),
                'plan' => 'Premium Plan',
                'type' => 'monthly',
                'status' => 'active',
                'renewsAt' => now()->addMonth()->toDateString(),
                'amount' => 29,
            ],
        ], 'Subscription Created Successfully');
    }

    public function watchHistory(Request $request)
    {
        $watchHistory = WatchHistory::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($watchHistory, JsonResource::collection($watchHistory), 'Watch history fetched successfully');
    }

    public function continueLearning(Request $request)
    {
        $user = $request->user();
        $continueLearning = [];

        foreach ($user->courses()->latest()->get() as $course) {
            $completedLessonIds = $user->completedLessons()
                ->where('lessons.course_id', $course->id)
                ->pluck('lessons.id');

            $nextLesson = Lesson::where('course_id', $course->id)
                ->whereNotIn('id', $completedLessonIds)
                ->orderBy('lesson_order')
                ->first();

            $continueLearning[] = [
                'course' => new CourseResource($course),
                'next_lesson' => $nextLesson ? new LessonResource($nextLesson) : null,
            ];
        }

        return $this->successResponse([
            'continue_learning' => $continueLearning,
        ], 'Continue learning fetched successfully');
    }

    public function enrolledCourses(Request $request)
    {
        $courses = $request->user()->courses()->with('teacher')->latest()->paginate(10);

        return $this->paginatedResponse($courses, CourseResource::collection($courses), 'Courses fetched successfully');
    }

    public function progress(Request $request, ?int $courseId = null)
    {
        $user = $request->user();
        $progressData = [];
        $coursesQuery = $user->courses();

        if ($courseId !== null) {
            $coursesQuery->where('courses.id', $courseId);
        }

        foreach ($coursesQuery->get() as $course) {
            $totalLessons = Lesson::where('course_id', $course->id)->count();
            $completedLessons = $user->completedLessons()
                ->where('lessons.course_id', $course->id)
                ->count();

            $progressData[] = [
                'course' => $course,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'progress_percentage' => $totalLessons > 0
                    ? round(($completedLessons / $totalLessons) * 100, 2)
                    : 0,
            ];
        }

        return $this->successResponse([
            'progress' => ProgressResource::collection($progressData),
        ], 'Progress fetched successfully');
    }

    public function wishlist(Request $request)
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->with('course')
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($wishlist, WishlistResource::collection($wishlist), 'Wishlist fetched successfully');
    }

    public function certificates(Request $request)
    {
        $certificates = Certificate::where('user_id', $request->user()->id)
            ->with('course')
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($certificates, CertificateResource::collection($certificates), 'Certificates fetched successfully');
    }

    public function notifications(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($notifications, NotificationResource::collection($notifications), 'Notifications fetched successfully');
    }

    public function quizzes(Request $request)
    {
        $courses = $request->user()->courses()->pluck('courses.id');
        $lessons = Lesson::whereIn('course_id', $courses)->pluck('id');

        $quizzes = Quiz::whereIn('lesson_id', $lessons)
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($quizzes, QuizResource::collection($quizzes), 'Quizzes fetched successfully');
    }

    public function resources(Request $request)
    {
        $courses = $request->user()->courses()->pluck('courses.id');

        $resources = Resource::whereIn('course_id', $courses)
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($resources, JsonResource::collection($resources), 'Resources fetched successfully');
    }

    public function assignments(Request $request)
    {
        $courses = $request->user()->courses()->pluck('courses.id');

        $assignments = Assignment::whereIn('course_id', $courses)
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($assignments, JsonResource::collection($assignments), 'Assignments fetched successfully');
    }

    public function discussions(Request $request)
    {
        $courses = $request->user()->courses()->pluck('courses.id');

        $discussions = Discussion::whereIn('course_id', $courses)
            ->with('user', 'course')
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($discussions, JsonResource::collection($discussions), 'Discussions fetched successfully');
    }

    public function payments(Request $request)
    {
        $payments = Order::where('user_id', $request->user()->id)
            ->with('course')
            ->latest()
            ->paginate(10);

        return $this->paginatedResponse($payments, OrderResource::collection($payments), 'Payments fetched successfully');
    }

    public function invoices(Request $request)
    {
        $invoices = [];

        $orders = Order::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        foreach ($orders as $order) {
            $invoices[] = [
                'invoice_no' => 'INV-' . $order->id,
                'payment_id' => $order->payment_id,
                'course_id' => $order->course_id,
                'amount' => $order->amount,
                'status' => $order->payment_status,
                'date' => $order->created_at,
            ];
        }

        return $this->paginatedResponse($orders, JsonResource::collection($invoices), 'Invoices fetched successfully');
    }

    public function lessons(Request $request)
    {
        $courses = $request->user()->courses()->pluck('courses.id');

        $lessons = Lesson::whereIn('course_id', $courses)
            ->orderBy('lesson_order')
            ->paginate(10);

        return $this->paginatedResponse($lessons, LessonResource::collection($lessons), 'Lessons fetched successfully');
    }

    public function categories()
    {
        return $this->successResponse([
            'categories' => [
                ['id' => 1, 'name' => 'Development', 'active' => true],
                ['id' => 2, 'name' => 'Design', 'active' => true],
                ['id' => 3, 'name' => 'Marketing', 'active' => true],
                ['id' => 4, 'name' => 'Business', 'active' => true],
            ],
        ], 'Categories fetched successfully');
    }
}
