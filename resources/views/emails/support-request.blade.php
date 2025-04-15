@component('mail::message')
# Nova Solicitação de Suporte

Você recebeu uma nova mensagem de **suporte** vinda da plataforma **Estuday**.

---

**Nome:** {{ $name }}

**E-mail do Usuário:** {{ $email }}

**Assunto:** {{ $subject }}

**Mensagem:**

{{ $message }}

---

@component('mail::button', ['url' => config('app.url')])
Visualizar Plataforma
@endcomponent

Atenciosamente,  
Equipe Estuday
@endcomponent
