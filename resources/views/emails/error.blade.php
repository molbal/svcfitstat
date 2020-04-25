@component('mail::message')
# Hello,

{{$message}}

If you think the error was caused on our end, please open a ticket at the [GitHub repository](https://github.com/molbal/svcfitstat/issues).

Thanks,<br>
{{ config('app.name') }}
@endcomponent
