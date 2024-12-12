<?php
session_start();

// Récupérer le contexte caché et les options
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$hiddenContext = $data['context'];
$optionsContext = $data['options'];

// Récupérer l'identifiant de l'utilisateur à partir du cookie
$userId = $_COOKIE['user_id'];
$contextFilePath = "user/$userId/context.txt";

// Lire le contexte actuel
$currentContext = file_get_contents($contextFilePath);

// Mettre à jour le contexte avec les nouvelles informations
$updatedContext = $currentContext;
$newLines = explode("\n", $hiddenContext);

foreach ($newLines as $line) {
    if (empty($line)) continue;
    if (strpos($line, ":") !== false) {
        $key = substr($line, 0, strpos($line, ":"));
        $value = substr($line, strpos($line, ":") + 1);
        $value = trim($value); // Enlever les espaces inutiles

        $found = false;
        $currentLines = explode("\n", $updatedContext);
        $updatedContext = "";

        foreach ($currentLines as $currentLine) {
            if (strpos($currentLine, ":") !== false) {
                $currentKey = substr($currentLine, 0, strpos($currentLine, ":"));
                if ($currentKey == $key) {
                   $updatedContext .= "$key: $value\n"; //mettre à jour la ligne avec la nouvelle valeur
                    $found = true;
                    continue;
                }
            }
             if(!empty($currentLine)) $updatedContext .= "$currentLine\n";
        }
        if (!$found) {
            $updatedContext .= "$key: $value\n"; //ajouter une nouvelle ligne si on ne l'a pas trouvé
        }

    }

}
// Ajout des nouvelles options
if(!empty($optionsContext))
{
    $optionsContext = trim($optionsContext);
    $currentLines = explode("\n", $updatedContext);
    $found = false;
    $updatedContext = "";
    foreach($currentLines as $currentLine)
    {
         if(strpos($currentLine, "options:") === 0)
         {
                $updatedContext .= "options: $optionsContext\n";
              $found = true;
               continue;
         }
          if(!empty($currentLine)) $updatedContext .= "$currentLine\n";
    }
    if(!$found) $updatedContext .= "options: $optionsContext\n";
}



// Stocker le contexte mis à jour dans le fichier
file_put_contents($contextFilePath, $updatedContext);
?>