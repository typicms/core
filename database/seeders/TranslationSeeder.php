<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            [
                'translation' => '{"fr":"En savoir plus","en":"More","nl":"Meer"}',
                'key' => 'More',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Aller au contenu","en":"Skip to content","nl":"Naar inhoud"}',
                'key' => 'Skip to content',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Chercher","en":"Search","nl":"Zoeken"}',
                'key' => 'Search',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Merci pour votre message. Nous vous répondrons dans les plus brefs délais.","en":"Thank you for your message. We will get back to you as soon as possible.","nl":"Bedankt voor uw bericht. We nemen zo spoedig mogelijk contact met u op."}',
                'key' => 'message when contact form is sent',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Merci pour votre inscription. Nous nous réjouissons de vous voir lors de l’événement.","en":"Thank you for your registration. We look forward to seeing you at the event.","nl":"Bedankt voor uw inschrijving. We kijken ernaar uit u op het evenement te zien."}',
                'key' => 'event registration message',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Ajouter au calendrier","en":"Add to calendar","nl":"Toevoegen aan Agenda"}',
                'key' => 'Add to calendar',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Toutes les actualités","nl":"Alle nieuws","en":"All news"}',
                'key' => 'All news',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Tous les événements","nl":"Alle evenementen","en":"All events"}',
                'key' => 'All events',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Partenaires","nl":"Partners","en":"Partners"}',
                'key' => 'Partners',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Dernières actualités","nl":"Laatste Nieuws","en":"Latest news"}',
                'key' => 'Latest news',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Prochains événements","nl":"Aankomende evenementen","en":"Upcoming events"}',
                'key' => 'Upcoming events',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Une erreur est survenue","nl":"Er is iets misgegaan","en":"Something went wrong"}',
                'key' => 'Something went wrong',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Une erreur inattendue s’est produite de notre côté. Veuillez réessayer dans un instant.","nl":"Er is een onverwachte fout opgetreden aan onze kant. Probeer het over een moment opnieuw.","en":"We hit an unexpected error on our end. Please try again in a moment."}',
                'key' => 'We hit an unexpected error on our end. Please try again in a moment.',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Vous n’avez pas l’autorisation d’accéder à cette page.","nl":"Je hebt geen toestemming om deze pagina te bekijken.","en":"You don’t have permission to access this page."}',
                'key' => 'You don’t have permission to access this page.',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"La page que vous recherchez n’existe pas ou a été déplacée.","nl":"De pagina die je zoekt bestaat niet of is verplaatst.","en":"The page you’re looking for doesn’t exist or has been moved."}',
                'key' => 'The page you’re looking for doesn’t exist or has been moved.',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Aller à la navigation","nl":"Open navigatie","en":"Open navigation"}',
                'key' => 'Open navigation',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Veuillez s’il vous plaît corriger les erreurs ci-dessous.","en":"Please correct the errors below.","nl":"Gelieve de onderstaande fouten te corrigeren."}',
                'key' => 'message on form error',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Vous devez vous connecter pour accéder à cette page.","nl":"Je moet inloggen om deze pagina te bekijken.","en":"You need to sign in to access this page."}',
                'key' => 'You need to sign in to access this page.',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Accès refusé","nl":"Toegang geweigerd","en":"Access denied"}',
                'key' => 'Access denied',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Votre session a expiré pour des raisons de sécurité. Veuillez actualiser la page et réessayer.","nl":"Je sessie is om veiligheidsredenen verlopen. Vernieuw de pagina en probeer het opnieuw.","en":"Your session has expired for security reasons. Please refresh the page and try again."}',
                'key' => 'Your session has expired for security reasons. Please refresh the page and try again.',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
            [
                'translation' => '{"fr":"Vous avez envoyé trop de requêtes en peu de temps. Veuillez patienter un instant avant de réessayer.","nl":"Je hebt in korte tijd te veel verzoeken verstuurd. Wacht even en probeer het opnieuw.","en":"You’ve made too many requests in a short time. Please wait a moment and try again."}',
                'key' => 'You’ve made too many requests in a short time. Please wait a moment and try again.',
                'created_at' => Date::now(),
                'updated_at' => Date::now(),
            ],
        ];

        DB::table('translations')->insert($translations);
    }
}
