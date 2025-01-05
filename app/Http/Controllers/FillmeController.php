<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FillmeController extends Controller
{
    public function getSentences(Request $request)
    {
        $length = $request->query('length');
        $category = $request->query('category');
        $limit = $request->query('limit');

        if ((int) $limit > 1) {
            $results = \App\Models\FillmeSentences::select('fillme_sentences.*', 'users.name')
                ->leftJoin('users', 'users.uuid', '=', 'fillme_sentences.user_id')
                ->where('length', '=', $length)
                ->where('category', '=', $category)
                ->inRandomOrder()  // Equivalent to NEWID() for random order
                ->limit($limit)
                ->get();

            return response()->json([
                'message' => 'Success',
                'data' => $results
            ]);
        } else {
            $result = \App\Models\FillmeSentences::select('fillme_sentences.*', 'users.name')
                ->leftJoin('users', 'users.uuid', '=', 'fillme_sentences.user_id')
                ->where('length', '=', $length)
                ->where('category', '=', $category)
                ->inRandomOrder()  // Equivalent to NEWID() for random order
                ->limit($limit)
                ->first();  // For a single result

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
            'user_id' => $request->attributes->get('accountDetail')['uuid'],
        ]);

        return response()->json([
            'message' => 'Success',
            'data' => $results
        ]);
    }
}
