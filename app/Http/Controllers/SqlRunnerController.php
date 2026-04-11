<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SqlRunnerController extends Controller
{
    public function sqlRunner(Request $request)
    {
        $query = $request->input('sql_query');
        $results = null;
        $error = null;
        $successMsg = null;

        if ($request->isMethod('post') && !empty(trim($query))) {
            try {
                if (preg_match('/^\s*(select|show|describe|explain)\b/i', trim($query))) {
                    $results = DB::select($query);
                    $results = json_decode(json_encode($results), true);
                } else {
                    DB::statement($query);
                    $successMsg = 'Query executada com sucesso. (Não há tabelas de retorno)';
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        return view('content.pages.master-sql-runner', compact('query', 'results', 'error', 'successMsg'));
    }
}
