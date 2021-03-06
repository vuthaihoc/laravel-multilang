<?php

namespace Longman\LaravelMultiLang;

use Illuminate\Http\Request;
use Longman\LaravelMultiLang\Models\Text;

trait TextsTrait
{

    public function index(Request $request)
    {
        $options['lang'] = config('multilang.default_locale');

        if ($request->lang) {
            $options['lang'] = $request->lang;
        }

        if ($request->keyword) {
            $options['keyword'] = $request->keyword;
        }

        if ($request->scope) {
            $options['scope'] = $request->scope;
        }

        $texts = Text::where(function ($q) use ($options) {
            foreach ($options as $k => $v) {
                if ($k == 'keyword') {
                    $q->where(function ($query) use ($v) {
                        $query->where('key', 'LIKE', '%' . $v . '%')->orWhere('value', 'LIKE', '%' . $v . '%');
                    });
                } else {
                    $q->where($k, $v);
                }
            }
        })->orderBy('value', 'asc')->get();

        if (isset($request->search)) {
            $options['search'] = true;
        }

        $options['keyword'] = $request->keyword;

        $data['texts'] = $texts;
        $data['options'] = $options;

        return view($this->view, $data);
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'texts' => 'required|array',
        ]);

        $locales = array_keys(config('multilang.locales'));
        foreach ($request->texts as $lang => $items) {
            if (!in_array($lang, $locales)) {
                //to do must set errors
                continue;
            }
            foreach ($items as $key => $value) {
                Text::where('lang', $lang)
                    ->where('key', $key)
                    ->update(['value' => $value]);
            }
        }
        return redirect()->back();
    }
}
