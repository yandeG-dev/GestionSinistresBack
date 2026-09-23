<x-mail::message>
# Bonjour {{ $user->prenom }} {{ $user->nom }},

Votre compte a été créé avec succès sur notre plateforme de gestion de sinistres.

Voici vos informations de connexion temporaires :

- **Email :** {{ $user->email }}
- **Mot de passe temporaire :** {{ $motDePasseTemporaire }}

> **Important :** Pour des raisons de sécurité, il vous sera demandé de modifier ce mot de passe dès votre première connexion.

<x-mail::button :url="env('APP_URL_FRONT', 'http://localhost:4200')">
Se connecter
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>