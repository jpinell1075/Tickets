<?php
namespace App\Http\Services;

use App\Models\Users\Templates\UserTemplate;
use App\Models\Users\Templates\UserTemplateFilter;
use Illuminate\Support\Facades\DB;

class UserTemplateServices
{
    public static function addUserModelFilter($request, UserTemplate $userTemplate)
    {
        DB::beginTransaction();

        try {
            if($request->comparison_type == "simple")
            {
                $filter = UserTemplateFilter::create([
                    'user_template_id' => $userTemplate->id,
                    'comparison_type' => $request->comparison_type,
                    'model' => $request->model,
                    'field' => $request->field,
                    'operator' => $request->operator,
                    'value' => $request->value,
                ]);

                DB::commit();
                return $filter;
            }

            if($request->comparison_type == "relations")
            {
                $filter = UserTemplateFilter::create([
                    'user_template_id' => $userTemplate->id,
                    'comparison_type' => $request->comparison_type,
                    'model' => $request->model,
                    'relation' => $request->relation,
                    'field' => $request->field,
                    'operator' => $request->operator,
                    'value' => $request->value,
                    'relation' => $request->relation,
                ]);

                DB::commit();
                return $filter;
            }

            if($request->comparison_type == "functions")
            {
                $filter = UserTemplateFilter::create([
                    'user_template_id' => $userTemplate->id,
                    'comparison_type' => $request->comparison_type,
                    'model' => $request->model,
                    'field' => $request->field,
                    'operator' => $request->operator,
                    'value' => $request->value,
                    'extra' => $request->extra,
                ]);

                DB::commit();
                return $filter;
            }

            if($request->comparison_type == "user_own")
            {
                $filter = UserTemplateFilter::create([
                    'user_template_id' => $userTemplate->id,
                    'comparison_type' => $request->comparison_type,
                    'model' => $request->model,
                    'extra' => $request->extra,
                ]);

                DB::commit();
                return $filter;
            }

            DB::rollBack();
            return null;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }
}