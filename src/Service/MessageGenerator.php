<?php

namespace App\Service;

class MessageGenerator
{

    public function getHappyMessage() : string {

        $messages = [ 'Vous êtes excellent !', 'Tu es trop beau !', 'Tu es le meilleur dev du monde !', 'Erreur de la banque en votre faveur ! Vous recevez 10000 €'];
    
        $index = array_rand($messages);

        return $messages[$index];
    }
}