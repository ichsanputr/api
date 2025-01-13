<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FillmeController extends Controller
{
    public function getSentences(Request $request)
    {
        $length = $request->query('length');
        $category = $request->query('category');
        $limit = $request->query('limit');
        $languange = $request->query('languange');
        $userid = $request->attributes->get('accountDetail')['uuid'];

        if ((int) $limit > 1) {
            // get list sentence
            $results = \App\Models\FillmeSentences::select(
                'fillme_sentences.*',
                'users.name',
                \DB::raw('EXISTS (
                    SELECT 1 
                    FROM fillme_results 
                    WHERE fillme_results.sentence_id = fillme_sentences.uuid 
                      AND fillme_results.user_id = "' . $userid . '"
                ) AS exists_in_results'),
                \DB::raw('(
                    SELECT COUNT(*)
                    FROM fillme_results
                    WHERE fillme_results.sentence_id = fillme_sentences.uuid
                ) AS played')
            )
            ->leftJoin('users', 'users.uuid', '=', 'fillme_sentences.user_id')
            ->where('length', '=', $length);

            if ($category != 5) {
                $results = $results->where('category', '=', $category);
            }

            $results = $results->limit($limit)->get();

            return response()->json([
                'message' => 'Success',
                'data' => $results
            ]);
        } else {
            // get one sentence
            $result = \App\Models\FillmeSentences::select('fillme_sentences.*', 'users.name')
                ->leftJoin('users', 'users.uuid', '=', 'fillme_sentences.user_id')
                ->where('length', '=', $length)
                ->where('languange', '=', $languange);

            if ($category != 5) {
                $result = $result->where('category', '=', $category);
            }

            $result = $result->inRandomOrder()
                ->limit($limit)
                ->first();

            return response()->json([
                'message' => 'Success',
                'data' => $result
            ]);
        }
    }

    public function addSentences(Request $request)
    {
        $results = \App\Models\FillmeSentences::create([
            'sentence' => $request->input('sentence'),
            'fill' => $request->input('fill'),
            'length' => $request->input('lengthCategory'),
            'category' => $request->input('kindCategory'),
            'languange' => $request->input('languange'),
            'user_id' => $request->attributes->get('accountDetail')['uuid'],
            'reported' => 0,
        ]);

        return response()->json([
            'message' => 'Success',
            'data' => $results
        ]);
    }

    public function addResult(Request $request)
    {
        $user_id = $request->attributes->get('accountDetail')['uuid'];
        $results = \App\Models\FillmeResults::updateOrCreate(
            ['user_id' => $user_id, 'sentence_id' => $request->input('sentence_id')],
            [
                'sentence_id' => $request->input('sentence_id'),
                'time' => $request->input('time'),
                'user_id' => $user_id
            ]
        );

        return response()->json([
            'message' => 'Success',
            'data' => $results
        ]);
    }

    public function addReport(Request $request)
    {
        $results = \App\Models\FillmeSentences::where(['uuid' => $request->input('sentence_id')])->increment('reported', 1);

        return response()->json([
            'message' => 'Success',
            'data' => $results
        ]);
    }

    public function getLeaderboard(Request $request)
    {
        $languange = $request->query('languange');

        $results = DB::select(
            DB::raw("
                SELECT 
                    u.uuid AS user_id,
                    u.name AS name,
                    COUNT(fr.uuid) AS total_sentence,
                    SUM(
                        CASE 
                            WHEN fs.length = 1 THEN GREATEST((30 - fr.time) * 3, 0)
                            WHEN fs.length = 2 THEN GREATEST((60 - fr.time) * 3, 0)
                            WHEN fs.length = 3 THEN GREATEST((90 - fr.time) * 3, 0)
                            ELSE 0
                        END
                    ) AS point,
                    SUM(fr.time) AS time
                FROM 
                    users u
                LEFT JOIN fillme_sentences fs ON u.uuid = fs.user_id
                LEFT JOIN fillme_results fr ON fs.uuid = fr.sentence_id
                WHERE 
                    fr.languange = $languange
                GROUP BY 
                    u.uuid, u.name
                ORDER BY 
                    point DESC
            ")
        );

        return response()->json([
            'message' => 'Success',
            'data' => $results
        ]);
    }
}
