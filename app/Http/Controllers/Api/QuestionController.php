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
use Illuminate\Validation\Rule;

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

    /**
     * Teacher: list questions for a quiz (teacher ownership required)
     */
    public function teacherQuizQuestions(Request $request, int $quizId)
    {
        $quiz = Quiz::with('lesson')->find($quizId);

        if (! $quiz) {
            return $this->errorResponse('Quiz Not Found', 404);
        }

        if (! $this->ownsQuiz($request->user(), $quiz)) {
            return $this->forbiddenResponse();
        }

        $questions = Question::where('quiz_id', $quizId)->get();

        return $this->successResponse([
            'questions' => QuestionResource::collection($questions),
        ], 'Questions fetched successfully');
    }

    /**
     * Teacher: create question for a quiz (ownership required).
     */
    public function storeForQuiz(Request $request, int $quizId)
    {
       

        $quiz = Quiz::with('lesson')->find($quizId);

        if (! $quiz) {
            return $this->errorResponse('Quiz Not Found', 404);
        }

        if (! $this->ownsQuiz($request->user(), $quiz)) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'question' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => ['required', 'string', Rule::in(['option_a','option_b','option_c','option_d'])],
        ]);

        $question = Question::create([
            'quiz_id' => $quizId,
            'question' => $validated['question'],
            'option_1' => $validated['option_a'],
            'option_2' => $validated['option_b'],
            'option_3' => $validated['option_c'],
            'option_4' => $validated['option_d'],
            'correct_answer' => $validated['correct_answer'],
        ]);

        return $this->successResponse([
            'question' => new QuestionResource($question),
        ], 'Question Created Successfully');
    }

    /**
     * Teacher: show a single question (ownership required)
     */
    public function teacherShow(Request $request, int $id)
    {
        $question = Question::with('quiz.lesson')->find($id);

        if (! $question) {
            return $this->errorResponse('Question Not Found', 404);
        }

        if (! $this->ownsQuiz($request->user(), $question->quiz)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse([
            'question' => new QuestionResource($question),
        ], 'Question fetched successfully');
    }

    /**
     * Teacher: update a question (ownership required)
     */
    public function teacherUpdate(Request $request, int $id)
    {
        $question = Question::with('quiz.lesson')->find($id);

        if (! $question) {
            return $this->errorResponse('Question Not Found', 404);
        }

        if (! $this->ownsQuiz($request->user(), $question->quiz)) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'question' => 'sometimes|required|string',
            'option_a' => 'sometimes|required|string',
            'option_b' => 'sometimes|required|string',
            'option_c' => 'sometimes|required|string',
            'option_d' => 'sometimes|required|string',
            'correct_answer' => ['sometimes','required','string', Rule::in(['option_a','option_b','option_c','option_d'])],
        ]);

        $data = [];
        if (isset($validated['question'])) $data['question'] = $validated['question'];
        if (isset($validated['option_a'])) $data['option_1'] = $validated['option_a'];
        if (isset($validated['option_b'])) $data['option_2'] = $validated['option_b'];
        if (isset($validated['option_c'])) $data['option_3'] = $validated['option_c'];
        if (isset($validated['option_d'])) $data['option_4'] = $validated['option_d'];
        if (isset($validated['correct_answer'])) $data['correct_answer'] = $validated['correct_answer'];

        $question->update($data);

        return $this->successResponse([
            'question' => new QuestionResource($question),
        ], 'Question updated successfully');
    }

    /**
     * Teacher: delete a question (ownership required)
     */
    public function teacherDestroy(Request $request, int $id)
    {
        $question = Question::with('quiz.lesson')->find($id);

        if (! $question) {
            return $this->errorResponse('Question Not Found', 404);
        }

        if (! $this->ownsQuiz($request->user(), $question->quiz)) {
            return $this->forbiddenResponse();
        }

        $question->delete();

        return $this->successResponse(null, 'Question deleted successfully');
    }
}
