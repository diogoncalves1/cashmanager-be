<?php
/**
 * Created by PhpStorm.
 * User: beatrizgomes
 * Date: 2019-05-09
 * Time: 16:24
 */

namespace Modules\EmailLayout\Repositories;


use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\EmailLayout\Entities\EmailType;
use Modules\EmailLayout\Entities\EmailTypeKeyword;

class EmailTypeRepository implements RepositoryInterface
{

    public function all()
    {
        $emailTypes = EmailType::all();

        return $emailTypes;
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $inputEmailType['name'] = $request->input('name');
                $inputEmailType['code'] = $request->input('code');
                $emailType = EmailType::create($inputEmailType);

                if ($request->keywords) {

                    $inputEmailTypeKeyword['email_type_id'] = $emailType->id;
                    foreach ($request->keywords as $key => $keyword) {
                        $inputEmailTypeKeyword['keyword'] = $keyword;
                        $inputEmailTypeKeyword['description'] = $request->descriptions[$key];
                        $emailTypeKeyword = EmailTypeKeyword::create($inputEmailTypeKeyword);
                    }

                }
            });

            Session::flash('success', 'Tipo de Email adicionado com Sucesso');
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('warning', 'Não foi possível adicionar tipo de email');

        }
    }

    public function update(Request $request, $id)
    {

        DB::transaction(function () use ($id, $request) {
            $emailType = $this->show($id);
            $inputEmailType['name'] = $request->input('name');
            $inputEmailType['code'] = $request->input('code');
            $emailType->name = $request->name;
            $emailType->code = $request->code;
            $emailType->update($inputEmailType);
            $emailType->keywords()->delete();
            if ($request->keywords) {

                $inputEmailTypeKeyword['email_type_id'] = $emailType->id;
                foreach ($request->keywords as $key => $keyword) {
                    $inputEmailTypeKeyword['keyword'] = $keyword;
                    $inputEmailTypeKeyword['description'] = $request->descriptions[$key];
                    $emailTypeKeyword = EmailTypeKeyword::create($inputEmailTypeKeyword);
                }

            }
        });

        Session::flash('success', 'Tipo de Email atualizado com Sucesso');

    }

    public function destroy($id)
    {
        $emailType = $this->show($id);
        if (!$emailType)
            return;
        try {
            $emailType->delete();
        } catch (\Exception $e) {
        }

        Session::flash('success', 'Tipo de Email removido com Sucesso');
    }

    public function show($id)
    {
        $emailType = EmailType::find($id);
        return $emailType;
    }

    public function showByCode($code)
    {
        $emailType = EmailType::where('code', $code)->first();

        return $emailType;
    }
}
