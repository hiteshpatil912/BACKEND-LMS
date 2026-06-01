<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesLmsContent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Quiz;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    use ApiResponse;
    use AuthorizesLmsContent;

    public function store(StoreQuestionRequest $request)
    {
        $quiz = Quiz::find($request->quiz_id);

        if (!$this->ownsQuiz($request->user(), $quiz)) {
            return $this->forbiddenResponse();
        }

        $question = Question::create([
            'quiz_id' => $request->quiz_id,
            'question' => $request->question,
            'option_1' => $request->option_1,
            'option_2' => $request->option_2,
            'option_3' => $request->option_3,
            'option_4' => $request->option_4,
            'correct_answer' => $request->correct_answer,
        ]);

        return $this->successResponse([
            'question' => new QuestionResource($question),
        ], 'Question Created Successfully');
    }

    public function quizQuestions(Request $request, int $id)
    {
        $quiz = Quiz::with('lesson')->find($id);

        if (!$quiz) {
            return $this->errorResponse('Quiz Not Found', 404);
        }

        $user = $request->user();

        if (
            !$this->ownsQuiz($user, $quiz)
            && !$this->isEnrolledInCourse($user, $quiz->lesson->course_id)
        ) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse([
            'questions' => QuestionResource::collection(Question::where('quiz_id', $id)->get()),
        ], 'Questions fetched successfully');
    }
}
