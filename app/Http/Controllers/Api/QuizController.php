<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\SubmitQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    use ApiResponse;
    use AuthorizesLmsContent;

    public function index()
    {
        $quizzes = Quiz::with('lesson.course.teacher')->latest()->paginate(10);

        return $this->paginatedResponse(
            $quizzes,
            QuizResource::collection($quizzes),
            'Quizzes fetched successfully'
        );
    }

    public function store(StoreQuizRequest $request)
    {
        $lesson = Lesson::find($request->lesson_id);

        if (!$this->ownsLesson($request->user(), $lesson)) {
            return $this->forbiddenResponse();
        }

        $quiz = Quiz::create([
            'lesson_id' => $request->lesson_id,
            'title' => $request->title,
            'description' => $request->description,
            'total_marks' => $request->total_marks,
        ]);

        return $this->successResponse([
            'quiz' => new QuizResource($quiz->load('lesson.course.teacher')),
        ], 'Quiz Created Successfully');
    }

public function studentAttempt(Request $request, int $id)
{
    $quiz = Quiz::with(['questions', 'lesson'])->find($id);

    if (!$quiz) {
        return $this->errorResponse('Quiz Not Found', 404);
    }

    if (!$this->isEnrolledInCourse($request->user(), $quiz->lesson->course_id)) {
        return $this->forbiddenResponse();
    }

    return $this->successResponse(new QuizResource($quiz), 'Quiz fetched successfully');
}
    public function studentSubmit(SubmitQuizRequest $request, int $id)
    {
        $quiz = Quiz::with('lesson')->find($id);

        if (!$quiz) {
            return $this->errorResponse('Quiz Not Found', 404);
        }

        if (!$this->isEnrolledInCourse($request->user(), $quiz->lesson->course_id)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse([
            'score' => rand(60, 100),
            'status' => 'passed',
        ], 'Quiz Submitted Successfully');
    }

    public function studentResult(Request $request, int $id)
    {
        $quiz = Quiz::with('lesson')->find($id);

        if (!$quiz) {
            return $this->errorResponse('Quiz Not Found', 404);
        }

        if (!$this->isEnrolledInCourse($request->user(), $quiz->lesson->course_id)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse([
            'quiz_id' => $quiz->id,
            'score' => rand(60, 100),
            'status' => 'passed',
        ], 'Quiz result fetched successfully');
    }

    public function submit(SubmitQuizRequest $request, int $id)
    {
        // dd($request->all());
        $quiz = Quiz::with('lesson')->find($id);

        if (!$quiz) {
            return $this->errorResponse('Quiz Not Found', 404);
        }

        if (!$this->isEnrolledInCourse($request->user(), $quiz->lesson->course_id)) {
            return $this->forbiddenResponse();
        }

        $questions = Question::where('quiz_id', $id)->get();
        $answers = $request->answers ?? [];
        $score = 0;

        foreach ($questions as $question) {
    //          dump([
    //     'question_id' => $question->id,
    //     'received' => $answers[$question->id] ?? null,
    //     'correct' => $question->correct_answer,
    //     'match' => ($answers[$question->id] ?? null) === $question->correct_answer,
    // ]);
            if (
                isset($answers[$question->id]) &&
                $answers[$question->id] === $question->correct_answer
            ) {
                $score++;
            }
        }

        return $this->successResponse([
            'quiz_id' => $id,
            'total_questions' => $questions->count(),
            'correct_answers' => $score,
            'score_percentage' => $questions->count() > 0
                ? round(($score / $questions->count()) * 100)
                : 0,
        ], 'Quiz submitted successfully');
    }
}
