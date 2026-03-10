<?php
/**
 * Created by PhpStorm.
 * User: beatrizgomes
 * Date: 2019-05-09
 * Time: 16:57
 */

namespace Modules\EmailLayout\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\EmailLayout\Entities\EmailLayout;
use Modules\EmailLayout\Entities\EmailType;

class EmailLayoutRepository
{
    public function update(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $emailLayout = $this->showByEmailType($id);
                $new         = false;
                $input       = $request->all();

                $oldFile = null;
                if ($request->hasFile('attach')) {
                    $path = 'uploads/files/attachments/';
                    if (! File::exists($path)) {
                        File::makeDirectory($path, 0777, true);
                    }

                    if ($emailLayout) {
                        $oldFile = $emailLayout->attach;
                    }

                    $name = Str::uuid() . '.' . $request->file('attach')->getClientOriginalExtension();

                    if (! Storage::disk('public_uploads')->putFileAs('/attachments', $request->file('attach'), $name)) {
                        return false;
                    }
                    $input['attach']      = '/' . $path . $name;
                    $input['attach_name'] = $name;

                }

                if (! $emailLayout) {
                    $input['email_type_id'] = $id;
                    $emailLayout            = EmailLayout::create($input);
                    $new                    = true;
                } else {

                    $emailLayout = $emailLayout->update($input);
                    if ($oldFile) {
                        File::delete(base_path() . '/public/' . $oldFile);
                    }

                }

                Log::info('Email Layout successfully created.');
                Session::flash('success', 'Layout Email ' . ($new ? 'adicionado' : 'atualizado') . ' com sucesso!');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('warning', 'Não foi possível adicionar layout de email.');
        }
    }

    public function showByEmailTypeCode($code)
    {
        $emailType   = EmailType::where('code', $code)->first();
        $emailLayout = $emailType->emailLayout()->first();
        return $emailLayout;
    }

    public function showByEmailType($id)
    {
        $emailType   = EmailType::find($id);
        $emailLayout = $emailType->emailLayout->first();
        return $emailLayout;
    }
}
