<?php

namespace Akyos\CanopeeSDK\Enum;

enum LogType: string
{
    case Notification = 'Notification';
    case Modification = 'Modification';
    case Creation = 'Création';
    case Suppression = 'Suppression';
    case Autre = 'Autre';
    case Email = 'Email';
    case SMS = 'SMS';
    case Erreur = 'Erreur';
    case Connexion = 'Connexion';
    case Workflow = 'Changement de statut';
}
