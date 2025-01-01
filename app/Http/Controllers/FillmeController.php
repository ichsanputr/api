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
            $results = \App\Models\FillmeSentences::select('fillme_sentences.*', 'users.username')
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
            $result = \App\Models\FillmeSentences::select('fillme_sentences.*', 'users.username')
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
}
