<?php

namespace Reinink;

use Closure;

class RememberQueryStrings
{
    public function handle($request, Closure $next)
    {
        if ($request->wantsJson()) {
            return $next($request);
        }

        if (empty($request->all())) {
            return $this->remembered($next, $request);
        }

        if ($request->get('remember') === 'no') {
            return $next($request);
        }

        if ($request->get('remember') === 'forget') {
            return $this->forget($next, $request);
        }

        return $this->remember($next, $request);
    }

    protected function remembered($next, $request)
    {
        $remembered = array_filter($request->session()->get('remember_query_strings.'.$this->getRouteIdentifier($request)) ?? []);

        if ($remembered) {
            $request->session()->reflash();

            return redirect(url($request->path()).'?'.http_build_query($remembered));
        }

        return $next($request);
    }

    protected function remember($next, $request)
    {
        $request->session()->put('remember_query_strings.'.$this->getRouteIdentifier($request), array_filter($request->all()));

        return $next($request);
    }

    protected function forget($next, $request)
    {
        $request->session()->remove('remember_query_strings.'.$this->getRouteIdentifier($request));

        return redirect(url($request->path()));
    }

    private function getRouteIdentifier($request)
    {
        $identifier = $request->route()->getName();

        foreach($request->route()->parameters as $k => $v )
        {
            $identifier .= '.'.$k.'['.$v.']';
        }

        return $identifier;
    }
}
