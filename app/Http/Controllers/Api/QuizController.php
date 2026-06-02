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
        $result = $this->evaluateQuiz($quiz, $request->answers ?? []);

        return $this->successResponse($result, 'Quiz Submitted Successfully');
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
        // Try to get answers from the request (if provided via query or payload).
        $answers = $request->input('answers', null);

        $result = $this->evaluateQuiz($quiz, is_array($answers) ? $answers : null);

        return $this->successResponse($result, 'Quiz result fetched successfully');
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
        $result = $this->evaluateQuiz($quiz, $request->answers ?? []);

        return $this->successResponse($result, 'Quiz submitted successfully');
    }

    /**
     * Evaluate quiz and return standardized result structure.
     * If $answers is null, generate a fallback random percentage (preserves previous behavior).
     *
     * @param Quiz $quiz
     * @param array|null $answers
     * @return array
     */
    private function evaluateQuiz(Quiz $quiz, ?array $answers = null): array
    {
        $questions = Question::where('quiz_id', $quiz->id)->get();
        $total = $questions->count();

        if ($answers === null) {
            // Fallback: preserve previous random behavior but include full fields
            $percentage = rand(60, 100);
            $correct = $total > 0 ? (int) round(($percentage / 100) * $total) : 0;

            return [
                'quiz_id' => $quiz->id,
                'total_questions' => $total,
                'correct_answers' => $correct,
                'score_percentage' => $percentage,
                'status' => 'passed',
            ];
        }

        $correct = 0;
        foreach ($questions as $question) {
            if (isset($answers[$question->id]) && $answers[$question->id] === $question->correct_answer) {
                $correct++;
            }
        }

        $percentage = $total > 0 ? (int) round(($correct / $total) * 100) : 0;

        return [
            'quiz_id' => $quiz->id,
            'total_questions' => $total,
            'correct_answers' => $correct,
            'score_percentage' => $percentage,
            'status' => 'passed',
        ];
    }
}
