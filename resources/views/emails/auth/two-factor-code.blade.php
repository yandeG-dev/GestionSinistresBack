<x-mail::message>
# Vérification de sécurité

Bonjour,

Voici votre code de vérification pour vous connecter à votre espace :

<x-mail::panel>
<div style="font-size: 28px; font-weight: 700; letter-spacing: 8px; text-align: center;">{{ $code }}</div>
</x-mail::panel>

Ce code est valable pendant 10 minutes. Si vous n'êtes pas à l'origine de cette tentative de connexion, nous vous recommandons de modifier votre mot de passe.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
